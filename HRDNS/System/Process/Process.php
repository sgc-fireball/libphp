<?php

namespace HRDNS\System\Process;

/**
 * Class Process
 *
 * @package HRDNS\System\Process
 */
class Process
{

    const EXITCODE_SUCCESSFUL = 0;
    const EXITCODE_FAILED = 1;
    const EXITCODE_NOCOMMAND = 254;
    const EXITCODE_NOT_STARTED = 1024;
    const EXITCODE_EXCEPTION = 1025;
    const EXITCODE_KILLED = 1026;

    /** @var array */
    protected $options = array();

    /** @var string */
    protected $id = null;

    /** @var integer  */
    protected $pid = 0;

    /** @var integer  */
    protected $exitCode = self::EXITCODE_NOT_STARTED;

    /** @var callable|null */
    protected $command = null;

    /** @var float */
    protected $startTime = 0.0;

    /** @var float */
    protected $endTime = 0.0;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->command = function (Process $process) {
            return $process::EXITCODE_NOCOMMAND;
        };
        $this->selfClone();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function addOption($key, $value)
    {
        $this->options[(string)$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @return null
     */
    public function getOption($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    /**
     * @param callable|string $command
     * @return self|boolean
     */
    public function setCommand($command)
    {
        if (is_callable($command)) {
            $this->command = $command;

            return $this;
        }
        if (is_string($command) && file_exists($command) && is_readable($command)) {
            $this->command = function (Process $process) use ($command) {
                return require_once($command);
            };

            return $this;
        }

        return false;
    }

    /**
     * @return integer
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return integer
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @return boolean
     */
    public function isRunning()
    {
        if ($this->pid == 0) {
            return false;
        }
        $pid = pcntl_waitpid($this->pid, $status, WNOHANG);
        if ($status > 0 || $pid == -1) {
            $this->endTime = microtime(true);
            $this->exitCode = pcntl_wexitstatus($status);
            $this->pid = 0;

            return false;
        }

        return true;
    }

    /**
     * @param array $whiteList
     * @return self
     */
    public function start(array $whiteList = array())
    {
        $this->startTime = microtime(true);
        $this->pid = pcntl_fork();
        if ($this->pid == -1) {
            trigger_error('Could not fork.', E_CORE_ERROR);
            exit(255);
        } else {
            if ($this->pid) {
                return $this;
            }
        }
        $this->pid = posix_getpid();
        $whiteList = array_merge($whiteList, array('this', 'whiteList'));
        $vars = array_keys(get_defined_vars());
        foreach ($vars as $var) {
            if (in_array($var, $whiteList)) {
                continue;
            }
            unset($$var);
        }
        unset($vars);
        unset($whiteList);
        try {
            $command = $this->command;
            $exitCode = (int)$command($this);
        } catch (\Exception $e) {
            $exitCode = $e->getCode();
        }
        $exitCode = $exitCode < 0 ? 0 : $exitCode;
        $exitCode = $exitCode > 255 ? 255 : $exitCode;
        exit($exitCode);
    }

    /**
     * @param integer  $signal
     * @param integer  $sec
     * @return boolean
     */
    public function stop($signal = null, $sec = 3)
    {
        $signal = $signal === null ? SIGTERM : $signal;
        if (!$this->isRunning()) {
            return true;
        }
        $round = 0;
        do {
            posix_kill($this->pid, $signal);
            $round++;
            sleep(1);
        } while ($this->isRunning() && $round < $sec);
        if ($this->pid > 0) {
            posix_kill($this->pid, SIGKILL);
        }
        $this->exitCode = self::EXITCODE_KILLED;

        return !$this->isRunning();
    }

    /**
     * clone
     *
     * @return void
     */
    public function __clone()
    {
        $this->selfClone();
    }

    /**
     * @return void
     */
    protected function selfClone()
    {
        $this->pid = 0;
        $this->exitCode = self::EXITCODE_NOT_STARTED;
        $this->startTime = 0;
        $this->endTime = 0;
        $this->id = spl_object_hash($this);
    }

}
