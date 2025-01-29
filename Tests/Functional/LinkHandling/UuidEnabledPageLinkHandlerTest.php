<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional\LinkHandling;

use AndreasWolf\Uuid\LinkHandling\UuidEnabledPageLinkHandler;
use AndreasWolf\Uuid\Tests\Functional\ImportXmlDataSet;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \AndreasWolf\Uuid\LinkHandling\UuidEnabledPageLinkHandler
 */
class UuidEnabledPageLinkHandlerTest extends FunctionalTestCase
{
    use ImportXmlDataSet;

    protected array $coreExtensionsToLoad = [
        'recordlist',
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/uuid/',
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/test_extension/',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/sites' => 'typo3conf/sites'
    ];

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->configurationToUseInTestInstance = [
            'SYS' => [
                'linkHandler' => [
                    'page' => UuidEnabledPageLinkHandler::class,
                ],
            ],
        ];
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/TestData.xml');
    }

    /** @test */
    public function uuidInT3PageUrlIsCorrectlyResolved(): void
    {
        $result = $this->executeFrontendSubRequest(new InternalRequest('http://example.org/?id=1'));

        $stream = $result->getBody();
        $stream->rewind();
        $content = $stream->getContents();
        static::assertSame('http://example.org/test-subpage', $content);
    }

    /** @test */
    public function otherParametersInT3PageUrlAreKept(): void
    {
        $result = $this->executeFrontendSubRequest(new InternalRequest('http://example.org/?id=2'));

        $stream = $result->getBody();
        $stream->rewind();
        $content = $stream->getContents();
        static::assertIsString($content);
        static::assertStringStartsWith('http://example.org/test-subpage?parameters=here&some=other&cHash=', $content);
    }
}
