<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional;

use AndreasWolf\Uuid\UuidResolver;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \AndreasWolf\Uuid\UuidResolver
 */
class UuidResolverTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/uuid/',
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/test_extension/',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/Fixtures/TestData.xml');
    }

    /** @test */
    public function pageUidCanBeResolved(): void
    {
        $subject = new UuidResolver(GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages'), 'pages');

        static::assertSame(1, $subject->getUidForUuid('137053f9-655b-4894-a74d-875d7e4169c9'));
        static::assertSame(2, $subject->getUidForUuid('b56867e6-88ae-49ea-9777-8d958c1a4f36'));
    }

    /** @test */
    public function nullIsReturnedIfUuidIsInADifferentTable(): void
    {
        $subject = new UuidResolver(GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages'), 'pages');

        static::assertNull($subject->getUidForUuid('e73d554c-fd4d-4e5d-bc11-23833daa3b9a'));
        static::assertNull($subject->getRecordForUuid('e73d554c-fd4d-4e5d-bc11-23833daa3b9a'));
    }

    /** @test */
    public function nullIsReturnedIfUuidIsNotFound(): void
    {
        $subject = new UuidResolver(GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages'), 'pages');

        static::assertNull($subject->getUidForUuid('bbf45dc5-b34f-4a8c-817b-29a3fa413137'));
        static::assertNull($subject->getRecordForUuid('bbf45dc5-b34f-4a8c-817b-29a3fa413137'));
    }

    /** @test */
    public function recordCanBeResolvedFromuuid(): void
    {
        $subject = new UuidResolver(GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages'), 'pages');

        $pageRecord1 = $subject->getRecordForUuid('137053f9-655b-4894-a74d-875d7e4169c9');
        static::assertIsArray($pageRecord1);
        static::assertSame(1, $pageRecord1['uid']);
        $pageRecord2 = $subject->getRecordForUuid('b56867e6-88ae-49ea-9777-8d958c1a4f36');
        static::assertIsArray($pageRecord2);
        static::assertSame(2, $pageRecord2['uid']);
    }
}
