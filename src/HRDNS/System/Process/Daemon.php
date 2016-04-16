<?php

namespace HRDNS\System\Process;

use HRDNS\System\FileSystem\File;
use Psr\Log\LoggerInterface;

/**
 * Class Daemon
 *
 * @package HRDNS\System\Process
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Daemon implements LoggerInterface
{

    /**
     * @static
     * @var self|null
     */
    protected static $instance = null;

    /** @var int */
    protected $userId = -1;

    /** @var int */
    protected $groupId = -1;

    /** @var string */
    protected $fqdn = 'localhost.local';

    /** @var mixed|string */
    protected $hostname = 'localhost';

    /** @var mixed|string */
    protected $processName = 'Daemon';

    /** @var int */
    protected $pid = -1;

    /** @var int */
    protected $parentPid = -1;

    /** @var string */
    protected $pidPath = '/var/run';

    /** @var File */
    protected $pidFile = null;

    /** @var string */
    protected $instanceName = 'daemon';

    /** @var LoggerInterface */
    protected $logger = null;

    /**
     * @static
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();

        return self::$instance;
    }

    /**
     * @private
     */
    private function __construct()
    {
        $system = posix_uname();
        $backtrace = debug_backtrace();
        $starter = array_pop($backtrace);
        unset($backtrace);
        $startFile = isset($starter['file']) ? $starter['file'] : 'Daemon';
        $startFile = $startFile == '-' ? 'Daemon' : $startFile;
        unset($starter);

        $this->fqdn = $system['nodename'];
        $this->hostname = preg_replace('/\..*/', '', $this->fqdn);
        $this->processName = str_replace(array('.phpt', '.php'), '', $startFile);
        $this->userId = posix_getuid();
        $this->groupId = posix_getgid();
        $this->pid = posix_getpid();
        $this->parentPid = posix_getppid();
        $this->pidPath = sprintf('/var/run/%s', $this->processName);
    }

    /**
     * @return string
     */
    public function getFqdn(): string
    {
        return $this->fqdn;
    }

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->groupId;
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @return int
     */
    public function getParentPid(): int
    {
        return $this->parentPid;
    }

    /**
     * @param string $instance
     * @return void
     */
    public function forked(string $instance = 'child')
    {
        $this->pid = posix_getpid();
        $this->parentPid = posix_getppid();
        $this->setInstanceName($instance);
    }

    /**
     * @param string $instance
     * @return self
     */
    public function setInstanceName(string $instance): self
    {
        $this->instanceName = $instance;
        return $this;
    }

    /**
     * @param string $processName
     * @return self
     */
    public function setProcessName(string $processName): self
    {
        $this->processName = $processName;
        return $this;
    }

    /**
     * @return string
     */
    public function getProcessName(): string
    {
        return $this->processName;
    }

    /**
     * @return string
     */
    public function getPidPath(): string
    {
        return $this->pidPath;
    }

    /**
     * @param string $pidPath
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setPidPath(string $pidPath): self
    {
        if (!is_dir($pidPath)) {
            throw new \InvalidArgumentException('The folder ' . $pidPath . ' does not exists.');
        }
        if (!is_writeable($pidPath)) {
            throw new \InvalidArgumentException('The folder ' . $pidPath . ' is not writeable.');
        }
        $this->pidPath = $pidPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getPidPathname(): string
    {
        return sprintf(
            '%s/%s.pid',
            $this->getPidPath(),
            $this->instanceName
        );
    }

    /**
     * @return File
     */
    public function getPidFile(): File
    {
        if ($this->pidFile === null) {
            $this->pidFile = new File($this->getPidPathname(), 'w+');
        }
        return $this->pidFile;
    }

    /**
     * @return bool
     */
    public function isDaemonAlreadyRunning(): bool
    {
        $pidPathName = $this->getPidPathname();
        if (!file_exists($pidPathName)) {
            return false;
        }
        $pidFile = $this->getPidFile();
        if (!$pidFile->isFile()) {
            return false;
        }
        $pidFile->fseek(0, SEEK_SET);
        $pid = $pidFile->read(4096);
        if (empty($pid)) {
            return false;
        }
        $pid = (int)$pid;
        return $this->isPidAlive($pid);
    }

    /**
     * @param int $userId
     * @return self
     * @throws \Exception
     */
    public function setUserId(int $userId): self
    {
        $userId = (int)$userId;
        if (!posix_setuid($userId)) {
            throw new \Exception(
                sprintf(
                    'Could not set user id to %d',
                    $userId
                )
            );
        }
        $this->userId = posix_getuid();

        return $this;
    }

    /**
     * @param int $groupId
     * @return self
     * @throws \Exception
     */
    public function setGroupId(int $groupId): self
    {
        $groupId = (int)$groupId;
        if (!posix_setgid($groupId)) {
            throw new \Exception(
                sprintf(
                    'Could not set group id to %d',
                    $groupId
                )
            );
        }
        $this->groupId = posix_getgid();

        return $this;
    }

    /**
     * @param int $pid
     * @return bool
     */
    public function isPidAlive(int $pid): bool
    {
        return (bool)posix_kill((int)$pid, 0);
    }

    /**
     * @return self
     * @throws \Exception
     */
    public function start(): self
    {
        if (!is_dir($this->getPidPath())) {
            if (!mkdir($this->getPidPath(), 0700, true)) {
                throw new \Exception(
                    sprintf(
                        'Could not create pid folder %s',
                        $this->getPidPath()
                    )
                );
            }
        }
        $pidFile = $this->getPidFile();
        $pidFile->ftruncate(0);
        $pidFile->fseek(0, SEEK_SET);
        if ($pidFile->write($this->pid) === null) {
            throw new \Exception(
                sprintf(
                    'Could not write pid to pidfile %s',
                    $pidFile->getPathname()
                )
            );
        }
        return $this;
    }

    /**
     * @return self
     */
    public function stop(): self
    {
        $pidFile = $this->getPidFile();
        if (!$pidFile->isFile()) {
            return $this;
        }
        $pidFile->fseek(0, SEEK_SET);
        $pid = (int)$pidFile->read(4096);
        if (0 < $pid && $pid < 65535 && $pid !== $this->pid) {
            return false;
        }
        $pidFile->unlink();
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return self
     */
    public function emergency($message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->emergency($message, $context);
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return self
     */
    public function alert($message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->alert($message, $context);
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return self
     */
    public function critical($message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->critical($message, $context);
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return self
     */
    public function error($message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->error($message, $context);
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return self
     */
    public function warning($message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->warning($message, $context);
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return self
     */
    public function notice($message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->notice($message, $context);
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return self
     */
    public function info($message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->info($message, $context);
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return self
     */
    public function debug($message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->debug($message, $context);
        return $this;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return self
     */
    public function log($level, $message, array $context = []): self
    {
        if (!$this->logger) {
            return $this;
        }
        $this->logger->log($level, $message, $context);
        return $this;
    }

}
