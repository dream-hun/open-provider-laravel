<?php

class OP_Request
{
    protected $cmd = null;
    protected $args = null;
    protected $username = null;
    protected $password = null;
    protected $hash = null;
    protected $token = null;
    protected $ip = null;
    protected $language = null;
    protected $raw = null;
    protected $dom = null;
    protected $misc = null;
    protected $filters = [];

    /**
     * Constructor.
     * @param string|null $str XML string to initialize with
     */
    public function __construct($str = null)
    {
        if ($str) {
            $this->setContent($str);
        }
    }

    /**
     * Add a filter to the request
     * @param mixed $filter
     * @return void
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Set the raw content
     * @param string $str
     * @return void
     */
    public function setContent($str)
    {
        $this->raw = $str;
    }

    /**
     * Initialize the DOM
     * @return void
     */
    protected function initDom()
    {
        if ($this->raw) {
            $this->dom = new DOMDocument;
            $this->dom->loadXML($this->raw, LIBXML_NOBLANKS);
        }
    }

    /**
     * Get the DOM object
     * @return DOMDocument|null
     */
    public function getDom()
    {
        if (!$this->dom) {
            $this->initDom();
        }
        return $this->dom;
    }

    /**
     * Set the DOM object
     * @param DOMDocument $dom
     * @return void
     */
    protected function setDom($dom)
    {
        $this->dom = $dom;
    }

    /**
     * Parse the content
     * @return void
     */
    public function parseContent()
    {
        $this->initDom();
        if (!$this->dom) {
            return;
        }
        foreach ($this->filters as $f) {
            $f->filter($this);
        }
        $this->_retrieveDataFromDom($this->dom);
    }

    /**
     * Parse request string to assign object properties
     * @param DOMDocument $dom
     * @return void
     */
    protected function _retrieveDataFromDom($dom)
    {
        $arr = OP_API::convertXmlToPhpObj($dom->documentElement);
        list($dummy, $credentials) = each($arr);
        list($this->cmd, $this->args) = each($arr);

        $this->username = $credentials['username'];
        $this->password = $credentials['password'];

        if (isset($credentials['hash'])) {
            $this->hash = $credentials['hash'];
        }
        if (isset($credentials['misc'])) {
            $this->misc = $credentials['misc'];
        }

        $this->token = isset($credentials['token']) ? $credentials['token'] : null;
        $this->ip = isset($credentials['ip']) ? $credentials['ip'] : null;

        if (isset($credentials['language'])) {
            $this->language = $credentials['language'];
        }
    }

    /**
     * Set the command
     * @param string $v
     * @return self
     */
    public function setCommand($v)
    {
        $this->cmd = $v;
        return $this;
    }

    /**
     * Get the command
     * @return string|null
     */
    public function getCommand()
    {
        return $this->cmd;
    }

    /**
     * Set the language
     * @param string $v
     * @return self
     */
    public function setLanguage($v)
    {
        $this->language = $v;
        return $this;
    }

    /**
     * Get the language
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set the arguments
     * @param mixed $v
     * @return self
     */
    public function setArgs($v)
    {
        $this->args = $v;
        return $this;
    }

    /**
     * Get the arguments
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Set misc data
     * @param mixed $v
     * @return self
     */
    public function setMisc($v)
    {
        $this->misc = $v;
        return $this;
    }

    /**
     * Get misc data
     * @return mixed
     */
    public function getMisc()
    {
        return $this->misc;
    }

    /**
     * Set authentication details
     * @param array $args
     * @return self
     */
    public function setAuth($args)
    {
        $this->username = isset($args["username"]) ? $args["username"] : null;
        $this->password = isset($args["password"]) ? $args["password"] : null;
        $this->hash = isset($args["hash"]) ? $args["hash"] : null;
        $this->token = isset($args["token"]) ? $args["token"] : null;
        $this->ip = isset($args["ip"]) ? $args["ip"] : null;
        $this->misc = isset($args["misc"]) ? $args["misc"] : null;
        return $this;
    }

    /**
     * Get authentication details
     * @return array
     */
    public function getAuth()
    {
        return array(
            "username" => $this->username,
            "password" => $this->password,
            "hash" => $this->hash,
            "token" => $this->token,
            "ip" => $this->ip,
            "misc" => $this->misc,
        );
    }

    /**
     * Get raw XML request
     * @return string
     */
    public function getRaw()
    {
        if (!$this->raw) {
            $this->raw = $this->_getRequest();
        }
        return $this->raw;
    }

    /**
     * Generate XML request
     * @return string
     */
    public function _getRequest()
    {
        $dom = new DOMDocument('1.0', OP_API::$encoding);

        // Create credentials element
        $credentialsElement = $dom->createElement('credentials');

        // Add username
        $usernameElement = $dom->createElement('username');
        $usernameElement->appendChild(
            $dom->createTextNode(OP_API::encode($this->username))
        );
        $credentialsElement->appendChild($usernameElement);

        // Add password
        $passwordElement = $dom->createElement('password');
        $passwordElement->appendChild(
            $dom->createTextNode(OP_API::encode($this->password))
        );
        $credentialsElement->appendChild($passwordElement);

        // Add hash
        $hashElement = $dom->createElement('hash');
        $hashElement->appendChild(
            $dom->createTextNode(OP_API::encode($this->hash))
        );
        $credentialsElement->appendChild($hashElement);

        // Add language if set
        if (isset($this->language)) {
            $languageElement = $dom->createElement('language');
            $languageElement->appendChild($dom->createTextNode($this->language));
            $credentialsElement->appendChild($languageElement);
        }

        // Add token if set
        if (isset($this->token)) {
            $tokenElement = $dom->createElement('token');
            $tokenElement->appendChild($dom->createTextNode($this->token));
            $credentialsElement->appendChild($tokenElement);
        }

        // Add IP if set
        if (isset($this->ip)) {
            $ipElement = $dom->createElement('ip');
            $ipElement->appendChild($dom->createTextNode($this->ip));
            $credentialsElement->appendChild($ipElement);
        }

        // Add misc if set
        if (isset($this->misc)) {
            $miscElement = $dom->createElement('misc');
            $credentialsElement->appendChild($miscElement);
            OP_API::convertPhpObjToDom($this->misc, $miscElement, $dom);
        }

        // Create root element and add credentials
        $rootElement = $dom->createElement('openXML');
        $rootElement->appendChild($credentialsElement);

        // Add command and arguments
        $rootNode = $dom->appendChild($rootElement);
        $cmdNode = $rootNode->appendChild(
            $dom->createElement($this->getCommand())
        );
        OP_API::convertPhpObjToDom($this->args, $cmdNode, $dom);

        return $dom->saveXML();
    }
}