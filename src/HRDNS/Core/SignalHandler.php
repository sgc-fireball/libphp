<?php

namespace HRDNS\Core;

class SignalHandler
{

    const SIGNULL = 0;
    const SIGHUP = 1;
    const SIGINT = 2;
    const SIGQUIT = 3;
    const SIGILL = 4;
    const SIGTRAP = 5;
    const SIGABRT = 6;
    const SIGBUS = 7;
    const SIGFPE = 8;
    const SIGKILL = 9;
    const SIGUSR1 = 10;
    const SIGSEGV = 11;
    const SIGUSR2 = 12;
    const SIGPIPE = 13;
    const SIGALRM = 14;
    const SIGTERM = 15;
    const SIGSTKFLT = 16;
    const SIGCHLD = 17;
    const SIGCONT = 18;
    const SIGSTOP = 19;
    const SIGTSTP = 20;
    const SIGTTIN = 21;
    const SIGTTOU = 22;
    const SIGURG = 23;
    const SIGXCPU = 24;
    const SIGXFSZ = 25;
    const SIGVTALRM = 26;
    const SIGPROF = 27;
    const SIGWINCH = 28;
    const SIGIO = 29;
    const SIGPWR = 30;
    const SIGUNUSED = 31;

    /** @var bool */
    static protected $terminated = false;

    /** @var callable[] */
    static protected $signalHandler = [];

    /** @var string[] */
    static protected $signalNames = array(
        1 => 'SIGHUP',
        2 => 'SIGINT',
        3 => 'SIGQUIT',
        4 => 'SIGILL',
        5 => 'SIGTRAP',
        6 => 'SIGABRT',
        7 => 'SIGBUS',
        8 => 'SIGFPE',
        10 => 'SIGUSR1',
        11 => 'SIGSEGV',
        12 => 'SIGUSR2',
        13 => 'SIGPIPE',
        14 => 'SIGALRM',
        15 => 'SIGTERM',
        16 => 'SIGSTKFLT',
        17 => 'SIGCHLD',
        18 => 'SIGCONT',
        20 => 'SIGTSTP',
        21 => 'SIGTTIN',
        22 => 'SIGTTOU',
        23 => 'SIGURG',
        24 => 'SIGXCPU',
        25 => 'SIGXFSZ',
        26 => 'SIGVTALRM',
        27 => 'SIGPROF',
        28 => 'SIGWINCH',
        29 => 'SIGIO',
        30 => 'SIGPWR',
        31 => 'SIGUNUSED',
        0 => 'SIGNULL',
        9 => 'SIGKILL',
        19 => 'SIGSTOP',
    );

    /**
     * @return boolean
     */
    public static function hasTerminated(): bool
    {
        return self::$terminated;
    }

    public static function init()
    {
        foreach (array_keys(self::$signalNames) as $signal) {
            if (in_array($signal, array(self::SIGNULL, self::SIGKILL, self::SIGSTOP))) {
                continue;
            }
            pcntl_signal($signal, array(__CLASS__, 'fireSignalHandler'));
        }
    }

    /**
     * @param integer $signal
     * @return void
     */
    public static function fireSignalHandler(int $signal)
    {
        $terminated = !in_array($signal, array(self::SIGCHLD, self::SIGWINCH));
        foreach (self::$signalHandler as $fnc) {
            if (!call_user_func($fnc, $signal)) {
                $terminated = false;
            }
        }
        if ($terminated) {
            self::$terminated = true;
        }
    }

    /**
     * @param callable $fnc
     * @return string
     */
    public static function addListener(callable $fnc): string
    {
        $listenerId = uniqid('signal_handler');
        self::$signalHandler[$listenerId] = $fnc;
        return $listenerId;
    }

    /**
     * @param integer $listenerId
     * @return void
     */
    public static function removeListener(int $listenerId)
    {
        if (isset(self::$signalHandler[$listenerId])) {
            unset(self::$signalHandler[$listenerId]);
        }
    }

}
