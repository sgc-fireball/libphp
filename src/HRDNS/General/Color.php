<?php

namespace HRDNS\General;

class Color
{

    public function rgb2hsv($red = 0, $green = 0, $blue = 0)
    {
        if (is_array($red)) {
            list($red, $green, $blue) = $red;
        }
        $red = min(max(0, (int)$red), 255) / 255;
        $green = min(max(0, (int)$green), 255) / 255;
        $blue = min(max(0, (int)$blue), 255) / 255;

        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $delta = $max - $min;

        if ($delta == 0) {
            $hue = 0;
        } else {
            if ($max == $red) {
                $hue = (($green - $blue) / $delta) % 6;
            } else {
                if ($max == $green) {
                    $hue = ($blue - $red) / $delta + 2;
                } else {
                    $hue = ($red - $green) / $delta + 4;
                }
            }
        }

        $hue *= 60;
        if ($hue < 0) {
            $hue += 360;
        }

        $value = $max;
        if ($value == 0) {
            $saturation = 0;
        } else {
            $saturation = $delta / $value;
        }

        $saturation *= 100;
        $value *= 100;

        return array ($hue, $saturation, $value);
    }

    public function rgb2hsl($red = 0, $green = 0, $blue = 0)
    {
        if (is_array($red)) {
            list($red, $green, $blue) = $red;
        }
        $red = min(max(0, (int)$red), 255) / 255;
        $green = min(max(0, (int)$green), 255) / 255;
        $blue = min(max(0, (int)$blue), 255) / 255;

        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $delta = $max - $min;

        if ($delta == 0) {
            $hue = 0;
        } else {
            if ($max == $red) {
                $hue = (($green - $blue) / $delta) % 6;
            } else {
                if ($max == $green) {
                    $hue = ($blue - $red) / $delta + 2;
                } else {
                    $hue = ($red - $green) / $delta + 4;
                }
            }
        }

        $hue *= 60;
        if ($hue < 0) {
            $hue += 360;
        }
        $lightness = ($max + $min) / 2;

        if ($delta == 0) {
            $saturation = 0;
        } else {
            $saturation = $delta / (1 - abs(2 * $lightness - 1));
        }
        $saturation *= 100;
        $lightness *= 100;

        return array ($hue, $saturation, $lightness);
    }

    public function rgb2hex($red = 0, $green = 0, $blue = 0)
    {
        if (is_array($red)) {
            list($red, $green, $blue) = $red;
        }
        $red = min(max(0, (int)$red), 255);
        $green = min(max(0, (int)$green), 255);
        $blue = min(max(0, (int)$blue), 255);
        $hex = $red * 65536 + $green * 256 + $blue;

        return str_pad($hex, 6, '0', STR_PAD_LEFT);
    }

    public function rgb2cmyk($red = 0, $green = 0, $blue = 0)
    {
        if (is_array($red)) {
            list($red, $green, $blue) = $red;
        }
        $red = min(max(0, (int)$red), 255);
        $green = min(max(0, (int)$green), 255);
        $blue = min(max(0, (int)$blue), 255);

        $k = 1 - max($red, $green, $blue);
        if ($k == 1) {
            $c = $m = $y = 0;
        } else {
            $c = (1 - $red - $k) / (1 - $k);
            $m = (1 - $green - $k) / (1 - $k);
            $y = (1 - $blue - $k) / (1 - $k);
        }

        return array (
            round($c, 2),
            round($m, 2),
            round($y, 2),
            round($k, 2),
        );
    }

    public function hsv2rgb($hue = 0, $saturation = 0, $value = 0)
    {
        if (is_array($hue)) {
            list($hue, $saturation, $value) = $hue;
        }
        $hue = min(max(0, (float)$hue), 359) / 60;
        $saturation = min(max(0, (float)$saturation), 100) / 100;
        $value = min(max(0, (float)$value), 100) / 100;

        $C = $value * $saturation;
        $hh = $hue / 60;
        $X = $C * (1 - abs($hh % 2 - 1));

        $red = $green = $blue = 0;
        if ($hh >= 0 && $hh < 1) {
            $red = $C;
            $green = $X;
        } else {
            if ($hh >= 1 && $hh < 2) {
                $red = $X;
                $green = $C;
            } else {
                if ($hh >= 2 && $hh < 3) {
                    $green = $C;
                    $blue = $X;
                } else {
                    if ($hh >= 3 && $hh < 4) {
                        $green = $X;
                        $blue = $C;
                    } else {
                        if ($hh >= 4 && $hh < 5) {
                            $red = $X;
                            $blue = $C;
                        } else {
                            $red = $C;
                            $blue = $X;
                        }
                    }
                }
            }
        }

        $m = $value - $C;
        $red = ($red + $m) * 255;
        $green = ($green + $m) * 255;
        $blue = ($blue + $m) * 255;

        return array ((int)$red, (int)$green, (int)$blue);
    }

