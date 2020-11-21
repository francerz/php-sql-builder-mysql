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
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;
use Francerz\SqlBuilder\SelectQuery;
use Francerz\SqlBuilder\UpdateQuery;
use InvalidArgumentException;
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

    public function executeSelect(CompiledQuery $query) : SelectResult
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Not valid database link.');
        }

        if (!$query->getObject() instanceof SelectQuery) {
            throw new InvalidArgumentException('Not valid SelectQuery.');
        }

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        return new SelectResult($query, $stmt->fetchAll(PDO::FETCH_CLASS));
    }

    public function executeInsert(CompiledQuery $query) : InsertResult
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Not valid database link.');
        }

        if (!$query->getObject() instanceof InsertQuery) {
            throw new InvalidArgumentException('Not valid InsertQuery.');
        }

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        return new InsertResult($query, $stmt->rowCount(), $this->link->lastInsertId());
    }

    public function executeUpdate(CompiledQuery $query) : UpdateResult
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Not valid database link.');
        }

        if (!$query->getObject() instanceof UpdateQuery) {
            throw new InvalidArgumentException('Not valid UpdateQuery.');
        }

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        return new UpdateResult($query, $stmt->rowCount());
    }

    public function executeDelete(CompiledQuery $query) : DeleteResult
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Not valid database link.');
        }

        if (!$query->getObject() instanceof DeleteQuery) {
            throw new InvalidArgumentException('Not valid DeleteQuery.');
        }

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        return new DeleteResult($query, $stmt->rowCount());
    }
}