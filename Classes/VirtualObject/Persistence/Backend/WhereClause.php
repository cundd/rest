<?php

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

class WhereClause
{
    private $clause = '';
    private $boundVariables = [];

    /**
     * WHERE-clause constructor
     *
     * @param string $clause
     * @param array  $boundVariables
     */
    public function __construct($clause = '', array $boundVariables = [])
    {
        $this->setClause($clause);
        $this->boundVariables = $boundVariables;
    }

    /**
     * Set the SQL WHERE-clause
     *
     * @param string $clause
     * @return $this
     */
    public function setClause($clause)
    {
        if (!is_string($clause)) {
            throw new \InvalidArgumentException();
        }
        $this->clause = $clause;

        return $this;
    }

    /**
     * Append the string to the SQL WHERE-clause
     *
     * @param string $clause
     * @param string $combinator
     * @return $this
     */
    public function appendSql($clause, $combinator = QueryInterface::COMBINATOR_AND)
    {
        if (!is_string($clause)) {
            throw new \InvalidArgumentException();
        }
        $this->assertCombinator($combinator);

        if ($this->clause) {
            $this->clause .= ' ' . strtoupper($combinator) . ' ' . $clause;
        } else {
            $this->clause = $clause;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getClause()
    {
        return $this->clause;
    }

    /**
     * @return array
     */
    public function getBoundVariables()
    {
        return $this->boundVariables;
    }

    /**
     * @param string           $key
     * @param string|int|float $value
     * @return $this
     */
    public function bindVariable($key, $value)
    {
        $this->boundVariables[$key] = $value;

        return $this;
    }

    /**
     * @param $combinator
     */
    public static function assertCombinator($combinator)
    {
        if (!is_string($combinator)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Logical combinator must be of type string, \'%s\' given',
                    is_object($combinator) ? get_class($combinator) : gettype($combinator)
                )
            );
        }
        if (!in_array(strtoupper($combinator), [QueryInterface::COMBINATOR_AND, QueryInterface::COMBINATOR_OR])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Logical combinator must be either \'%s\' or \'%s\'',
                    QueryInterface::COMBINATOR_AND,
                    QueryInterface::COMBINATOR_OR
                )
            );
        }
    }
}
