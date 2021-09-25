<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Service;

use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;

/**
 * Event listener for TYPO3 v11+.
 */
class TcaBuildEventListener
{
    /** @var TableConfigurationService */
    private $service;

    public function __construct(TableConfigurationService $service)
    {
        $this->service = $service;
    }

    public function addUuidFieldsToTca(AfterTcaCompilationEvent $event): void
    {
        $this->service->addUuidFieldsToTca($GLOBALS['TCA']);

        $event->setTca($GLOBALS['TCA']);
    }
}
