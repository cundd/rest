<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\ConfigurationInterface;

/**
 * A persistence query interface
 */
interface QueryInterface
{
    /**
     * The '=' comparison operator.
     *
     * @api
     */
    const OPERATOR_EQUAL_TO = 1;

    /**
     * The '!=' comparison operator.
     *
     * @api
     */
    const OPERATOR_NOT_EQUAL_TO = 2;

    /**
     * The '<' comparison operator.
     *
     * @api
     */
    const OPERATOR_LESS_THAN = 3;

    /**
     * The '<=' comparison operator.
     *
     * @api
     */
    const OPERATOR_LESS_THAN_OR_EQUAL_TO = 4;

    /**
     * The '>' comparison operator.
     *
     * @api
     */
    const OPERATOR_GREATER_THAN = 5;

    /**
     * The '>=' comparison operator.
     *
     * @api
     */
    const OPERATOR_GREATER_THAN_OR_EQUAL_TO = 6;

    /**
     * The 'like' comparison operator.
     *
     * @api
     */
    const OPERATOR_LIKE = 7;

    /**
     * The 'contains' comparison operator for collections.
     *
     * @api
     */
    const OPERATOR_CONTAINS = 8;

    /**
     * The 'in' comparison operator.
     *
     * @api
     */
    const OPERATOR_IN = 9;

    /**
     * The 'is NULL' comparison operator.
     *
     * @api
     */
    const OPERATOR_IS_NULL = 10;

    /**
     * The 'is empty' comparison operator for collections.
     *
     * @api
     */
    const OPERATOR_IS_EMPTY = 11;

    /**
     * Logical AND
     */
    const COMBINATOR_AND = 'AND';

    /**
     * Logical OR
     */
    const COMBINATOR_OR = 'OR';

    /**
     * Ascending ordering of results
     */
    const ORDER_ASCENDING = 'ASC';

    /**
     * Descending ordering of results
     */
    const ORDER_DESCENDING = 'DESC';

    /**
     * Executes the query and returns the result
     *
     * @return array Return the result
     * @api
     */
    public function execute();

    /**
     * Return a copy of the Query with the given constraints
     *
     * @param array $constraint
     * @return Query
     */
    public function withConstraints(array $constraint): self;

    /**
     * Return a copy of the Query with the given property names to order the result by
     *
     * @example
     *  [
     *      'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     *      'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     *  ]
     *
     * @param array $orderings
     * @return Query
     */
    public function withOrderings(array $orderings): self;

    /**
     * Return a copy of the Query with the given limit
     *
     * @param int $limit
     * @return Query
     */
    public function withLimit(int $limit): self;

    /**
     * Return a copy of the Query with the given offset
     *
     * @param int $offset
     * @return Query
     */
    public function withOffset(int $offset): self;

    /**
     * Return the query result count
     *
     * @return integer The query result count
     * @api
     */
    public function count(): int;

    /**
     * Gets the property names to order the result by, like this:
     * array(
     *  'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     *  'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     *
     * @return array
     * @api
     */
    public function getOrderings(): array;

    /**
     * Return the maximum size of the result set to limit
     *
     * Return zero if no limit should be applied
     *
     * @return integer
     * @api
     */
    public function getLimit(): int;

    /**
     * Return the start offset of the result set
     *
     * @return integer
     * @api
     */
    public function getOffset(): int;

    /**
     * Gets the constraint for this query
     *
     * @return mixed the constraint, or null if none
     * @api
     */
    public function getConstraint(): array;

    /**
     * Return the source identifier for the new query
     *
     * @return string
     */
    public function getSourceIdentifier(): string;

    /**
     * Set the configuration to use when converting
     *
     * @param ConfigurationInterface $configuration
     * @return self
     */
    public function setConfiguration(ConfigurationInterface $configuration): self;

    /**
     * Return the configuration to use when converting
     *
     * @return ConfigurationInterface|null
     * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException if the configuration is not set
     * @return \Cundd\Rest\VirtualObject\ConfigurationInterface|null
     */
    public function getConfiguration(): ?ConfigurationInterface;
}
