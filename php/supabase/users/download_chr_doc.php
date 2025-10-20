<?php
/**
 * CHR Document Download Proxy
 * Downloads CHR documents from local storage and serves them to users
 * Uses local file storage to avoid Cloudinary download restrictions
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Unauthorized');
}

// Get the Cloudinary URL from query parameter
$cloudinaryUrl = $_GET['url'] ?? '';
if (empty($cloudinaryUrl)) {
    http_response_code(400);
    die('Missing URL parameter');
}

// Decode the URL in case it was encoded
$cloudinaryUrl = urldecode($cloudinaryUrl);

// Debug: Log the URL for troubleshooting
error_log("Download attempt for URL: " . $cloudinaryUrl);

// If Cloudinary/full URL, redirect directly (force attachment); else support legacy local path
try {
    $isRemote = preg_match('/^https?:\/\//i', $cloudinaryUrl) === 1;
    if ($isRemote) {
        // Only allow Cloudinary host to avoid open redirect
        $parsed = parse_url($cloudinaryUrl);
        $host = strtolower($parsed['host'] ?? '');
        if (strpos($host, 'res.cloudinary.com') === false) {
            http_response_code(400);
            die('Invalid remote host');
        }

        // Try generating a signed private download URL via Cloudinary Admin API
        try {
            require_once __DIR__ . '/../../../vendor/autoload.php';
            $cloudinaryConfig = include __DIR__ . '/../../../assets/config/cloudinary.php';

            // Derive public_id and format from URL path
            $path = ltrim((string)($parsed['path'] ?? ''), '/');
            $segments = explode('/', $path);
            if (count($segments) > 0 && $segments[0] === $cloudinaryConfig['cloud_name']) { array_shift($segments); }
            $resourceType = (count($segments) > 0) ? $segments[0] : 'image';
            if (!in_array($resourceType, ['image','raw','video'], true)) { $resourceType = 'image'; }
            if (isset($segments[1]) && $segments[1] === 'upload') { array_splice($segments, 0, 2); } else { array_shift($segments); }
            if (isset($segments[0]) && $segments[0] === 'fl_attachment') { array_shift($segments); }
            if (isset($segments[0]) && preg_match('/^v\d+$/', $segments[0])) { array_shift($segments); }
            $rest = implode('/', $segments);
            $rest = explode('?', $rest)[0];
            $dot = strrpos($rest, '.');
            $format = $dot !== false ? substr($rest, $dot + 1) : 'pdf';
            $publicId = $dot !== false ? substr($rest, 0, $dot) : $rest;

            // Debug: log parsed values
            error_log('[DL] resourceType=' . $resourceType . ' publicId=' . $publicId . ' format=' . $format);

            // POST to Admin API /download to get a signed URL
            $apiUrl = 'https://api.cloudinary.com/v1_1/' . rawurlencode($cloudinaryConfig['cloud_name']) . '/' . $resourceType . '/download';
            $postFields = http_build_query([
                'public_id' => $publicId,
                'format' => $format,
                'type' => 'upload',
                'attachment' => 'true'
            ]);
            $ch2 = curl_init();
            curl_setopt($ch2, CURLOPT_URL, $apiUrl);
            curl_setopt($ch2, CURLOPT_POST, true);
            curl_setopt($ch2, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_USERPWD, $cloudinaryConfig['api_key'] . ':' . $cloudinaryConfig['api_secret']);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 15);
            $resp = curl_exec($ch2);
            $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            $err2 = curl_error($ch2);
            curl_close($ch2);
            error_log('[DL] /download http=' . $httpCode2 . ' resp=' . substr((string)$resp, 0, 300));
            if ($resp !== false && $httpCode2 < 400) {
                $json = json_decode($resp, true);
                if (isset($json['url']) && $json['url']) {
                    error_log('[DL] redirect signed=' . $json['url']);
                    header('Location: ' . $json['url']);
                    exit();
                }
            }
        } catch (Exception $e) { /* fall through */ }

        // Fallback: Fetch bytes from Cloudinary via cURL (no transformations) and stream as attachment
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cloudinaryUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ebakunado-downloader');
        $fileContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($fileContent === false || $httpCode >= 400) {
            error_log('[DL] curl HTTP=' . $httpCode . ' error=' . ($curlErr ?: '')); 
            http_response_code(502);
            die('Download failed: Unable to fetch from Cloudinary (HTTP ' . $httpCode . '). ' . ($curlErr ?: ''));
        }

        $filename = 'CHR_Document_' . date('Y-m-d_H-i-s') . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($fileContent));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        echo $fileContent;
        exit();
    } else {
        if (strpos($cloudinaryUrl, '/ebakunado/') !== 0) {
            http_response_code(400);
            die('Invalid URL - must be Cloudinary https URL or /ebakunado/ local path');
        }
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $cloudinaryUrl;
        if (!file_exists($localPath)) {
            throw new Exception('Local file not found: ' . $localPath);
        }
        $fileContent = file_get_contents($localPath);
        if ($fileContent === false) {
            throw new Exception('Failed to read local file');
        }
        
        // Generate filename
        $filename = 'CHR_Document_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($fileContent));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        echo $fileContent;
    }

} catch (Exception $e) {
    http_response_code(500);
    die('Download failed: ' . $e->getMessage());
}
?>
