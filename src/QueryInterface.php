<?php

declare(strict_types=1);

namespace midorikocak\querymaker;

interface QueryInterface
{
    public static function select($table, array $columns = ['*']) : self;

    public static function update($table, array $values) : self;

    public function where($key, $value) : self;

    public function and($key, $value) : self;

    public function or($key, $value) : self;

    public function between($key, $before, $after) : self;

    public function getQuery() : string;

    public function getStatement() : string;

    public function getParams() : array;
}
