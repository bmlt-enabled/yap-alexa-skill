<?php
const days_of_the_week = [1 => "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

class Coordinates {
    public $location;
    public $latitude;
    public $longitude;
}

function getCoordinatesForAddress($address) {
    $coordinates = new Coordinates();
    if (strlen($address) > 0) {
        $map_details_response = get("https://maps.googleapis.com/maps/api/geocode/json?key="
            . $GLOBALS['google_maps_api_key']
            . "&address="
            . urlencode($address)
            . "&components=" . urlencode($GLOBALS['location_lookup_bias']));
        $map_details = json_decode($map_details_response);
        if (count($map_details->results) > 0) {
            $coordinates->location  = $map_details->results[0]->formatted_address;
            $geometry               = $map_details->results[0]->geometry->location;
            $coordinates->latitude  = $geometry->lat;
            $coordinates->longitude = $geometry->lng;
        }
    }
    return $coordinates;
}

function getTimeZoneForCoordinates($latitude, $longitude) {
    $time_zone = get("https://maps.googleapis.com/maps/api/timezone/json?key=" . $GLOBALS['google_maps_api_key'] . "&location=" . $latitude . "," . $longitude . "&timestamp=" . time());
    return json_decode($time_zone);
}


function setTimeZoneForLatitudeAndLongitude($latitude, $longitude) {
    $time_zone_results = getTimeZoneForCoordinates($latitude, $longitude);
    date_default_timezone_set($time_zone_results->timeZoneId);
}

function getMeetings($location) {
    $speechResponse = "";
    $coordinates = getCoordinatesForAddress($location);
    $max_results = 5;
    setTimeZoneForLatitudeAndLongitude($coordinates->latitude, $coordinates->longitude);
    $graced_date_time = (new DateTime())->modify(sprintf("-%s minutes", "15"));
    $today = $graced_date_time->format("w") + 1;
    $tomorrow = $graced_date_time->modify("+24 hours")->format("w") + 1;
    $get_meetings_from_yap = get(sprintf($GLOBALS['yap_api_server'] . "/api/getMeetings.php?latitude=%s&longitude=%s&results_count=%s&today=%s&tomorrow=%s",
        $coordinates->latitude, $coordinates->longitude, $max_results, $today, $tomorrow));
    $meetings = json_decode($get_meetings_from_yap)->filteredList;
    $x = 1;
    foreach ($meetings as $meeting) {
        $municipality = $meeting->location_municipality !== "" ? " , " . $meeting->location_municipality : "";
        $province = $meeting->location_province !== "" ? " , " . $meeting->location_province : "";

        $response = sprintf("Result Number %s. %s. Starts at %s %s. %s %s %s...",
            strval($x),
            $meeting->meeting_name,
            days_of_the_week[intval($meeting->weekday_tinyint)],
            (new DateTime($meeting->start_time))->format('g:i A'),
            $meeting->location_street,
            $municipality,
            $province
        );

        $speechResponse = $speechResponse . $response;

        if ($x === $max_results) {
            break;
        }
        $x++;
    }

    return $speechResponse;
}

function get($url) {
    error_log($url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +yap-alexaskill' );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $errorno = curl_errno($ch);
    curl_close($ch);

    if ($errorno > 0) {
        throw new Exception(curl_strerror($errorno));
    }

    return $data;
}

