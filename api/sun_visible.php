<?php
header("Content-Type: application/json");

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve latitude, longitude, and optional datetime from POST request
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $dateTimeInput = $_POST['datetime'] ?? null;

    // Validate latitude and longitude
    if ($latitude === null || $longitude === null || !is_numeric($latitude) || !is_numeric($longitude) ||
        $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        
        // Set HTTP response code to 400 Bad Request
        http_response_code(400);
        echo json_encode(["error" => "Invalid latitude or longitude. Ensure both are provided and within the valid ranges."]);
        exit;
    }

    // Use provided datetime or default to server UTC time
    $dateTime = $dateTimeInput ? new DateTime($dateTimeInput, new DateTimeZone("UTC")) : new DateTime("now", new DateTimeZone("UTC"));
    $timestamp = $dateTime->getTimestamp();

    // Get sun information for the given timestamp, latitude, and longitude
    $sunInfo = date_sun_info($timestamp, $latitude, $longitude);

    // Determine if the current time is between sunrise and sunset
    $isVisible = ($timestamp >= $sunInfo['sunrise'] && $timestamp <= $sunInfo['sunset']) ? 1 : 0;

    echo json_encode(["visible" => $isVisible]);

} else {
    // If not POST, send 400 Bad Request
    http_response_code(400);
    echo json_encode(["error" => "Only POST requests are accepted"]);
}
