<?php 

class Calendar {
    public static function google($eventName, $eventDate) {
        $eventDate = date('Ymd',strtotime($eventDate));
// build the url
        $url = 'http://www.google.com/calendar/event?action=TEMPLATE';
        $url .= '&text=' . rawurlencode($eventName);
        $url .= '&dates=' . $eventDate . '/' . $eventDate;
        return $url;
    }

    public static function outlook($eventName, $eventDate) {
        $eventName = rawurldecode($eventName);

        $eventDate = date('Ymd',strtotime($eventDate));

        $url = "https://outlook.live.com/owa/?bO=1#path=%2fcalendar%2faction%2fcompose&startdt={$eventDate}&enddt={$eventDate}&subject={$eventName}&allday=true";

        return $url;
    }
}