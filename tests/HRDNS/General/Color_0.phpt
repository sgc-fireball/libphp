--TEST--
Testing \HRDNS\General\Color - converter
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\General\Color;

$color = new Color();

echo 'rgb2hsv: ' . implode(':', $color->rgb2hsv(0, 0, 0)) . "\n";
echo 'rgb2hsl: ' . implode(':', $color->rgb2hsl(0, 0, 0)) . "\n";
echo 'rgb2hex: ' . $color->rgb2hex(0, 0, 0) . "\n";
echo 'rgb2cmyk: ' . implode(':', $color->rgb2cmyk(0, 0, 0)) . "\n";

echo 'hsv2rgb: ' . implode(':', $color->hsv2rgb(0, 0, 0)) . "\n";
echo 'hsv2hex: ' . $color->hsv2hex(0, 0, 0) . "\n";
echo 'hsv2hsl: ' . implode(':', $color->hsv2hsl(0, 0, 0)) . "\n";
echo 'hsv2cmyk: ' . implode(':', $color->hsv2cmyk(0, 0, 0)) . "\n";

echo 'hsl2rgb: ' . implode(':', $color->hsl2rgb(0, 0, 0)) . "\n";
echo 'hsl2hex: ' . $color->hsl2hex(0, 0, 0) . "\n";
echo 'hsl2cmyk: ' . implode(':', $color->hsl2cmyk(0, 0, 0)) . "\n";
echo 'hsl2hsv: ' . implode(':', $color->hsl2hsv(0, 0, 0)) . "\n";

echo 'hex2rgb: ' . implode(':', $color->hex2rgb('000000')) . "\n";
echo 'hex2hsl: ' . implode(':', $color->hex2hsl('000000')) . "\n";
echo 'hex2cmyk: ' . implode(':', $color->hex2cmyk('000000')) . "\n";
echo 'hex2hsv: ' . implode(':', $color->hex2hsv('000000')) . "\n";

echo 'cmyk2rgb: ' . implode(':', $color->cmyk2rgb(0, 0, 0, 0)) . "\n";
echo 'cmyk2hsl: ' . implode(':', $color->cmyk2hsl(0, 0, 0, 0)) . "\n";
echo 'cmyk2hex: ' . $color->cmyk2hex(0, 0, 0, 0) . "\n";
echo 'cmyk2hsv: ' . implode(':', $color->cmyk2hsv(0, 0, 0, 0)) . "\n";

?>
--EXPECT--
rgb2hsv: 0:0:0
rgb2hsl: 0:0:0
rgb2hex: 000000
rgb2cmyk: 0:0:0:1
hsv2rgb: 0:0:0
hsv2hex: 000000
hsv2hsl: 0:0:0
hsv2cmyk: 0:0:0:1
hsl2rgb: 0:0:0
hsl2hex: 000000
hsl2cmyk: 0:0:0:1
hsl2hsv: 0:0:0
hex2rgb: 0:0:0
hex2hsl: 0:0:0
hex2cmyk: 0:0:0:1
hex2hsv: 0:0:0
cmyk2rgb: 255:255:255
cmyk2hsl: 0:0:100
cmyk2hex: 16777215
cmyk2hsv: 0:0:100
