<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Service;

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

    /**
     * Signal listener for TYPO3 v9
     *
     * @param string[] $existingDefinitions
     * @return array{0: string[]} The list of SQL definitions for all registered tables
     */
    public function addUuidFieldsToDatabaseSchemaSlot(array $existingDefinitions): array
    {
        $sqlDefinitions = $this->getUuidFieldDefinitions();

        return [array_merge($existingDefinitions, $sqlDefinitions)];
    }

    /**
     * @return string[]
     */
    public function getUuidFieldDefinitions(): array
    {
        $this->addTablesWithEnabledUuidInTcaControlSection();

        return array_map(function (string $tableName) {
            return $this->createTableDefinition($tableName);
        }, $this->tablesWithUuidField);
    }

    /**
     * @param string $tableName
     * @return string
     */
    private function createTableDefinition(string $tableName): string
    {
        return <<<SQL
CREATE TABLE $tableName (
	uuid VARCHAR(36) DEFAULT NULL,

	KEY uuid (uuid)
);
SQL;
    }

    /**
     * Loops over the TCA and adds tables that have uuid = true in their TCA ctrl section.
     */
    private function addTablesWithEnabledUuidInTcaControlSection(): void
    {
        foreach ($GLOBALS['TCA'] as $tableName => $configuration) {
            if (array_key_exists('uuid', $configuration['ctrl']) && $configuration['ctrl']['uuid'] === true) {
                $this->tablesWithUuidField[] = $tableName;
            }
        }

        $this->tablesWithUuidField = array_unique($this->tablesWithUuidField);
    }
}
