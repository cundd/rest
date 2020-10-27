<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Authentication;

use Cundd\Rest\Authentication\UserProvider\FeUserProvider;
use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\Functional\FeUserCaseTrait;

/**
 * Test for the FeUser based User Provider
 */
class FeUserProviderTest extends AbstractCase
{
    use FeUserCaseTrait;

    /**
     * @var UserProviderInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = new FeUserProvider();

        $this->addApiKeyColumn();
        $this->importDataSet(__DIR__ . '/../Fixtures/login.xml');
    }

    /**
     * @test
     */
    public function checkCredentialsForValidUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('dan', ''));
        $this->assertFalse($this->fixture->checkCredentials('dan', 'wrongKey'));

        $this->assertTrue($this->fixture->checkCredentials('dan', 'api-key'));
    }

    /**
     * @test
     */
    public function checkCredentialsForUserWithoutApiKeyTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('test', 'someKey'));
        $this->assertFalse($this->fixture->checkCredentials('test', 'NULL'));
        $this->assertFalse($this->fixture->checkCredentials('test', ''));
    }

    /**
     * @test
     */
    public function checkCredentialsForDeletedUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', 'api-key'));
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', ''));
    }

    /**
     * @test
     */
    public function checkCredentialsForDisabledUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', 'api-key'));
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', ''));
    }

    /**
     * @test
     */
    public function checkCredentialsForExpiredUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('expired_user', 'api-key'));
        $this->assertFalse($this->fixture->checkCredentials('expired_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('expired_user', ''));
    }

    /**
     * @test
     */
    public function checkCredentialsForFutureUserTest()
    {
        $this->assertFalse($this->fixture->checkCredentials('future_user', 'api-key'));
        $this->assertFalse($this->fixture->checkCredentials('future_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('future_user', ''));
    }
}
