<?php

namespace HRDNS\System\FileSystem;

class TSFile extends File
{

    public function read($length)
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

    public function write($string, $length = null)
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
