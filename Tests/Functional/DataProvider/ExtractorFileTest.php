<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Cundd\Rest\DataProvider\Extractor;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\RequestBuilderUtility;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\UriInterface;

/**
 * Test case for class file related Data Provider functions
 */
class ExtractorFileTest extends AbstractCase
{
    use FileBuilderTrait;
    use DomainModelProphetTrait;

    protected ExtractorInterface $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixture = $this->getContainer()->get(Extractor::class);
    }

    #[Test]
    public function extractForFileTest(): void
    {
        $testModel = $this->createFileMock();

        $result = $this->fixture->extract(
            self::buildTestUri(),
            $testModel
        );
        $this->assertNotEmpty($result);
        $this->assertEquals(
            [
                'name'     => 'Original file name',
                'mimeType' => 'MimeType',
                'url'      => 'http://url',
                'size'     => 10,
            ],
            $result
        );
    }

    #[Test]
    public function extractForModelWithFileTest(): void
    {
        $testModel = $this->createDomainModelFixture(
            [
                'title' => 'Test',
                'file'  => $this->createFileMock(),
            ]
        );

        $result = $this->fixture->extract(
            self::buildTestUri(),
            $testModel
        );
        $this->assertNotEmpty($result);
        $this->assertEquals(
            [
                'title' => 'Test',
                'file'  => [
                    'name'     => 'Original file name',
                    'mimeType' => 'MimeType',
                    'url'      => 'http://url',
                    'size'     => 10,
                ],
            ],
            $result
        );
    }

    private static function buildTestUri(): UriInterface
    {
        return RequestBuilderUtility::buildTestUri('/');
    }
}
