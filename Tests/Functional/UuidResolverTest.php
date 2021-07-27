<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional;

use AndreasWolf\Uuid\UuidResolver;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UuidResolverTest extends FunctionalTestCase
{
    /** @var string[] */
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
    }

    /** @test */
    public function nullIsReturnedIfUuidIsNotFound(): void
    {
        $subject = new UuidResolver(GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages'), 'pages');

        static::assertNull($subject->getUidForUuid('bbf45dc5-b34f-4a8c-817b-29a3fa413137'));
    }
}
