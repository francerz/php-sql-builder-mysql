<?php

namespace Francerz\SqlBuilder\MySQL;

use Francerz\SqlBuilder\Driver\QueryCompiler;

class MySQLCompiler extends QueryCompiler
{
    protected function compileTableAlias(string $alias)
    {
        return "`$alias`";
    }
    protected function compileTableName(string $name)
    {
        return "`$name`";
    }
    protected function compileTableDatabase(string $database)
    {
        return "`$database`";
    }
    protected function compileColumnName(string $name)
    {
        return "`$name`";
    }
    protected function compileColumnAlias(string $alias)
    {
        return "`$alias`";
    }
    protected function compileColumnTable(string $table)
    {
        return "`$table`";
    }
}