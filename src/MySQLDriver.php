<?php

namespace Francerz\SqlBuilder\MySQL;

use Francerz\SqlBuilder\Compiles\CompiledDelete;
use Francerz\SqlBuilder\Compiles\CompiledInsert;
use Francerz\SqlBuilder\Compiles\CompiledProcedure;
use Francerz\SqlBuilder\Compiles\CompiledSelect;
use Francerz\SqlBuilder\Compiles\CompiledUpdate;
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\Driver\DriverInterface;
use Francerz\SqlBuilder\Driver\QueryCompilerInterface;
use Francerz\SqlBuilder\Exceptions\ExecuteStatementException;
use Francerz\SqlBuilder\Exceptions\TransactionException;
use Francerz\SqlBuilder\Results\DeleteResult;
use Francerz\SqlBuilder\Results\InsertResult;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;
use LogicException;
use PDO;
use PDOException;

class MySQLDriver implements DriverInterface
{
    /**
     * Database link connection
     *
     * @var PDO
     */
    private $link;
    private $compiler;

    public function __construct()
    {
        $this->compiler = new MySQLCompiler();
    }

    public function connect(ConnectParams $params)
    {
        $paramsArr = array(
            "host={$params->getHost()}",
            'charset=' . ($params->getEncoding() ?? 'utf8')
        );
        $paramsArr[] = 'port=' . ($params->getPort() ?? 3306);
        $database = $params->getDatabase();
        if (isset($database)) {
            $paramsArr[] = "dbname={$database}";
        }

        $dsn = 'mysql:' . join(';', $paramsArr);
        $this->link = new PDO($dsn, $params->getUser(), $params->getPassword());
    }

    public function getCompiler(): ?QueryCompilerInterface
    {
        return $this->compiler;
    }

    public function getDefaultHost(): string
    {
        return 'localhost';
    }

    public function getDefaultPort(): int
    {
        return 3306;
    }

    public function getDefaultUser(): string
    {
        return 'root';
    }

    public function getDefaultPswd(): string
    {
        return '';
    }

    public function executeSelect(CompiledSelect $query): SelectResult
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Invalid database link.');
        }
        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        if ($stmt->errorCode() !== '00000') {
            throw new ExecuteStatementException($query, $stmt->errorInfo()[2]);
        }

        return new SelectResult($stmt->fetchAll(PDO::FETCH_CLASS));
    }

    public function executeInsert(CompiledInsert $query): InsertResult
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Invalid database link.');
        }

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        if ($stmt->errorCode() !== '00000') {
            throw new ExecuteStatementException($query, $stmt->errorInfo()[2]);
        }

        return new InsertResult($stmt->rowCount(), $this->link->lastInsertId());
    }

    public function executeUpdate(CompiledUpdate $query): UpdateResult
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Invalid database link.');
        }

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        if ($stmt->errorCode() !== '00000') {
            throw new ExecuteStatementException($query, $stmt->errorInfo()[2]);
        }

        return new UpdateResult($stmt->rowCount());
    }

    public function executeDelete(CompiledDelete $query): DeleteResult
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Invalid database link.');
        }

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        if ($stmt->errorCode() !== '00000') {
            throw new ExecuteStatementException($query, $stmt->errorInfo()[2]);
        }

        return new DeleteResult($stmt->rowCount());
    }

    public function executeProcedure(CompiledProcedure $query): array
    {
        if (!$this->link instanceof PDO) {
            throw new LogicException('Invalid database link.');
        }

        $stmt = $this->link->prepare($query->getQuery());
        $stmt->execute($query->getValues());

        if ($stmt->errorCode() !== '00000') {
            throw new ExecuteStatementException($query, $stmt->errorInfo()[2]);
        }

        $results = [];
        do {
            $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
            if (false !== $rows) {
                $results[] = new SelectResult($rows);
            }
        } while ($stmt->nextRowset());
        array_pop($results);

        return $results;
    }

    public function inTransaction(): bool
    {
        try {
            return $this->link->inTransaction();
        } catch (PDOException $pdoex) {
            throw new TransactionException($pdoex->getMessage(), 4, $pdoex);
        }
    }

    public function startTransaction(): bool
    {
        try {
            return $this->link->beginTransaction();
        } catch (PDOException $pdoex) {
            throw new TransactionException($pdoex->getMessage(), 1, $pdoex);
        }
    }

    public function rollback(): bool
    {
        try {
            return $this->link->rollBack();
        } catch (PDOException $pdoex) {
            throw new TransactionException($pdoex->getMessage(), 2, $pdoex);
        }
    }

    public function commit(): bool
    {
        try {
            return $this->link->commit();
        } catch (PDOException $pdoex) {
            throw new TransactionException($pdoex->getMessage(), 3, $pdoex);
        }
    }
}
