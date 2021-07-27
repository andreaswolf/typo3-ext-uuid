<?php

$tableConfigurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\AndreasWolf\Uuid\Service\TableConfigurationService::class);

$tableConfigurationService->enableUuidForTable('tx_testextension_with_uuid');
