<?php

/**
 * @author juanvladimir13 <juanvladimir13@gmail.com>
 * @see https://github.com/juanvladimir13
 */

declare(strict_types=1);

namespace PGDatabase;

class Postgres
{
    private $connection;
    private static $database;

    private function __construct()
    {
        $resource = \pg_connect(Dotenv::connection_string_pg());
        $this->connection = $resource;
    }

    private static function getInstance(): Postgres
    {
        if (!self::$database instanceof self) {
            self::$database = new self();
        }

        return self::$database;
    }

    public static function isConnected(): bool
    {
        return self::getInstance()->connection !== false;
    }

    /**
     * @param string $query
     * @param int $result_type
     * @return array
     */
    public static function fetchAll(string $query, int $result_type = PGSQL_ASSOC): array
    {
        $instance = self::getInstance();
        if ($instance->connection === false) return [];

        $resource = \pg_query($instance->connection, $query);
        if (!$resource) return [];

        $rows = \pg_fetch_all($resource, $result_type);
        \pg_free_result($resource);

        return !$rows ? [] : $rows;
    }

    /**
     * @param string $query
     * @param array $params
     * @param int $result_type
     * @return array
     */
    public static function fetchAllParams(string $query, array $params = [], int $result_type = PGSQL_ASSOC): array
    {
        $instance = self::getInstance();
        if ($instance->connection === false) return [];

        $resource = \pg_query_params($instance->connection, $query, $params);
        if (!$resource) return [];
        $rows = \pg_fetch_all($resource, $result_type);

        \pg_free_result($resource);
        return !$rows ? [] : $rows;
    }

    public static function insert(string $table_name, array $dataAssocArray, string $pk = 'id'): int
    {
        $instance = self::getInstance();
        if ($instance->connection === false) return 0;

        $columns = implode(', ', array_keys($dataAssocArray));
        
        $placeholders = array_map(function($i) { return '$' . $i; }, range(1, count($dataAssocArray)));
        $params = implode(', ', $placeholders);

        $sql = sprintf('INSERT INTO %s (%s) VALUES(%s) RETURNING ' . $pk . ';', $table_name, $columns, $params);
        $result = \pg_query_params($instance->connection, $sql, array_values($dataAssocArray));
        if (!$result)
            return 0;

        $insert_row = \pg_fetch_row($result);
        if (!$insert_row)
            return 0;

        \pg_free_result($result);

        return intval($insert_row[0]);
    }

    public static function update(string $table_name, array $dataAssocArray, array $conditionAssocArray): bool
    {
        $instance = self::getInstance();
        if ($instance->connection === false) return false;
        
        $result = \pg_update($instance->connection, $table_name, $dataAssocArray, $conditionAssocArray);
        return $result === true;
    }

    public static function delete(string $table_name, array $dataAssocArray): bool
    {
        $instance = self::getInstance();
        if ($instance->connection === false) return false;
        
        $result = \pg_delete($instance->connection, $table_name, $dataAssocArray);
        return $result === true;
    }

    public static function close(): void
    {
        \pg_close(self::getInstance()->connection);
    }

    public static function getError(): string
    {
        $result = \pg_last_error(self::getInstance()->connection);
        return $result !== false ? $result : '';
    }
}
