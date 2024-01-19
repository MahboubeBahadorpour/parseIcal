<?php
## PHP Simple ParseIcal from URL
## https://github.com/hsa599/parseIcal

function parseIcal($icalUrl) {
    $icalData = getCachedCurl($icalUrl);
    $events = array();

    if ($icalData) {
        $lines = explode("\n", $icalData);

        $event = array();
        foreach ($lines as $line) {
            if (strpos($line, 'BEGIN:VEVENT') !== false) {
                $event = array();
            } elseif (strpos($line, 'END:VEVENT') !== false) {
                $events[] = $event;
            } else {
                $parts = explode(':', $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $event[$key] = $value;
                }
            }
        }
    }

    return $events;
}


function getCachedCurl($url) {

	$cacheFile = "curl" . md5($url) . ".cache";
    $cacheTime = 1800; // 30 minutes

    // Check if cached version exists and is still valid
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        return file_get_contents($cacheFile);
    }

    // Create a cURL handle
    $ch = curl_init();

    // Set the URL and other options
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0');

	$output = curl_exec($ch);
	
	$error = curl_error($ch);
	
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if ($output === false) {
		
		echo "cURL Error: " . $error;
		exit();
	} 
	
	$length = strlen($output);

    // Close cURL handle
    curl_close($ch);
	
	if ($http_status != '200' || curl_error($ch)) {
	
	$err_log = "CachedCurl.log";
	file_put_contents($err_log, "URL:".$url. " code: ".$http_status." e: ".curl_error($ch) . PHP_EOL, FILE_APPEND);
	die("Get info Error from: ".$url." EE: ".$http_status);
	}

	file_put_contents($cacheFile, $output);

    return $output;
}

$ical = parseIcal($icalUrl);

print_r($ical);