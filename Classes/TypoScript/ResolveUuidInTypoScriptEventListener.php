<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\TypoScript;

use AndreasWolf\Uuid\UuidResolverFactory;
use TYPO3\CMS\Core\TypoScript\AST\Event\EvaluateModifierFunctionEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ResolveUuidInTypoScriptEventListener
{
    public function __construct(private readonly UuidResolverFactory $resolverFactory)
    {
    }

    public function __invoke(EvaluateModifierFunctionEvent $event): void
    {
        $functionArgument = $event->getFunctionArgument();
        [$table, $uuidOrListOfUuids] = GeneralUtility::trimExplode(',', $functionArgument, true, 2);
        $uuids = GeneralUtility::trimExplode(',', $uuidOrListOfUuids);

        $tableUuidResolver = $this->resolverFactory->getResolverForTable($table);
        $uids = array_map(
            static fn (string $uuid) => $tableUuidResolver->getUidForUuid($uuid),
            $uuids
        );

        $event->setValue(implode(',', array_filter($uids)));
    }
}
