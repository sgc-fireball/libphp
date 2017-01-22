<?php

namespace HRDNS\Core;

class EventHandler
{

    /** @var static */
    private static $instance = null;

    /** @var array[] */
    private $events = [];

    /**
     * @return self
     */
    public static function get(): self
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $self = $this;
        $this->prepareEvent('tick');
        $this->prepareEvent('shutdown');
        \register_tick_function(
            function () use ($self) {
                $self->fireEvent('tick');
            }
        );
        register_shutdown_function(
            function () use ($self) {
                $self->fireEvent('shutdown');
            }
        );
    }

    /**
     * @param string $name
     * @param integer $priority
     * @return self
     */
    private function prepareEvent(string $name, int $priority = 0): self
    {
        $priority = (int)$priority;
        if (!isset($this->events[$name])) {
            $this->events[$name] = [];
        }
        if (!isset($this->events[$name][$priority])) {
            $this->events[$name][$priority] = [];
        }
        ksort($this->events[$name], SORT_NUMERIC);
        return $this;
    }

    /**
     * @param string $name
     * @param callable $callable
     * @param integer $priority
     * @return boolean
     */
    public function addEvent(string $name, callable $callable, int $priority = 0): bool
    {
        $this->prepareEvent($name, $priority);
        $this->events[$name][$priority][] = $callable;
        return true;
    }

    /**
     * @param string $name
     * @param EventInterface|null $event
     * @return self
     */
    public function fireEvent(string $name, EventInterface $event = null): self
    {
        if (!isset($this->events[$name])) {
            return $this;
        }
        $event = $event instanceof EventInterface ? $event : new Event();
        foreach ($this->events[$name] as $priorities) {
            foreach ($priorities as $callable) {
                $callable($event);
                if (!$event->isPropagationStopped()) {
                    return $this;
                }
            }
        }
        return $this;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function __sleep()
    {
        throw new \Exception('It is not allow to call ' . __METHOD__);
    }

}
