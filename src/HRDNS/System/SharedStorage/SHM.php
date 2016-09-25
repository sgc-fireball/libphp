<?php

namespace HRDNS\System\SharedStorage;

class SHM
{

    /** @var int */
    protected $shmKey = 0;

    /**
     * @return integer
     */
    public function getKey(): int
    {
        return $this->shmKey;
    }

    /**
     * @param integer $shmKey
     * @return void
     */
    public function __construct(int $shmKey = 0)
    {
        $this->shmKey = $shmKey ? (int)$shmKey : mt_rand(1, 65535);
    }

    /**
     * @return boolean
     */
    public function exists(): bool
    {
        $shmId = @shmop_open($this->shmKey, 'a', 0, 0);
        if ($shmId) {
            @shmop_close($shmId);
            return true;
        }
        return false;
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
        $shmId = @shmop_open($this->shmKey, 'w', 0, 0);
        if (!$shmId) {
            return null;
        }
        $size = @shmop_size($shmId);
        if (!$size) {
            return null;
        }
        $data = @shmop_read($shmId, 0, $size);
        @shmop_close($shmId);
        return (string)$data;
    }

    /**
     * @param string $data
     * @return boolean
     */
    public function write(string $data): bool
    {
        $data = (string)$data;
        if ($this->exists($this->shmKey)) {
            $this->delete();
        }
        $size = mb_strlen($data, 'UTF-8');
        $shmId = @shmop_open($this->shmKey, 'c', 0666, $size);
        if (!$shmId) {
            return false;
        }
        if (@shmop_write($shmId, $data, 0)===false) {
            return false;
        }
        @shmop_close($shmId);
        return true;
    }

    /**
     * @return boolean
     */
    public function delete(): bool
    {
        if (!$this->exists()) {
            return true;
        }
        $shmId = @shmop_open($this->shmKey, 'w', 0, 0);
        $result = (bool)@shmop_delete($shmId);
        @shmop_close($shmId);
        return $result;
    }

}
