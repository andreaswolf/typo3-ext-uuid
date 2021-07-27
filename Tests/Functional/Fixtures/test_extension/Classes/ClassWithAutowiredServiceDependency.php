<?php
declare(strict_types = 1);

namespace AndreasWolf\UuidTestExtension;

use AndreasWolf\Uuid\UuidResolver;

/**
 * See Services.yaml: the constructor argument $namedUuidResolver is registered as a service for that variable name in
 * the configuration => we need no further configuration for this service, just the parameter name has to match
 */
class ClassWithAutowiredServiceDependency
{
    /** @var UuidResolver */
    private $namedUuidResolver;

    public function __construct(UuidResolver $namedUuidResolver)
    {
        $this->namedUuidResolver = $namedUuidResolver;
    }

    public function getNamedUuidResolver(): UuidResolver
    {
        return $this->namedUuidResolver;
    }
}
