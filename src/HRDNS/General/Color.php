<?php

namespace HRDNS\General;

/**
 * Class Color
 *
 * @package HRDNS\General
 * @SuppressWarnings(PHPMD.ElseExpression)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class Color
{

    /**
     * converts rgb 2 hsv color value
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return array
     */
    public function rgb2hsv(int $red = 0, int $green = 0, int $blue = 0)
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

        return array($hue, $saturation, $value);
    }

    /**
     * convert rgb 2 hsl color value
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return array
     */
    public function rgb2hsl(int $red = 0, int $green = 0, int $blue = 0)
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

        return array($hue, $saturation, $lightness);
    }

    /**
     * convert rgb 2 hex color value
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return string
     */
    public function rgb2hex(int $red = 0, int $green = 0, int $blue = 0)
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

    /**
     * convert rgb 2 cmyk color value
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return array
     */
    public function rgb2cmyk(int $red = 0, int $green = 0, int $blue = 0)
    {
        if (is_array($red)) {
            list($red, $green, $blue) = $red;
        }
        $red = min(max(0, (int)$red), 255);
        $green = min(max(0, (int)$green), 255);
        $blue = min(max(0, (int)$blue), 255);

        $key = 1 - max($red, $green, $blue);
        if ($key == 1) {
            $cyan = $magenta = $yellow = 0;
        } else {
            $cyan = (1 - $red - $key) / (1 - $key);
            $magenta = (1 - $green - $key) / (1 - $key);
            $yellow = (1 - $blue - $key) / (1 - $key);
        }

        return array(
            round($cyan, 2),
            round($magenta, 2),
            round($yellow, 2),
            round($key, 2),
        );
    }

    /**
     * convert hsv 2 rgb color value
     *
     * @param integer $hue
     * @param integer $saturation
     * @param integer $value
     * @return array
     */
    public function hsv2rgb(int $hue = 0, int $saturation = 0, int $value = 0)
    {
        if (is_array($hue)) {
            list($hue, $saturation, $value) = $hue;
        }
        $hue = min(max(0, (float)$hue), 359) / 60;
        $saturation = min(max(0, (float)$saturation), 100) / 100;
        $value = min(max(0, (float)$value), 100) / 100;

        $ccc = $value * $saturation;
        $hhh = $hue / 60;
        $xxx = $ccc * (1 - abs($hhh % 2 - 1));

        $red = $green = $blue = 0;
        if ($hhh >= 0 && $hhh < 1) {
            $red = $ccc;
            $green = $xxx;
        } else {
            if ($hhh >= 1 && $hhh < 2) {
                $red = $xxx;
                $green = $ccc;
            } else {
                if ($hhh >= 2 && $hhh < 3) {
                    $green = $ccc;
                    $blue = $xxx;
                } else {
                    if ($hhh >= 3 && $hhh < 4) {
                        $green = $xxx;
                        $blue = $ccc;
                    } else {
                        if ($hhh >= 4 && $hhh < 5) {
                            $red = $xxx;
                            $blue = $ccc;
                        } else {
                            $red = $ccc;
                            $blue = $xxx;
                        }
                    }
                }
            }
        }

        $magenta = $value - $ccc;
        $red = ($red + $magenta) * 255;
        $green = ($green + $magenta) * 255;
        $blue = ($blue + $magenta) * 255;

        return array((int)$red, (int)$green, (int)$blue);
    }

    /**
     * convert hsl 2 rgb color value
     *
     * @param integer $hue
     * @param integer $saturation
     * @param integer $lightness
     * @return array
     */
    public function hsl2rgb(int $hue = 0, int $saturation = 0, int $lightness = 0)
    {
        if (is_array($hue)) {
            list($hue, $saturation, $lightness) = $hue;
        }

        $hhh = min(max(0, (float)$hue), 359) / 60;
        $saturation = min(max(0, (float)$saturation), 100) / 100;
        $lightness = min(max(0, (float)$lightness), 100) / 100;

        $cyan = (1 - abs(2 * $lightness - 1)) * $saturation;
        $xxx = $cyan * (1 - abs($hhh % 2 - 1));

        $red = $green = $blue = 0;

        if ($hhh >= 0 && $hhh < 1) {
            $red = $cyan;
            $green = $xxx;
        } else {
            if ($hhh >= 1 && $hhh < 2) {
                $red = $xxx;
                $green = $cyan;
            } else {
                if ($hhh >= 2 && $hhh < 3) {
                    $green = $cyan;
                    $blue = $xxx;
                } else {
                    if ($hhh >= 3 && $hhh < 4) {
                        $green = $xxx;
                        $blue = $cyan;
                    } else {
                        if ($hhh >= 4 && $hhh < 5) {
                            $red = $xxx;
                            $blue = $cyan;
                        } else {
                            $red = $cyan;
                            $blue = $xxx;
                        }
                    }
                }
            }
        }

        $magenta = $lightness - $cyan / 2;
        $red = (float)($red + $magenta) * 255;
        $green = (float)($green + $magenta) * 255;
        $blue = ($blue + $magenta) * 255;

        return array((int)$red, (int)$green, (int)$blue);
    }

    /**
     * convert hex 2 rgb color value
     *
     * @param string $hex
     * @return array
     */
    public function hex2rgb(string $hex = '000000')
    {
        $hex = strtolower($hex);
        $hex = str_pad($hex, 6, '0', STR_PAD_LEFT);
        if (!preg_match('/^([0-9a-f]{6})$/', $hex)) {
            $hex = '000000';
        }
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return array((int)$red, (int)$green, (int)$blue);
    }

    /**
     * convert cmyk 2 rgb color value
     *
     * @param integer $cyan
     * @param integer $magenta
     * @param integer $yellow
     * @param integer $key
     * @return array
     */
    public function cmyk2rgb(int $cyan = 0, int $magenta = 0, int $yellow = 0, int $key = 0)
    {
        if (is_array($cyan)) {
            list($cyan, $magenta, $yellow, $key) = $cyan;
        }
        $cyan = min(max(0, (float)$cyan), 1);
        $magenta = min(max(0, (float)$magenta), 1);
        $yellow = min(max(0, (float)$yellow), 1);
        $key = min(max(0, (float)$key), 1);

        $red = (int)(1 - $cyan) * (1 - $key) * 255;
        $green = (int)(1 - $magenta) * (1 - $key) * 255;
        $blue = (int)(1 - $yellow) * (1 - $key) * 255;

        return array((int)$red, (int)$green, (int)$blue);
    }

    /**
     * convert hsl 2 hex color value
     *
     * @param integer $hue
     * @param integer $saturation
     * @param integer $lightness
     * @return string
     */
    public function hsl2hex(int $hue = 0, int $saturation = 0, int $lightness = 0)
    {
        list($red, $green, $blue) = $this->hsl2rgb($hue, $saturation, $lightness);
        return $this->rgb2hex($red, $green, $blue);
    }

    /**
     * convert hsl 2 cmyk color value
     *
     * @param integer $hue
     * @param integer $saturation
     * @param integer $lightness
     * @return array
     */
    public function hsl2cmyk(int $hue = 0, int $saturation = 0, int $lightness = 0)
    {
        list($red, $green, $blue) = $this->hsl2rgb($hue, $saturation, $lightness);
        return $this->rgb2cmyk($red, $green, $blue);
    }

    /**
     * convert hsl 2 hsv color value
     *
     * @param integer $hue
     * @param integer $saturation
     * @param integer $lightness
     * @return array
     */
    public function hsl2hsv(int $hue = 0, int $saturation = 0, int $lightness = 0)
    {
        list($red, $green, $blue) = $this->hsl2rgb($hue, $saturation, $lightness);
        return $this->rgb2hsv($red, $green, $blue);
    }

    /**
     * convert hex 2 hsl
     *
     * @param string $hex
     * @return array
     */
    public function hex2hsl(string $hex = '000000')
    {
        list($red, $green, $blue) = $this->hex2rgb($hex);
        return $this->rgb2hsl($red, $green, $blue);
    }

    /**
     * convert hex 2 cmyk color value
     *
     * @param string $hex
     * @return array
     */
    public function hex2cmyk(string $hex = '000000')
    {
        list($red, $green, $blue) = $this->hex2rgb($hex);
        return $this->rgb2cmyk($red, $green, $blue);
    }

    /**
     * convert hex 2 hsv color value
     *
     * @param string $hex
     * @return array
     */
    public function hex2hsv(string $hex = '000000')
    {
        list($red, $green, $blue) = $this->hex2rgb($hex);
        return $this->rgb2hsv($red, $green, $blue);
    }

    /**
     * convert hsv to hex color value
     *
     * @param integer $hue
     * @param integer $saturation
     * @param integer $value
     * @return string
     */
    public function hsv2hex(int $hue = 0, int $saturation = 0, int $value = 0)
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);
        return $this->rgb2hex($red, $green, $blue);
    }

    /**
     * convert hsv 2 hsl color value
     *
     * @param integer $hue
     * @param integer $saturation
     * @param integer $value
     * @return array
     */
    public function hsv2hsl(int $hue = 0, int $saturation = 0, int $value = 0)
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);
        return $this->rgb2hsl($red, $green, $blue);
    }

    /**
     * convert hsv 2 cmyk color value
     *
     * @param integer $hue
     * @param integer $saturation
     * @param integer $value
     * @return array
     */
    public function hsv2cmyk(int $hue = 0, int $saturation = 0, int $value = 0)
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);
        return $this->rgb2cmyk($red, $green, $blue);
    }

    /**
     * convert cmyk 2 hex color value
     *
     * @param integer $cyan
     * @param integer $magenta
     * @param integer $yellow
     * @param integer $key
     * @return string
     */
    public function cmyk2hex(int $cyan = 0, int $magenta = 0, int $yellow = 0, int $key = 0)
    {
        list($red, $green, $blue) = $this->cmyk2rgb($cyan, $magenta, $yellow, $key);
        return $this->rgb2hex($red, $green, $blue);
    }

    /**
     * convert cmyk 2 hsl color value
     *
     * @param integer $cyan
     * @param integer $magenta
     * @param integer $yellow
     * @param integer $key
     * @return array
     */
    public function cmyk2hsl(int $cyan = 0, int $magenta = 0, int $yellow = 0, int $key = 0)
    {
        list($red, $green, $blue) = $this->cmyk2rgb($cyan, $magenta, $yellow, $key);
        return $this->rgb2hsl($red, $green, $blue);
    }

    /**
     * convert cmyk 2 hsv color value
     *
     * @param integer $cyan
     * @param integer $magenta
     * @param integer $yellow
     * @param integer $key
     * @return array
     */
    public function cmyk2hsv(int $cyan = 0, int $magenta = 0, int $yellow = 0, int $key = 0)
    {
        list($red, $green, $blue) = $this->cmyk2rgb($cyan, $magenta, $yellow, $key);
        return $this->rgb2hsv($red, $green, $blue);
    }

}
