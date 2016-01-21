<?php

namespace HRDNS\System\FileSystem;

class File extends \SplFileObject
{

    /** @var integer */
    protected $tailSeek = -1;

    /**
     * @param mixed $fileName
     * @param string $openMode
     * @param boolean $useIncludePath
     * @param resource $context
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct($fileName, $openMode = 'a+', $useIncludePath = false, $context = null)
    {
        parent::__construct($fileName, $openMode, $useIncludePath, $context);
    }

    /**
     * @param integer $length
     * @return mixed
     */
    public function read($length)
    {
        if (version_compare(phpversion(), '5.5.11', '<')) {
            trigger_error(
                sprintf(
                    '%s is not supported on PHP %s < 5.5.11.',
                    __METHOD__,
                    phpversion()
                ),
                E_USER_WARNING
            );
            $buffer = '';
            while (strlen($buffer) < $length && !$this->eof()) {
                $buffer .= $this->fgets();
            }
            return $buffer;
        }
        return parent::fread($length);
    }

    /**
     * @param mixed $string
     * @param integer|null $length
     * @return integer
     */
    public function write($string, $length = null)
    {
        return parent::fwrite($string, $length ?: mb_strlen($string));
    }

    /**
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
    public function unlink()
    {
        return unlink($this->getPathname());
    }

    /**
     * @param string $openMode
     * @param boolean $useIncludePath
     * @param resource $context
     * @return File
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function openFile($openMode = 'a+', $useIncludePath = false, $context = null)
    {
        $className = get_class($this);
        return new $className($this->getPathname($openMode, $useIncludePath, $context));
    }

}
