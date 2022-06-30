<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\DataProvider;

use LogicException;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

trait DomainModelProphetTrait
{
    /**
     * @param string|null $classOrInterface
     * @return ObjectProphecy
     * @throws LogicException
     */
    abstract protected function prophesize(?string $classOrInterface = null): ObjectProphecy;

    /**
     * @param array $properties
     * @return DomainObjectInterface
     */
    protected function createDomainModelFixture(array $properties = []): DomainObjectInterface
    {
        /** @var DomainObjectInterface|ObjectProphecy $domainModelProphecy */
        $domainModelProphecy = $this->prophesize(DomainObjectInterface::class);
        /** @var MethodProphecy $methodProphecy */
        $methodProphecy = $domainModelProphecy->_getProperties();
        $methodProphecy->willReturn($properties);

        return $domainModelProphecy->reveal();
    }
}
