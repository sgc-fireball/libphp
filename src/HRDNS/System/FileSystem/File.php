<?php

namespace HRDNS\System\FileSystem;

class File extends \SplFileObject
{

    /** @var integer */
    protected $tailSeek = -1;

    /**
     * @param mixed $file_name
     * @param string $open_mode
     * @param boolean $use_include_path
     * @param resource $context
     */
    public function __construct($file_name, $open_mode = 'a+', $use_include_path = false, $context = null)
    {
        parent::__construct($file_name, $open_mode, $use_include_path, $context);
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
     * @param string $open_mode
     * @param boolean $use_include_path
     * @param resource $context
     * @return File
     */
    public function openFile($open_mode = 'a+', $use_include_path = false, $context = null)
    {
        $className = get_class($this);
        return new $className($this->getPathname($open_mode, $use_include_path, $context));
    }

}
