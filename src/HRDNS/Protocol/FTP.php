<?php

namespace HRDNS\Protocol;

use HRDNS\Exception\IOException;

class FTP
{

    /** @var string */
    private $host = '127.0.0.1';

    /** @var int */
    private $port = 21;

    /** @var string */
    private $user = 'anonymous';

    /** @var string */
    private $password = 'anonymous@anonymous';

    /** @var bool */
    private $ssl = false;

    /** @var int */
    private $timeout = 4;

    /** @var null */
    private $socket = null;

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setHost(string $host): self
    {
        if (!$host) {
            throw new \InvalidArgumentException('Invalid ftp host.');
        }
        $this->host = $host;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param integer $port
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setPort(int $port): self
    {
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException('Invalid ftp port.');
        }
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return self
     */
    public function setUser(string $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSsl(): bool
    {
        return $this->ssl;
    }

    /**
     * @param boolean $ssl
     * @return self
     */
    public function setSsl(bool $ssl): self
    {
        $this->ssl = $ssl;
        return $this;
    }

    /**
     * @param integer $timeout
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setTimeout(int $timeout): self
    {
        if ($timeout < 1) {
            throw new \InvalidArgumentException('The timeout must be greater then 0 second.');
        }
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return integer
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return self
     * @throws IOException
     */
    public function connect(): self
    {
        $this->disconnect();

        $func = $this->isSsl() ? 'ftp_ssl_connect' : 'ftp_connect';
        $this->socket = @$func(
            $this->host,
            $this->port,
            $this->timeout
        );
        if (!$this->socket) {
            $this->socket = null;
            throw new IOException(
                sprintf(
                    'Could not connect to ftp%s://%s:%d',
                    $this->ssl ? 's' : '',
                    $this->host,
                    $this->port
                )
            );
        }
        return $this;
    }

    /**
     * @param null|string $user
     * @param null|string $password
     * @return self
     * @throws IOException
     */
    public function login($user = null, $password = null)
    {
        $this->user = $user ?: $this->user;
        $this->password = $password ?: $this->password;
        if (!$this->user && !$this->password) {
            return $this;
        }
        if (!@ftp_login($this->socket, $this->user, $this->password)) {
            throw new IOException(
                sprintf(
                    'Could not login on ftp%s://%s%s:%d',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port
                )
            );
        }
        return $this;
    }

    /**
     * @return self
     * @throws IOException
     */
    public function passiv()
    {
        if (@ftp_pasv($this->socket, true) !== true) {
            throw new IOException(
                sprintf(
                    'Could not switch to passiv mode (ftp%s://%s%s:%d)',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port
                )
            );
        }
        return $this;
    }

    /**
     * @return self
     */
    public function disconnect()
    {
        if ($this->socket === null) {
            return $this;
        }
        @ftp_close($this->socket);
        $this->socket = null;
        return $this;
    }

    /**
     * @param string $path
     * @return array
     * @throws IOException
     */
    public function dir(string $path = '.'): array
    {
        if (($result = @ftp_nlist($this->socket, $path)) === false) {
            throw new IOException(
                sprintf(
                    'Could not receive file list ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $result;
    }

    /**
     * @param string $path
     * @return self
     * @throws IOException
     */
    public function cd(string $path): self
    {
        if (@ftp_chdir($this->socket, $path) === false) {
            throw new IOException(
                sprintf(
                    'Could not change directory ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $this;
    }

    /**
     * @return string
     * @throws IOException
     */
    public function pwd(): string
    {
        if (($path = @ftp_pwd($this->socket)) === false) {
            throw new IOException(
                sprintf(
                    'Could receive pwd folder ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $path;
    }

    /**
     * @param integer $chmod
     * @param string $path
     * @return self
     * @throws IOException
     */
    public function chmod(int $chmod, string $path): self
    {
        if (@ftp_chmod($this->socket, $chmod, $path) === false) {
            throw new IOException(
                sprintf(
                    'Could set chmod on ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $this;
    }

    /**
     * @param string $path
     * @return self
     * @throws IOException
     */
    public function rm(string $path): self
    {
        if (@ftp_delete($this->socket, $path) === false) {
            throw new IOException(
                sprintf(
                    'Could not remove ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $this;
    }

    /**
     * @param string $path
     * @return self
     * @throws IOException
     */
    public function mkdir(string $path): self
    {
        if (@ftp_mkdir($this->socket, $path) === false) {
            throw new IOException(
                sprintf(
                    'Could not create folder ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $this;
    }

    /**
     * @param string $path
     * @return self
     * @throws IOException
     */
    public function rmdir(string $path): self
    {
        if (@ftp_rmdir($this->socket, $path) === false) {
            throw new IOException(
                sprintf(
                    'Could remove directory ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $this;
    }

    /**
     * @param string $path
     * @return integer
     * @throws IOException
     */
    public function size(string $path): int
    {
        if (($size = @ftp_size($this->socket, $path)) === -1) {
            throw new IOException(
                sprintf(
                    'Could not read size from ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $size;
    }

    /**
     * @param string $path
     * @return integer
     * @throws IOException
     */
    public function modifiedTime(string $path): int
    {
        if (($time = @ftp_mdtm($this->socket, $path)) === -1) {
            throw new IOException(
                sprintf(
                    'Could read modification time from ftp%s://%s%s:%d/%s',
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port,
                    ltrim($path, '/')
                )
            );
        }
        return $time;
    }

    /**
     * @param string $from
     * @param string $to
     * @return self
     * @throws IOException
     */
    public function get(string $from, string $to): self
    {
        if (!@ftp_get($this->socket, $to, $from, FTP_BINARY, 0)) {
            throw new IOException(
                sprintf(
                    'Could not download %s from ftp%s://%s%s:%d',
                    $from,
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port
                )
            );
        }
        return $this;
    }

    /**
     * @param string $from
     * @param string $to
     * @return self
     * @throws IOException
     * @throws \RuntimeException
     */
    public function put(string $from, string $to): self
    {
        if (!file_exists($from)) {
            throw new \RuntimeException('Files does not exists: ' . $from);
        }
        if (!@ftp_put($this->socket, $from, $to, FTP_BINARY)) {
            throw new IOException(
                sprintf(
                    'Could not upload %s to ftp%s://%s%s:%d',
                    $from,
                    $this->ssl ? 's' : '',
                    $this->user ? $this->user . '@' : '',
                    $this->host,
                    $this->port
                )
            );
        }
        return $this;
    }

    /**
     * @access public
     * @param string $command
     * @return mixed
     */
    public function exec(string $command)
    {
        return @ftp_raw($this->socket, $command);
    }

}
