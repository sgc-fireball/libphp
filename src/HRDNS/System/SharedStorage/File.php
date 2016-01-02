<?php

namespace HRDNS\System\SharedStorage;

class File
{

    /** @var integer */
    protected $file = null;

    /** @var resource */
    protected $fp = null;

    /**
     * @return integer
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function __construct($file = null)
    {
        $this->file = $file ? (string)$file : sprintf(
            '%s%sshm_%s.tmp',
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            sha1(uniqid('', true))
        );
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->file);
    }

    /**
     * @return null|string
     */
    public function read()
    {
        if (!$this->exists()) {
            return null;
        }
        if (!$this->selfLock('r')) {
            return null;
        }
        fseek($this->fp, 0, SEEK_END);
        $size = ftell($this->fp);
        if ($size === 0) {
            $this->selfUnlock();

            return null;
        }
        fseek($this->fp, 0, SEEK_SET);
        $data = (string)fread($this->fp, $size);
        $this->selfUnlock();

        return $data;
    }

    /**
     * @param string $data
     * @return boolean
     */
    public function write($data)
    {
        $data = (string)$data;
        if (!$this->selfLock('w')) {
            return false;
        }
        $result = fwrite($this->fp, $data, mb_strlen($data, 'UTF-8')) !== false;
        $this->selfUnlock();

        return $result;
    }

    /**
     * @return boolean
     */
    public function delete()
    {
        if (!$this->exists()) {
            return true;
        }
        $this->selfUnlock();

        return (bool)unlink($this->file);
    }

    /**
     * @param string $mode
     * @return boolean
     */
    protected function selfLock($mode)
    {
        $this->fp = @fopen($this->file, $mode);
        $errors = 0;
        while (!@flock($this->fp, LOCK_EX | LOCK_SH)) {
            usleep(5000);
            $errors ++;
            if ($errors > 10) {
                @fclose($this->fp);
                $this->fp = false;
            }
        }

        return is_resource($this->fp);
    }

    /**
     * @return void
     */
    protected function selfUnlock()
    {
        if (is_resource($this->fp)) {
            @fclose($this->fp);
        }
        $this->fp = null;
    }

}
