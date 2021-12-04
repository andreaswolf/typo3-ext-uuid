.. include:: /Includes.rst.txt
.. highlight:: php

.. _developer:

==============
For Developers
==============

.. _developer-api:

API
===

Resolving UUIDs to records in PHP
---------------------------------

Resolving a UUID to a record is performed by ``\AndreasWolf\Uuid\UuidResolver``.
One instance of this class is required per table that should be resolved.

To get an instance of this class, you can use ``\AndreasWolf\Uuid\UuidResolverFactory``.
This class also checks if a table has UUIDs enabled.

You can also define Dependency Injection services to get resolvers injected into your class:

`Configuration/Services.yaml`:

.. code-block:: yaml

   Vendor\MyExtension\MyClass:
     public: true
     arguments:
       $pageUuidResolver: '@pageUuidResolver'
       # this resolver is only available for this class
       $contentUuidResolver: !service
         factory: ['@AndreasWolf\Uuid\UuidResolverFactory', 'getResolverForTable']
         arguments:
           $table: 'tt_content'

   # this resolver can be used in different classes
   # use this in the 'arguments' section:
   #   $myArgumentName: '@pageUuidResolver'
   'pageUuidResolver':
      factory: ['@AndreasWolf\Uuid\UuidResolverFactory', 'getResolverForTable']
      class: AndreasWolf\Uuid\UuidResolver
      arguments:
        $table: 'pages'

   # you can also enable autowiring: all constructor parameters $namedUuidResolver
   # in any class will be filled with a UUID resolver for tt_content
   AndreasWolf\Uuid\UuidResolver $namedUuidResolver:
     factory: ['@AndreasWolf\Uuid\UuidResolverFactory', 'getResolverForTable']
     class: AndreasWolf\Uuid\UuidResolver
     arguments:
       $table: 'tx_myextension_sometable'


The setup above can then be used to get the resolvers injected into a class, like this:

.. code-block:: php

    namespace Vendor\MyExtension;

    use \AndreasWolf\Uuid\UuidResolver;

    class MyClass {
        public function __construct(
            UuidResolver $pageUuidResolver, // for 'pages'
            UuidResolver $contentResolver, // for 'tt_content'
            UuidResolver $namedUuidResolver // for 'tx_myextension_sometable'
        ) {
            // UUIDs from pages, tt_content and tx_myextension_sometable can be resolved
            // with the three resolvers
        }
    }

Resolving UUIDs to records in TypoScript
----------------------------------------

For TypoScript,
there is a custom preprocessor function `uuid()` that resolves a table-UUID combination into a record UID.

Use it like this:

.. code-block:: typoscript

    # e.g. in the sitepackage's constants.typoscript file
    somePage := uuid(pages, 12345678-90ab-cdef-1234-567890123456)


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
