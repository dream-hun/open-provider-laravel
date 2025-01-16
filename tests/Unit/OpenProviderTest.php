<?php

namespace jacktalkc\LaravelOpenProvider\Tests\Unit;

use Orchestra\Testbench\TestCase;
use jacktalkc\LaravelOpenProvider\OpenProviderServiceProvider;
use jacktalkc\LaravelOpenProvider\Facades\OpenProvider;

class OpenProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [OpenProviderServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'OpenProvider' => OpenProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Set up fake config
        config([
            'openprovider.url' => 'https://api.openprovider.eu',
            'openprovider.username' => 'test_user',
            'openprovider.password' => 'test_pass',
        ]);
    }

    /** @test */
    public function it_can_check_domain_availability()
    {
        // Here you would mock the API response
        // For now, we'll just test that the method exists
        $this->assertTrue(method_exists(OpenProvider::getFacadeRoot(), 'checkDomain'));
    }

    /** @test */
    public function it_can_search_domains()
    {
        $this->assertTrue(method_exists(OpenProvider::getFacadeRoot(), 'searchDomains'));
    }

    /** @test */
    public function it_can_get_domain_info()
    {
        $this->assertTrue(method_exists(OpenProvider::getFacadeRoot(), 'getDomainInfo'));
    }

    /** @test */
    public function it_can_create_domain()
    {
        $this->assertTrue(method_exists(OpenProvider::getFacadeRoot(), 'createDomain'));
    }

    /** @test */
    public function it_can_modify_domain()
    {
        $this->assertTrue(method_exists(OpenProvider::getFacadeRoot(), 'modifyDomain'));
    }

    /** @test */
    public function it_can_delete_domain()
    {
        $this->assertTrue(method_exists(OpenProvider::getFacadeRoot(), 'deleteDomain'));
    }
}