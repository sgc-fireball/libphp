<?php

namespace HRDNS\Types;

/**
 * Class Struct
 *
 * @package HRDNS\Types
 */
class Struct
{

    /**
     * @var array
     */
    protected $data = array ();

    /**
     * @param array $data
     */
    public function __construct(array $data = array ())
    {
        if (empty($data)) {
            return;
        }
        $this->data = $data;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return self
     * @throws \Exception
     */
    public function __set($key, $value)
    {
        if (!isset($this->data[$key])) {
            throw new \Exception(sprintf('%s does not exists on %s', $key, __CLASS__));
        }
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @param mixed $key
     * @return mixed
     * @throws \Exception
     */
    public function __get($key)
    {
        if (!isset($this->data[$key])) {
            throw new \Exception(sprintf('%s does not exists on %s', $key, __CLASS__));
        }
        return $this->data[$key];
    }

    /**
     * @param mixed $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return self
     */
    public function setArray(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getJSON()
    {
        return json_encode($this->data);
    }

    /**
     * @param string $json
     * @return self
     * @throws \Exception
     */
    public function loadFromJSON(string $json)
    {
        $data = json_decode($json);
        $data = $data instanceof \stdClass ? (array)$data : false;
        if (!is_array($data)) {
            throw new \Exception(sprintf('Fail to load from JSON.'));
        }
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getSerialize()
    {
        return serialize($this->data);
    }

    /**
     * @param string $serialize
     * @return self
     * @throws \Exception
     */
    public function loadFromSerialize(string $serialize)
    {
        $data = unserialize($serialize);
        if (!is_array($data)) {
            throw new \Exception(sprintf('Fail to load from Serialize.'));
        }
        $this->data = $data;
        return $this;
    }

    /**
     * @return XML
     */
    public function getXML()
    {
        $xml = new XML();
        $xml->setName('struct');
        $xml->setRoot(true);
        $xml->setCharset('UTF-8');
        $xml->setAttribute('type', 'object');
        $xml->setAttribute('class', __CLASS__);
        $this->appendDataXml($xml, $this->data);
        return $xml->getXML();
    }

    /**
     * @param XML $xml
     * @param array $data
     * @return void
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function appendDataXml(XML &$xml, array &$data)
    {
        foreach ($data as $key => &$value) {
            $child = new XML();
            $child->setName($key);
            $child->setCData(true);
            $child->setRoot(false);
            $type = gettype($value);
            $child->setAttribute('type', $type);
            switch ($type) {
                case 'bool':
                    $child->setValue((int)$value);
                    break;
                case 'integer':
                    $child->setValue((int)$value);
                    break;
                case 'double':
                case 'float':
                    $child->setValue((float)$value);
                    break;
                case 'string':
                    $child->setValue((string)$value);
                    break;
                case 'array':
                    $this->appendDataXml($child, $value);
                    break;
                case 'object':
                    $child->setAttribute('class', get_class($value));
                    if ($value instanceof self) {
                        $array = $value->getArray();
                        $this->appendDataXml($child, $array);
                    } else {
                        $serialize = serialize($value);
                        $child->setValue($serialize);
                    }
                    break;
                case 'resource':
                case 'null':
                case 'unknown type':
                default:
                    $child->setValue('null');
                    break;
            }
            $xml->appendChild($child);
        }
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array ('data');
    }

}
