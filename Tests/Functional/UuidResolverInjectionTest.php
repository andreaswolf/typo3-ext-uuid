<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional;

use AndreasWolf\Uuid\UuidResolver;
use AndreasWolf\UuidTestExtension\ClassWithAutowiredServiceDependency;
use AndreasWolf\UuidTestExtension\ClassWithNamedServiceUuidResolverDependency;
use AndreasWolf\UuidTestExtension\ClassWithUuidResolverDependency;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests dependency injection via a TYPO3 v10 service
 */
class UuidResolverInjectionTest extends FunctionalTestCase
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

        if ((new Typo3Version())->getMajorVersion() < 10) {
            static::markTestSkipped('Dependency injection is only supported on TYPO3 v10+');
        }
    }

    /** @test */
    public function classWithDependencyOnNamedResolverServiceGetsCorrectResolverInjected(): void
    {
        $subject = GeneralUtility::getContainer()->get(ClassWithNamedServiceUuidResolverDependency::class);

        static::assertInstanceOf(UuidResolver::class, $subject->getPageUuidResolver());
    }

    /** @test */
    public function classWithDependencyOnInlineFactoryResolverGetsCorrectResolverInjected(): void
    {
        $subject = GeneralUtility::getContainer()->get(ClassWithUuidResolverDependency::class);

        static::assertInstanceOf(UuidResolver::class, $subject->getContentUuidResolver());
    }

    /** @test */
    public function autowiredDependencyIsCorrectlyInjected(): void
    {
        $subject = GeneralUtility::getContainer()->get(ClassWithAutowiredServiceDependency::class);

        static::assertInstanceOf(UuidResolver::class, $subject->getNamedUuidResolver());
    }
}
