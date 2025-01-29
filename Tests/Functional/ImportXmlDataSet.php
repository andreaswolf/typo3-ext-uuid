<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait ImportXmlDataSet
{
    /**
     * Imports a data set represented as XML into the test database,
     *
     * @param non-empty-string $path Absolute path to the XML file containing the data set to load
     * @throws Exception
     * @deprecated Will be removed with core v12 compatible testing-framework.
     *             Importing database fixtures based on XML format is discouraged. Switch to CSV format
     *             instead. See core functional tests or styleguide for many examples how these look like.
     *             Use method importCSVDataSet() to import such fixture files and assertCSVDataSet() to
     *             compare database state with fixture files.
     */
    protected function importDataSet(string $path): void
    {
        $this->importXmlDatabaseFixture($path);
    }

    /**
     * Imports a data set represented as XML into the test database,
     *
     * @param string $path Absolute path to the XML file containing the data set to load
     * @param non-empty-string $path Absolute path to the XML file containing the data set to load
     * @throws \Doctrine\DBAL\Exception
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function importXmlDatabaseFixture($path): void
    {
        $path = $this->resolvePath($path);
        if (!is_file($path)) {
            throw new \RuntimeException(
                'Fixture file ' . $path . ' not found',
                1376746261
            );
        }

        $fileContent = file_get_contents($path);
        $previousValueOfEntityLoader = false;
        if (PHP_MAJOR_VERSION < 8) {
            // Disables the functionality to allow external entities to be loaded when parsing the XML, must be kept
            $previousValueOfEntityLoader = libxml_disable_entity_loader(true);
        }
        $xml = simplexml_load_string($fileContent);
        if (PHP_MAJOR_VERSION < 8) {
            libxml_disable_entity_loader($previousValueOfEntityLoader);
        }
        $foreignKeys = [];

        /** @var \SimpleXMLElement $table */
        foreach ($xml->children() as $table) {
            $insertArray = [];

            /** @var \SimpleXMLElement $column */
            foreach ($table->children() as $column) {
                $columnName = $column->getName();
                $columnValue = null;

                if (isset($column['ref'])) {
                    [$tableName, $elementId] = explode('#', $column['ref']);
                    $columnValue = $foreignKeys[$tableName][$elementId];
                } elseif (isset($column['is-NULL']) && ($column['is-NULL'] === 'yes')) {
                    $columnValue = null;
                } else {
                    $columnValue = (string)$table->$columnName;
                }

                $insertArray[$columnName] = $columnValue;
            }

            $tableName = $table->getName();
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);

            // With mssql, hard setting uid auto-increment primary keys is only allowed if
            // the table is prepared for such an operation beforehand
            $platform = $connection->getDatabasePlatform();
            $sqlServerIdentityDisabled = false;
            if ($platform instanceof SQLServerPlatform) {
                try {
                    $connection->executeStatement('SET IDENTITY_INSERT ' . $tableName . ' ON');
                    $sqlServerIdentityDisabled = true;
                } catch (Exception) {
                    // Some tables like sys_refindex don't have an auto-increment uid field and thus no
                    // IDENTITY column. Instead of testing existance, we just try to set IDENTITY ON
                    // and catch the possible error that occurs.
                }
            }

            // Some DBMS like mssql are picky about inserting blob types with correct cast, setting
            // types correctly (like Connection::PARAM_LOB) allows doctrine to create valid SQL
            $types = [];
            $tableDetails = $connection->createSchemaManager()->introspectSchema()->getTable($tableName);
            foreach (array_keys($insertArray) as $columnName) {
                $types[] = $tableDetails->getColumn($columnName)->getType()->getBindingType();
            }

            // Insert the row
            $connection->insert($tableName, $insertArray, $types);

            if ($sqlServerIdentityDisabled) {
                // Reset identity if it has been changed
                $connection->executeStatement('SET IDENTITY_INSERT ' . $tableName . ' OFF');
            }

            static::resetTableSequences($connection, $tableName);

            if (isset($table['id'])) {
                $elementId = (string)$table['id'];
                $foreignKeys[$tableName][$elementId] = $connection->lastInsertId($tableName);
            }
        }
    }

    /**
     * Perform post processing of database tables after an insert has been performed.
     * Doing this once per insert is rather slow, but due to the soft reference behavior
     * this needs to be done after every row to ensure consistent results.
     *
     * @param Connection $connection
     * @param string $tableName
     * @throws \Doctrine\DBAL\Exception
     */
    public static function resetTableSequences(Connection $connection, string $tableName): void
    {
        $platform = $connection->getDatabasePlatform();
        if ($platform instanceof PostgreSqlPlatform) {
            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder->getRestrictions()->removeAll();
            $statement = $queryBuilder->select('PGT.schemaname', 'S.relname', 'C.attname', 'T.relname AS tablename')
                ->from('pg_class', 'S')
                ->from('pg_depend', 'D')
                ->from('pg_class', 'T')
                ->from('pg_attribute', 'C')
                ->from('pg_tables', 'PGT')
                ->where(
                    $queryBuilder->expr()->eq('S.relkind', $queryBuilder->quote('S')),
                    $queryBuilder->expr()->eq('S.oid', $queryBuilder->quoteIdentifier('D.objid')),
                    $queryBuilder->expr()->eq('D.refobjid', $queryBuilder->quoteIdentifier('T.oid')),
                    $queryBuilder->expr()->eq('D.refobjid', $queryBuilder->quoteIdentifier('C.attrelid')),
                    $queryBuilder->expr()->eq('D.refobjsubid', $queryBuilder->quoteIdentifier('C.attnum')),
                    $queryBuilder->expr()->eq('T.relname', $queryBuilder->quoteIdentifier('PGT.tablename')),
                    $queryBuilder->expr()->eq('PGT.tablename', $queryBuilder->quote($tableName))
                )
                ->setMaxResults(1)
                ->executeQuery();
            $row = $statement->fetchAssociative();
            if ($row !== false) {
                $connection->executeQuery(
                    sprintf(
                        'SELECT SETVAL(%s, COALESCE(MAX(%s), 0)+1, FALSE) FROM %s',
                        $connection->quote($row['schemaname'] . '.' . $row['relname']),
                        $connection->quoteIdentifier($row['attname']),
                        $connection->quoteIdentifier($row['schemaname'] . '.' . $row['tablename'])
                    )
                );
            }
        } elseif ($platform instanceof SqlitePlatform) {
            // Drop eventually existing sqlite sequence for this table
            $connection->executeStatement(
                sprintf(
                    'DELETE FROM sqlite_sequence WHERE name=%s',
                    $connection->quote($tableName)
                )
            );
        }
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, 'EXT:')) {
            return GeneralUtility::getFileAbsFileName($path);
        }

        if (str_starts_with($path, 'PACKAGE:')) {
            throw new \RuntimeException('PACKAGE: paths are not supported', 1719401569);
        }
        return $path;
    }
}
