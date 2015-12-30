<?php

namespace HRDNS\System\Process;

/**
 * Class ProcessList
 *
 * @package HRDNS\System\Process
 */
class ProcessList implements \Iterator
{

    /**
     * @var Process[]
     */
    protected $processes = array();

    /**
     * @var int
     */
    protected $worker = 2;

    /**
     * @return void
     */
    public function rewind()
    {
        reset($this->processes);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->processes);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->processes);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return next($this->processes);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * @param Process $process
     * @return self
     */
    public function addProcess(Process $process)
    {
        $id = $process->getId();
        $this->processes[$id] = $process;

        return $this;
    }

    /**
     * @param int $worker
     * @return self
     */
    public function setWorker($worker)
    {
        $worker = (int)$worker;
        $this->worker = $worker ?: 1;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRunning()
    {
        /** @var Process $process */
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return self
     */
    public function start()
    {
        $active = 0;
        /** @var Process $process */
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $active++;
            }
            if ($active >= $this->worker) {
                return $this;
            }
            if ($process->start()) {
                $active++;
            }
        }

        return $this;
    }

    /**
     * @return Process|null
     */
    public function getFreeProcess()
    {
        /** @var Process $process */
        foreach ($this->processes as $process) {
            if (!$process->isRunning()) {
                return $process;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getStats()
    {
        $stats = array(
            'worker' => $this->worker,
            'count' => count($this->processes),
            'running' => 0,
            'stopped' => 0,
            'processes' => array(),
        );
        /**
         * @var string $id
         * @var Process $process
         */
        foreach ($this->processes as $id => $process) {
            $stats['process'][$id] = array(
                'running' => $process->isRunning(),
                'pid' => $process->getPid(),
            );
            if ($stats['process'][$id]['running']) {
                $stats['running']++;
            } else {
                $stats['stopped']++;
            }
        }

        return $stats;
    }

    /**
     * @param integer  $signal
     * @param integer  $sec
     * @return self
     */
    public function stop($signal = null, $sec = 3)
    {
        $signal = $signal === null ? SIGTERM : $signal;
        /** @var Process $process */
        foreach ($this->processes as $process) {
            $process->stop($sec);
        }

        return $this;
    }

}
