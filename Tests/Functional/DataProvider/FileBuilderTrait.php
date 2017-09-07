<?php


namespace Cundd\Rest\Tests\Functional\DataProvider;

use PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_Stub_Return;
use TYPO3\CMS\Core\Resource\File;

trait FileBuilderTrait
{
    /**
     * Returns a matcher that matches when the method is executed
     * zero or more times.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    public static function any()
    {
        return new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
    }

    /**
     * @param mixed $value
     *
     * @return PHPUnit_Framework_MockObject_Stub_Return
     */
    public static function returnValue($value)
    {
        return new PHPUnit_Framework_MockObject_Stub_Return($value);
    }

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @param string $className
     *
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    abstract public function getMockBuilder($className);

    /**
     * @return File|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createFileMock()
    {
        $originalFileProperties = [
            'identifier' => sha1('testFile' . time()),
            'name'       => 'Original file name',
            'mimeType'   => 'MimeType',
        ];
        /** @var File|\PHPUnit_Framework_MockObject_MockObject $originalFileMock */
        $originalFileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMockClassName('Mock_TYPO3_CMS_Core_Resource_File')
            ->getMock();
        $originalFileMock->expects($this->any())
            ->method('getProperties')
            ->will(
                $this->returnValue($originalFileProperties)
            );
        $originalFileMock->expects($this->any())
            ->method('getName')
            ->will(
                $this->returnValue($originalFileProperties['name'])
            );
        $originalFileMock->expects($this->any())
            ->method('getMimeType')
            ->will(
                $this->returnValue($originalFileProperties['mimeType'])
            );
        $originalFileMock->expects($this->any())
            ->method('getPublicUrl')
            ->will(
                $this->returnValue('http://url')
            );
        $originalFileMock->expects($this->any())
            ->method('getSize')
            ->will(
                $this->returnValue(10)
            );

        return $originalFileMock;
    }
}