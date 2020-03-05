<?php

declare(strict_types=1);

namespace midorikocak\querymaker;

use InvalidArgumentException;

use function array_keys;
use function array_map;
use function array_values;
use function implode;
use function in_array;
use function preg_replace;
use function uniqid;

class QueryMaker implements QueryInterface
{
    private string $query;
    private string $statement;
    private array $params;
    private string $offset;
    private string $limit;
    private string $orderBy;

    public function __construct()
    {
        $this->reset();
    }

    private function reset(): void
    {
        $this->query = '';
        $this->statement = '';
        $this->params = [];

        $this->limit = '';
        $this->orderBy = '';
    }

    public function select($table, array $columns = ['*']): QueryInterface
    {
        $this->reset();
        $columnsText = implode(', ', $columns);
        $this->statement = 'SELECT ' . $columnsText . ' FROM ' . $table;
        $this->query = 'SELECT ' . $columnsText . ' FROM ' . $table;
        return $this;
    }

    public function count($table = null): QueryInterface
    {
        if ($table && $this->query === '') {
            $this->reset();
            $this->statement = 'SELECT COUNT(*) FROM ' . $table;
            $this->query = 'SELECT COUNT(*) FROM ' . $table;
            return $this;
        }

        if (!$table && $this->query === '') {
            throw new InvalidArgumentException('Cannot count');
        }

        $query = $this->query;
        $statement = $this->statement;
        $this->query = preg_replace('/SELECT .*? FROM/', 'SELECT COUNT(*) FROM', $this->query);
        $this->statement = preg_replace('/SELECT .*? FROM/', 'SELECT COUNT(*) FROM', $this->statement);

        $toReturn = clone $this;

        $this->query = $query;
        $this->statement = $statement;

        return $toReturn;
    }

    public function update($table, array $values): QueryInterface
    {
        $this->reset();
        $this->statement = 'UPDATE ' . $table . ' SET ';
        $this->query = 'UPDATE ' . $table . ' SET ';
        $this->prepareParams($values, ', ');
        return $this;
    }

    public function insert($table, array $values): QueryInterface
    {
        $this->reset();
        $fields = implode(', ', array_keys($values));
        $params = implode(', ', array_map(fn($key) => ':' . $key, array_keys($values)));
        $queryValues = implode(', ', array_map(fn($value) => "'$value'", array_values($values)));

        $this->statement = "INSERT INTO $table ($fields) VALUES ($params)";
        $this->query = "INSERT INTO $table ($fields) VALUES ($queryValues)";
        $this->params = $values;

        return $this;
    }

    public function delete($table): QueryInterface
    {
        $this->reset();
        $this->statement = 'DELETE FROM ' . $table;
        $this->query = 'DELETE FROM ' . $table;
        return $this;
    }

    public function where($key, $value, string $operator = '='): QueryInterface
    {
        $this->checkOperator($operator);

        $this->statement .= ' WHERE ' . $key . ' ' . $operator . ' :' . $key;
        $this->query .= ' WHERE ' . $key . ' ' . $operator . ' \'' . $value . '\'';
        $this->params[$key] = $value;
        return $this;
    }

    public function and($key, $value, string $operator = '='): QueryInterface
    {
        $this->checkOperator($operator);

        $this->query .= ' AND ';
        $this->statement .= ' AND ';
        $this->prepareParam($key, $value, 'AND', $operator);
        return $this;
    }

    public function orderBy($key, string $order = 'ASC'): QueryInterface
    {
        if ($order !== 'DESC' && $order !== 'ASC') {
            throw new InvalidArgumentException('Invalid order value');
        }

        $this->orderBy .= ' ORDER BY ' . $key . ' ' . $order;
        return $this;
    }

    public function limit(int $limit): QueryInterface
    {
        $this->limit .= ' LIMIT ' . $limit;
        return $this;
    }

    public function offset(int $offset): QueryInterface
    {
        $this->limit .= ' OFFSET ' . $offset;
        return $this;
    }

    public function or($key, $value, $operator = '='): QueryInterface
    {
        $this->checkOperator($operator);

        $this->query .= " OR ";
        $this->statement .= " OR ";
        $this->prepareParam($key, $value, 'OR');
        return $this;
    }

    public function between($key, $before, $after): QueryInterface
    {
        $this->query .= $key . " BETWEEN $before AND $after";
        $this->statement .= $key . ' BETWEEN :before AND :after';

        $this->params['before'] = $before;
        $this->params['after'] = $after;
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query . $this->orderBy . $this->limit;
    }

    public function getStatement(): string
    {
        return $this->statement . $this->orderBy . $this->limit;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    private function prepareParams(array $values, string $glue, string $operator = '=')
    {
        $this->checkOperator($operator);
        $params = [];
        $queryValues = [];

        foreach ($values as $key => $value) {
            if (!isset($this->params[$key])) {
                $queryValues[] = $key . ' ' . $operator . ' \'' . $value . '\'';
                $params [] = $key . ' ' . $operator . ' :' . $key;

                $this->params[$key] = $value;
            } else {
                $uniqid = uniqid('', true);
                $queryValues[] = $key . ' ' . $operator . ' \'' . $value . '\'';
                $params [] = $key . ' ' . $operator . ' :' . $key . $uniqid;

                $this->params[$key . $uniqid] = $value;
            }
        }

        $this->query .= implode($glue, $queryValues);
        $this->statement .= implode($glue, $params);
    }

    private function prepareParam(string $key, $value, string $glue, $operator = '='): void
    {
        $this->prepareParams([$key => $value], $glue, $operator);
    }

    private function checkOperator(string $operator): void
    {
        $operators = ['=', '>', '>=', '<', '<=', 'LIKE'];
        if (!in_array($operator, $operators, true)) {
            throw new InvalidArgumentException('Invalid Operator');
        }
    }
}
