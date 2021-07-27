<?php
declare(strict_types = 1);

namespace AndreasWolf\UuidTestExtension;

use AndreasWolf\Uuid\UuidResolver;

class ClassWithUuidResolverDependency
{
    /** @var UuidResolver */
    private $contentUuidResolver;

    public function __construct(UuidResolver $contentUuidResolver)
    {
        $this->contentUuidResolver = $contentUuidResolver;
    }

    public function getContentUuidResolver(): UuidResolver
    {
        return $this->contentUuidResolver;
    }
}
