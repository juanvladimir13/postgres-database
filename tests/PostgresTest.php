<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PGDatabase\Postgres;

class PostgresTest extends TestCase
{
    protected function setUp(): void
    {
        // Forzamos 127.0.0.1 en lugar de localhost para obtener un "Connection refused" 
        // casi instantáneo y evitar timeouts del sistema operativo por resolución de red.
        putenv('DB_SECURITY_ENVIROMENT=true');
        putenv('DB_HOST=127.0.0.1');
        putenv('DB_PORT=5432');
    }

    protected function tearDown(): void
    {
        putenv('DB_SECURITY_ENVIROMENT');
        putenv('DB_HOST');
        putenv('DB_PORT');
    }

    public function testIsConnectedReturnsFalseWhenNoDbAvailable(): void
    {
        // En un entorno de CI/CD sin base de datos real configurada, 
        // la conexión debería fallar silenciosamente y retornar falso.
        $this->assertFalse(Postgres::isConnected());
    }

    public function testFetchAllReturnsEmptyArrayWhenNotConnected(): void
    {
        $this->assertEquals([], Postgres::fetchAll('SELECT * FROM users'));
    }

    public function testFetchAllParamsReturnsEmptyArrayWhenNotConnected(): void
    {
        $this->assertEquals([], Postgres::fetchAllParams('SELECT * FROM users WHERE id = $1', [1]));
    }

    public function testInsertReturnsZeroWhenNotConnected(): void
    {
        $this->assertEquals(0, Postgres::insert('users', ['name' => 'John']));
    }

    public function testUpdateReturnsFalseWhenNotConnected(): void
    {
        $this->assertFalse(Postgres::update('users', ['name' => 'John'], ['id' => 1]));
    }

    public function testDeleteReturnsFalseWhenNotConnected(): void
    {
        $this->assertFalse(Postgres::delete('users', ['id' => 1]));
    }
}
