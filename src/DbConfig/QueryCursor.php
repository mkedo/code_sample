<?php

namespace App\DbConfig;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\PgSQL\Driver as PDOPgSqlDriver;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NativeQuery;

/**
 * Класс для создания курсоров
 */
class QueryCursor
{
    private const DIRECTION_ABSOLUTE = 'ABSOLUTE';
    private const DIRECTION_RELATIVE = 'RELATIVE';
    private const DIRECTION_FORWARD  = 'FORWARD';
    private const DIRECTION_BACKWARD = 'BACKWARD';

    /**
     * @var NativeQuery
     */
    private NativeQuery $query;

    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @var bool
     */
    private bool $isOpen = false;

    /**
     * @var string
     */
    private string $cursorName;


    /**
     * @param NativeQuery $query
     */
    public function __construct(NativeQuery $query)
    {
        $this->query = $query;
        $this->connection = $query->getEntityManager()->getConnection();
        $this->cursorName = uniqid('cursor_');

        assert($this->connection->getDriver() instanceof PDOPgSqlDriver);
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        if ($this->isOpen) {
            $this->close();
        }
    }

    /**
     * @param int $count
     * @param string $direction
     * @return NativeQuery
     */
    public function getFetchQuery(int $count = 1, string $direction = self::DIRECTION_FORWARD): NativeQuery
    {
        if (!$this->isOpen) {
            $this->openCursor();
        }

        $query = clone $this->query;
        $query->setParameters([]);

        if ($direction === self::DIRECTION_ABSOLUTE
            || $direction === self::DIRECTION_RELATIVE
            || $direction === self::DIRECTION_FORWARD
            || $direction === self::DIRECTION_BACKWARD
        ) {
            $query->setSQL(sprintf(
                'FETCH %s %d FROM %s',
                $direction,
                $count,
                $this->connection->quoteIdentifier($this->cursorName)
            ));
        } else {
            $query->setSQL(sprintf(
                'FETCH %s FROM %s',
                $direction,
                $this->connection->quoteIdentifier($this->cursorName)
            ));
        }

        return $query;
    }

    /**
     * @throws Exception
     */
    public function close(): void
    {
        if (!$this->isOpen) {
            return;
        }

        $this->connection->executeStatement('CLOSE ' . $this->connection->quoteIdentifier($this->cursorName));
        $this->isOpen = false;
    }

    /**
     * Открытие курсора
     */
    private function openCursor(): void
    {
        if ($this->query->getEntityManager()->getConnection()->getTransactionNestingLevel() === 0) {
            throw new \BadMethodCallException('Cursor must be used inside a transaction');
        }

        $query = clone $this->query;
        $query->setSQL(sprintf(
            'DECLARE %s CURSOR FOR (%s)',
            $this->connection->quoteIdentifier($this->cursorName),
            $this->query->getSQL()
        ));
        $query->execute($this->query->getParameters());

        $this->isOpen = true;
    }
}