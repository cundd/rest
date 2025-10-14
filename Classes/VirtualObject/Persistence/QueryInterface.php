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
     *
     * @api
     */
    public function execute(): iterable;

    /**
     * Return a copy of the Query with the given constraint
     *
     * @param ConstraintInterface[]|ConstraintInterface $constraint
     *
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
     * @return Query
     */
    public function withOrderings(array $orderings): self;

    /**
     * Return a copy of the Query with the given limit
     *
     * @return Query
     */
    public function withLimit(int $limit): self;

    /**
     * Return a copy of the Query with the given offset
     *
     * @return Query
     */
    public function withOffset(int $offset): self;

    /**
     * Return the query result count
     *
     * @return int The query result count
     *
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
     * @api
     */
    public function getOrderings(): array;

    /**
     * Return the maximum size of the result set to limit
     *
     * Return zero if no limit should be applied
     *
     * @api
     */
    public function getLimit(): int;

    /**
     * Return the start offset of the result set
     *
     * @api
     */
    public function getOffset(): int;

    /**
     * Gets the constraint for this query
     *
     * @return ConstraintInterface[]
     *
     * @api
     */
    public function getConstraint(): array;

    /**
     * Return the source identifier for the new query
     */
    public function getSourceIdentifier(): string;

    /**
     * Set the configuration to use when converting
     */
    public function setConfiguration(ConfigurationInterface $configuration): self;

    /**
     * Return the configuration to use when converting
     *
     * @throws MissingConfigurationException if the configuration is not set
     */
    public function getConfiguration(): ?ConfigurationInterface;
}
