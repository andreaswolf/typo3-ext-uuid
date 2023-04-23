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
        [$table, $uuidOrListOfUuids] = GeneralUtility::trimExplode(',', $params['functionArgument'], true, 2);
        $uuids = GeneralUtility::trimExplode(',', $uuidOrListOfUuids);

        $tableUuidResolver = $this->resolverFactory->getResolverForTable($table);
        $uids = array_map(
            static fn (string $uuid) => $tableUuidResolver->getUidForUuid($uuid),
            $uuids
        );

        return implode(',', array_filter($uids));
    }
}
