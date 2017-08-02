<?php
##
##		Extracts transferred stats from log files
##
## 		Pass arguments from command line like this
##		php calculate_transferred.php file=Logs/2017-07-01/20-25-f6c28f5/site-anchorhost-dropbox.txt
##

parse_str(implode('&', array_slice($argv, 1)), $_GET);

if ($_GET && $_GET['file']) {
	$file = $_GET['file'];
} else {
	$file = "~/Logs/anchor_dropbox_log_overall.txt";
}

function secs_to_str($duration) {
    $periods = array(
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    );

    $parts = array();

    foreach ($periods as $name => $dur) {
        $div = floor($duration / $dur);

        if ($div == 0)
            continue;
        else
            if ($div == 1)
                $parts[] = $div . " " . $name;
            else
                $parts[] = $div . " " . $name . "s";
        $duration %= $dur;
    }

    $last = array_pop($parts);

    if (empty($parts))
        return $last;
    else
        return join(', ', $parts) . " and " . $last;
}

if (file_exists($file)) {
$file = file_get_contents($file);
	// Bytes
	$pattern = '/(\d.*)(?= Bytes )/';
	preg_match_all($pattern, $file, $matches);
	$total_bytes = array_sum($matches[0]);

	// KBs
	$pattern = '/(\d.*)(?= kBytes )/';
	preg_match_all($pattern, $file, $matches);
	$total_kbytes = array_sum($matches[0]);

	// MBs
	$pattern = '/(\d.*)(?= MBytes )/';
	preg_match_all($pattern, $file, $matches);
	$total_mbytes = array_sum($matches[0]);

	// GBs
	$pattern = '/(\d.*)(?= GBytes )/';
	preg_match_all($pattern, $file, $matches);
	$total_gbytes = array_sum($matches[0]);

	// Add it all up
	$total_gb = round($total_bytes / 1024 / 1024 / 1024, 2) + round($total_kbytes / 1024 / 1024, 2) + round($total_mbytes / 1024, 2) + round($total_gbytes, 2);

	// Errors
	$pattern = '/(\d.*)(?=\sChecks)/';
	preg_match_all($pattern, $file, $matches);
	$total_errors = array_sum($matches[0]);

	// Checks
	$pattern = '/(\d.*)(?=\sTransferred)/';
	preg_match_all($pattern, $file, $matches);
	$total_checks = array_sum($matches[0]);

	// Transferred
	$pattern = '/(\d.*)(?=\sElapsed time)/';
	preg_match_all($pattern, $file, $matches);
	$total_transferred = array_sum($matches[0]);

	// Elapsed time
	$pattern = '/(?:Elapsed time:\s+)(\d.*)/';
	preg_match_all($pattern, $file, $matches);
	$elapsed_time = $matches[1];

	$total_time_in_seconds = 0;

	foreach($elapsed_time as $time) {

		// Search for hours
		if (strpos($time, 'h') !== false) {
			$pattern = '/(.+)(?:h)(.+)(?:m)(.+)(?:s)/';
			preg_match_all($pattern, $time, $matches);
			$hours = $matches[1][0] * 60 * 60;
			$minutes = $matches[2][0] * 60;
			$seconds = $matches[3][0] + $hours + $minutes;
		// Search for minutes
		} elseif (strpos($time, 'm') !== false) {
			$pattern = '/(.+)(?:m)(.+)(?:s)/';
			preg_match_all($pattern, $time, $matches);
			$minutes = $matches[1][0] * 60;
			$seconds = $matches[2][0] + $minutes;
		// Search for seconds
		} elseif (strpos($time, 's') !== false) {
			$pattern = '/(.+)(?:s)/';
			preg_match_all($pattern, $time, $matches);
			$seconds = $matches[1][0];
		}
		$total_time_in_seconds = $total_time_in_seconds + $seconds;
	}

	$total_time = secs_to_str($total_time_in_seconds);

	// return GBs transferred
	echo $total_gb ." GB  - " . $total_errors . " errors - " . $total_checks . " checks - $total_transferred transferred - $total_time";
}
