<?php

namespace Francerz\SqlBuilder\MySQL;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\DeleteQuery;
use Francerz\SqlBuilder\Driver\DriverInterface;
use Francerz\SqlBuilder\Driver\QueryCompilerInterface;
use Francerz\SqlBuilder\Driver\QueryTranslatorInterface;
use Francerz\SqlBuilder\InsertQuery;
use Francerz\SqlBuilder\Results\DeleteResult;
use Francerz\SqlBuilder\Results\InsertResult;
use Francerz\SqlBuilder\Results\QueryResultInterface;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;
use Francerz\SqlBuilder\SelectQuery;
use Francerz\SqlBuilder\UpdateQuery;
use LogicException;
use PDO;

class MySQLDriver implements DriverInterface
{
    private $link;
    private $compiler, $translator;

    public function __construct()
    {
        $this->compiler = new MySQLCompiler();
        $this->translator = null;
    }

    public function connect(ConnectParams $params)
    {
        $paramsArr = array(
            "host={$params->getHost()}",
            'charset=utf8'
        );
        $paramsArr[] = 'port='.($params->getPort() ?? 3306);
        $database = $params->getDatabase();
        if (isset($database)) {
            $paramsArr[] = "dbname={$database}";
        }

        $dsn = 'mysql:'.join(';', $paramsArr);
        $this->link = new PDO($dsn, $params->getUser(), $params->getPassword());
    }

    public function getCompiler(): ?QueryCompilerInterface
    {
        return $this->compiler;
    }

    public function getTranslator(): ?QueryTranslatorInterface
    {
        return $this->translator;
    }
    
    public function execute(CompiledQuery $query): QueryResultInterface
    {
        if (!isset($this->link)) {
            throw new LogicException('Database not connected.');
        }
        if (!$this->link instanceof PDO) throw new LogicException('Not valid DB Link.');

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        $object = $query->getObject();

        if ($object instanceof SelectQuery) {
            return new SelectResult($query, $stmt->fetchAll(PDO::FETCH_CLASS));
        } elseif ($object instanceof InsertQuery) {
            return new InsertResult($query, $stmt->rowCount(), $this->link->lastInsertId());
        } elseif ($object instanceof UpdateQuery) {
            return new UpdateResult($query, $stmt->rowCount());
        } elseif ($object instanceof DeleteQuery) {
            return new DeleteResult($query, $stmt->rowCount());
        }
    }
}