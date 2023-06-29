<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

abstract class AbstractLogicalConstraint implements ConstraintInterface
{
    /**
     * @var array|ConstraintInterface[]
     */
    private $constraints;

    /**
     * LogicalAnd constructor.
     *
     * @param ConstraintInterface[] $constraints
     */
    public function __construct(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public static function build(ConstraintInterface ...$constraints): self
    {
        return new static($constraints);
    }

    /**
     * @return array|ConstraintInterface[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
