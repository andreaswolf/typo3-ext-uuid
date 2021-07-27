<?php

// this tests both ways of enabling a UUID field for an existing (core) table. We're using tables from the core here to
// ensure they always exist in test systems, but this should work the same for non-core, 3rd party extensions.

$tableConfigurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\AndreasWolf\Uuid\Service\TableConfigurationService::class);

$tableConfigurationService->enableUuidForTable('pages');

$GLOBALS['TCA']['tt_content']['ctrl']['uuid'] = true;
