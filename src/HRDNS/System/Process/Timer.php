<?php

namespace HRDNS\System\Process;

use HRDNS\System\Process\Timer\Timeout;

declare(ticks = 10);

class Timer
{

    /**
     * @static
     * @var self
     */
    protected static $instance = null;

    /**
     * @var int
     */
    protected $startTime = 0;

    /**
     * @var int
     */
    protected $currentTime = 0;

    /**
     * @var int
     */
    protected $durotationTime = 0;

    /**
     * @var int
     */
    protected $lastTimeoutCheck = 0;

    /**
     * @var array
     */
    protected $timeouts = array();

    /**
     * @var int
     */
    protected $lastIntervalCheck = 0;

    /**
     * @var array
     */
    protected $intervals = array();

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        register_tick_function(array(self::$instance, 'tick'));

        return self::$instance;
    }

    /**
     * construct
     *
     * @return void
     */
    protected function __construct()
    {
        $this->currentTime = (int)(microtime(true) * 1000);
        $this->startTime = $this->currentTime;
    }

    /**
     * tick
     *
     * @return void
     */
    public function tick()
    {
        $this->currentTime = (int)(microtime(true) * 1000);
        $this->durotationTime = $this->currentTime - $this->startTime;
        $this->checkTimeouts();
        $this->checkIntervals();
    }

    /**
     * checkTimeouts
     *
     * @return void
     */
    protected function checkTimeouts()
    {
        if (!count($this->timeouts)) {
            return;
        }
        if ($this->lastTimeoutCheck === $this->currentTime) {
            return;
        }
        $timeouts = array_keys($this->timeouts);
        foreach ($timeouts as $id) {
            /** @var Timer\Timeout $timeout */
            $timeout = $this->timeouts[$id];
            if ($this->currentTime < $timeout->run) {
                continue;
            }
            if (is_callable($timeout->func)) {
                $func = $timeout->func;
                $func();
            } else {
                call_user_func($timeout->func);
            }
            unset($this->timeouts[$id]);
        }
        $this->lastTimeoutCheck = $this->currentTime;
    }

    /**
     * checkIntervals
     *
     * @return void
     */
    protected function checkIntervals()
    {
        if (!count($this->intervals)) {
            return;
        }
        if ($this->lastIntervalCheck === $this->currentTime) {
            return;
        }
        /**
         * @var string $id
         * @var Timer\Interval $interval
         */
        foreach ($this->intervals as $id => $interval) {
            if (($this->currentTime - $interval->interval) < $interval->lastRun) {
                continue;
            }
            if (is_callable($interval->func)) {
                $func = $interval->func;
                $func();
            } else {
                call_user_func($interval->func);
            }
            $interval->lastRun = $this->currentTime;
        }
        $this->lastIntervalCheck = $this->currentTime;
    }

    /**
     * @param callable|array|string $func
     * @param integer  $timeoutTime
     * @return mixed
     */
    public function addTimeout($func, $timeoutTime = 1)
    {
        $timeout = new Timer\Timeout(
            array(
                'func' => $func,
                'run' => $this->currentTime + (int)($timeoutTime * 1000)
            )
        );
        $this->timeouts[$timeout->id] = $timeout;

        return $timeout->id;
    }

    /**
     * @param string $id
     * @return self
     */
    public function clearTimeout($id)
    {
        if (isset($this->timeouts[$id])) {
            unset($this->timeouts[$id]);
        }

        return $this;
    }

    /**
     * @param callable|array|string $func
     * @param integer  $intervalTime
     * @return mixed
     */
    public function addInterval($func, $intervalTime = 1)
    {
        $interval = new Timer\Interval(
            array(
                'func' => $func,
                'lastRun' => $this->currentTime,
                'interval' => (int)($intervalTime * 1000)
            )
        );
        $this->intervals[$interval->id] = $interval;

        return $interval->id;
    }

    /**
     * @param string $id
     * @return self
     */
    public function clearInterval($id)
    {
        if (isset($this->intervals[$id])) {
            unset($this->intervals[$id]);
        }

        return $this;
    }

}
