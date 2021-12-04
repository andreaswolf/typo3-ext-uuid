.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

There is not much configuration to do for this extension.

One decision you have to make is enabling support for UUIDs in ``t3://page`` links.
If you want to do this (it is not widely tested and thus not enabled by default currently),
you can add the following code block to your ``AdditionalConfiguration.php``:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['page'] =
       \AndreasWolf\Uuid\LinkHandling\UuidEnabledPageLinkHandler::class;

By default, no UUID field is added to any table.
So you need to configure UUIDs for your extension‘s own tables and/or core or third-party extension tables.
See :ref:`the developer chapter <developer>` for more information.
