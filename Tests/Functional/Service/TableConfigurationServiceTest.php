<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional\Service;

use AndreasWolf\Uuid\Service\TableConfigurationService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TableConfigurationServiceTest extends FunctionalTestCase
{
    /** @var string[] */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/uuid/',
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/test_extension/',
    ];

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
}
