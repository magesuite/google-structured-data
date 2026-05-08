<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Setup\Patch\Data;

class MigrateSocialProfilesConfig implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {}

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('core_config_data');

        $connection->update(
            $tableName,
            ['path' => new \Zend_Db_Expr(
                'REPLACE(path, \'structured_data/social/\', \'structured_data/organization/social/\')'
            )],
            ['path LIKE \'structured_data/social/%\'']
        );

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
