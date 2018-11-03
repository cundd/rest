<?php


namespace Cundd\Rest\Tests\Functional\DataProvider;


use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

trait DomainModelProphetTrait
{
    /**
     * @param string|null $classOrInterface
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     *
     * @throws \LogicException
     */
    abstract protected function prophesize($classOrInterface = null);

    /**
     * @param array $properties
     * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
     */
    protected function createDomainModelFixture(array $properties = [])
    {
        /** @var DomainObjectInterface|ObjectProphecy $domainModelProphecy */
        $domainModelProphecy = $this->prophesize(DomainObjectInterface::class);
        $domainModelProphecy->_getProperties()->willReturn($properties);

        return $domainModelProphecy->reveal();
    }
}
