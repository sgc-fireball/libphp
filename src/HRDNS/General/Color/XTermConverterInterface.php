<?php

namespace HRDNS\General\Color;

interface XTermConverterInterface
{

    /**
     * @return mixed
     */
    public static function getMap(): array;

    /**
     * @param int $xterm
     * @return string
     */
    public function xterm2hex(int $xterm): string;

    /**
     * @param string $hexIn
     * @return int
     */
    public function hex2xterm(string $hexIn): int;

}
