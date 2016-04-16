<?php

namespace HRDNS\System\SharedStorage;

class File
{

    /** @var string */
    protected $file = '';

    /** @var resource */
    protected $filePointer = null;

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return void
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
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->file);
    }

    /**
     * @todo fix mixed return types!
     * @return string|null
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
     * @todo fix mixed return types!
     * @param string $data
     * @return int|bool
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
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->exists()) {
            return true;
        }
        $this->selfUnlock();
        return (bool)unlink($this->file);
    }

    /**
     * @param string $mode
     * @return bool
     */
    protected function selfLock(string $mode): bool
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
