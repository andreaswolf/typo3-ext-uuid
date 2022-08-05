<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Service;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

/**
 * Event listener for TYPO3 v10+.
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
