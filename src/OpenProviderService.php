<?php

namespace jacktalkc\LaravelOpenProvider;

use OP_API;
use OP_Reply;
use OP_Request;
use OP_API_Exception;

class OpenProviderService
{
    protected OP_API $api;
    protected array $config;

    public function __construct(OP_API $api, array $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    /**
     * Create a new API request
     *
     * @param string $command
     * @param array $args
     * @return OP_Reply
     * @throws OP_API_Exception
     */
    protected function request(string $command, array $args = []): OP_Reply
    {
        $request = new OP_Request();
        $request->setCommand($command);
        $request->setArgs($args);
        $request->setAuth([
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'hash' => $this->config['hash'] ?? null,
            'token' => $this->config['token'] ?? null,
            'ip' => $this->config['ip'] ?? null,
        ]);

        return $this->api->process($request);
    }

    /**
     * Check domain availability
     *
     * @param string $domain
     * @return mixed
     * @throws OP_API_Exception
     */
    public function checkDomain(string $domain)
    {
        $response = $this->request('checkDomainRequest', [
            'domains' => [
                'domain' => [
                    'name' => $domain
                ]
            ]
        ]);
        return $response->getValue();
    }

    /**
     * Search domains
     *
     * @param array $criteria
     * @return mixed
     * @throws OP_API_Exception
     */
    public function searchDomains(array $criteria = [])
    {
        $response = $this->request('searchDomainRequest', $criteria);
        return $response->getValue();
    }

    /**
     * Get domain info
     *
     * @param string $domain
     * @return mixed
     * @throws OP_API_Exception
     */
    public function getDomainInfo(string $domain)
    {
        $response = $this->request('retrieveDomainRequest', [
            'domain' => [
                'name' => $domain
            ]
        ]);
        return $response->getValue();
    }

    // Add more domain-related methods as needed
}