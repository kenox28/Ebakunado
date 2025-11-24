<?php
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

header('Content-Type: application/json');

try {
    $consents = supabaseSelect('user_privacy_consents', '*', [], 'agreed_date.desc');

    if ($consents === false) {
        echo json_encode([
            'status' => 'failed',
            'message' => 'Unable to fetch privacy consents at this time.'
        ]);
        exit();
    }

    echo json_encode([
        'status' => 'success',
        'data' => $consents
    ]);
} catch (Exception $e) {
    error_log('Failed to load privacy consents: ' . $e->getMessage());
    echo json_encode([
        'status' => 'failed',
        'message' => 'An unexpected error occurred while loading consents.'
        ]);
}

