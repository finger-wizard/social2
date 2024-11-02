<?php
function time_elapsed_string($datetime, $full = false)
{
    date_default_timezone_set('Asia/Kolkata');
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',

    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'now';
}

function formatDateToEnglish($date)
{
    $day = date("d", strtotime($date));
    $month = date("m", strtotime($date)) - 1;
    $year = date("Y", strtotime($date));

    $monthEN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    return "$day $monthEN[$month] $year";
}
?>