<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TableConfigurationService implements SingletonInterface
{
    /** @var string[] */
    private $tablesWithUuidField = [];

    /** @var bool */
    private $tablesFromTcaLoaded = false;

    public function enableUuidForTable(string $tableName): void
    {
        // This is required to later on restore the tables w/ UUID fields from the cached TCA (since TCA/Overrides/ files
        // are only executed once)
        $GLOBALS['TCA'][$tableName]['ctrl']['uuid'] = true;

        $this->tablesWithUuidField[] = $tableName;

        $this->configureFieldInTca($tableName);
    }

    private function configureFieldInTca(string $tableName): void
    {
        if (isset($GLOBALS['TCA'][$tableName]['columns']['uuid'])) {
            return;
        }

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
     * Signal slot for TYPO3 v9
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
     * Signal slot for TYPO3 v9
     *
     * @param array<string, array{ctrl: array{uuid?: true}}> $TCA
     * @return array{0: array<string, array{ctrl: array{uuid?: true}}>}
     */
    public function addUuidFieldsToTca(array $TCA): array
    {
        foreach ($TCA as $tableName => $configuration) {
            if (array_key_exists('uuid', $configuration['ctrl']) && $configuration['ctrl']['uuid'] === true) {
                if (!in_array($tableName, $this->tablesWithUuidField, true)) {
                    $this->tablesWithUuidField[] = $tableName;
                }

                $this->configureFieldInTca($tableName);
            }
        }

        return [$GLOBALS['TCA']];
    }

    /**
     * Returns the names of all tables that have been enabled for UUID handling
     *
     * @return string[]
     */
    public function getTablesWithUuid(): array
    {
        $this->addTablesWithEnabledUuidInTcaControlSection();

        return $this->tablesWithUuidField;
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
        if ($this->tablesFromTcaLoaded === true) {
            return;
        }

        foreach ($GLOBALS['TCA'] as $tableName => $configuration) {
            if (array_key_exists('uuid', $configuration['ctrl']) && $configuration['ctrl']['uuid'] === true) {
                $this->tablesWithUuidField[] = $tableName;
            }
        }

        $this->tablesWithUuidField = array_unique($this->tablesWithUuidField);

        $this->tablesFromTcaLoaded = true;
    }
}
