<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Service;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

/**
 * Event listener for TYPO3 v10+.
 *
 * See TableConfigurationService::addUuidFieldsToDatabaseSchemaSlot() for the same functionality in TYPO3 v9.
 *
 * This is a separate class because TYPO3 v9 throws an exception when reflecting a class w/ a method that has an undefined
 * class as type parameter (even if that method is never invoked)
 */
class TableDefinitionEventListener
{
    /** @var TableConfigurationService */
    private $service;

    public function __construct(TableConfigurationService $service)
    {
        $this->service = $service;
    }

    /**
     * Event listener for TYPO3 v10+
     */
    public function addUuidFieldsToDatabaseSchema(AlterTableDefinitionStatementsEvent $event): void
    {
        foreach ($this->service->getUuidFieldDefinitions() as $sql) {
            $event->addSqlData($sql);
        }
    }
}
