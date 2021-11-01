<?php

namespace Francerz\SqlBuilder\MySQL\Tests;

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

        $compiled = $this->compiler->compileQuery($query);

        $this->assertEquals(
            'SELECT `t1`.`firstCol` AS `a`, `t1`.`secondCol` AS `b` FROM `db`.`table` AS `t1`',
            $compiled->getQuery()
        );
    }
}
