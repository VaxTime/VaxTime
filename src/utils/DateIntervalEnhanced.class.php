<?php 

class DateIntervalEnhanced extends DateInterval {

    const MINUTE = 60;
    const HOUR = 3600;
    const DAY = 86400;
    const WEEK = 604800;
    const MONTH = 2629800;
    const YEAR = 31536000;

    public static function getSecondsFromInterval($count, $period) {
        switch ($period) {
            case 'Y':
                return $count * self::YEAR;
                break;
            case 'M':
                return $count * self::MONTH;
                break;
            case 'W':
                return $count * self::WEEK;
                break;
            case 'D':
                return $count * self::DAY;
                break;
            default:
                return 0;            
        }
    }

    public static function fromDateInterval(DateInterval $from, $format=false) {
        $format = !$format ? 'P%yY%mM%dD' : $format;
        return new DateIntervalEnhanced($from->format($format)); // Time format: T%hH%iM%sS
    }

    public function toSeconds() { 
        return ($this->y * self::YEAR) + ($this->m * self::MONTH) + ($this->d * self::DAY) + ($this->h * self::HOUR) + ($this->i * self::MINUTE) + $this->s; 
    }     

    public function add(DateInterval $interval) {
        foreach (str_split('ymdhis') as $prop) {
            $this->$prop += $interval->$prop;
        }
        $this->i += (int)($this->s / 60);
        $this->s = $this->s % 60;
        $this->h += (int)($this->i / 60);
        $this->i = $this->i % 60;
    }
}