<?php

/**
 * @author juanvladimir13
 * @see https://github.com/juanvladimir13
 */

declare(strict_types=1);

namespace PGDatabase;

class Dotenv
{
    private static function load(): array
    {
        if (getenv('DB_SECURITY_ENVIROMENT') === 'true') {
            return [
                'DB_DATABASE' => getenv('DB_DATABASE') ?: 'postgres',
                'DB_PASSWORD' => getenv('DB_PASSWORD') ?: '',
                'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
                'DB_PORT' => getenv('DB_PORT') ?: 5432,
                'DB_USERNAME' => getenv('DB_USERNAME') ?: 'postgres',
            ];
        }

        $defaults = [
            'DB_DATABASE' => 'postgres',
            'DB_PASSWORD' => 'postgres',
            'DB_HOST' => 'localhost',
            'DB_PORT' => 5432,
            'DB_USERNAME' => 'postgres',
        ];

        try {
            $paths = ['../.env', './.env'];

            $envFile = null;
            foreach ($paths as $path) {
                if (is_readable($path)) {
                    $envFile = $path;
                    break;
                }
            }

            if ($envFile === null) {
                return $defaults;
            }

            $dataEnvironment = self::parseDotenv($envFile);
            return array_merge($defaults, $dataEnvironment);

        } catch (\Throwable $e) {
            return $defaults;
        }
    }

    private static function parseDotenv(string $file): array
    {
        $vars = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            if (trim($line)[0] === '#') {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $vars[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
            }
        }

        return $vars;
    }

    public static function connection_string_pg(): string
    {
        $config = self::load();

        return sprintf(
            "host=%s port=%s dbname=%s user=%s password=%s",
            $config['DB_HOST'],
            $config['DB_PORT'],
            $config['DB_DATABASE'],
            $config['DB_USERNAME'],
            $config['DB_PASSWORD']
        );
    }
}
