<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Authentication;

use Cundd\Rest\Authentication\UserProvider\FeUserProvider;
use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test for the `fe_users`-based User Provider
 */
final class FeUserProviderTest extends AbstractCase
{
    private UserProviderInterface $fixture;

    private const PASSWORD_USER_DAN = 'u"W5hk<)V<MR';
    private const PASSWORD_USER_DUPLICATE_USERNAME = 't@UX%E2k:ge';
    private const PASSWORD_USER_NOT_ENABLEd = 'd/44E"<dpj%(';

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = new FeUserProvider(
            new PasswordHashFactory(),
            GeneralUtility::makeInstance(ConnectionPool::class)
        );

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/login.csv');
    }

    #[Test]
    #[DataProvider('checkCredentialsDataProvider')]
    public function checkCredentialsTest(
        string $username,
        string $password,
        ?int $storagePid,
        bool $expectedResult,
    ): void {
        $configurationProvider = $this->prophesize(
            ConfigurationProviderInterface::class
        );
        $configurationProvider->getSetting(
            'authentication.feUserProvider.storagePageId'
        )->willReturn($storagePid);

        $result = $this->fixture->checkCredentials(
            $configurationProvider->reveal(),
            $username,
            $password
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<non-empty-string, array{0:non-empty-string, 1: string, 2: ?int, 3: bool}>
     */
    public static function checkCredentialsDataProvider(): array
    {
        return [
            'user w/o api key' => [
                'test',
                'any-key',
                100,
                false,
            ],
            'user w/o api key (empty password)' => [
                'test',
                '',
                100,
                false,
            ],
            'user w/ incorrect key' => [
                'dan',
                'any-key',
                100,
                false,
            ],
            'user w/ incorrect key (empty)' => [
                'dan',
                '',
                100,
                false,
            ],
            'user w/ incorrect PID' => [
                'dan',
                self::PASSWORD_USER_DAN,
                2,
                false,
            ],
            'user w/ correct key' => [
                'dan',
                self::PASSWORD_USER_DAN,
                100,
                true,
            ],

            'duplicate user w/ correct key and PID' => [
                'duplicate-username',
                self::PASSWORD_USER_DUPLICATE_USERNAME,
                102,
                true,
            ],
            'duplicate user w/ incorrect PID' => [
                'duplicate-username',
                self::PASSWORD_USER_DUPLICATE_USERNAME,
                104,
                false,
            ],

            'deleted_user w/ correct key and PID' => [
                'deleted_user',
                self::PASSWORD_USER_NOT_ENABLEd,
                100,
                false,
            ],

            'disabled_user w/ correct key and PID' => [
                'disabled_user',
                self::PASSWORD_USER_NOT_ENABLEd,
                100,
                false,
            ],

            'expired_user w/ correct key and PID' => [
                'expired_user',
                self::PASSWORD_USER_NOT_ENABLEd,
                100,
                false,
            ],
        ];
    }
}
