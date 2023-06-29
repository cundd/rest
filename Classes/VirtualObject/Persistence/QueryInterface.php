<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\Persistence\Backend\ConstraintInterface;

/**
 * A persistence query interface
 */
interface QueryInterface extends OperatorInterface
{
    /**
     * Logical AND
     */
    public const COMBINATOR_AND = 'AND';

    /**
     * Logical OR
     */
    public const COMBINATOR_OR = 'OR';

    /**
     * Ascending ordering of results
     */
    public const ORDER_ASCENDING = 'ASC';

    /**
     * Descending ordering of results
     */
    public const ORDER_DESCENDING = 'DESC';

    /**
     * Executes the query and returns the result
     *
     * @return array Return the result
     * @api
     */
    public function execute(): iterable;

    /**
     * Return a copy of the Query with the given constraint
     *
     * @param ConstraintInterface[]|ConstraintInterface $constraint
     * @return Query
     */
    public function withConstraint($constraint): self;

    /**
     * Return a copy of the Query with the given property names to order the result by
     *
     * ```
     *  [
     *      'foo' => \Cundd\Rest\VirtualObject\Persistence\QueryInterface::ORDER_ASCENDING,
     *      'bar' => \Cundd\Rest\VirtualObject\Persistence\QueryInterface::ORDER_DESCENDING
     *  ]
     * ```
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
     *
     * ```
     *  [
     *      'foo' => \Cundd\Rest\VirtualObject\Persistence\QueryInterface::ORDER_ASCENDING,
     *      'bar' => \Cundd\Rest\VirtualObject\Persistence\QueryInterface::ORDER_DESCENDING
     *  ]
     * ```
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
     * @return ConstraintInterface[]
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
     * @throws MissingConfigurationException if the configuration is not set
     */
    public function getConfiguration(): ?ConfigurationInterface;
}
