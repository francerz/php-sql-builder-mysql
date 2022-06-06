<?php

namespace Francerz\SqlBuilder\MySQL;

use DateTimeInterface;
use Francerz\SqlBuilder\Components\JoinTypes;
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
    protected function compileJoinType($joinType): string
    {
        switch ($joinType) {
            case JoinTypes::CROSS_JOIN:
                return ' CROSS JOIN ';
            default:
                return parent::compileJoinType($joinType);
        }
    }
    protected function compileDatetime(DateTimeInterface $datetime)
    {
        return $datetime->format('Y-m-d H:i:s');
    }
}
