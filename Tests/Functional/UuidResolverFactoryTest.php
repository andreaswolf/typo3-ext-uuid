<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional;

use AndreasWolf\Uuid\UuidResolverFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \AndreasWolf\Uuid\UuidResolverFactory
 */
class UuidResolverFactoryTest extends FunctionalTestCase
{
    use ImportXmlDataSet;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/uuid/',
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/test_extension/',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/Fixtures/TestData.xml');
    }

    /** @test */
    public function correctResolverIsReturnedForGivenTable(): void
    {
        $subject = GeneralUtility::makeInstance(UuidResolverFactory::class);
        $resolver = $subject->getResolverForTable('pages');

        static::assertSame(1, $resolver->getUidForUuid('137053f9-655b-4894-a74d-875d7e4169c9'));
        static::assertSame(2, $resolver->getUidForUuid('b56867e6-88ae-49ea-9777-8d958c1a4f36'));
    }

    /** @test */
    public function factoryReturnsExceptionIfTableIsNotRegisteredForUuid(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1627416387);

        $subject = GeneralUtility::makeInstance(UuidResolverFactory::class);
        $subject->getResolverForTable('be_users');
    }
}
