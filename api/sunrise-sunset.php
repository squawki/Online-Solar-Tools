<?php

header("Content-Type: application/json");

// Function to get sunrise and sunset times using date_sun_info
function getSunriseSunset($latitude, $longitude, $date, $timezoneOffset = 0) {
    // Convert provided date to a timestamp
    $timestamp = strtotime($date->format('Y-m-d'));

    // Get sunrise, sunset, and other solar-related info in UTC
    $sunInfo = date_sun_info($timestamp, $latitude, $longitude);

    // Adjust for timezone offset
    $sunriseTimestamp = $sunInfo['sunrise'] + ($timezoneOffset * 3600);
    $sunsetTimestamp = $sunInfo['sunset'] + ($timezoneOffset * 3600);

    // Format times without 'Z' if offset is provided, with 'Z' if UTC
    $sunrise = $timezoneOffset === 0 
        ? gmdate("Y-m-d\TH:i:s\Z", $sunriseTimestamp)
        : gmdate("Y-m-d\TH:i:s", $sunriseTimestamp);
    
    $sunset = $timezoneOffset === 0 
        ? gmdate("Y-m-d\TH:i:s\Z", $sunsetTimestamp)
        : gmdate("Y-m-d\TH:i:s", $sunsetTimestamp);

    return [
        "sunrise" => $sunrise,
        "sunset" => $sunset
    ];
}

// Main code for receiving POST data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture and sanitize input data
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
    $dateTimeInput = isset($_POST['datetime']) ? $_POST['datetime'] : null;
    $timezoneOffset = isset($_POST['timezoneOffset']) ? intval($_POST['timezoneOffset']) : 0;

    // Validate latitude and longitude
    if ($latitude === null || $longitude === null ||
        !is_numeric($latitude) || !is_numeric($longitude) ||
        $latitude < -90 || $latitude > 90 ||
        $longitude < -180 || $longitude > 180) {
        
        // Set HTTP response code to 400 Bad Request and return error message
        http_response_code(400);
        echo json_encode(["error" => "Invalid latitude or longitude. Ensure both are provided and within valid ranges."]);
        exit;
    }

    // Validate timezoneOffset
    if (!is_numeric($timezoneOffset)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid timezone offset. It must be a valid integer."]);
        exit;
    }

    // Use server UTC date if no datetime is provided
    $date = $dateTimeInput ? new DateTime($dateTimeInput, new DateTimeZone("UTC")) : new DateTime("now", new DateTimeZone("UTC"));

    // Calculate sunrise and sunset times in UTC with optional timezone adjustment
    $sunTimes = getSunriseSunset($latitude, $longitude, $date, $timezoneOffset);

    // Return results
    echo json_encode($sunTimes);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Only POST requests are accepted"]);
}
?>
