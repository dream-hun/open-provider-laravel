<?php


class OP_API
{
    protected $url = null;
    protected $error = null;
    protected $timeout = null;
    protected $debug = null;
    static public string $encoding = 'UTF-8';

    /**
     * Constructor
     * @param string|null $url API URL
     * @param int $timeout Timeout in milliseconds
     */
    public function __construct(string $url = null, int $timeout = 1000)
    {
        $this->url = $url;
        $this->timeout = $timeout;
    }

    /**
     * Set debug mode
     * @param mixed $v
     * @return self
     */
    public function setDebug($v): OP_API
    {
        $this->debug = $v;
        return $this;
    }

    /**
     * Process raw API reply
     * @param OP_Request $r
     * @return string
     * @throws OP_API_Exception
     */
    public function processRawReply(OP_Request $r): string
    {
        if ($this->debug) {
            echo $r->getRaw() . "\n";
        }
        $msg = $r->getRaw();
        $str = $this->_send($msg);
        if (!$str) {
            throw new OP_API_Exception("Bad reply", 4004);
        }
        if ($this->debug) {
            echo $str . "\n";
        }
        return $str;
    }

    /**
     * Process API request
     * @param OP_Request $r
     * @return OP_Reply
     * @throws OP_API_Exception
     */
    public function process(OP_Request $r): OP_Reply
    {
        if ($this->debug) {
            echo $r->getRaw() . "\n";
        }

        $msg = $r->getRaw();
        $str = $this->_send($msg);
        if (!$str) {
            throw new OP_API_Exception("Bad reply", 4004);
        }
        if ($this->debug) {
            echo $str . "\n";
        }
        return new OP_Reply($str);
    }

    /**
     * Check if XML was created successfully with $str
     * @param string $str
     * @return boolean
     * @throws DOMException
     */
    static function checkCreateXml(string $str): bool
    {
        $dom = new DOMDocument;
        $dom->encoding = 'utf-8';

        $textNode = $dom->createTextNode($str);

        if (!$textNode) {
            return false;
        }

        $element = $dom->createElement('element')
            ->appendChild($textNode);

        if (!$element) {
            return false;
        }

        @$dom->appendChild($element);

        $xml = $dom->saveXML();

        return !empty($xml);
    }

    /**
     * Encode string for XML
     * @param mixed $str
     * @return mixed
     */
    static function encode($str)
    {
        $ret = @htmlentities($str, null, OP_API::$encoding);

        // Some tables have data stored in two encodings
        if (strlen($str) && !strlen($ret)) {
            error_log('ISO charset date = ' . date('d.m.Y H:i:s') . ',STR = ' . $str);
            $str = iconv('ISO-8859-1', 'UTF-8', $str);
        }

        if (!empty($str) && is_object($str)) {
            error_log('Exception convertPhpObjToDom date = ' . date('d.m.Y H:i:s') . ', object class = ' . get_class($str));
            if (method_exists($str, '__toString')) {
                $str = $str->__toString();
            } else {
                return $str;
            }
        }

        if (!empty($str) && is_string($str) && !self::checkCreateXml($str)) {
            error_log('Exception convertPhpObjToDom date = ' . date('d.m.Y H:i:s') . ', STR = ' . $str);
            $str = htmlentities($str, null, OP_API::$encoding);
        }
        return $str;
    }

    /**
     * Decode string from XML
     * @param string $str
     * @return string
     */
    static function decode(string $str): string
    {
        return $str;
    }

    /**
     * Create new request object
     * @param string|null $xmlStr
     * @return OP_Request
     */
    static function createRequest(string $xmlStr = null): OP_Request
    {
        return new OP_Request($xmlStr);
    }

    /**
     * Create new reply object
     * @param string|null $xmlStr
     * @return OP_Reply
     * @throws OP_API_Exception
     */
    static function createReply(string $xmlStr = null): OP_Reply
    {
        return new OP_Reply($xmlStr);
    }

    /**
     * Send request to API
     * @param string $str
     * @return string|false
     */
    protected function _send(string $str)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $ret = curl_exec($ch);
        $errno = curl_errno($ch);
        $this->error = $error = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            error_log("CURL error. Code: $errno, Message: $error");
            return false;
        } else {
            return $ret;
        }
    }

    /**
     * Convert SimpleXML to PHP Object
     * @param mixed $node
     * @return array|string|null|false
     * @throws OP_API_Exception
     */
    public static function convertXmlToPhpObj($node)
    {
        $ret = array();

        if (is_object($node) && $node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $name = self::decode($child->nodeName);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $ret = self::decode($child->nodeValue);
                } else {
                    if ('array' === $name) {
                        return self::parseArray($child);
                    } else {
                        $ret[$name] = self::convertXmlToPhpObj($child);
                    }
                }
            }
        }

        if (is_string($ret)) {
            return (0 < strlen($ret)) ? $ret : null;
        }
        else if (is_array($ret)) {
            return (!empty($ret)) ? $ret : null;
        }
        else if (is_null($ret)) {
            return null;
        }
        else {
            return false;
        }
    }

    /**
     * Parse array from XML
     * @param mixed $node
     * @return array
     * @throws OP_API_Exception
     */
    protected static function parseArray($node): array
    {
        $ret = array();
        foreach ($node->childNodes as $child) {
            $name = self::decode($child->nodeName);
            if ('item' !== $name) {
                throw new OP_API_Exception('Wrong message format', 4006);
            }
            $ret[] = self::convertXmlToPhpObj($child);
        }
        return $ret;
    }

    /**
     * Convert PHP structure to DOM object
     * @param array $arr
     * @param mixed $node
     * @param DOMDocument $dom
     * @throws DOMException
     */
    public static function convertPhpObjToDom($arr, $node, $dom)
    {
        if (is_array($arr)) {
            $arrayParam = array();
            foreach ($arr as $k => $v) {
                if (is_integer($k)) {
                    $arrayParam[] = $v;
                }
            }
            if (0 < count($arrayParam)) {
                $node->appendChild($arrayDom = $dom->createElement("array"));
                foreach ($arrayParam as $key => $val) {
                    $new = $arrayDom->appendChild($dom->createElement('item'));
                    self::convertPhpObjToDom($val, $new, $dom);
                }
            } else {
                foreach ($arr as $key => $val) {
                    $new = $node->appendChild(
                        $dom->createElement(self::encode($key))
                    );
                    self::convertPhpObjToDom($val, $new, $dom);
                }
            }
        } elseif (!is_object($arr)) {
            $node->appendChild($dom->createTextNode(self::encode($arr)));
        }
    }
}