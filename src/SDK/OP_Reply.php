<?php



class OP_Reply
{
    protected int $faultCode = 0;
    protected ?string $faultString = null;
    protected array $value = array();
    protected array $warnings = array();
    protected ?string $raw = null;
    protected ?DOMDocument $dom = null;
    protected array $filters = [];
    protected $maintenance = null;

    /**
     * Constructor
     * @param string|null $str
     * @throws OP_API_Exception
     */
    public function __construct(string $str = null)
    {
        if ($str) {
            $this->raw = $str;
            $this->_parseReply($str);
        }
    }

    /**
     * Parse API reply
     * @param string $str
     * @throws OP_API_Exception
     */
    protected function _parseReply(string $str = '')
    {
        $dom = new DOMDocument;
        $result = $dom->loadXML(trim($str));
        if (!$result) {
            error_log("Cannot parse xml: '$str'");
        }

        $arr = OP_API::convertXmlToPhpObj($dom->documentElement);
        if ((!is_array($arr) && trim($arr) == '') ||
            $arr['reply']['code'] == 4005)
        {
            throw new OP_API_Exception("API is temporarily unavailable due to maintenance", 4005);
        }

        $this->faultCode = (int) $arr['reply']['code'];
        $this->faultString = $arr['reply']['desc'];
        $this->value = $arr['reply']['data'];
        if (isset($arr['reply']['warnings'])) {
            $this->warnings = $arr['reply']['warnings'];
        }
        if (isset($arr['reply']['maintenance'])) {
            $this->maintenance = $arr['reply']['maintenance'];
        }
    }

    /**
     * Encode string for XML
     * @param mixed $str
     * @return mixed
     */
    public function encode($str)
    {
        return OP_API::encode($str);
    }

    /**
     * Set fault code
     * @param int $v
     * @return self
     */
    public function setFaultCode(int $v): OP_Reply
    {
        $this->faultCode = $v;
        return $this;
    }

    /**
     * Set fault string
     * @param string $v
     * @return self
     */
    public function setFaultString(string $v): OP_Reply
    {
        $this->faultString = $v;
        return $this;
    }

    /**
     * Set value
     * @param mixed $v
     * @return self
     */
    public function setValue($v): OP_Reply
    {
        $this->value = $v;
        return $this;
    }

    /**
     * Get value
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set warnings
     * @param array $v
     * @return self
     */
    public function setWarnings(array $v): OP_Reply
    {
        $this->warnings = $v;
        return $this;
    }

    /**
     * Get DOM object
     * @return DOMDocument|null
     */
    public function getDom(): ?DOMDocument
    {
        return $this->dom;
    }

    /**
     * Get warnings
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get maintenance info
     * @return mixed
     */
    public function getMaintenance()
    {
        return $this->maintenance;
    }

    /**
     * Get fault string
     * @return string|null
     */
    public function getFaultString(): ?string
    {
        return $this->faultString;
    }

    /**
     * Get fault code
     * @return int
     */
    public function getFaultCode(): int
    {
        return $this->faultCode;
    }

    /**
     * Get raw XML
     * @return string|null
     * @throws DOMException
     */
    public function getRaw(): ?string
    {
        if (!$this->raw) {
            $this->raw = $this->_getReply();
        }
        return $this->raw;
    }

    /**
     * Add filter
     * @param mixed $filter
     * @return void
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Generate XML reply
     * @return string
     * @throws DOMException
     */
    public function _getReply(): string
    {
        $dom = new DOMDocument('1.0', OP_API::$encoding);

        $rootNode = $dom->appendChild($dom->createElement('openXML'));
        $replyNode = $rootNode->appendChild($dom->createElement('reply'));

        // Add code
        $codeNode = $replyNode->appendChild($dom->createElement('code'));
        $codeNode->appendChild($dom->createTextNode($this->faultCode));

        // Add description
        $descNode = $replyNode->appendChild($dom->createElement('desc'));
        $descNode->appendChild(
            $dom->createTextNode(OP_API::encode($this->faultString))
        );

        // Add data
        $dataNode = $replyNode->appendChild($dom->createElement('data'));
        OP_API::convertPhpObjToDom($this->value, $dataNode, $dom);

        // Add warnings if any
        if (0 < count($this->warnings)) {
            $warningsNode = $replyNode->appendChild($dom->createElement('warnings'));
            OP_API::convertPhpObjToDom($this->warnings, $warningsNode, $dom);
        }

        $this->dom = $dom;

        // Apply filters
        foreach ($this->filters as $f) {
            $f->filter($this);
        }

        return $dom->saveXML();
    }
}