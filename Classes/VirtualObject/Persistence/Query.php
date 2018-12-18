<?php

namespace Cundd\Rest\VirtualObject\Persistence;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement;

/**
 * Query implementation
 */
class Query implements QueryInterface
{
    /**
     * @var \Cundd\Rest\VirtualObject\Persistence\BackendInterface
     * @inject
     * @deprecated will be removed in 4.0.0
     */
    protected $backend;

    /**
     * @var \Cundd\Rest\VirtualObject\Persistence\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * Constraints array
     *
     * @var array
     */
    protected $constraint = [];

    /**
     * @var array
     */
    protected $orderings = [];

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var string
     */
    protected $sourceIdentifier;

    /**
     * @var Statement
     */
    protected $statement;

    public function execute()
    {
        return $this->persistenceManager->getObjectDataByQuery($this);
    }

    public function count()
    {
        return $this->persistenceManager->getObjectCountByQuery($this);
    }

    public function setOrderings(array $orderings)
    {
        $this->orderings = $orderings;

        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function getOrderings()
    {
        return $this->orderings;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getConstraint()
    {
        return $this->constraint;
    }

    public function setConstraint($constraint)
    {
        $this->constraint = $constraint;

        return $this;
    }

    public function getSourceIdentifier()
    {
        return $this->sourceIdentifier;
    }

    public function setConfiguration($configuration)
    {
        $this->persistenceManager->setConfiguration($configuration);

        return $this;
    }

    public function getConfiguration()
    {
        return $this->persistenceManager->getConfiguration();
    }

    /**
     * Sets the statement of this query programmatically. If you use this, you will lose the abstraction from a concrete
     * storage backend (database)
     *
     * @param string $statement  The statement
     * @param array  $parameters An array of parameters. These will be bound to placeholders '?' in the $statement.
     * @deprecated only implemented for TYPO3 without Doctrine. Will be removed in 4.0.0
     * @return QueryInterface
     */
    public function statement($statement, array $parameters = [])
    {
        $this->statement = new Statement($statement, $parameters);

        return $this;
    }

    /**
     * Returns the statement of this query
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement
     * @deprecated only implemented for TYPO3 without Doctrine. Will be removed in 4.0.0
     */
    public function getStatement()
    {
        return $this->statement;
    }
}
