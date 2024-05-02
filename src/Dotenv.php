<?php

/**
 * @author juanvladimir13 <juanvladimir13@gmail.com>
 * @see https://github.com/juanvladimir13
 */

declare(strict_types=1);

namespace PGDatabase;

class Dotenv
{

    private static function load(): array
    {
        $config = [
            'DB_DATABASE' => 'postgres',
            'DB_PASSWORD' => 'postgres',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => 5432,
            'DB_USERNAME' => 'postgres'
        ];

        try {
            $parentEnv = file_exists('../.env');
            $brotherEnv = file_exists('./.env');
            if (!($parentEnv || $brotherEnv))
                throw new \Exception('File not found');

            $dataEnvironment = $parentEnv ? include '../.env' : include './.env';
            return array_merge($config, $dataEnvironment);
        } catch (\Exception $e) {
            return $config;
        }
    }

    public static function connection_string_pg(): string
    {
        list('DB_HOST' => $host, 'DB_PORT' => $port, 'DB_DATABASE' => $database, 'DB_USERNAME' => $username, 'DB_PASSWORD' => $password) = self::load();
        return "host=$host port=$port dbname=$database user=$username password=$password";
    }
}
