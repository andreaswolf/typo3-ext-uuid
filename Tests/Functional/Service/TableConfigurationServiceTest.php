<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional\Service;

use AndreasWolf\Uuid\Service\TableConfigurationService;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \AndreasWolf\Uuid\Service\TableConfigurationService
 */
class TableConfigurationServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/uuid/',
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/test_extension/',
    ];

    /** @test */
    public function getTablesWithUuidReturnsTablesFromTca(): void
    {
        $subject = GeneralUtility::makeInstance(TableConfigurationService::class);

        $result = $subject->getTablesWithUuid();
        sort($result);

        // these are set in the TCA/Overrides/ folder in test_extension
        static::assertSame(['pages', 'tt_content', 'tx_testextension_with_uuid', 'tx_testextension_with_uuid_in_tca_ctrl'], $result);
    }

    /** @test */
    public function getTablesWithUuidReturnsManuallyRegisteredTables(): void
    {
        $tableName = 'tx_testextension_without_uuid';
        $subject = GeneralUtility::makeInstance(TableConfigurationService::class);
        $subject->enableUuidForTable($tableName);

        $result = $subject->getTablesWithUuid();
        sort($result);

        static::assertGreaterThan(0, array_search($tableName, $result, true));
    }

    /** @test */
    public function enableUuidForTableAddsUuidFieldToTCA(): void
    {
        $tableName = 'tx_testextension_without_uuid';
        $subject = GeneralUtility::makeInstance(TableConfigurationService::class);

        static::assertIsArray($GLOBALS['TCA'][$tableName]);
        static::assertStringNotContainsString('uuid', $GLOBALS['TCA'][$tableName]['types'][0]['showitem']);

        $subject->enableUuidForTable($tableName);

        static::assertIsArray($GLOBALS['TCA'][$tableName]['columns']['uuid']);
        static::assertIsArray($GLOBALS['TCA'][$tableName]['types'][0]);
        static::assertStringContainsString('uuid', $GLOBALS['TCA'][$tableName]['types'][0]['showitem']);
    }

    /** @test */
    public function schemaMigrationAddsUuidFieldToTable(): void
    {
        $tableName = 'tx_testextension_without_uuid';
        $subject = GeneralUtility::makeInstance(TableConfigurationService::class);

        $subject->enableUuidForTable($tableName);

        $foo = GeneralUtility::makeInstance(SqlReader::class);
        $result = $foo->getTablesDefinitionString();

        static::assertStringContainsString('uuid VARCHAR(36) DEFAULT NULL', $result);
        static::assertStringContainsString('KEY uuid (uuid)', $result);
    }

    /** @test */
    public function uuidFieldIsAutomaticallyCreatedWhenEnabledViaTcaOverride(): void
    {
        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName('Default');

        /** @var Schema $schema */
        $schema = $conn->getSchemaManager()->createSchema();

        $table = $schema->getTable('tx_testextension_with_uuid');

        static::assertInstanceOf(Table::class, $table);
        static::assertTrue($table->hasColumn('uuid'), 'Field "uuid" does not exist in table "tx_testextension_with_uuid"');
    }

    /** @test */
    public function uuidFieldIsAutomaticallyCreatedWhenEnabledViaTcaCtrlSection(): void
    {
        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName('Default');

        /** @var Schema $schema */
        $schema = $conn->getSchemaManager()->createSchema();

        $table = $schema->getTable('tx_testextension_with_uuid_in_tca_ctrl');

        static::assertInstanceOf(Table::class, $table);
        static::assertTrue($table->hasColumn('uuid'), 'Field "uuid" does not exist in table "tx_testextension_with_uuid_in_tca_ctrl"');
    }

    /** @test */
    public function uuidFieldIsAddedToExistingCoreTablesIfAddedInTcaOverrides(): void
    {
        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName('Default');

        /** @var Schema $schema */
        $schema = $conn->getSchemaManager()->createSchema();

        $pagesSchema = $schema->getTable('pages');
        static::assertInstanceOf(Table::class, $pagesSchema);
        static::assertTrue($pagesSchema->hasColumn('uuid'), 'Field "uuid" does not exist in table "pages"');

        $contentSchema = $schema->getTable('tt_content');
        static::assertInstanceOf(Table::class, $contentSchema);
        static::assertTrue($contentSchema->hasColumn('uuid'), 'Field "uuid" does not exist in table "tt_content"');
    }

    /** @test */
    public function uuidFieldIsRegisteredInTcaForExistingCoreTablesIfAddedInTcaOverrides(): void
    {
        $tableName = 'pages';
        static::assertIsArray($GLOBALS['TCA'][$tableName]['columns']['uuid']);
        static::assertIsArray($GLOBALS['TCA'][$tableName]['types'][1]);
        static::assertStringContainsString('uuid', $GLOBALS['TCA'][$tableName]['types'][1]['showitem']);

        $tableName = 'tt_content';
        static::assertIsArray($GLOBALS['TCA'][$tableName]['columns']['uuid']);
        static::assertIsArray($GLOBALS['TCA'][$tableName]['types']['text']);
        static::assertStringContainsString('uuid', $GLOBALS['TCA'][$tableName]['types']['text']['showitem']);
    }
}
