<?php declare(strict_types=1);

namespace HRDNS\General\Color;

interface XTermConverterInterface
{

    /**
     * @return array
     */
    public static function getMap(): array;

    /**
     * @param integer $xterm
     * @return string
     */
    public function xterm2hex(int $xterm): string;

    /**
     * @param string $hexIn
     * @return integer
     */
    public function hex2xterm(string $hexIn): int;

}
