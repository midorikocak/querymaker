<?php

declare(strict_types=1);

namespace midorikocak\querymaker;

interface QueryInterface
{
    /**
     * @param string[] $columns
     * @return static
     */
    public static function select(string $table, array $columns = ['*']) : self;

    public static function delete(string $table) : self;

    public static function update(string $table, array $values) : self;

    public static function insert(string $table, array $values) : self;

    public function where(string $key, $value, string $operator = '=') : self;

    public function and(string $key, $value, string $operator = '=') : self;

    public function or(string $key, $value, string $operator = '=') : self;

    public function orderBy(string $key, string $order = 'ASC') : self;

    public function limit(int $limit) : self;

    public function offset(int $offset) : self;

    public function between(string $key, $before, $after) : self;

    public function getQuery() : string;

    public function getStatement() : string;

    public function getParams() : array;
}
