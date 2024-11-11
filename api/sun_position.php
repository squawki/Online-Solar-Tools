<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve latitude, longitude, and datetime, or default to server UTC time
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $dateTimeInput = $_POST['datetime'] ?? null;

    // Validate latitude and longitude
    if (
        !isset($latitude) || !isset($longitude) || 
        !is_numeric($latitude) || !is_numeric($longitude) || 
        $latitude < -90 || $latitude > 90 || 
        $longitude < -180 || $longitude > 180
    ) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid latitude or longitude. Ensure both are provided and within valid ranges."]);
        exit;
    }

    // Use server UTC time if no datetime provided
    $dateTime = $dateTimeInput ? new DateTime($dateTimeInput, new DateTimeZone("UTC")) : new DateTime("now", new DateTimeZone("UTC"));

    // Precompute constants for solar calculation
    $eccentricity = 0.016708634;
    $obliquity = deg2rad(23.439292 - 0.0000004 * ($dateTime->format("U") / 86400.0 + 2440587.5 - 2451545.0));  // radians

    // Julian Date and Days Since J2000
    $julianDate = ($dateTime->format("U") / 86400.0) + 2440587.5;
    $daysSinceJ2000 = $julianDate - 2451545.0;

    // Simplify mean longitude and anomaly calculations
    $meanLongitude = fmod(280.46646 + 0.9856474 * $daysSinceJ2000, 360.0);
    $meanAnomaly = deg2rad(fmod(357.52911 + 0.98560028 * $daysSinceJ2000, 360.0));

    // Equation of center and true longitude
    $center = sin($meanAnomaly) * (1.914602 - 0.000014 * $daysSinceJ2000) + sin(2 * $meanAnomaly) * 0.019993 - sin(3 * $meanAnomaly) * 0.000101;
    $trueLongitude = fmod($meanLongitude + $center, 360.0);

    // Ecliptic longitude and declination
    $eclipticLongitude = deg2rad($trueLongitude);
    $declination = asin(sin($obliquity) * sin($eclipticLongitude));

    // Solar Time Calculation (without complex time zone adjustments)
    $solarTimeOffset = $longitude * 4 + 229.18 * (0.000075 + 0.001868 * cos($meanAnomaly) - 0.032077 * sin($meanAnomaly) - 0.014615 * cos(2 * $meanAnomaly) - 0.040849 * sin(2 * $meanAnomaly));
    $solarTime = ($dateTime->format("H") * 60 + $dateTime->format("i") + $solarTimeOffset) / 60;
    $hourAngle = deg2rad(($solarTime - 12) * 15);  // degrees

    // Altitude and Azimuth Calculations
    $latitudeRad = deg2rad($latitude);
    $altitude = asin(sin($latitudeRad) * sin($declination) + cos($latitudeRad) * cos($declination) * cos($hourAngle));
    $azimuth = atan2(-sin($hourAngle), (tan($declination) * cos($latitudeRad) - sin($latitudeRad) * cos($hourAngle)));

    // Convert radians to degrees and apply refraction if necessary
    $altitude = rad2deg($altitude);
    $azimuth = fmod(rad2deg($azimuth) + 360, 360);

    // Refraction correction (only for altitudes less than 10 degrees)
    if ($altitude < 10) {
        $altitude += (1.02 / tan(deg2rad($altitude + 10.3 / ($altitude + 5.11)))) / 60;
    }

    // Return final results in JSON
    echo json_encode([
        "altitude" => round($altitude, 2),
        "azimuth" => round($azimuth, 2)
    ]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Only POST requests are accepted"]);
}
?>
