<?php declare(strict_types=1);

namespace HRDNS\General;

use HRDNS\General\Color\XTermConverter;
use HRDNS\General\Color\XTermConverterInterface;

/**
 * @SuppressWarnings(PHPMD.ElseExpression)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class Color
{

    /** @$XTermConverterInterface */
    private $xtermConverter;

    /**
     * @param XTermConverterInterface|null $xtermConverter
     */
    public function __construct(XTermConverterInterface $xtermConverter = null)
    {
        $this->xtermConverter = $xtermConverter ?: new XTermConverter();
    }

    /**
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return array
     */
    private function helpRgb2HsvHsl(int $red = 0, int $green = 0, int $blue = 0): array
    {
        $red = min(max(0, $red), 255) / 255;
        $green = min(max(0, $green), 255) / 255;
        $blue = min(max(0, $blue), 255) / 255;

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

        return [$hue, $delta, $min, $max];
    }

    /**
     * converts rgb 2 hsv color value
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return array
     */
    public function rgb2hsv(int $red = 0, int $green = 0, int $blue = 0): array
    {
        list($hue, $delta, , $max) = $this->helpRgb2HsvHsl($red, $green, $blue);

        $value = $max;
        if ($value == 0) {
            $saturation = 0;
        } else {
            $saturation = $delta / $value;
        }

        $saturation *= 100;
        $value *= 100;

        return [
            round($hue, 2),
            round($saturation, 2),
            round($value, 2)
        ];
    }

    /**
     * convert rgb 2 hsl color value
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return array
     */
    public function rgb2hsl(int $red = 0, int $green = 0, int $blue = 0): array
    {
        list($hue, $delta, $min, $max) = $this->helpRgb2HsvHsl($red, $green, $blue);

        $lightness = ($max + $min) / 2;

        if ($delta == 0) {
            $saturation = 0;
        } else {
            $saturation = $delta / (1 - abs(2 * $lightness - 1));
        }
        $saturation *= 100;
        $lightness *= 100;

        return [
            round($hue, 2),
            round($saturation, 2),
            round($lightness, 2)
        ];
    }

    /**
     * convert rgb 2 hex color value
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return string
     */
    public function rgb2hex(int $red = 0, int $green = 0, int $blue = 0): string
    {
        $red = min(max(0, $red), 255);
        $green = min(max(0, $green), 255);
        $blue = min(max(0, $blue), 255);
        $dec = $red * 65536 + $green * 256 + $blue;
        $hex = dechex($dec);

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
    public function rgb2cmyk(int $red = 0, int $green = 0, int $blue = 0): array
    {
        $red = min(max(0, $red), 255);
        $green = min(max(0, $green), 255);
        $blue = min(max(0, $blue), 255);

        if ($red === 0 && $green === 0 && $blue === 0) {
            return [0, 0, 0, 1];
        }

        $cyan = 1 - ($red / 255);
        $magenta = 1 - ($green / 255);
        $yellow = 1 - ($blue / 255);
        $key = min($cyan, min($magenta, $yellow));

        $cyan = ($cyan - $key) / (1 - $key);
        $magenta = ($magenta - $key) / (1 - $key);
        $yellow = ($yellow - $key) / (1 - $key);

        return [
            min(max(0, round($cyan, 4)), 1),
            min(max(0, round($magenta, 4)), 1),
            min(max(0, round($yellow, 4)), 1),
            min(max(0, round($key, 4)), 1)
        ];
    }

    /**
     * convert hsv 2 rgb color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $value
     * @return array
     */
    public function hsv2rgb(float $hue = 0.0, float $saturation = 0.0, float $value = 0.0): array
    {
        $result = [0, 0, 0];
        $hue = abs($hue) % 360;
        $saturation = max(0, min(abs($saturation) / 100, 1));
        $value = max(0, min(abs($value) / 100, 1));

        if (!$saturation) {
            $result = [$value, $value, $value];
        } else {
            $b = ((1 - $saturation) * $value);
            $vb = $value - $b;
            $hm = $hue % 60;
            switch (($hue / 60) | 0) {
                case 0:
                    $result = [
                        $value,
                        $vb * $hue / 60 + $b,
                        $b
                    ];
                    break;
                case 1:
                    $result = [
                        $vb * (60 - $hm) / 60 + $b,
                        $value,
                        $b
                    ];
                    break;
                case 2:
                    $result = [
                        $b,
                        $value,
                        $vb * $hm / 60 + $b
                    ];
                    break;
                case 3:
                    $result = [
                        $b,
                        $vb * (60 - $hm) / 60 + $b,
                        $value
                    ];
                    break;
                case 4:
                    $result = [
                        $vb * $hm / 60 + $b,
                        $b,
                        $value
                    ];
                    break;
                case 5:
                    $result = [
                        $value,
                        $b,
                        $vb * (60 - $hm) / 60 + $b
                    ];
                    break;
            }
        }

        return [
            (int)round($result[0] * 255, 0),
            (int)round($result[1] * 255, 0),
            (int)round($result[2] * 255, 0)
        ];
    }

    /**
     * convert hsl 2 rgb color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $lightness
     * @return array
     */
    public function hsl2rgb(float $hue = 0.0, float $saturation = 0.0, float $lightness = 0.0): array
    {
        $hhh = min(max(0, $hue), 359) / 60;
        $saturation = min(max(0, $saturation), 100) / 100;
        $lightness = min(max(0, $lightness), 100) / 100;

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

        return [
            (int)round($red, 0),
            (int)round($green, 0),
            (int)round($blue, 0)
        ];
    }

    /**
     * convert hex 2 rgb color value
     *
     * @param string $hex
     * @return array
     */
    public function hex2rgb(string $hex = '000000'): array
    {
        $hex = strtolower($hex);
        $hex = str_pad($hex, 6, '0', STR_PAD_LEFT);
        if (!preg_match('/^([0-9a-f]{6})$/i', $hex)) {
            $hex = '000000';
        }
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return [
            (int)round($red, 0),
            (int)round($green, 0),
            (int)round($blue, 0)
        ];
    }

    /**
     * convert cmyk 2 rgb color value
     *
     * @param float $cyan
     * @param float $magenta
     * @param float $yellow
     * @param float $key
     * @return array
     */
    public function cmyk2rgb(float $cyan = 0.0, float $magenta = 0.0, float $yellow = 0.0, float $key = 0.0): array
    {
        $cyan = min(max(0, $cyan), 1);
        $magenta = min(max(0, $magenta), 1);
        $yellow = min(max(0, $yellow), 1);
        $key = min(max(0, $key), 1);

        $red = (1 - $cyan) * (1 - $key) * 255;
        $green = (1 - $magenta) * (1 - $key) * 255;
        $blue = (1 - $yellow) * (1 - $key) * 255;

        return [
            (int)round($red, 0),
            (int)round($green, 0),
            (int)round($blue, 0)
        ];
    }

    /**
     * convert hsl 2 hex color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $lightness
     * @return string
     */
    public function hsl2hex(float $hue = 0.0, float $saturation = 0.0, float $lightness = 0.0): string
    {
        list($red, $green, $blue) = $this->hsl2rgb($hue, $saturation, $lightness);

        return $this->rgb2hex($red, $green, $blue);
    }

    /**
     * convert hsl 2 cmyk color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $lightness
     * @return array
     */
    public function hsl2cmyk(float $hue = 0.0, float $saturation = 0.0, float $lightness = 0.0): array
    {
        list($red, $green, $blue) = $this->hsl2rgb($hue, $saturation, $lightness);

        return $this->rgb2cmyk($red, $green, $blue);
    }

    /**
     * convert hsl 2 hsv color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $lightness
     * @return array
     */
    public function hsl2hsv(float $hue = 0.0, float $saturation = 0.0, float $lightness = 0.0): array
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
    public function hex2hsl(string $hex = '000000'): array
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
    public function hex2cmyk(string $hex = '000000'): array
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
    public function hex2hsv(string $hex = '000000'): array
    {
        list($red, $green, $blue) = $this->hex2rgb($hex);

        return $this->rgb2hsv($red, $green, $blue);
    }

    /**
     * convert hsv to hex color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $value
     * @return string
     */
    public function hsv2hex(float $hue = 0.0, float $saturation = 0.0, float $value = 0.0): string
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);

        return $this->rgb2hex($red, $green, $blue);
    }

    /**
     * convert hsv 2 hsl color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $value
     * @return array
     */
    public function hsv2hsl(float $hue = 0.0, float $saturation = 0.0, float $value = 0.0): array
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);

        return $this->rgb2hsl($red, $green, $blue);
    }

    /**
     * convert hsv 2 cmyk color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $value
     * @return array
     */
    public function hsv2cmyk(float $hue = 0.0, float $saturation = 0.0, float $value = 0.0): array
    {
        list($red, $green, $blue) = $this->hsv2rgb($hue, $saturation, $value);

        return $this->rgb2cmyk($red, $green, $blue);
    }

    /**
     * convert cmyk 2 hex color value
     *
     * @param float $cyan
     * @param float $magenta
     * @param float $yellow
     * @param float $key
     * @return string
     */
    public function cmyk2hex(float $cyan = 0.0, float $magenta = 0.0, float $yellow = 0.0, float $key = 0.0): string
    {
        list($red, $green, $blue) = $this->cmyk2rgb($cyan, $magenta, $yellow, $key);

        return $this->rgb2hex($red, $green, $blue);
    }

    /**
     * convert cmyk 2 hsl color value
     *
     * @param float $cyan
     * @param float $magenta
     * @param float $yellow
     * @param float $key
     * @return array
     */
    public function cmyk2hsl(float $cyan = 0.0, float $magenta = 0.0, float $yellow = 0.0, float $key = 0.0): array
    {
        list($red, $green, $blue) = $this->cmyk2rgb($cyan, $magenta, $yellow, $key);

        return $this->rgb2hsl($red, $green, $blue);
    }

    /**
     * convert cmyk 2 hsv color value
     *
     * @param float $cyan
     * @param float $magenta
     * @param float $yellow
     * @param float $key
     * @return array
     */
    public function cmyk2hsv(float $cyan = 0.0, float $magenta = 0.0, float $yellow = 0.0, float $key = 0.0): array
    {
        list($red, $green, $blue) = $this->cmyk2rgb($cyan, $magenta, $yellow, $key);

        return $this->rgb2hsv($red, $green, $blue);
    }

    /**
     * convert xterm 2 cymk color value
     *
     * @param integer $xterm
     * @return array
     */
    public function xterm2cmyk(int $xterm = 0): array
    {
        $hex = $this->xtermConverter->xterm2hex($xterm);

        return $this->hex2cmyk($hex);
    }

    /**
     * convert xterm 2 hex color value
     *
     * @param integer $xterm
     * @return string
     */
    public function xterm2hex(int $xterm = 0): string
    {
        return $this->xtermConverter->xterm2hex($xterm);
    }

    /**
     * convert xterm 2 hsl color value
     *
     * @param integer $xterm
     * @return array
     */
    public function xterm2hsl(int $xterm = 0): array
    {
        $hex = $this->xtermConverter->xterm2hex($xterm);

        return $this->hex2hsl($hex);
    }

    /**
     * convert xterm 2 hsv color value
     *
     * @param integer $xterm
     * @return array
     */
    public function xterm2hsv(int $xterm = 0): array
    {
        $hex = $this->xtermConverter->xterm2hex($xterm);

        return $this->hex2hsv($hex);
    }

    /**
     * convert xterm 2 rgb color value
     *
     * @param integer $xterm
     * @return array
     */
    public function xterm2rgb(int $xterm): array
    {
        $hex = $this->xterm2hex($xterm);

        return $this->hex2rgb($hex);
    }

    /**
     * convert cymk 2 xterm color value
     *
     * @param float $cyan
     * @param float $magenta
     * @param float $yellow
     * @param float $key
     * @return integer
     */
    public function cmyk2xterm(float $cyan = 0.0, float $magenta = 0.0, float $yellow = 0.0, float $key = 0.0): int
    {
        $hex = $this->cmyk2hex($cyan, $magenta, $yellow, $key);

        return $this->hex2xterm($hex);
    }

    /**
     * convert hex 2 xterm color value
     *
     * @param string $hex
     * @return integer
     */
    public function hex2xterm(string $hex = '000000'): int
    {
        return $this->xtermConverter->hex2xterm($hex);
    }

    /**
     * convert hsl 2 xterm color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $lightness
     * @return integer
     */
    public function hsl2xterm(float $hue = 0.0, float $saturation = 0.0, float $lightness = 0.0): int
    {
        $hex = $this->hsl2hex($hue, $saturation, $lightness);

        return $this->hex2xterm($hex);
    }

    /**
     * convert hsv 2 xterm color value
     *
     * @param float $hue
     * @param float $saturation
     * @param float $value
     * @return integer
     */
    public function hsv2xterm(float $hue = 0.0, float $saturation = 0.0, float $value = 0.0): int
    {
        $hex = $this->hsv2hex($hue, $saturation, $value);

        return $this->hex2xterm($hex);
    }

    /**
     * convert rgb 2 xterm color value
     *
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return integer
     */
    public function rgb2xterm(int $red = 0, int $green = 0, int $blue = 0): int
    {
        $hex = $this->rgb2hex($red, $green, $blue);

        return $this->xtermConverter->hex2xterm($hex);
    }

    /**
     * @param float $x
     * @param float $y
     * @param int $brightness
     * @return array(int,int,int)
     */
    public function xyb2rgb(float $x, float $y, int $brightness = 254): array
    {
        // Calculate XYZ values Convert using the following formulas
        $z = 1.0 - $x - $y;
        $Y = $brightness;
        $X = ($Y / $y) * $x;
        $Z = ($Y / $y) * $z;

        // Convert to RGB using Wide RGB D65 conversion
        $r = $X * 1.656492 - $Y * 0.354851 - $Z * 0.255038;
        $g = -$X * 0.707196 + $Y * 1.655397 + $Z * 0.036152;
        $b = $X * 0.051713 - $Y * 0.121364 + $Z * 1.011530;

        // Apply reverse gamma correction
        $r = $r <= 0.0031308 ? 12.92 * $r : (1.0 + 0.055) * pow($r, (1.0 / 2.4)) - 0.055;
        $g = $g <= 0.0031308 ? 12.92 * $g : (1.0 + 0.055) * pow($g, (1.0 / 2.4)) - 0.055;
        $b = $b <= 0.0031308 ? 12.92 * $b : (1.0 + 0.055) * pow($b, (1.0 / 2.4)) - 0.055;

        if (($maxValue = max($r, $g, $b)) && $maxValue > 1) {
            $r /= $maxValue;
            $g /= $maxValue;
            $b /= $maxValue;
        }
        $r = (int)max(0, min(255, $r * 255));
        $g = (int)max(0, min(255, $g * 255));
        $b = (int)max(0, min(255, $b * 255));

        return [$r, $g, $b];
    }

    /**
     * @see https://developers.meethue.com/documentation/color-conversions-rgb-xy
     * @param int $red
     * @param int $green
     * @param int $blue
     * @return array(float,float,integer)
     */
    public function rgb2xyb(int $red = 0, int $green = 0, int $blue = 0): array
    {
        // Get the RGB values from your color object and convert them to be between 0 and 1.
        // So the RGB color (255, 0, 100) becomes (1.0, 0.0, 0.39)
        $red /= 255;
        $green /= 255;
        $blue /= 255;

        // Apply a gamma correction to the RGB values, which makes the color more vivid and more the like the color
        // displayed on the screen of your device. This gamma correction is also applied to the screen of your
        // computer or phone, thus we need this to create a similar color on the light as on screen.
        // This is done by the following formulas:
        $red = ($red > 0.04045) ? pow(($red + 0.055) / (1.0 + 0.055), 2.4) : ($red / 12.92);
        $green = ($green > 0.04045) ? pow(($green + 0.055) / (1.0 + 0.055), 2.4) : ($green / 12.92);
        $blue = ($blue > 0.04045) ? pow(($blue + 0.055) / (1.0 + 0.055), 2.4) : ($blue / 12.92);

        // Convert the RGB values to XYZ using the Wide RGB D65 conversion formula The formulas used
        $X = $red * 0.664511 + $green * 0.154324 + $blue * 0.162028;
        $Y = $red * 0.283881 + $green * 0.668433 + $blue * 0.047685;
        $Z = $red * 0.000088 + $green * 0.072310 + $blue * 0.986039;

        // Calculate the xy values from the XYZ values
        $x = $X + $Y + $Z;
        $x = $x == 0 ? 0 : $X / $x;
        $y = $X + $Y + $Z;
        $y = $y == 0 ? 0 : $Y / $y;

        // Use the Y value of XYZ as brightness The Y value indicates the brightness of the converted color.
        return [round($x, 6), round($y, 6), min(254, max(0, round($Y * 255, 0)))];
    }

}
