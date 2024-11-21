<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid;

use TYPO3\CMS\Core\Database\Connection;

class UuidResolver
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $tableName;

    public function __construct(Connection $connection, string $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function getUidForUuid(string $uuid): ?int
    {
        $result = $this->connection
            ->select(
                ['uid'],
                $this->tableName,
                ['uuid' => $uuid]
            );

        return $result->fetchOne() ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRecordForUuid(string $uuid): ?array
    {
        $result = $this->connection
            ->select(
                ['*'],
                $this->tableName,
                ['uuid' => $uuid]
            );

        return $result->fetchAssociative() ?: null;
    }
}
