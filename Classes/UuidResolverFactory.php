<?php
declare(strict_types = 1);

namespace AndreasWolf\Uuid;

use AndreasWolf\Uuid\Service\TableConfigurationService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UuidResolverFactory implements SingletonInterface
{
    /** @var TableConfigurationService */
    private $configurationService;

    /** @var ConnectionPool */
    private $connectionPool;

    /** @var array<string, UuidResolver> */
    private $resolversByTable = [];

    public function __construct(TableConfigurationService $service = null, ConnectionPool $connectionPool = null)
    {
        $this->configurationService = $service ?? GeneralUtility::makeInstance(TableConfigurationService::class);
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }

    public function getResolverForTable(string $table): UuidResolver
    {
        if (!in_array($table, $this->configurationService->getTablesWithUuid())) {
            throw new \RuntimeException(sprintf('Table "%s" does not have UUID field registered', $table), 1627416387);
        }

        if (!isset($this->resolversByTable[$table])) {
            $this->resolversByTable[$table] = new UuidResolver($this->connectionPool->getConnectionForTable($table), $table);
        }

        return $this->resolversByTable[$table];
    }
}
