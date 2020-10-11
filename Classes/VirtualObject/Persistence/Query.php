<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Persistence\Backend\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement;

/**
 * Query implementation
 */
class Query implements QueryInterface
{
    /**
     * @var PersistenceManager
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

    /**
     * Query constructor
     *
     * @param ConstraintInterface[]|ConstraintInterface $constraint
     * @param array                                     $orderings
     * @param int                                       $limit
     * @param int                                       $offset
     * @param string                                    $sourceIdentifier
     * @param PersistenceManager|null                   $persistenceManager
     */
    public function __construct(
        $constraint = [],
        array $orderings = [],
        int $limit = 0,
        int $offset = 0,
        string $sourceIdentifier = '',
        ?PersistenceManager $persistenceManager = null
    ) {
        $this->setConstraint($constraint);
        $this->persistenceManager = $persistenceManager;
        $this->orderings = $orderings;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->sourceIdentifier = $sourceIdentifier;
    }

    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function execute(): iterable
    {
        return $this->persistenceManager->getObjectDataByQuery($this);
    }

    public function count(): int
    {
        return $this->persistenceManager->getObjectCountByQuery($this);
    }

    public function getOrderings(): array
    {
        return $this->orderings;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getConstraint(): array
    {
        return $this->constraint;
    }

    public function getSourceIdentifier(): string
    {
        return $this->sourceIdentifier;
    }

    public function withConstraint($constraint): QueryInterface
    {
        $clone = clone $this;
        $clone->setConstraint($constraint);

        return $clone;
    }

    public function withOrderings(array $orderings): QueryInterface
    {
        $clone = clone $this;
        $clone->orderings = $orderings;

        return $clone;
    }

    public function withLimit(int $limit): QueryInterface
    {
        $clone = clone $this;
        $clone->limit = $limit;

        return $clone;
    }

    public function withOffset(int $offset): QueryInterface
    {
        $clone = clone $this;
        $clone->offset = $offset;

        return $clone;
    }

    public function setConfiguration(ConfigurationInterface $configuration): QueryInterface
    {
        $this->persistenceManager->setConfiguration($configuration);

        return $this;
    }

    public function getConfiguration(): ?ConfigurationInterface
    {
        if (!$this->persistenceManager) {
            return null;
        }

        return $this->persistenceManager->getConfiguration();
    }

    private function setConstraint($constraint)
    {
        if ($constraint instanceof ConstraintInterface) {
            $this->constraint = [$constraint];

            return;
        }
        if (!is_array($constraint)) {
            throw new InvalidArgumentException(
                sprintf('Argument "constraint" must be an array of or an instance of "%s"', ConstraintInterface::class)
            );
        }
        $this->constraint = $constraint;
    }
}
