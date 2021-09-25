<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Takes care of updating the TCA for enabling UUIDs for tables.
 *
 * Enabling can be done in different ways:
 *
 * - calling enableUuidForTable() in a TCA/Overrides/ file (esp. for core or third-party tables that you have no control over)
 * - setting [ctrl][uuid] to true (for tables from your own extension)
 */
class TableConfigurationService implements SingletonInterface
{
    /** @var string[] */
    private $tablesWithUuidField = [];

    /** @var bool */
    private $tablesFromTcaLoaded = false;

    /**
     * Enables UUIDs for the given table.
     *
     * Can be used for enabling UUIDs for tables w/ existing TCA, e.g. from the Core or third-party extensions.
     */
    public function enableUuidForTable(string $tableName): void
    {
        // This is required to later on restore the tables w/ UUID fields from the cached TCA (since TCA/Overrides/ files
        // are only executed once)
        $GLOBALS['TCA'][$tableName]['ctrl']['uuid'] = true;

        $this->tablesWithUuidField[] = $tableName;

        $this->configureFieldInTca($tableName);
    }

    /**
     * Configures the UUID field in the table's TCA field.
     */
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
     * Signal slot for TYPO3 v9. Called while the SQL schema is compiled by the Install Tool.
     *
     * @see TableDefinitionEventListener for TYPO3 v10+.
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
     * Signal slot for TYPO3 v9. Called while the TCA is compiled by the Core's ExtensionManagementUtility.
     *
     * This is called after both the base TCA files and overrides have been evaluated, so we're sure that we know all
     * tables that should have UUIDs enabled (both if they have [ctrl][uuid] initially set or if enableUuidForTable()
     * was called).
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
     * Returns the SQL definitions for all tables.
     *
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
