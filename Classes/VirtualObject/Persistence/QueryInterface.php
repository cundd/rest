<?php

namespace Cundd\Rest\VirtualObject\Persistence;

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
    const ORDER_ASCENDING = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING;

    /**
     * Descending ordering of results
     */
    const ORDER_DESCENDING = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;

    /**
     * Executes the query and returns the result
     *
     * @return array Returns the result
     * @api
     */
    public function execute();

    /**
     * Sets the property names to order the result by. Expected like this:
     * array(
     *  'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     *  'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     *
     * @param array $orderings The property names to order by
     * @return QueryInterface
     * @api
     * @deprecated will be removed in 4.0.0
     */
    public function setOrderings(array $orderings);

    /**
     * Sets the maximum size of the result set to limit
     *
     * Returns $this to allow for chaining (fluid interface)
     *
     * @param integer $limit
     * @return QueryInterface
     * @api
     * @deprecated will be removed in 4.0.0
     */
    public function setLimit($limit);

    /**
     * Sets the start offset of the result set to offset
     *
     * Returns $this to allow for chaining (fluid interface).
     *
     * @param integer $offset
     * @return QueryInterface
     * @api
     * @deprecated will be removed in 4.0.0
     */
    public function setOffset($offset);

    /**
     * Gets the constraint for this query
     *
     * @param array $constraint
     * @return QueryInterface
     * @api
     * @deprecated will be removed in 4.0.0
     */
    public function setConstraint($constraint);

    /**
     * Returns the query result count
     *
     * @return integer The query result count
     * @api
     */
    public function count();

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
    public function getOrderings();

    /**
     * Returns the maximum size of the result set to limit
     *
     * Return zero if no limit should be applied
     *
     * @return integer
     * @api
     */
    public function getLimit();

    /**
     * Returns the start offset of the result set
     *
     * @return integer
     * @api
     */
    public function getOffset();

    /**
     * Gets the constraint for this query
     *
     * @return mixed the constraint, or null if none
     * @api
     */
    public function getConstraint();

    /**
     * Returns the source identifier for the new query
     *
     * @return string
     */
    public function getSourceIdentifier();

    /**
     * Sets the configuration to use when converting
     *
     * @param \Cundd\Rest\VirtualObject\ConfigurationInterface $configuration
     * @return $this
     * @deprecated will be removed in 4.0.0
     */
    public function setConfiguration($configuration);

    /**
     * Returns the configuration to use when converting
     *
     * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException if the configuration is not set
     * @return \Cundd\Rest\VirtualObject\ConfigurationInterface
     */
    public function getConfiguration();
}
