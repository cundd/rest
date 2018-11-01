<?php

namespace Cundd\Rest\VirtualObject\Persistence;

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
     * @var \Cundd\Rest\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * The configuration to use when converting
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var \Cundd\Rest\VirtualObject\Persistence\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * Registers the given Virtual Object
     *
     * This is a high level shorthand for:
     * Object exists?
     *    Yes -> update
     *    No -> add
     *
     * @param VirtualObject $object
     * @return VirtualObject Returns the registered Document
     */
    public function registerObject($object)
    {
        return $this->persistenceManager->registerObject($object);
    }

    /**
     * Adds the given object to the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function add($object)
    {
        $this->persistenceManager->add($object);
    }

    /**
     * Updates the given object in the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function update($object)
    {
        $this->persistenceManager->update($object);
    }

    /**
     * Removes the given object from the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function remove($object)
    {
        $this->persistenceManager->remove($object);
    }

    /**
     * Returns all objects from the database
     *
     * @return array
     */
    public function findAll()
    {
        return $this->createQuery()->execute();
    }

    /**
     * Returns the total number objects of this repository.
     *
     * @return integer The object count
     * @api
     */
    public function countAll()
    {
        return $this->createQuery()->count();
    }

    /**
     * Removes all objects of this repository as if remove() was called for
     * all of them.
     *
     * @return void
     * @api
     */
    public function removeAll()
    {
        foreach ($this->findAll() as $object) {
            $this->remove($object);
        }
    }

    /**
     * Returns the object with the given identifier
     *
     * @param string $identifier
     * @return VirtualObject
     */
    public function findByIdentifier($identifier)
    {
        return $this->persistenceManager->getObjectByIdentifier($identifier);
    }

    /**
     * Sets the configuration to use when converting
     *
     * @param \Cundd\Rest\VirtualObject\ConfigurationInterface $configuration
     * @return $this
     */
    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
        $this->persistenceManager->setConfiguration($configuration);

        return $this;
    }

    /**
     * Returns the configuration to use when converting
     *
     * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException if the configuration is not set
     * @return \Cundd\Rest\VirtualObject\ConfigurationInterface
     */
    public function getConfiguration()
    {
        if (!$this->configuration) {
            throw new MissingConfigurationException('Configuration not set', 1395681118);
        }

        return $this->configuration;
    }

    /**
     * Finds an object matching the given identifier.
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
    public function createQuery()
    {
        /** @var QueryInterface $query */
        $query = $this->objectManager->get(QueryInterface::class);
        $query->setConfiguration($this->getConfiguration());

        return $query;
    }
}
