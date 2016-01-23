<?php

namespace HRDNS\Types;

/**
 * Class XML
 *
 * @package HRDNS\Types
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class XML
{

    /** @var string */
    protected $charset = 'UTF-8';

    /** @var string */
    protected $name = 'root';

    /** @var string */
    protected $value = '';

    /** @var boolean */
    protected $cData = false;

    /** @var array */
    protected $attributes = array ();

    /** @var array */
    protected $children = array ();

    /** @var array */
    protected $currentChild = array ();

    /** @var boolean */
    protected $root = false;

    /**
     * @param string|null $xml
     */
    public function __construct($xml = null)
    {
        $xml ? $this->parse($xml) : null;
    }

    /**
     * @param boolean $root
     * @return self
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function setRoot($root = false)
    {
        $this->root = (bool)$root;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRoot()
    {
        return $this->root;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setValue($value)
    {
        $value = (string)$value;

        if (strlen($value) > 256) {
            $this->setCData(true);
        }
        if (strpos($value, '<') !== false || strpos($value, '>') !== false || strpos($value, '&') !== false) {
            $this->setCData(true);
        }
        $this->value = $value;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCData()
    {
        return $this->cData;
    }

    /**
     * @param boolean $cData
     * @return self
     */
    public function setCData($cData)
    {
        $this->cData = (boolean)$cData;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return mixed|boolean
     */
    public function getAttribute($name)
    {
        $name = (string)$name;
        return isset($this->attributes[$name]) ? $this->attributes[$name] : false;
    }

    /**
     * @param array $attributes
     * @return self
     */
    public function setAttributes(array $attributes = array ())
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function setAttribute($name, $value = null)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @param self|array $xml
     * @return self|boolean
     */
    public function appendChild($xml)
    {
        if ($xml instanceof self) {
            $this->children[$xml->getName()][] = $xml;
            return $this;
        }
        if (is_array($xml)) {
            $this->children = $xml;
            return $this;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return self
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * @param string $xml
     * @param string $charset
     * @param integer $tagStart
     * @param integer $skipWhite
     * @param integer $caseFolding
     * @return self|boolean
     */
    public function parse($xml, $charset = 'UTF-8', $tagStart = 0, $skipWhite = 1, $caseFolding = 0)
    {
        $this->charset = strtoupper($charset);
        $xmlParser = xml_parser_create($this->charset);

        xml_parser_set_option($xmlParser, XML_OPTION_SKIP_TAGSTART, $tagStart);
        xml_parser_set_option($xmlParser, XML_OPTION_SKIP_WHITE, $skipWhite);
        xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, $caseFolding);
        xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, $this->charset);
        xml_parse_into_struct($xmlParser, $xml, $xmlArray);
        xml_parser_free($xmlParser);

        $result = $this->internalParse($xmlArray);
        if (empty($result)) {
            return false;
        }
        list($this->children, $error) = $result;

        if ($error === -1) {
            return $this;
        }
        return false;
    }

    /**
     * @param array $option
     * @param integer $currentXml
     * @return array
     */
    private function internalParse(array &$option, $currentXml = 0)
    {
        if ($currentXml == 0) {
            if (!isset($option[0])) {
                return array ();
            }
            $currentXmlArray = $option[0];
            $this->setName(isset($currentXmlArray['tag']) ? $currentXmlArray['tag'] : 'noname');
            $this->setValue(isset($currentXmlArray['value']) ? $currentXmlArray['value'] : '');
            $this->setAttributes(isset($currentXmlArray['attributes']) ? $currentXmlArray['attributes'] : array ());
            $this->setRoot(true);
        }

        $xmlArray = array ();
        $currentXml++;

        $deep = $currentXml;
        while ($deep < count($option)) {

            $currentXmlArray = $option[$deep];

            if ($currentXmlArray['type'] == 'close') {
                return array ($xmlArray, $deep);
            }

            $xmlObject = new self('');
            $xmlObject->setName(isset($currentXmlArray['tag']) ? $currentXmlArray['tag'] : 'noname');
            $xmlObject->setValue(isset($currentXmlArray['value']) ? $currentXmlArray['value'] : '');
            $xmlObject->setAttributes(
                isset($currentXmlArray['attributes']) ? $currentXmlArray['attributes'] : array ()
            );

            if ($currentXmlArray['type'] == 'open') {
                $result = $this->internalParse($option, $deep++);
                if (!empty($result)) {
                    list ($children, $deep) = $result;
                    $xmlObject->appendChild($children);
                }
            }

            $xmlArray[$xmlObject->getName()][] = $xmlObject;

            $deep++;
        }

        return array ($xmlArray, $deep);
    }

    /**
     * @param string|null $searchNode
     * @return array
     */
    public function getChildren($searchNode = null)
    {
        if (!$searchNode) {
            return $this->children;
        }
        foreach ($this->children as $nodeName => $children) {
            if ($nodeName == $searchNode) {
                return $children;
            }
        }
        return array ();
    }

    /**
     * @param string $nodeName
     * @param integer $note
     * @return self|boolean
     */
    public function getChild($nodeName, $note = 0)
    {
        if (isset($this->children[$nodeName][$note])) {
            return $this->children[$nodeName][$note];
        }
        if ($this->getName() == $nodeName) {
            return $this;
        }
        return false;
    }

    /**
     * @param string $nodeName
     * @param integer $note
     * @return boolean
     */
    public function childExists($nodeName, $note = 0)
    {
        return ($this->getChild($nodeName, $note) instanceof XML);
    }

    /**
     * @param string $nodeName
     * @return self|boolean
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getNode($nodeName)
    {
        $xml = $this;
        $nodes = explode('..', $nodeName);
        foreach ($nodes as $nodeName) {
            if (strpos($nodeName, ';') === false) {
                $note = 0;
            } else {
                list($nodeName, $note) = explode(';', $nodeName);
            }
            $xml = $xml->getChild($nodeName, $note);
            if (!$xml) {
                return false;
            }
        }
        return $xml;
    }

    /**
     * @return integer
     */
    public function getLength()
    {
        $count = 0;
        foreach ($this->children as $children) {
            $count += count($children);
        }
        return $count;
    }

    /**
     * @return self|boolean
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function next()
    {
        if (empty($this->currentChild)) {
            foreach ($this->children as $name => $children) {
                foreach ($children as $key => $child) {
                    $this->currentChild['key'] = $key;
                    $this->currentChild['name'] = $name;
                    return $child;
                }
            }
        } else {
            $return = false;
            foreach ($this->children as $name => $children) {
                foreach ($children as $key => $child) {
                    if ($key == $this->currentChild['key'] && $name == $this->currentChild['name']) {
                        $return = true;
                        continue;
                    }
                    if ($return) {
                        $this->currentChild['key'] = $key;
                        $this->currentChild['name'] = $name;
                        return $child;
                    }
                }
            }
        }
        $this->currentChild = array ();
        return false;
    }

    /**
     * @param integer $deep
     * @return string
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getXML($deep = -1)
    {
        $deep++;
        $xml = '';
        if ($this->isRoot()) {
            $xml .= '<' . '?xml version="1.0" encoding="' . $this->charset . '" ?' . '>' . PHP_EOL;
        }
        $xml .= $this->padding($deep);
        $xml .= '<' . $this->getName();
        foreach ($this->attributes as $attribute => $value) {
            $xml .= ' ' . $attribute . '="' . $this->escape($value) . '"';
        }

        if (empty($this->value) && empty($this->children)) {
            $xml .= ' />' . PHP_EOL;
        } else {
            if (!empty($this->value)) {
                $xml .= '>';
                if ($this->cData) {
                    $xml .= '<![CDATA[';
                }
                $xml .= (string)$this->value;
                if ($this->cData) {
                    $xml .= ']]>';
                }
            } else {
                $xml .= '>' . PHP_EOL;
                /** @var self[] $children */
                foreach ($this->children as $children) {
                    /** @var self $child */
                    foreach ($children as $child) {
                        $xml .= $child->getXML($deep);
                    }
                }
                $xml .= $this->padding($deep);
            }
            $xml .= '</' . $this->getName() . '>' . PHP_EOL;
        }
        return $xml;
    }

    /**
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        $value = str_replace('&', '&amp;', $value);
        $value = str_replace('"', '&quot;', $value);
        $value = str_replace("'", '&apos;', $value);
        $value = str_replace('>', '&gt;', $value);
        $value = str_replace('<', '&lt;', $value);
        return $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getXML();
    }

    /**
     * @param integer $deep
     * @return string
     */
    public function padding($deep = 0)
    {
        $value = '';
        for ($count = 0 ; $count < $deep ; $count++) {
            $value .= '    ';
        }
        return $value;
    }

}
