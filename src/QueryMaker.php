<?php

declare(strict_types=1);

namespace midorikocak\querymaker;

use function implode;
use function preg_match;
use function reset;
use function strlen;
use function substr;
use function uniqid;

class QueryMaker implements QueryInterface
{
    private string $query;
    private string $statement;
    private array $params;

    private function __construct()
    {
        $this->query     = '';
        $this->statement = '';
        $this->params    = [];
    }

    public static function select($table, array $columns = ['*']) : QueryInterface
    {
        $instance = new QueryMaker();
        $columnsText     = implode(', ', $columns);
        $instance->statement = 'SELECT ' . $columnsText . ' FROM ' . $table;
        $instance->query     = 'SELECT ' . $columnsText . ' FROM ' . $table;
        return $instance;
    }

    public static function update($table, array $values) : QueryInterface
    {
        $instance = new QueryMaker();
        $instance->statement = 'UPDATE ' . $table . ' SET ';
        $instance->query     = 'UPDATE ' . $table . ' SET ';
        $instance->prepareParams($values, ', ');
        return $instance;
    }

    public function where($key, $value) : QueryInterface
    {
        $hasOperator = preg_match('~^(([<>=])+(=)*)~', (string) $value);
        if (! empty($hasOperator)) {
            $operator = '';
        } else {
            $operator = '=';
        }

        $this->statement   .= ' WHERE ' . $key . $operator . ':' . $key;
        $this->query       .= ' WHERE ' . $key . $operator . '\'' . $value . '\'';
        $this->params[$key] = $value;
        return $this;
    }

    public function and($key, $value) : QueryInterface
    {
        $this->query     .= " AND ";
        $this->statement .= " AND ";
        $this->prepareParam($key, $value, 'AND');
        return $this;
    }

    public function or($key, $value) : QueryInterface
    {
        $this->query     .= " OR ";
        $this->statement .= " OR ";
        $this->prepareParam($key, $value, 'OR');
        return $this;
    }

    public function between($key, $before, $after) : QueryInterface
    {
        $this->query     .= $key . " BETWEEN $before AND $after";
        $this->statement .= $key . ' BETWEEN :before AND :after';

        $this->params['before'] = $before;
        $this->params['after']  = $after;
        return $this;
    }

    public function getQuery() : string
    {
        return $this->query;
    }

    public function getStatement() : string
    {
        return $this->statement;
    }

    public function getParams() : array
    {
        return $this->params;
    }

    private function prepareParams(array $values, string $glue)
    {
        $params      = [];
        $queryValues = [];

        foreach ($values as $key => $value) {
            $hasOperator = preg_match('~^(([<>=])+(=)*)~', $value, $matches);
            if (! empty($hasOperator)) {
                $operator = reset($matches);
                $value    = substr($value, strlen($operator));
            } else {
                $operator = '=';
            }

            if (! isset($this->params[$key])) {
                $queryValues[] = $key . $operator . '\'' . $value . '\'';
                $params []     = $key . $operator . ':' . $key;

                $this->params[$key] = $value;
            } else {
                $uniqid        = uniqid('', true);
                $queryValues[] = $key . $operator . '\'' . $value . '\'';
                $params []     = $key . $operator . ':' . $key . $uniqid;

                $this->params[$key . $uniqid] = $value;
            }
        }

        $this->query     .= implode($glue, $queryValues);
        $this->statement .= implode($glue, $params);
    }

    private function prepareParam(string $key, $value, string $glue)
    {
        $this->prepareParams([$key => $value], $glue);
    }
}
