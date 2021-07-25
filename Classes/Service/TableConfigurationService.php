<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Service;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TableConfigurationService implements SingletonInterface
{
    /** @var string[] */
    private $tablesWithUuidField = [];

    public function enableUuidForTable(string $tableName): void
    {
        $this->tablesWithUuidField[] = $tableName;

        ExtensionManagementUtility::addTCAcolumns($tableName, [
            'uuid' => [
                'exclude' => true,
                'label' => 'UUID (v4)',
                'config' => [
                    'type' => 'input',
                    'readOnly' => true,
                ],
            ],
        ]);

        ExtensionManagementUtility::addToAllTCAtypes(
            $tableName,
            'uuid'
        );
    }

    public function addUuidFieldsToDatabaseSchema(AlterTableDefinitionStatementsEvent $event): void
    {
        foreach ($this->tablesWithUuidField as $tableName) {
            $event->addSqlData(<<<SQL
CREATE TABLE $tableName (
	uuid VARCHAR(36) DEFAULT NULL,

	KEY uuid (uuid)
);
SQL);
        }
    }
}
