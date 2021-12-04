.. include:: /Includes.rst.txt

.. _introduction:

============
Introduction
============

.. _what-it-does:

What does it do?
================

This extension provides support for Universally Unique Identifiers (UUIDs) in TYPO3.

From Wikipedia:

.. epigraph::

   A universally unique identifier (UUID) is a 128-bit label used for information in computer systems.

   -- https://en.wikipedia.org/wiki/Universally_unique_identifier

With the help of this extension, a UUID can be added to possibly every record in TYPO3.
This allows for e.g. safely identifying records that were added by a migration
(in contrast to their ``uid``,
which depends on the time of execution of the migration,
which will be different in each instance like development, stage and production).

.. _screenshots:

Screenshots
===========

This chapter should help people understand how the extension works.
Remove it if it is not relevant.

.. figure:: /Images/IntroductionPackage.png
   :class: with-shadow
   :alt: Introduction Package
   :width: 300px

   Introduction Package after installation (caption of the image).

How the Frontend of the Introduction Package looks like after installation (legend of the image).
