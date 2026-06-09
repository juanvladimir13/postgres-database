<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PGDatabase\Dotenv;

class DotenvTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear environment variable for a clean state
        putenv('DB_SECURITY_ENVIROMENT');
    }

    public function testDefaultConnectionStringWhenNoEnvFile(): void
    {
        // Change working directory to a temp folder without .env
        $originalDir = getcwd();
        $tmpDir = sys_get_temp_dir() . '/pg_test_dir_' . uniqid();
        mkdir($tmpDir);
        chdir($tmpDir);

        $expected = "host=localhost port=5432 dbname=postgres user=postgres password=postgres";
        $this->assertEquals($expected, Dotenv::connection_string_pg());

        chdir($originalDir);
        rmdir($tmpDir);
    }

    public function testConnectionStringWithSecurityEnvironment(): void
    {
        putenv('DB_SECURITY_ENVIROMENT=true');
        putenv('DB_DATABASE=test_db');
        putenv('DB_PASSWORD=secret');
        putenv('DB_HOST=127.0.0.1');
        putenv('DB_PORT=5433');
        putenv('DB_USERNAME=admin');

        $expected = "host=127.0.0.1 port=5433 dbname=test_db user=admin password=secret";
        $this->assertEquals($expected, Dotenv::connection_string_pg());

        putenv('DB_SECURITY_ENVIROMENT');
        putenv('DB_DATABASE');
        putenv('DB_PASSWORD');
        putenv('DB_HOST');
        putenv('DB_PORT');
        putenv('DB_USERNAME');
    }

    public function testConnectionStringWithEnvFile(): void
    {
        $originalDir = getcwd();
        $tmpDir = sys_get_temp_dir() . '/pg_test_dir_' . uniqid();
        mkdir($tmpDir);
        chdir($tmpDir);

        $envContent = <<<ENV
# Configuración de base de datos
DB_DATABASE=app_db
DB_PASSWORD=12345
DB_HOST=db.example.com
DB_PORT=5432
DB_USERNAME=app_user
ENV;

        file_put_contents('./.env', $envContent);

        $expected = "host=db.example.com port=5432 dbname=app_db user=app_user password=12345";
        $this->assertEquals($expected, Dotenv::connection_string_pg());

        unlink('./.env');
        chdir($originalDir);
        rmdir($tmpDir);
    }
}