    public function hsl2rgb($hue = 0, $saturation = 0, $lightness = 0)
    {
        if (is_array($hue)) {
            list($hue, $saturation, $lightness) = $hue;
        }

        $hh = min(max(0, (float)$hue), 359) / 60;
        $saturation = min(max(0, (float)$saturation), 100) / 100;
        $lightness = min(max(0, (float)$lightness), 100) / 100;

        $c = (1 - abs(2 * $lightness - 1)) * $saturation;
        $x = $c * (1 - abs($hh % 2 - 1));

        $red = $green = $blue = 0;

        if ($hh >= 0 && $hh < 1) {
            $red = $c;
            $green = $x;
        } else {
            if ($hh >= 1 && $hh < 2) {
                $red = $x;
                $green = $c;
            } else {
                if ($hh >= 2 && $hh < 3) {
                    $green = $c;
                    $blue = $x;
                } else {
                    if ($hh >= 3 && $hh < 4) {
                        $green = $x;
                        $blue = $c;
                    } else {
                        if ($hh >= 4 && $hh < 5) {
                            $red = $x;
                            $blue = $c;
                        } else {
                            $red = $c;
                            $blue = $x;
                        }
                    }
                }
            }
        }

        $m = $lightness - $c / 2;
        $red = (float)($red + $m) * 255;
        $green = (float)($green + $m) * 255;
        $blue = ($blue + $m) * 255;

        return array ((int)$red, (int)$green, (int)$blue);
    }

    public function hex2rgb($hex = '000000')
    {
        $hex = strtolower($hex);
        $hex = str_pad($hex, 6, '0', STR_PAD_LEFT);
        if (!preg_match('/^([0-9a-f]{6})$/', $hex)) {
            $hex = '000000';
        }
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return array ((int)$red, (int)$green, (int)$blue);
    }

    public function cmyk2rgb($c = 0, $m = 0, $y = 0, $k = 0)
    {
        if (is_array($c)) {
            list($c, $m, $y, $k) = $c;
        }
        $c = min(max(0, (float)$c), 1);
        $m = min(max(0, (float)$m), 1);
        $y = min(max(0, (float)$y), 1);
        $k = min(max(0, (float)$k), 1);

        $red = (int)(1 - $c) * (1 - $k) * 255;
        $green = (int)(1 - $m) * (1 - $k) * 255;
        $blue = (int)(1 - $y) * (1 - $k) * 255;

        return array ((int)$red, (int)$green, (int)$blue);
    }

    public function hsl2hex($hue = 0, $saturation = 0, $lightness = 0)
    {
        list($red, $green, $blue) = $this->hsl2rgb($hue, $saturation, $lightness);

        return $this->rgb2hex($red, $green, $blue);
    }

    public function hsl2cymk($hue = 0, $saturation = 0, $lightness = 0)
    {
        list($red, $green, $blue) = $this->hsl2rgb($hue, $saturation, $lightness);

        return $this->rgb2cmyk($red, $green, $blue);
    }

    public function hsl2hsv($hue = 0, $saturation = 0, $lightness = 0)
    {
        list($red, $green, $blue) = $this->hsl2rgb($hue, $saturation, $lightness);

        return $this->rgb2hsv($red, $green, $blue);
    }

    public function hex2hsl($hex = '000000')
    {
        list($red, $green, $blue) = $this->hex2rgb($hex);

        return $this->rgb2hsl($red, $green, $blue);
    }

    public function hex2cmyk($hex = '000000')
    {
        list($red, $green, $blue) = $this->hex2rgb($hex);

        return $this->rgb2cmyk($red, $green, $blue);
    }

    public function hex2hsv($hex = '000000')
    {
        list($red, $green, $blue) = $this->hex2rgb($hex);

        return $this->rgb2hsv($red, $green, $blue);
    }

    public function cymk2hsl($c = 0, $m = 0, $y = 0, $k = 0)
    {
        list($red, $green, $blue) = $this->cmyk2rgb($c, $m, $y, $k);

        return $this->rgb2hsl($red, $green, $blue);
    }

    public function hsv2hex($hue = 0, $saturation = 0, $value = 0)
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);

        return $this->rgb2hex($red, $green, $blue);
    }

    public function hsv2hsl($hue = 0, $saturation = 0, $value = 0)
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);

        return $this->rgb2hsl($red, $green, $blue);
    }

    public function hsv2cymk($hue = 0, $saturation = 0, $value = 0)
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);

        return $this->rgb2cmyk($red, $green, $blue);
    }

    public function cmyk2hex($c = 0, $m = 0, $y = 0, $k = 0)
    {
        list($red, $green, $blue) = $this->cmyk2rgb($c, $m, $y, $k);

        return $this->rgb2hex($red, $green, $blue);
    }

    public function cmyk2hsl($c = 0, $m = 0, $y = 0, $k = 0)
    {
        list($red, $green, $blue) = $this->cmyk2rgb($c, $m, $y, $k);

        return $this->rgb2hsl($red, $green, $blue);
    }

    public function cmyk2hsv($c = 0, $m = 0, $y = 0, $k = 0)
    {
        list($red, $green, $blue) = $this->cmyk2rgb($c, $m, $y, $k);

        return $this->rgb2hsv($red, $green, $blue);
    }

}
