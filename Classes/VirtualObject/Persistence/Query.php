<?php

namespace Cundd\Rest\VirtualObject\Persistence;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement;

/**
 * Query implementation
 */
class Query implements QueryInterface
{
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

    public function getSourceIdentifier()
    {
        return $this->sourceIdentifier;
    }

    /**
     * Return a copy of the Query with the given constraints
     *
     * @param array $constraint
     * @return QueryInterface
     */
    public function withConstraints(array $constraint): QueryInterface
    {
        $clone = clone $this;
        $clone->constraint = $constraint;

        return $clone;
    }

    /**
     * Return a copy of the Query with the given orderings
     *
     * @param array $orderings
     * @return QueryInterface
     */
    public function withOrderings(array $orderings): QueryInterface
    {
        $clone = clone $this;
        $clone->orderings = $orderings;

        return $clone;
    }

    /**
     * Return a copy of the Query with the given limit
     *
     * @param int $limit
     * @return QueryInterface
     */
    public function withLimit(int $limit): QueryInterface
    {
        $clone = clone $this;
        $clone->limit = $limit;

        return $clone;
    }

    /**
     * Return a copy of the Query with the given offset
     *
     * @param int $offset
     * @return QueryInterface
     */
    public function withOffset(int $offset): QueryInterface
    {
        $clone = clone $this;
        $clone->offset = $offset;

        return $clone;
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
