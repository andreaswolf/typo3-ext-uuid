<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional\TypoScript;

use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \AndreasWolf\Uuid\TypoScript\UuidPreprocessor
 */
class UuidPreprocessorTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/uuid/',
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/test_extension/',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/TestData.xml');
    }

    /** @test */
    public function uuidOfPageIsResolvedToUid(): void
    {
        $typoScript = <<<TS
page = PAGE
page.10 = TEXT
page.10.value := uuid(pages, 137053f9-655b-4894-a74d-875d7e4169c9)
TS;

        $parser = GeneralUtility::makeInstance(TypoScriptParser::class);
        $parser->parse($typoScript);

        static::assertSame('1', $parser->setup['page.']['10.']['value']);
    }

    /** @test */
    public function uuidsOfPagesAreResolvedToUids(): void
    {
        $typoScript = <<<TS
page = PAGE
page.10 = TEXT
page.10.value := uuid(pages, 137053f9-655b-4894-a74d-875d7e4169c9, b56867e6-88ae-49ea-9777-8d958c1a4f36)
TS;

        $parser = GeneralUtility::makeInstance(TypoScriptParser::class);
        $parser->parse($typoScript);

        static::assertSame('1,2', $parser->setup['page.']['10.']['value']);
    }

    /** @test */
    public function emptyValueIsSetInKeyIfUuidDoesNotExist(): void
    {
        $typoScript = <<<TS
page = PAGE
page.10 = TEXT
page.10.value := uuid(pages, 00000000-0000-0000-0000-000000000000)
TS;

        $parser = GeneralUtility::makeInstance(TypoScriptParser::class);
        $parser->parse($typoScript);

        static::assertSame('', $parser->setup['page.']['10.']['value']);
    }

    /** @test */
    public function invalidUuidsAreIgnoredWhileResolvingUuidsToUids(): void
    {
        $typoScript = <<<TS
page = PAGE
page.10 = TEXT
page.10.value := uuid(pages, 137053f9-655b-4894-a74d-875d7e4169c9, 00000000-0000-0000-0000-000000000000, b56867e6-88ae-49ea-9777-8d958c1a4f36)
TS;

        $parser = GeneralUtility::makeInstance(TypoScriptParser::class);
        $parser->parse($typoScript);

        static::assertSame('1,2', $parser->setup['page.']['10.']['value']);
    }
}
