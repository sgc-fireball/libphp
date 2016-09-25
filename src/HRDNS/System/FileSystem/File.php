<?php

namespace HRDNS\System\FileSystem;

class File extends \SplFileObject
{

    /** @var int */
    protected $tailSeek = -1;

    /**
     * @todo fix mixed return types!
     * @param integer $length
     * @return string|boolean
     */
    public function read(int $length)
    {
        return parent::fread($length);
    }

    /**
     * @todo fix mixed return types!
     * @param mixed $string
     * @param integer|null $length
     * @return integer|boolean
     */
    public function write($string, int $length = null)
    {
        return parent::fwrite($string, $length ?: mb_strlen($string));
    }

    /**
     * @todo fix mixed return types!
     * @return boolean|string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function tail()
    {
        if (version_compare(phpversion(), '5.5.11', '<')) {
            trigger_error(
                sprintf(
                    '%s is not supported on PHP %s < 5.5.11.',
                    __METHOD__,
                    phpversion()
                ),
                E_USER_ERROR
            );
            return false;
        }

        $file = new File($this->getPathname(), 'r');
        if ($file->fseek(0, SEEK_END) == -1) {
            return false;
        }
        $end = $file->ftell();
        if ($end === false) {
            return false;
        }
        if ($this->tailSeek === -1) {
            $this->tailSeek = $end;
        }
        if ($end < 0) {
            return '';
        }
        if ($end <= $this->tailSeek) {
            return '';
        }
        if ($file->fseek($this->tailSeek) == -1) {
            return false;
        }
        $content = $file->fread(4096);
        if ($content === false) {
            return false;
        }
        $this->tailSeek = $file->ftell();
        unset($file);

        return $content;
    }

    /**
     * @return boolean
     */
    public function unlink(): bool
    {
        return unlink($this->getPathname());
    }

}
