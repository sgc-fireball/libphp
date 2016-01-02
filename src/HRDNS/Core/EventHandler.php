<?php

namespace HRDNS\Core;

class EventHandler
{

    /** @var static */
    private static $instance = null;

    /** @var array[] */
    private $events = array ();

    public static function get()
    {
        if (!(self::$instance instanceof static)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $self = $this;
        $this->prepareEvent('tick');
        $this->prepareEvent('shutdown');
        register_tick_function(
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

    private function prepareEvent($name, $priority = 0)
    {
        $priority = (int)$priority;
        if (!isset($this->events[$name])) {
            $this->events[$name] = array ();
        }
        if (!isset($this->events[$name][$priority])) {
            $this->events[$name][$priority] = array ();
        }
        ksort($this->events[$name], SORT_NUMERIC);
    }

    public function addEvent($name, $callable, $priority = 0)
    {
        $priority = (int)$priority;
        if (!is_callable($callable)) {
            return false;
        }
        $this->prepareEvent($name, $priority);
        $this->events[$name][$priority][] = $callable;
        return true;
    }

    public function fireEvent($name, EventInterface $event = null)
    {
        if (!isset($this->events[$name])) {
            return;
        }
        $event = $event instanceof EventInterface ? $event : new Event();
        foreach ($this->events[$name] as $priorities) {
            foreach ($priorities as $callable) {
                $callable($event);
                if (!$event->getPropagationStatus()) {
                    return;
                }
            }
        }
    }

    public function __sleep()
    {
        throw new \Exception('It is not allow to call ' . __METHOD__);
    }

}
