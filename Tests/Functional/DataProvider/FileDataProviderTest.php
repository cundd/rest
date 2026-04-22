<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\RequestBuilderUtility;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test case for class file related Data Provider functions
 */
final class FileDataProviderTest extends AbstractCase
{
    use FileBuilderTrait;
    use DomainModelProphetTrait;

    protected DataProviderInterface $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixture = $this->getContainer()->get(DataProvider::class);
    }

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    public function getModelDataForFileTest(): void
    {
        $testModel = $this->createFileMock();

        $result = $this->fixture->getModelData(
            RequestBuilderUtility::buildTestRequest('/'),
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
    public function getModelDataForModelWithFileTest(): void
    {
        $testModel = $this->createDomainModelFixture(
            [
                'title' => 'Test',
                'file'  => $this->createFileMock(),
            ]
        );

        $result = $this->fixture->getModelData(
            RequestBuilderUtility::buildTestRequest('/'),
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
}
