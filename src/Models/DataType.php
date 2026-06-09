<?php

/**
 * @author Juan Vladimir <juanvladimir13@gmail.com>
 * @link https://github.com/juanvladimir13
 */

declare(strict_types=1);

namespace PGDatabase\Models;

class DataType
{
    public const INT = 'int';
    public const FLOAT = 'float';
    public const STRING = 'string';
    public const STRING_UPPER = 'string-upper';
    public const UTF8 = 'utf-8';
    public const UTF8_UPPER = 'utf-8-upper';
    public const BOOL = 'bool';
    public const DATE = 'date';
    public const DATETIME = 'datetime';
    public const ARRAY = 'array';
    public const JSON = 'json';
    public const OBJECT = 'object';
}
