<?php
declare(strict_types = 1);

namespace AndreasWolf\UuidTestExtension;

use AndreasWolf\Uuid\UuidResolver;

/**
 * See Services.yaml: the constructor argument $pageUuidResolver is registered as service 'pageUuidResolver' in the configuration
 */
class ClassWithNamedServiceUuidResolverDependency
{
    /** @var UuidResolver */
    private $pageUuidResolver;

    public function __construct(UuidResolver $pageUuidResolver)
    {
        $this->pageUuidResolver = $pageUuidResolver;
    }

    public function getPageUuidResolver(): UuidResolver
    {
        return $this->pageUuidResolver;
    }
}
