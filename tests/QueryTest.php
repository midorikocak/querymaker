<?php

declare(strict_types=1);

namespace midorikocak\querymaker;

use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    private $queryMaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryMaker = new QueryMaker();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->queryMaker);
    }

    public function testSelect(): void
    {
        $this->queryMaker->select('users');
        $this->assertEquals('SELECT * FROM users', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT * FROM users', $this->queryMaker->getStatement());
    }

    public function testOrder(): void
    {
        $this->queryMaker->select('users')->orderBy('id');
        $this->assertEquals('SELECT * FROM users ORDER BY id ASC', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT * FROM users ORDER BY id ASC', $this->queryMaker->getStatement());
    }

    public function testLimit(): void
    {
        $this->queryMaker->select('users')->orderBy('id')->limit(3);
        $this->assertEquals('SELECT * FROM users ORDER BY id ASC LIMIT 3', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT * FROM users ORDER BY id ASC LIMIT 3', $this->queryMaker->getStatement());
    }

    public function testOffset(): void
    {
        $this->queryMaker->select('users')->orderBy('id')->limit(3)->offset(2);
        $this->assertEquals('SELECT * FROM users ORDER BY id ASC LIMIT 3 OFFSET 2', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT * FROM users ORDER BY id ASC LIMIT 3 OFFSET 2', $this->queryMaker->getStatement());
    }

    public function testDelete(): void
    {
        $this->queryMaker->delete('users');
        $this->assertEquals('DELETE FROM users', $this->queryMaker->getQuery());
        $this->assertEquals('DELETE FROM users', $this->queryMaker->getStatement());
    }

    public function testSelectFields()
    {
        $this->queryMaker->select('users', ['id', 'email']);
        $this->assertEquals('SELECT id, email FROM users', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT id, email FROM users', $this->queryMaker->getStatement());
    }

    public function testSelectFieldsWhereOperator()
    {
        $this->queryMaker->select('users', ['id', 'email'])->where('id', '3', '>=');
        $this->assertEquals('SELECT id, email FROM users WHERE id >= \'3\'', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT id, email FROM users WHERE id >= :id', $this->queryMaker->getStatement());
    }

    public function testSelectFieldsLIKE()
    {
        $this->queryMaker->select('users', ['id', 'email'])->where('email', '%gmail%', 'LIKE');
        $this->assertEquals('SELECT id, email FROM users WHERE email LIKE \'%gmail%\'', $this->queryMaker->getQuery());
        $this->assertEquals('SELECT id, email FROM users WHERE email LIKE :email', $this->queryMaker->getStatement());
    }

    public function testUpdate()
    {
        $this->queryMaker->update('users', [
            'email' => 'mtkocak@gmail.com',
            'username' => 'midorikocak',
        ])->where(
            'id',
            3
        );
        $this->assertEquals(
            "UPDATE users SET email = 'mtkocak@gmail.com', username = 'midorikocak' WHERE id = '3'",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "UPDATE users SET email = :email, username = :username WHERE id = :id",
            $this->queryMaker->getStatement()
        );
    }

    public function testInsert()
    {
        $this->queryMaker->insert('users', ['email' => 'mtkocak@gmail.com', 'username' => 'midorikocak']);

        $this->assertEquals(
            "INSERT INTO users (email, username) VALUES ('mtkocak@gmail.com', 'midorikocak')",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "INSERT INTO users (email, username) VALUES (:email, :username)",
            $this->queryMaker->getStatement()
        );
    }

    public function testWhereAnd()
    {
        $this->queryMaker->select('users', ['id', 'email'])->where('id', 3)->and('username', 'midori');
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id = '3' AND username = 'midori'",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id = :id AND username = :username",
            $this->queryMaker->getStatement()
        );
    }

    public function testWhereOr()
    {
        $this->queryMaker->select('users', ['id', 'email'])->where('id', 3)->or('username', 'midori');
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id = '3' OR username = 'midori'",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id = :id OR username = :username",
            $this->queryMaker->getStatement()
        );
    }

    public function testWhereAndOr()
    {
        $this->queryMaker->select('users', ['id', 'email'])->where(
            'id',
            3
        )->and('email', 'mtkocak@gmail.com')->or('username', 'midori');
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id = '3' AND email = 'mtkocak@gmail.com' OR username = 'midori'",
            $this->queryMaker->getQuery()
        );
        $this->assertEquals(
            "SELECT id, email FROM users WHERE id = :id AND email = :email OR username = :username",
            $this->queryMaker->getStatement()
        );
    }
}
