<?php
function saveToken() {
    $url = "http://202.72.235.218:8028/api/v1/auth/login";
    $headers = [
        "Accept: */*",
        "Accept-Language: en-US,en;q=0.9,bn;q=0.8",
        "Authorization: Bearer",
        "Connection: keep-alive",
        "Content-Type: application/json",
        "Origin: http://202.72.235.217:3028",
        "Referer: http://202.72.235.217:3028/",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36"
    ];

    $data = [
        "username" => "101045",
        "password" => "532688"
    ];

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("cURL error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    // Decode and save token
    $responseArray = json_decode($response, true);
    if (isset($responseArray['accessToken'])) {
        $tokenData = [
            "accessToken" => $responseArray['accessToken'],
            "retrieved_at" => date("Y-m-d H:i:s")
        ];
        file_put_contents("token.json", json_encode($tokenData, JSON_PRETTY_PRINT));
        return true;
    } else {
        error_log("Failed to retrieve token: " . $response);
        return false;
    }
}

// Call the function
if (saveToken()) {
    echo "Token saved successfully.";
} else {
    echo "Failed to save token.";
}
?>
