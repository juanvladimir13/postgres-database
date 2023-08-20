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

        if (!self::$database->connection) {
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
        if (!self::getInstance()->isConnected()) return [];

        $resource = \pg_query(self::getInstance()->connection, $query);
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
        if (!self::getInstance()->isConnected()) return [];

        $resource = \pg_query_params(self::getInstance()->connection, $query, $params);
        if (!$resource) return [];
        $rows = \pg_fetch_all($resource, $result_type);

        \pg_free_result($resource);
        return !$rows ? [] : $rows;
    }

    public static function insert(string $table_name, array $dataAssocArray, string $pk = 'id'): int
    {
        if (!self::getInstance()->isConnected()) return 0;

        $columns = '';
        $params = '';
        $index = 1;

        foreach ($dataAssocArray as $key => $column) {
            $columns .= $key . ',';
            $params .= '$' . $index . ',';
            $index++;
        }

        $columns = substr($columns, 0, strlen($columns) - 1);
        $params = substr($params, 0, strlen($params) - 1);

        $sql = sprintf('INSERT INTO %s (%s) VALUES(%s) RETURNING ' . $pk . ';', $table_name, $columns, $params);
        $result = \pg_query_params(self::getInstance()->connection, $sql, array_values($dataAssocArray));
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
        if (!self::getInstance()->isConnected()) return false;
        $result = \pg_update(self::getInstance()->connection, $table_name, $dataAssocArray, $conditionAssocArray);
        return $result === true;
    }

    public static function delete(string $table_name, array $dataAssocArray): bool
    {
        if (!self::getInstance()->isConnected()) return false;
        $result = \pg_delete(self::getInstance()->connection, $table_name, $dataAssocArray);
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
