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
    protected $processes = [];

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
    public function valid(): bool
    {
        return $this->current() !== false;
    }

    /**
     * @param Process $process
     * @return self
     */
    public function addProcess(Process $process): self
    {
        $this->processes[$process->getId()] = $process;
        return $this;
    }

    /**
     * @param integer $worker
     * @return ProcessList
     * @throws \InvalidArgumentException
     */
    public function setWorker(int $worker): self
    {
        if ($worker < 1) {
            throw new \InvalidArgumentException('Invalid value '.$worker.' count. Minimum 1 worker must be exist.');
        }
        $this->worker = $worker;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRunning(): bool
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
    public function start(): self
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
     * @todo fix mixed return types!
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
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getStats(): array
    {
        $stats = array(
            'worker' => $this->worker,
            'count' => count($this->processes),
            'running' => 0,
            'stopped' => 0,
            'processes' => [],
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
     * @param integer $signal
     * @param integer $sec
     * @return self
     */
    public function stop(int $signal = null, int $sec = 3)
    {
        $signal = $signal === null ? SIGTERM : $signal;
        /** @var Process $process */
        foreach ($this->processes as $process) {
            $process->stop($signal, $sec);
        }
        return $this;
    }

}
