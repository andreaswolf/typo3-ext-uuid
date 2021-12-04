<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\Tests\Functional\LinkHandling;

use AndreasWolf\Uuid\LinkHandling\UuidEnabledPageLinkHandler;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \AndreasWolf\Uuid\LinkHandling\UuidEnabledPageLinkHandler
 */
class UuidEnabledPageLinkHandlerTest extends FunctionalTestCase
{
    /** @var array<int, string> */
    protected $coreExtensionsToLoad = [
        'recordlist',
    ];

    /** @var string[] */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/uuid/',
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/test_extension/',
    ];

    /** @var array<string, string> */
    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/uuid/Tests/Functional/Fixtures/sites' => 'typo3conf/sites'
    ];

    /**
     * @param array<int, mixed> $data
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

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
        $result = $this->getFrontendResponse(1);

        static::assertSame('http://example.org/test-subpage', $result->getContent());
    }

    /** @test */
    public function otherParametersInT3PageUrlAreKept(): void
    {
        $result = $this->getFrontendResponse(2);

        $content = $result->getContent();
        static::assertIsString($content);
        static::assertStringStartsWith('http://example.org/test-subpage?parameters=here&some=other&cHash=', $content);
    }
}
