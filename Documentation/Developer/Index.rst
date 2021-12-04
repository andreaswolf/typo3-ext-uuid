.. include:: /Includes.rst.txt
.. highlight:: php

.. _developer:

==============
For Developers
==============

.. _developer-api:

API
===

Enabling UUIDs for your own tables
----------------------------------

Adding UUIDs to your extension’s tables is easy:
Just add ``uuid = true`` to the ``ctrl`` section in your table’s TCA file:

``Configuration/TCA/tx_yourext_yourtable.php``::

   return [
       'ctrl' => [
           // other configuration options here
           'uuid' => true,
       ],
       'columns' => [
           // …
       ],
   ];


Enabling UUIDs for Core/third-party tables
------------------------------------------

Enabling the UUIDs for existing tables is done via a service that can be called in TCA override files.

To enable UUIDs for the ``pages`` table,
place this ``pages.php`` file in your extensions‘s ``Configuration/TCA/Overrides/`` folder::

   $tableConfigurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
       \AndreasWolf\Uuid\Service\TableConfigurationService::class
   );
   $tableConfigurationService->enableUuidForTable('pages');
