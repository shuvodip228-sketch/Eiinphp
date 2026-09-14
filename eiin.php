<?php 
if (isset($_GET['eiin'])) {
    $eiin = $_GET['eiin'];

    // Set API URL for the first request
    $firstApiUrl = "http://202.72.235.218:8082/api/v1/institute/list?eiinNo=" . $eiin;

    // Load the access token from token.json
    $tokenJson = file_get_contents('token.json');
    $tokenData = json_decode($tokenJson, true);
    if (isset($tokenData['accessToken'])) {
        $authToken = $tokenData['accessToken'];
    } else {
        die(json_encode([
            "status" => "error",
            "message" => "Token not found in token.json"
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // Initialize cURL for the first API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firstApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $authToken"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Execute first API request
    $response1 = curl_exec($ch);
    if (curl_errno($ch)) {
        die(json_encode([
            "status" => "error",
            "message" => "First API Error: " . curl_error($ch)
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // Decode the first API response
    $response1Array = json_decode($response1, true);
    if (isset($response1Array['data'][0]['id']) && isset($response1Array['data'][0]['esurveyId'])) {
        $instituteId = $response1Array['data'][0]['id'];
        $eSurveyId = $response1Array['data'][0]['esurveyId'];

        // Set API URL for the second request
        $secondApiUrl = "http://202.72.235.218:8028/api/v1/employee/list?page=1&size=100&eSurveyId=$eSurveyId&instituteId=$instituteId";

        // Initialize cURL for the second API request
        curl_setopt($ch, CURLOPT_URL, $secondApiUrl);

        // Execute second API request
        $response2 = curl_exec($ch);
        if (curl_errno($ch)) {
            die(json_encode([
                "status" => "error",
                "message" => "Second API Error: " . curl_error($ch)
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // Decode the second API response
        $response2Array = json_decode($response2, true);

        // Extract required data
        $filteredData = array_map(function ($item) {
            return [
                "name" => $item['generalInformation']['employeeNameBn'] ?? null,
                 "email" => $item['generalInformation']['email'] ?? null,
                "gender" => $item['generalInformation']['gender'] ?? null,
                "designation" => $item['recruitmentInformation']['designationName'] ?? null,
                "nid" => $item['generalInformation']['nid'] ?? null,
                "dob" => $item['generalInformation']['dateOfBirth'] ?? null,
                "tin" => $item['recruitmentInformation']['tinno'] ?? null,
                "number" => $item['generalInformation']['mobileNumber'] ?? null,
            ];
        }, $response2Array['data'] ?? []);

        // Output filtered response
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode([
            "status" => "success",
            "data" => $filteredData,
            "Owner" => "https://t.me/Police_Your_Dad"
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        // If no data found in the first API
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode([
            "status" => "error",
            "message" => "Invalid EIIN number or no data found",
            "Owner" => "https://t.me/Police_Your_Dad"
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    curl_close($ch);
} else {
    // If EIIN number is not provided
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "status" => "error",
        "message" => "Please provide an EIIN number",
        "Owner" => "https://t.me/Police_Your_Dad"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
