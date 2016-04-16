<?php

namespace HRDNS\System\FileSystem;

class TSFile extends File
{

    /**
     * @todo fix mixed return types!
     * @param int $length
     * @return bool|mixed
     */
    public function read(int $length)
    {
        if ($this->flock(LOCK_EX) === false) {
            return false;
        }
        $content = parent::read($length);
        if ($this->flock(LOCK_UN) === false) {
            return false;
        }

        return $content;
    }

    /**
     * @todo fix mixed return types!
     * @param string $string
     * @param int $length
     * @return bool|int
     */
    public function write($string, int $length = null)
    {
        if ($this->flock(LOCK_EX) === false) {
            return false;
        }
        $bytes = parent::write($string, $length ?: mb_strlen($string));
        if ($this->flock(LOCK_UN) === false) {
            return false;
        }

        return $bytes;
    }

}
