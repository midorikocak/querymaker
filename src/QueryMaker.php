<?php

declare(strict_types=1);

namespace midorikocak\querymaker;

use InvalidArgumentException;

use function array_keys;
use function array_map;
use function array_values;
use function implode;
use function in_array;
use function uniqid;

class QueryMaker implements QueryInterface
{
    private string $query;
    private string $statement;
    private array $params;
    private string $offset;
    private string $limit;
    private string $orderBy;

    private function __construct()
    {
        $this->query     = '';
        $this->statement = '';
        $this->params    = [];

        $this->limit   = '';
        $this->orderBy = '';
    }

    public static function select($table, array $columns = ['*']) : QueryInterface
    {
        $instance            = new QueryMaker();
        $columnsText         = implode(', ', $columns);
        $instance->statement = 'SELECT ' . $columnsText . ' FROM ' . $table;
        $instance->query     = 'SELECT ' . $columnsText . ' FROM ' . $table;
        return $instance;
    }

    public static function update($table, array $values) : QueryInterface
    {
        $instance            = new QueryMaker();
        $instance->statement = 'UPDATE ' . $table . ' SET ';
        $instance->query     = 'UPDATE ' . $table . ' SET ';
        $instance->prepareParams($values, ', ');
        return $instance;
    }

    public static function insert($table, array $values) : QueryInterface
    {
        $fields      = implode(', ', array_keys($values));
        $params      = implode(', ', array_map(fn($key) => ':' . $key, array_keys($values)));
        $queryValues = implode(', ', array_map(fn($value) => "'$value'", array_values($values)));

        $instance            = new QueryMaker();
        $instance->statement = "INSERT INTO $table ($fields) VALUES ($params)";
        $instance->query     = "INSERT INTO $table ($fields) VALUES ($queryValues)";
        $instance->params    = $values;

        return $instance;
    }

    public static function delete($table) : QueryInterface
    {
        $instance            = new QueryMaker();
        $instance->statement = 'DELETE FROM ' . $table;
        $instance->query     = 'DELETE FROM ' . $table;
        return $instance;
    }

    public function where($key, $value, string $operator = '=') : QueryInterface
    {
        $this->checkOperator($operator);

        $this->statement   .= ' WHERE ' . $key . $operator . ':' . $key;
        $this->query       .= ' WHERE ' . $key . $operator . '\'' . $value . '\'';
        $this->params[$key] = $value;
        return $this;
    }

    public function and($key, $value, string $operator = '=') : QueryInterface
    {
        $this->checkOperator($operator);

        $this->query     .= ' AND ';
        $this->statement .= ' AND ';
        $this->prepareParam($key, $value, 'AND', $operator);
        return $this;
    }

    public function orderBy($key, string $order = 'ASC') : QueryInterface
    {
        $this->orderBy .= ' ORDER BY ' . $key . ' ' . $order;
        return $this;
    }

    public function limit(int $limit) : QueryInterface
    {
        $this->limit .= ' LIMIT ' . $limit;
        return $this;
    }

    public function offset(int $offset) : QueryInterface
    {
        $this->limit .= ' OFFSET ' . $offset;
        return $this;
    }

    public function or($key, $value, $operator = '=') : QueryInterface
    {
        $this->checkOperator($operator);

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
        return $this->query . $this->orderBy . $this->limit;
    }

    public function getStatement() : string
    {
        return $this->statement . $this->orderBy . $this->limit;
    }

    public function getParams() : array
    {
        return $this->params;
    }

    private function prepareParams(array $values, string $glue, string $operator = '=')
    {
        $this->checkOperator($operator);
        $params      = [];
        $queryValues = [];

        foreach ($values as $key => $value) {
            if (!isset($this->params[$key])) {
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

    private function prepareParam(string $key, $value, string $glue, $operator = '=') : void
    {
        $this->prepareParams([$key => $value], $glue, $operator);
    }

    private function checkOperator(string $operator) : void
    {
        $operators = ['=', '>', '>=', '<', '<=', 'LIKE'];
        if (!in_array($operator, $operators, true)) {
            throw new InvalidArgumentException('Invalid Operator');
        }
    }
}
