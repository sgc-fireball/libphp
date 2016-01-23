<?php

namespace HRDNS\System\Process;

declare(ticks = 100);

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
    protected $timeouts = array ();

    /**
     * @var int
     */
    protected $lastIntervalCheck = 0;

    /**
     * @var array
     */
    protected $intervals = array ();

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        \register_tick_function(array (self::$instance, 'tick'));

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
     * @SuppressWarnings(PHPMD.ElseExpression)
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
                $fnc = $timeout->func;
                $fnc();
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
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function checkIntervals()
    {
        if (!count($this->intervals)) {
            return;
        }
        if ($this->lastIntervalCheck === $this->currentTime) {
            return;
        }
        /** @var Timer\Interval $interval */
        foreach ($this->intervals as $interval) {
            if (($this->currentTime - $interval->interval) < $interval->lastRun) {
                continue;
            }
            if (is_callable($interval->func)) {
                $fnc = $interval->func;
                $fnc();
            } else {
                call_user_func($interval->func);
            }
            $interval->lastRun = $this->currentTime;
        }
        $this->lastIntervalCheck = $this->currentTime;
    }

    /**
     * @param callable|string $fnc
     * @param integer $timeoutTime
     * @return string|boolean
     */
    public function addTimeout($fnc, $timeoutTime = 1)
    {
        if (!is_callable($fnc)) {
            trigger_error(sprintf('%s :: parameter 1 must be callable or a function name!!', __METHOD__), E_USER_ERROR);
            return false;
        }
        $timeout = new Timer\Timeout(
            array (
                'func' => $fnc,
                'run' => $this->currentTime + (int)($timeoutTime * 1000)
            )
        );
        $this->timeouts[$timeout->id] = $timeout;
        return $timeout->id;
    }

    /**
     * @param string $timerId
     * @return self
     */
    public function clearTimeout($timerId)
    {
        if (isset($this->timeouts[$timerId])) {
            unset($this->timeouts[$timerId]);
        }
        return $this;
    }

    /**
     * @param callable $fnc
     * @param integer $intervalTime
     * @return string|boolean
     */
    public function addInterval(callable $fnc, $intervalTime = 1)
    {
        if (!is_callable($fnc)) {
            trigger_error(sprintf('%s :: parameter 1 must be callable or a function name!!', __METHOD__), E_USER_ERROR);
            return false;
        }
        $interval = new Timer\Interval(
            array (
                'func' => $fnc,
                'lastRun' => $this->currentTime,
                'interval' => (int)($intervalTime * 1000)
            )
        );
        $this->intervals[$interval->id] = $interval;
        return $interval->id;
    }

    /**
     * @param string $intervalId
     * @return self
     */
    public function clearInterval($intervalId)
    {
        if (isset($this->intervals[$intervalId])) {
            unset($this->intervals[$intervalId]);
        }

        return $this;
    }

}
