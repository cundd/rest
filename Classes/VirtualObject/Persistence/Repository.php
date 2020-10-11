<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\ObjectManager;
use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\VirtualObject;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;

/**
 * Repository for Virtual Objects
 */
class Repository implements RepositoryInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * The configuration to use when converting
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * Repository constructor.
     *
     * @param ObjectManager               $objectManager
     * @param PersistenceManager          $persistenceManager
     * @param ConfigurationInterface|null $configuration
     */
    public function __construct(
        ObjectManager $objectManager,
        PersistenceManager $persistenceManager,
        ?ConfigurationInterface $configuration = null
    ) {
        $this->objectManager = $objectManager;
        $this->configuration = $configuration;
        $this->persistenceManager = $persistenceManager;
    }

    public function registerObject(VirtualObject $object): VirtualObject
    {
        return $this->persistenceManager->registerObject($object);
    }

    public function add(VirtualObject $object): void
    {
        $this->persistenceManager->add($object);
    }

    public function update(VirtualObject $object): void
    {
        $this->persistenceManager->update($object);
    }

    public function remove(VirtualObject $object): void
    {
        $this->persistenceManager->remove($object);
    }

    public function findAll(): iterable
    {
        return $this->createQuery()->execute();
    }

    public function countAll(): int
    {
        return $this->createQuery()->count();
    }

    public function removeAll(): void
    {
        foreach ($this->findAll() as $object) {
            $this->remove($object);
        }
    }

    public function findByIdentifier($identifier): ?VirtualObject
    {
        return $this->persistenceManager->getObjectByIdentifier($identifier);
    }

    public function setConfiguration(ConfigurationInterface $configuration): RepositoryInterface
    {
        $this->configuration = $configuration;
        $this->persistenceManager->setConfiguration($configuration);

        return $this;
    }

    public function getConfiguration(): ConfigurationInterface
    {
        if (!$this->configuration) {
            throw new MissingConfigurationException('Configuration not set', 1395681118);
        }

        return $this->configuration;
    }

    /**
     * Finds an object matching the given identifier
     *
     * @param integer $uid The identifier of the object to find
     * @return object The matching object if found, otherwise NULL
     * @api
     */
    public function findByUid($uid)
    {
        return $this->findByIdentifier($uid);
    }

    /**
     * Sets the property names to order the result by per default.
     * Expected like this:
     * array(
     * 'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     * 'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     *
     * @param array $defaultOrderings The property names to order by
     * @return void
     * @api
     */
    public function setDefaultOrderings(array $defaultOrderings)
    {
        // TODO: Implement setDefaultOrderings() method
    }

    /**
     * Sets the default query settings to be used in this repository
     *
     * @param QuerySettingsInterface $defaultQuerySettings The query settings to be used by default
     * @return void
     * @api
     */
    public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings)
    {
        // TODO: Implement setDefaultQuerySettings() method.
    }

    /**
     * Returns a query for objects of this repository
     *
     * @return QueryInterface
     * @api
     */
    public function createQuery(): QueryInterface
    {
        /** @var QueryInterface $query */
        $query = $this->objectManager->get(QueryInterface::class);
        $query->setConfiguration($this->getConfiguration());

        return $query;
    }
}
