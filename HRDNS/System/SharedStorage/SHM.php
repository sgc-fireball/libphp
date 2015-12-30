<?php

namespace HRDNS\System\SharedStorage;

class SHM
{

    /** @var integer  */
    protected $shmKey = 0;

    /**
     * @return integer
     */
    public function getKey()
    {
        return $this->shmKey;
    }

    /**
     * @param integer  $shmKey
     */
    public function __construct($shmKey = 0)
    {
        $this->shmKey = $shmKey ? (int)$shmKey : mt_rand(1, 65535);
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        $shmId = @shmop_open($this->shmKey, 'a', 0, 0);
        if ($shmId) {
            shmop_close($shmId);

            return true;
        }

        return false;
    }

    /**
     * @return null|string
     */
    public function read()
    {
        if (!$this->exists()) {
            return null;
        }
        $shmId = shmop_open($this->shmKey, 'w', 0, 0);
        if (!$shmId) {
            return null;
        }
        $size = shmop_size($shmId);
        if (!$size) {
            return null;
        }
        $data = shmop_read($shmId, 0, $size);
        shmop_close($shmId);

        return (string)$data;
    }

    /**
     * @param string $data
     * @return boolean
     */
    public function write($data)
    {
        $data = (string)$data;
        if ($this->exists($this->shmKey)) {
            $this->delete();
        }
        $size = mb_strlen($data, 'UTF-8');
        $shmId = shmop_open($this->shmKey, 'c', 666, $size);
        if (!$shmId) {
            return false;
        }
        shmop_write($shmId, $data, 0);
        shmop_close($shmId);

        return true;
    }

    /**
     * @return boolean
     */
    public function delete()
    {
        if (!$this->exists()) {
            return true;
        }
        $shmId = shmop_open($this->shmKey, 'w', 0, 0);
        $result = (bool)shmop_delete($shmId);
        shmop_close($shmId);

        return $result;
    }

}
