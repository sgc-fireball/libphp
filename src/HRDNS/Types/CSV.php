<?php

namespace HRDNS\Types;

use HRDNS\Exception\IOException;

class CSV implements \Iterator, \ArrayAccess
{

    /** @var string|string */
    private $file = '';

    /** @var string */
    private $delimiter = ',';

    /** @var string */
    private $enclosure = '"';

    /** @var string */
    private $escape = '\\';

    /** @var int */
    private $line = 0;

    /** @var resource */
    private $fp = null;

    /** @var int */
    private $tell = 0;

    /** @var array */
    private $cache = [];

    /**
     * @param string $file
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     */
    public function __construct(
        string $file,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\'
    ) {
        $this->file = $file;
        $this->delimiter = $delimiter ?: ',';
        $this->enclosure = $enclosure ?: '"';
        $this->escape = $escape ?: '\\';
    }

    /**
     * @return CSV
     * @throws IOException
     */
    public function open(): self
    {
        if (($this->fp = fopen($this->file, 'rw+')) === false) {
            throw new IOException();
        }
        $this->tell = @ftell($this->fp);
        return $this;
    }

    /**
     * @return array
     * @throws IOException
     */
    public function current(): array
    {
        if (!isset($this->cache[$this->line])) {
            @fseek($this->fp, $this->tell);
            if (($csv = @fgetcsv($this->fp, 4096, $this->delimiter, $this->enclosure, $this->escape)) === false) {
                throw new IOException();
            }
            $this->tell = @ftell($this->fp);
            $this->cache[$this->line] = $csv;
        }
        return $this->cache[$this->line];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->line;
    }

    public function next()
    {
        $this->line++;
    }

    public function rewind()
    {
        $this->line = 0;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        @fseek($this->fp, 0, SEEK_END);
        $tell = @ftell($this->fp);
        @fseek($this->fp, $this->tell);
        return $this->tell != $tell || isset($this->cache[$this->line]);
    }

    /**
     * @param array $row
     * @return $this
     * @throws IOException
     * @throws \InvalidArgumentException
     */
    public function write(array $row = [])
    {
        if (empty($row)) {
            throw new \InvalidArgumentException();
        }
        @fseek($this->fp, 0, SEEK_END);
        if (@fputcsv($this->fp, $row, $this->delimiter, $this->enclosure, $this->escape) === false) {
            throw new IOException();
        }
        return $this;
    }

    /**
     * @return CSV
     */
    public function close(): self
    {
        @fclose($this->fp);
        $this->fp = null;
        $this->tell = 0;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return bool
     * @throws IOException
     */
    public function offsetExists($offset): bool
    {
        if (isset($this->cache[$offset])) {
            return true;
        }
        $this->readAllLines();
        return isset($this->cache[$offset]);
    }

    /**
     * @param mixed $offset
     * @return array
     * @throws \RuntimeException
     */
    public function offsetGet($offset): array
    {
        if (isset($this->cache[$offset])) {
            return $this->cache[$offset];
        }
        $this->readAllLines();
        if (!isset($this->cache[$offset])) {
            throw new \RuntimeException('Item ' . $offset . ' does not exist on ' . __CLASS__);
        }
        return $this->cache[$offset];
    }

    /**
     * @param mixed $offset
     * @throws \RuntimeException
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('The method ' . __METHOD__ . ' is forbidden.');
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \RuntimeException
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('The method ' . __METHOD__ . ' is forbidden.');
    }

    /**
     * @return void
     * @throws IOException
     */
    public function readAllLines()
    {
        @fseek($this->fp, 0, SEEK_END);
        $tell = @ftell($this->fp);
        @fseek($this->fp, $this->tell);
        while ($tell != $this->tell) {
            if (($csv = @fgetcsv($this->fp, 4096, $this->delimiter, $this->enclosure, $this->escape)) === false) {
                throw new IOException();
            }
            $this->tell = @ftell($this->fp);
            $this->cache[$this->line] = $csv;
            $this->line++;
        }
    }

    public function __destruct()
    {
        $this->close();
    }

}
