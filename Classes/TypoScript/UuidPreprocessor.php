<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\TypoScript;

use AndreasWolf\Uuid\UuidResolverFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Preprocesses UUIDs from TypoScript into their respective uids.
 *
 * Can be invoked like this:
 *
 *   foo.bar := uuid(pages, 12345678-90ab-cdef-1234-567890123456)
 *
 * If a value cannot be resolved, an empty string is returned instead.
 */
class UuidPreprocessor implements SingletonInterface
{
    /** @var UuidResolverFactory */
    private $resolverFactory;

    public function __construct(?UuidResolverFactory $resolverFactory = null)
    {
        $this->resolverFactory = $resolverFactory ?? GeneralUtility::makeInstance(UuidResolverFactory::class);
    }

    /**
     * @param array{functionArgument: string, currentValue: string} $params
     */
    public function resolveUuidInTypoScript(array $params): string
    {
        [$table, $uid] = GeneralUtility::trimExplode(',', $params['functionArgument']);

        $tableUuidResolver = $this->resolverFactory->getResolverForTable($table);
        $uid = $tableUuidResolver->getUidForUuid($uid);

        return $uid !== null ? (string)$uid : '';
    }
}
