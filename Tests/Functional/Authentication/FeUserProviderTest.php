<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Authentication;

use Cundd\Rest\Authentication\UserProvider\FeUserProvider;
use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\Functional\FeUserCaseTrait;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test for the FeUser based User Provider
 */
class FeUserProviderTest extends AbstractCase
{
    use FeUserCaseTrait;

    protected UserProviderInterface $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = new FeUserProvider();

        $this->addApiKeyColumn();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/login.csv');
    }

    #[Test]
    public function checkCredentialsForValidUserTest(): void
    {
        $this->assertFalse($this->fixture->checkCredentials('dan', ''));
        $this->assertFalse($this->fixture->checkCredentials('dan', 'wrongKey'));

        $this->assertTrue($this->fixture->checkCredentials('dan', 'api-key'));
    }

    #[Test]
    public function checkCredentialsForUserWithoutApiKeyTest(): void
    {
        $this->assertFalse($this->fixture->checkCredentials('test', 'someKey'));
        $this->assertFalse($this->fixture->checkCredentials('test', 'NULL'));
        $this->assertFalse($this->fixture->checkCredentials('test', ''));
    }

    #[Test]
    public function checkCredentialsForDeletedUserTest(): void
    {
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', 'api-key'));
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('deleted_user', ''));
    }

    #[Test]
    public function checkCredentialsForDisabledUserTest(): void
    {
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', 'api-key'));
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('disabled_user', ''));
    }

    #[Test]
    public function checkCredentialsForExpiredUserTest(): void
    {
        $this->assertFalse($this->fixture->checkCredentials('expired_user', 'api-key'));
        $this->assertFalse($this->fixture->checkCredentials('expired_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('expired_user', ''));
    }

    #[Test]
    public function checkCredentialsForFutureUserTest(): void
    {
        $this->assertFalse($this->fixture->checkCredentials('future_user', 'api-key'));
        $this->assertFalse($this->fixture->checkCredentials('future_user', 'wrongKey'));
        $this->assertFalse($this->fixture->checkCredentials('future_user', ''));
    }
}
