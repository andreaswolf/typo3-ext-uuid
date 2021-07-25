<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TableConfigurationService implements SingletonInterface
{
    public function enableUuidForTable(string $tableName): void
    {
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
}
