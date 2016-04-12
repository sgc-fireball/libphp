<?php

namespace HRDNS\System\SharedStorage;

class File
{

    /** @var integer */
    protected $file = null;

    /** @var resource */
    protected $filePointer = null;

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
    public function __construct(string $file = null)
    {
        $this->file = $file ? $file : sprintf(
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
        fseek($this->filePointer, 0, SEEK_END);
        $size = ftell($this->filePointer);
        if ($size === 0) {
            $this->selfUnlock();

            return null;
        }
        fseek($this->filePointer, 0, SEEK_SET);
        $data = (string)fread($this->filePointer, $size);
        $this->selfUnlock();

        return $data;
    }

    /**
     * @param string $data
     * @return boolean
     */
    public function write(string $data)
    {
        if (!$this->selfLock('w')) {
            return false;
        }
        $result = fwrite($this->filePointer, $data, mb_strlen($data, 'UTF-8')) !== false;
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
    protected function selfLock(string $mode)
    {
        $this->filePointer = @fopen($this->file, $mode);
        $errors = 0;
        while (!@flock($this->filePointer, LOCK_EX | LOCK_SH)) {
            usleep(5000);
            $errors++;
            if ($errors > 10) {
                @fclose($this->filePointer);
                $this->filePointer = false;
            }
        }

        return is_resource($this->filePointer);
    }

    /**
     * @return void
     */
    protected function selfUnlock()
    {
        if (is_resource($this->filePointer)) {
            @fclose($this->filePointer);
        }
        $this->filePointer = null;
    }

}
