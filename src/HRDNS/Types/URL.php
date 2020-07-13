<?php declare(strict_types=1);

namespace HRDNS\Types;

class URL
{

    /** @var array */
    private $parts = [
        'scheme' => 'http',
        'user' => '',
        'pass' => '',
        'host' => '127.0.0.1',
        'port' => 0,
        'path' => '/',
        'query' => '',
        'fragment' => ''
    ];

    /**
     * @param string $url
     */
    public function __construct(string $url = '')
    {
        $this->setURL($url);
    }

    /**
     * @param string $url
     * @return self
     */
    public function setURL(string $url)
    {
        if (empty($url)) {
            return $this;
        }

        if (preg_match('#^//#', $url)) {
            $url = $this->parts['scheme'] . ':' . $url;
        }

        $url = @parse_url($url);
        if (isset($url['scheme']) && $url['scheme'] != $this->parts['scheme'] ||
            isset($url['host']) && $url['host'] != $this->parts['host'] ||
            isset($url['port']) && $url['port'] != $this->parts['port']
        ) {
            $this->parts['port'] = 0;
            $this->parts['user'] = '';
            $this->parts['pass'] = '';
        }

        $this->parts['scheme'] = isset($url['scheme']) ? $url['scheme'] : $this->parts['scheme'];
        $this->parts['user'] = isset($url['user']) ? $url['user'] : $this->parts['user'];
        $this->parts['pass'] = isset($url['pass']) ? $url['pass'] : $this->parts['pass'];
        $this->parts['host'] = isset($url['host']) ? $url['host'] : $this->parts['host'];
        $this->parts['port'] = isset($url['port']) ? $url['port'] : $this->parts['port'];
        $this->parts['path'] = '/' . (isset($url['path']) ? ltrim($url['path'], '/') : '');
        $this->parts['query'] = isset($url['query']) ? $url['query'] : '';
        $this->parts['fragment'] = isset($url['fragment']) ? $url['fragment'] : '';

        if (!$this->parts['port']) {
            $port = @getservbyname($this->parts['scheme'], 'tcp');
            $port = $port !== false ? $port : @getservbyname($this->parts['scheme'], 'udp');
            $this->parts['port'] = $port !== false ? $port : $this->parts['port'];
        }

        return $this;
    }

    /**
     * @return string#
     */
    public function getURL(): string
    {
        $url = '';
        $url .= $this->parts['scheme'] ? $this->parts['scheme'] . ':' : '';
        $url .= '//';
        $url .= $this->parts['user'] ?: '';
        $url .= $this->parts['pass'] ? ':' . $this->parts['pass'] : '';
        $url .= $this->parts['user'] || $this->parts['pass'] ? '@' : '';
        $url .= $this->parts['host'];

        if ($this->parts['port']) {
            $port = @getservbyname($this->parts['scheme'], 'tcp');
            $port = $port !== false ? $port : @getservbyname($this->parts['scheme'], 'udp');
            $url .= $port != $this->parts['port'] ? ':' . $this->parts['port'] : '';
        }

        $url .= '/' . ltrim($this->parts['path'], '/');
        $url .= $this->parts['query'] ? '?' . $this->parts['query'] : '';
        $url .= $this->parts['fragment'] ? '#' . $this->parts['fragment'] : '';
        return $url;
    }

    /**
     * @param string $scheme
     * @return self
     */
    public function setScheme(string $scheme)
    {
        $this->parts['scheme'] = $scheme;
        return $this;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->parts['scheme'];
    }

    /**
     * @param string $user
     * @return self
     */
    public function setUser(string $user)
    {
        $this->parts['user'] = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->parts['user'];
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPassword(string $password)
    {
        $this->parts['pass'] = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->parts['pass'];
    }

    /**
     * @param string $host
     * @return self
     */
    public function setHost(string $host)
    {
        $this->parts['host'] = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->parts['host'];
    }

    /**
     * @param integer $port
     * @return self
     */
    public function setPort(int $port)
    {
        $this->parts['port'] = $port;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPort(): int
    {
        return $this->parts['port'];
    }

    /**
     * @param string $path
     * @return self
     */
    public function setPath(string $path)
    {
        $this->parts['path'] = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->parts['path'];
    }

    /**
     * @param string $query
     * @return self
     */
    public function setQuery(string $query)
    {
        $this->parts['query'] = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->parts['query'];
    }

    /**
     * @param string $fragment
     * @return self
     */
    public function setFragment(string $fragment)
    {
        $this->parts['fragment'] = $fragment;
        return $this;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->parts['fragment'];
    }

}
