<?php

namespace Francerz\SqlBuilder\MySQL\Tests;

use DateTime;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\MySQL\MySQLDriver;
use Francerz\SqlBuilder\Query;
use PHPUnit\Framework\TestCase;

class MySQLCompilerTest extends TestCase
{
    private $compiler;

    public function __construct()
    {
        parent::__construct();
        $this->driver = new MySQLDriver();
        $this->compiler = $this->driver->getCompiler();
    }

    public function testCompileSingleQuery()
    {
        $query = Query::selectFrom(new Table('table', 't1', 'db'), ['a' => 'firstCol', 'b' => 'secondCol']);
        $query->where('a.datetime', new DateTime('2022-06-04 17:40:34'));

        $compiled = $this->compiler->compileSelect($query);

        $this->assertEquals(
            'SELECT `t1`.`firstCol` AS `a`, `t1`.`secondCol` AS `b` FROM `db`.`table` AS `t1` WHERE `a`.`datetime` = :v1',
            $compiled->getQuery()
        );
        $this->assertEquals(
            ['v1' => '2022-06-04 17:40:34'],
            $compiled->getValues()
        );
    }
}
