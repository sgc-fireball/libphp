<?php

namespace HRDNS\Types;

/**
 * @see https://www.koordinaten-umrechner.de/decimal/51.388923,6.642949
 */
class GPS
{

    /** @var string */
    protected $latitudeDirections = 'N';

    /** @var int */
    protected $latitudeHours = 0;

    /** @var int */
    protected $latitudeMinutes = 0;

    /** @var float */
    protected $latitudeSeconds = 0;

    /** @var string */
    protected $longitudeDirection = 'E';

    /** @var int */
    protected $longitudeHours = 0;

    /** @var int */
    protected $longitudeMinutes = 0;

    /** @var float */
    protected $longitudeSeconds = 0;

    /**
     * @param string $position
     */
    public function __construct(string $position)
    {
        $position = preg_replace("/[^NSOEW0-9\-\+\.\"\'\s]/", '', $position);
        if (preg_match('/^([\-\+]{0,1}\d{1,2})\.(\d{1,}) ([\-\+]{0,1}\d{1,3})\.(\d{1,})$/', $position, $match)) {
            $this->latitudeDirections = ($match[1] < 0) ? 'S' : 'N';
            $this->latitudeHours = (int)$match[1];
            $this->latitudeHours = $this->latitudeHours < 0 ? $this->latitudeHours * -1 : $this->latitudeHours;
            $tmp = (float)('0.' . $match[2]) * 60;
            $this->latitudeMinutes = (int)$tmp;
            $this->latitudeSeconds = (float)($tmp - (int)$tmp) * 60;
            $this->longitudeDirection = ($match[3] < 0) ? 'W' : 'E';
            $this->longitudeHours = (int)$match[3];
            $this->longitudeHours = $this->longitudeHours < 0 ? $this->longitudeHours * -1 : $this->longitudeHours;
            $tmp = (float)('0.' . $match[4]) * 60;
            $this->longitudeMinutes = (int)$tmp;
            $this->longitudeSeconds = (float)($tmp - (int)$tmp) * 60;
        } elseif (preg_match('/^(N|S) (\d{1,2}) (\d{1,2})\.(\d{1,3}) (O|E|W) (\d{1,3}) (\d{1,2})\.(\d{1,3})$/i', $position, $match)) {
            if ($match[5] == 'O') {
                $match[5] = 'E';
            }
            $this->latitudeDirections = strtoupper($match[1]);
            $this->latitudeHours = (int)$match[2];
            $this->latitudeMinutes = (int)$match[3];
            $this->latitudeSeconds = (float)((int)$match[4] / 1000 * 60);
            $this->longitudeDirection = strtoupper($match[5]);
            $this->longitudeHours = (int)$match[6];
            $this->longitudeMinutes = (int)$match[7];
            $this->longitudeSeconds = (float)((int)$match[8] / 1000 * 60);
        } elseif (preg_match("/^(N|S)\s{0,1}(\d{1,2}) (\d{1,2}) (\d{1,2}) (E|O|W)\s{0,1}(\d{1,2}) (\d{1,2}) (\d{1,2})$/", $position, $match)) {
            if ($match[5] == 'O') {
                $match[5] = 'E';
            }
            $this->latitudeDirections = strtoupper($match[1]);
            $this->latitudeHours = (int)$match[2];
            $this->latitudeMinutes = (int)$match[3];
            $this->latitudeSeconds = (int)$match[4];
            $this->longitudeDirection = strtoupper($match[5]);
            $this->longitudeHours = (int)$match[6];
            $this->longitudeMinutes = (int)$match[7];
            $this->longitudeSeconds = (int)$match[8];
        } elseif (preg_match('/^(N|S) (\d{1,2}) (\d{1,2})\' (\d{1,2}.\d{1,4})["|\'\'] (E|O|W) (\d{1,3}) (\d{1,2})\' (\d{1,2}.\d{1,4})["|\'\']$/i', $position, $match)) {
            if ($match[6] == 'O') {
                $match[6] = 'E';
            }
            $this->latitudeDirections = strtoupper($match[1]);
            $this->latitudeHours = (int)$match[2];
            $this->latitudeMinutes = (int)$match[3];
            $this->latitudeSeconds = (float)$match[4];
            $this->longitudeDirection = strtoupper($match[5]);
            $this->longitudeHours = (int)$match[6];
            $this->longitudeMinutes = (int)$match[7];
            $this->longitudeSeconds = (float)$match[8];
        }
    }

    /**
     * @return string
     */
    public function getGPS(): string
    {
        return sprintf(
            '%s %s %s.%s %s %s %s.%s',
            $this->latitudeDirections,
            $this->latitudeHours,
            $this->latitudeMinutes,
            round($this->latitudeSeconds / 60 * 1000),
            $this->longitudeDirection,
            $this->longitudeHours,
            $this->longitudeMinutes,
            round($this->longitudeSeconds / 60 * 1000)
        );
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        $result = $this->latitudeDirections == 'N' ? '' : '-';
        $result .= $this->latitudeHours + ($this->latitudeMinutes / 60) + (($this->latitudeSeconds) / 3600);
        return (float)$result;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        $result = $this->longitudeDirection == 'W' ? '-' : '';
        $result .= $this->longitudeHours + ($this->longitudeMinutes / 60) + (($this->longitudeSeconds) / 3600);
        return (float)$result;
    }

    public function getWGS84Hour(): string
    {
        return sprintf(
            '%s%s %s%s',
            $this->latitudeDirections,
            $this->latitudeHours + $this->latitudeMinutes / 60 + $this->latitudeSeconds / 3600,
            $this->longitudeDirection,
            $this->longitudeHours + $this->longitudeMinutes / 60 + $this->longitudeSeconds / 3600
        );
    }

    public function getWGS84Minutes(): string
    {
        return sprintf(
            '%s%s %s %s%s %s',
            $this->latitudeDirections,
            $this->latitudeHours,
            $this->latitudeMinutes + ($this->latitudeSeconds / 3600),
            $this->longitudeDirection,
            $this->longitudeHours,
            $this->longitudeMinutes + ($this->longitudeSeconds / 3600)
        );
    }

    public function getWGS84Seconds(): string
    {
        return sprintf(
            '%s%s %s %s %s%s %s %s',
            $this->latitudeDirections,
            $this->latitudeHours,
            $this->latitudeMinutes,
            round($this->latitudeSeconds, 0),
            $this->longitudeDirection,
            $this->longitudeHours,
            $this->longitudeMinutes,
            round($this->longitudeSeconds, 0)
        );
    }

    /**
     * testing!
     * @param string|GPS $position
     * @param string $unit K=km, N=nautical miles, M or others=miles
     * @return float
     */
    public function distance($position, string $unit = 'K'): float
    {
        $position = $position instanceof self ? $position : new self($position);
        $latitudeA = $this->getLatitude();
        $longitudeA = $this->getLongitude();
        $latitudeB = $position->getLatitude();
        $longitudeB = $position->getLongitude();

        if (($latitudeA == $latitudeB) && ($longitudeA == $longitudeB)) {
            return 0;
        }

        $theta = $longitudeA - $longitudeB;
        $dist = sin(deg2rad($latitudeA)) * sin(deg2rad($latitudeB)) + cos(deg2rad($latitudeA)) * cos(deg2rad($latitudeB)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

}
