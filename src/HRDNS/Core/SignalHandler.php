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

    static protected $terminated = false;

    static protected $signalHandler = array();

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

    public static function hasTerminated()
    {
        return self::$terminated;
    }

    public static function init()
    {
        foreach (self::$signalNames as $signal => &$name) {
            if (in_array($signal, array(self::SIGNULL, self::SIGKILL, self::SIGSTOP))) {
                continue;
            }
            pcntl_signal($signal, array(__CLASS__, '__signalHandler'));
        }
    }

    public static function __signalHandler($signal)
    {
        $terminated = !in_array($signal, array(self::SIGCHLD, self::SIGWINCH));
        foreach (self::$signalHandler as $id => &$fnc) {
            if (!call_user_func($fnc, $signal)) {
                $terminated = false;
            }
        }
        if ($terminated) {
            self::$terminated = true;
        }
    }

    public static function addListner($fnc)
    {
        if (!is_callable($fnc)) {
            trigger_error(sprintf('%s :: parameter 1 must be callable!', __METHOD__), E_USER_ERROR);

            return false;
        }
        $id = uniqid('signal_handler');
        self::$signalHandler[$id] = $fnc;

        return $id;
    }

    public static function removeListner($id)
    {
        if (isset(self::$signalHandler[$id])) {
            unset(self::$signalHandler[$id]);
        }

        return true;
    }

}
