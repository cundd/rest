<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 11.08.15
 * Time: 15:03
 */

namespace Cundd\Rest\Tests\Functional\Authentication;

use Cundd\Rest\Authentication\UserProvider\FeUserProvider;
use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;



/**
 * Test for the FeUser based User Provider
 */
class FeUserProviderTest extends AbstractCase
{
    /**
     * @var UserProviderInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = new FeUserProvider();

        $databaseConnection = $this->getDatabaseConnection();
        $databaseConnection->sql_query('ALTER TABLE fe_users ADD tx_rest_apikey TINYTEXT;');
        if ($databaseConnection->sql_errno() && $databaseConnection->sql_errno() != 1060) {
            throw new \Exception($databaseConnection->sql_error());
        }
        $this->importDataSet(__DIR__ . '/../Fixtures/login.xml');
    }

    /**
     * @test
     */
    public function checkCredentialsForValidUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('dan', null));
        $this->assertFalse($this->fixture->checkCredentials('dan', ''));
        $this->assertFalse($this->fixture->checkCredentials('dan', 'wrongKey'));

        $this->assertTrue($this->fixture->checkCredentials('dan', 'myApiKey'));
    }

    /**
     * @test
     */
    public function checkCredentialsForUserWithoutApiKeyTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('test', 'someKey'));
        $this->assertFalse($this->fixture->checkCredentials('test', 'NULL'));
        $this->assertFalse($this->fixture->checkCredentials('test', ''));
        $this->assertFalse($this->fixture->checkCredentials('test', null));
    }

    /**
     * @test
     */
    public function checkCredentialsForDeletedUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', 'myApiKey'));
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', ''));
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', null));
    }

    /**
     * @test
     */
    public function checkCredentialsForDisabledUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', 'myApiKey'));
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', ''));
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', null));
    }

    /**
     * @test
     */
    public function checkCredentialsForExpiredUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('expired_user', 'myApiKey'));
        $this->assertFalse($this->fixture->checkCredentials('expired_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('expired_user', ''));
        $this->assertFalse($this->fixture->checkCredentials('expired_user', null));
    }

    /**
     * @test
     */
    public function checkCredentialsForFutureUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('future_user', 'myApiKey'));
        $this->assertFalse($this->fixture->checkCredentials('future_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('future_user', ''));
        $this->assertFalse($this->fixture->checkCredentials('future_user', null));
    }
}
