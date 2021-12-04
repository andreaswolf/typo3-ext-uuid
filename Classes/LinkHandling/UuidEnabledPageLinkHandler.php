<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid\LinkHandling;

use AndreasWolf\Uuid\UuidResolverFactory;
use TYPO3\CMS\Core\LinkHandling\PageLinkHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extension of the core page link handler.
 *
 * Allows using links like t3://page?uuid=a7d52cb3-e3f0-4a0c-b2cb-2962e414c33b.
 *
 * To enable this, add the following line to your AdditionalConfiguration.php (it's not enabled by default for now since
 * it was not tested widely enough):
 *
 * $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['page'] = \AndreasWolf\Uuid\LinkHandling\UuidEnabledPageLinkHandler::class;
 */
class UuidEnabledPageLinkHandler extends PageLinkHandler
{
    /**
     * @param array{uuid?: string} $data
     * @return array{pageuid: int}
     */
    public function resolveHandlerData(array $data): array
    {
        if (isset($data['uuid'])) {
            // TODO check uuid for validity

            $resolver = GeneralUtility::makeInstance(UuidResolverFactory::class)->getResolverForTable('pages');
            $uid = $resolver->getUidForUuid($data['uuid']);

            unset($data['uuid']);
            // TODO check if $uid is null
            $data['uid'] = $uid;
        }
        /** @var array{pageuid: int} $result */
        $result = parent::resolveHandlerData($data);
        return $result;
    }
}
