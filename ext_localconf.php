<?php

defined('TYPO3_MODE') or die();

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);

$signalSlotDispatcher->connect(
    'TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService',
    'tablesDefinitionIsBeingBuilt',
    \AndreasWolf\Uuid\Service\TableConfigurationService::class,
    'addUuidFieldsToDatabaseSchemaSlot'
);
$signalSlotDispatcher->connect(
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::class,
    'tcaIsBeingBuilt',
    \AndreasWolf\Uuid\Service\TableConfigurationService::class,
    'addUuidFieldsToTca'
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tsparser.php']['preParseFunc']['uuid'] =
    \AndreasWolf\Uuid\TypoScript\UuidPreprocessor::class . '->resolveUuidInTypoScript';
