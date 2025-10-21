<?php
session_start();
header('Content-Type: application/json');

try {
    // Basic auth: allow logged-in users (bhw, midwife, or user)
    if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id']) && !isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    require_once __DIR__ . '/../../../vendor/autoload.php';
    $cloudinaryConfig = include __DIR__ . '/../../../assets/config/cloudinary.php';

    \Cloudinary\Configuration\Configuration::instance([
        'cloud' => [
            'cloud_name' => $cloudinaryConfig['cloud_name'],
            'api_key' => $cloudinaryConfig['api_key'],
            'api_secret' => $cloudinaryConfig['api_secret']
        ],
        'url' => ['secure' => $cloudinaryConfig['secure']]
    ]);

    $url = isset($_GET['url']) ? urldecode((string)$_GET['url']) : '';
    $publicIdParam = isset($_GET['public_id']) ? (string)$_GET['public_id'] : '';
    if ($url === '' && $publicIdParam === '') {
        throw new Exception('Provide either url or public_id');
    }

    $result = [
        'input' => [ 'url' => $url, 'public_id' => $publicIdParam ],
        'http_check' => null,
        'derived' => [ 'public_id' => null, 'resource_type_guess' => null ],
        'admin_raw' => null,
        'admin_image' => null
    ];

    // If URL provided, do a HEAD/GET check and derive public_id
    if ($url !== '') {
        $parsed = parse_url($url);
        $host = strtolower($parsed['host'] ?? '');
        if (strpos($host, 'res.cloudinary.com') === false) {
            throw new Exception('URL is not a Cloudinary host');
        }

        // HTTP HEAD check via cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $curlErr = curl_error($curl);
        curl_close($curl);

        $result['http_check'] = [ 'status' => $httpCode, 'content_type' => $contentType, 'error' => $curlErr ];

        // Derive public_id from URL: /{resource}/upload[/fl_attachment]/vNNN/<folder>/<public_id>.<ext>
        $path = $parsed['path'] ?? '';
        // Normalize to remove leading '/'
        $path = ltrim($path, '/');
        // Remove cloud name prefix if present: {cloud}/...
        $parts = explode('/', $path);
        // Expect: {cloud}/raw/upload/... or {cloud}/image/upload/...
        if (count($parts) >= 4) {
            // Drop first segment (cloud name)
            array_shift($parts);
        }
        // Now parts like: raw|image, upload, maybe fl_attachment, v123456, ...rest
        if (count($parts) >= 2) {
            $resourceType = $parts[0]; // raw | image | video
            $result['derived']['resource_type_guess'] = $resourceType;
            // Drop resource type and 'upload'
            array_shift($parts); // drop resource type
            if (isset($parts[0]) && $parts[0] === 'upload') { array_shift($parts); }
            if (isset($parts[0]) && $parts[0] === 'fl_attachment') { array_shift($parts); }
            if (isset($parts[0]) && preg_match('/^v\d+$/', $parts[0])) { array_shift($parts); }

            // Remainder should be folder + filename.ext
            $remainder = implode('/', $parts);
            // Remove query string if any
            $remainder = explode('?', $remainder)[0];
            // Strip extension
            $dotPos = strrpos($remainder, '.');
            if ($dotPos !== false) {
                $publicIdFromUrl = substr($remainder, 0, $dotPos);
            } else {
                $publicIdFromUrl = $remainder;
            }
            $result['derived']['public_id'] = $publicIdFromUrl;
        }
    }

    $publicId = $publicIdParam !== '' ? $publicIdParam : ($result['derived']['public_id'] ?? '');
    if ($publicId !== '') {
        // Query Admin API for raw then image
        try {
            $admin = new \Cloudinary\Api\Admin\AdminApi();
            $raw = $admin->asset($publicId, [ 'resource_type' => 'raw', 'type' => 'upload' ]);
            $result['admin_raw'] = [
                'found' => true,
                'bytes' => $raw['bytes'] ?? null,
                'format' => $raw['format'] ?? null,
                'secure_url' => $raw['secure_url'] ?? null,
                'created_at' => $raw['created_at'] ?? null
            ];
        } catch (Exception $e) {
            $result['admin_raw'] = [ 'found' => false, 'error' => $e->getMessage() ];
        }

        try {
            $admin = new \Cloudinary\Api\Admin\AdminApi();
            $img = $admin->asset($publicId, [ 'resource_type' => 'image', 'type' => 'upload' ]);
            $result['admin_image'] = [
                'found' => true,
                'bytes' => $img['bytes'] ?? null,
                'format' => $img['format'] ?? null,
                'secure_url' => $img['secure_url'] ?? null,
                'created_at' => $img['created_at'] ?? null
            ];
        } catch (Exception $e) {
            $result['admin_image'] = [ 'found' => false, 'error' => $e->getMessage() ];
        }
    }

    echo json_encode([ 'status' => 'success', 'data' => $result ]);
    exit();

} catch (Exception $e) {
    echo json_encode([ 'status' => 'error', 'message' => $e->getMessage() ]);
    exit();
}
?>


