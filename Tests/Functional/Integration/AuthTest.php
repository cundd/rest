<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Tests\Functional\FeUserCaseTrait;
use Cundd\Rest\Tests\Functional\Fixtures\FrontendUserAuthentication;
use Cundd\Rest\Tests\Functional\Fixtures\UserProvider;

class AuthTest extends AbstractIntegrationCase
{
    use FeUserCaseTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->configureFrontendUserAuthentication();

        $this->addApiKeyColumn();
        $this->importDataSet(__DIR__ . '/../Fixtures/login.xml');
    }

    private function configureUserProvider(): void
    {
        $this->getContainer()->set(UserProviderInterface::class, new UserProvider());
    }

    private function configureFrontendUserAuthentication(): void
    {
        FrontendUserAuthentication::reset();

        $GLOBALS['TSFE'] = (object)['fe_user' => new FrontendUserAuthentication()];
    }

    /**
     * @test
     */
    public function getStatusTest()
    {
        $response = $this->buildRequestAndDispatch($this->getContainer(), 'auth/login');

        $this->assertSame(
            200,
            $response->getStatusCode(),
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            '{"status":"logged-out"}',
            (string)$response->getBody(),
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function checkLoginJsonTest()
    {
        $objectManager = $this->getContainer();
        $this->configureUserProvider();
        $response = $this->buildRequestAndDispatch($objectManager, 'auth/login', 'POST');
        $this->assertSame(
            '{"status":"logged-out"}',
            (string)$response->getBody(),
            $this->getErrorDescription($response)
        );

        $response = $this->buildRequestAndDispatch(
            $objectManager,
            'auth/login',
            'POST',
            ['username' => $this->getApiUser(), 'apikey' => $this->getApiKey()],
            ['Content-Type' => 'multipart/form-data']
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            '{"status":"logged-in"}',
            (string)$response->getBody(),
            $this->getErrorDescription($response)
        );
    }

    protected function getApiUser(): string
    {
        return UserProvider::getApiUser();
    }

    protected function getApiKey(): string
    {
        return UserProvider::getApiKey();
    }
}
