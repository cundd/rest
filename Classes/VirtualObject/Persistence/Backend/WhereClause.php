<?php

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

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
     * @return $this
     */
    public function appendSql($clause)
    {
        if (!is_string($clause)) {
            throw new \InvalidArgumentException();
        }

        if ($this->clause) {
            $this->clause .= ' AND ' . $clause;
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
}
