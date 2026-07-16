<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Setup\Patch\Data;

class MigrateSearchBoxConfig implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    protected const OLD_PATH = 'structured_data/search_box/is_enabled';
    protected const NEW_PATH = 'structured_data/website/search_box_enabled';
    protected const WEBSITE_ENABLED_PATH = 'structured_data/website/is_enabled';

    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {}

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('core_config_data');

        $this->preserveWebsiteEnabledState($connection, $tableName);

        $connection->update(
            $tableName,
            ['path' => self::NEW_PATH],
            ['path = ?' => self::OLD_PATH]
        );

        return $this;
    }

    /**
     * Previously a single toggle (old search box) controlled the whole WebSite node.
     * Mirror its explicitly saved value per scope into the new website/is_enabled flag
     * so an upgrade preserves the exact state - disabled parents stay disabled and
     * scoped overrides that explicitly enabled it keep working.
     */
    protected function preserveWebsiteEnabledState(
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        string $tableName
    ): void {
        $select = $connection->select()
            ->from($tableName, ['scope', 'scope_id', 'value'])
            ->where('path = ?', self::OLD_PATH);

        foreach ($connection->fetchAll($select) as $row) {
            $connection->insertOnDuplicate(
                $tableName,
                [
                    'scope' => $row['scope'],
                    'scope_id' => (int)$row['scope_id'],
                    'path' => self::WEBSITE_ENABLED_PATH,
                    'value' => $row['value']
                ],
                ['value']
            );
        }
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
