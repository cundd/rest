<?php


namespace Cundd\Rest\Tests\Functional\Integration;


use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Tests\Functional\Fixtures\FrontendUserAuthentication;
use Cundd\Rest\Tests\Functional\Fixtures\UserProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;

class AuthTest extends AbstractIntegrationCase
{
    public function setUp()
    {
        parent::setUp();
        $this->configureUserProvider();
        $this->configureFrontendUserAuthentication();

        $databaseConnection = $this->getDatabaseConnection();
        $databaseConnection->sql_query('ALTER TABLE fe_users ADD tx_rest_apikey TINYTEXT;');
        if ($databaseConnection->sql_errno() && $databaseConnection->sql_errno() != 1060) {
            throw new \Exception($databaseConnection->sql_error());
        }
        $this->importDataSet(__DIR__ . '/../Fixtures/login.xml');
    }

    private function configureUserProvider()
    {
        /** @var Container $objectContainer */
        $objectContainer = GeneralUtility::makeInstance(Container::class);

        $objectContainer->registerImplementation(
            UserProviderInterface::class,
            UserProvider::class
        );
    }

    private function configureFrontendUserAuthentication()
    {
        FrontendUserAuthentication::reset();

        $GLOBALS['TSFE'] = (object)['fe_user' => new FrontendUserAuthentication()];
    }

    /**
     * @test
     */
    public function getStatusTest()
    {
        $response = $this->request('auth/login');

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
        $response = $this->request('auth/login', 'POST');
        $this->assertSame(
            '{"status":"logged-out"}',
            (string)$response->getBody(),
            $this->getErrorDescription($response)
        );

        $response = $this->request(
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

    /**
     * @return string
     */
    protected function getApiUser()
    {
        return UserProvider::getApiUser();
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        return UserProvider::getApiKey();
    }
}
