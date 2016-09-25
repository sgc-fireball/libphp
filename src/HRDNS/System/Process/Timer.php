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
    protected $timeouts = [];

    /**
     * @var int
     */
    protected $lastIntervalCheck = 0;

    /**
     * @var array
     */
    protected $intervals = [];

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        \register_tick_function(array(self::$instance, 'tick'));
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
        $this->checkTimeouts()->checkIntervals();
    }

    /**
     * checkTimeouts
     *
     * @return self
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function checkTimeouts(): self
    {
        if (!count($this->timeouts)) {
            return $this;
        }
        if ($this->lastTimeoutCheck === $this->currentTime) {
            return $this;
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
        return $this;
    }

    /**
     * checkIntervals
     *
     * @return self
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function checkIntervals(): self
    {
        if (!count($this->intervals)) {
            return $this;
        }
        if ($this->lastIntervalCheck === $this->currentTime) {
            return $this;
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
        return $this;
    }

    /**
     * @param callable $fnc
     * @param integer $timeoutTime
     * @return string
     * @throws \InvalidArgumentException
     */
    public function addTimeout(callable $fnc, int $timeoutTime = 1): string
    {
        if (!is_callable($fnc)) {
            throw new \InvalidArgumentException('Argument one must be callable!');
        }
        $timeout = new Timer\Timeout(
            array(
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
    public function clearTimeout(string $timerId): self
    {
        if (isset($this->timeouts[$timerId])) {
            unset($this->timeouts[$timerId]);
        }
        return $this;
    }

    /**
     * @param callable $fnc
     * @param integer $intervalTime
     * @return string
     * @throws \InvalidArgumentException
     */
    public function addInterval(callable $fnc, int $intervalTime = 1): string
    {
        if (!is_callable($fnc)) {
            throw new \InvalidArgumentException('Argument one must be callable!');
        }
        $interval = new Timer\Interval(
            array(
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
    public function clearInterval(string $intervalId): self
    {
        if (isset($this->intervals[$intervalId])) {
            unset($this->intervals[$intervalId]);
        }
        return $this;
    }

}
