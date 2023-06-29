<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

interface OperatorInterface
{
    /**
     * The '=' comparison operator.
     *
     * @api
     */
    public const OPERATOR_EQUAL_TO = 1;

    /**
     * The '!=' comparison operator.
     *
     * @api
     */
    public const OPERATOR_NOT_EQUAL_TO = 2;

    /**
     * The '<' comparison operator.
     *
     * @api
     */
    public const OPERATOR_LESS_THAN = 3;

    /**
     * The '<=' comparison operator.
     *
     * @api
     */
    public const OPERATOR_LESS_THAN_OR_EQUAL_TO = 4;

    /**
     * The '>' comparison operator.
     *
     * @api
     */
    public const OPERATOR_GREATER_THAN = 5;

    /**
     * The '>=' comparison operator.
     *
     * @api
     */
    public const OPERATOR_GREATER_THAN_OR_EQUAL_TO = 6;

    /**
     * The 'like' comparison operator.
     *
     * @api
     */
    public const OPERATOR_LIKE = 7;

    /**
     * The 'contains' comparison operator for collections.
     *
     * @api
     */
    public const OPERATOR_CONTAINS = 8;

    /**
     * The 'in' comparison operator.
     *
     * @api
     */
    public const OPERATOR_IN = 9;

    /**
     * The 'is NULL' comparison operator.
     *
     * @api
     */
    public const OPERATOR_IS_NULL = 10;

    /**
     * The 'is empty' comparison operator for collections.
     *
     * @api
     */
    public const OPERATOR_IS_EMPTY = 11;
}
