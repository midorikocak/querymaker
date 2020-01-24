<?php

declare(strict_types=1);

namespace midorikocak\querymaker;

use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    private $queryMaker;

    protected function tearDown() : void
    {
        parent::tearDown();
        unset($this->queryMaker);
    }

    public function testSelect()
    {
        $this->queryMaker = QueryMaker::select('users');
        $this->assertEquals('SELECT * FROM users', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT * FROM users', $this->queryMaker->getStatement());
    }

    public function testSelectFields()
    {
        $this->queryMaker = QueryMaker::select('users', ['id', 'email']);
        $this->assertEquals('SELECT id, email FROM users', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT id, email FROM users', $this->queryMaker->getStatement());
    }

    public function testSelectFieldsWhere()
    {
        $this->queryMaker = QueryMaker::select('users', ['id', 'email'])->where('id', 3);
        $this->assertEquals('SELECT id, email FROM users WHERE id=\'3\'', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT id, email FROM users WHERE id=:id', $this->queryMaker->getStatement());
    }

    public function testUpdate()
    {
        $this->queryMaker = QueryMaker::update('users', ['email' => 'mtkocak@gmail.com', 'username' => 'midorikocak'])->where(
            'id',
            3
        );
        $this->assertEquals(
            "UPDATE users SET email='mtkocak@gmail.com', username='midorikocak' WHERE id='3'",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "UPDATE users SET email=:email, username=:username WHERE id=:id",
            $this->queryMaker->getStatement()
        );
    }

    public function testWhereAnd()
    {
        $this->queryMaker = QueryMaker::select('users', ['id', 'email'])->where('id', 3)->and('username', 'midori');
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id='3' AND username='midori'",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id=:id AND username=:username",
            $this->queryMaker->getStatement()
        );
    }

    public function testWhereOr()
    {
        $this->queryMaker = QueryMaker::select('users', ['id', 'email'])->where('id', 3)->or('username', 'midori');
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id='3' OR username='midori'",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id=:id OR username=:username",
            $this->queryMaker->getStatement()
        );
    }

    public function testWhereAndOr()
    {
        $this->queryMaker = QueryMaker::select('users', ['id', 'email'])->where(
            'id',
            3
        )->and('email', 'mtkocak@gmail.com')->or('username', 'midori');
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id='3' AND email='mtkocak@gmail.com' OR username='midori'",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id=:id AND email=:email OR username=:username",
            $this->queryMaker->getStatement()
        );
    }
}
