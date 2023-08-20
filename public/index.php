<?php

/**
 * @author juanvladimir13 <juanvladimir13@gmail.com>
 * @see https://github.com/juanvladimir13
 */

declare(strict_types=1);

require '../vendor/autoload.php';

use PGDatabase\Postgres;

$pacientes = Postgres::fetchAll('SELECT * FROM paciente');
var_dump($pacientes);
