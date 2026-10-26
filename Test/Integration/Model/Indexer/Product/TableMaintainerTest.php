<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Model\Indexer\Product;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class TableMaintainerTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager = null;
    protected ?\MageSuite\GoogleStructuredData\Model\Indexer\Product\TableMaintainer $tableMaintainer = null;
    protected ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->tableMaintainer = $this->objectManager->get(\MageSuite\GoogleStructuredData\Model\Indexer\Product\TableMaintainer::class);
        $this->connection = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
        $this->connection->delete($this->tableMaintainer->getMainTable());
        $this->connection->delete($this->tableMaintainer->getMainReplicaTable());
    }

    public function testItReturnsOnlyProductsWhoseDataWasAddedChangedOrRemoved(): void
    {
        $this->connection->insertMultiple($this->tableMaintainer->getMainReplicaTable(), [
            ['product_id' => 1, 'store_id' => 1, 'data' => '{"price":"10.00"}'],
            ['product_id' => 2, 'store_id' => 1, 'data' => '{"price":"20.00"}'],
            ['product_id' => 2, 'store_id' => 2, 'data' => '{"price":"20.00"}'],
            ['product_id' => 4, 'store_id' => 1, 'data' => '{"price":"40.00"}'],
            ['product_id' => 5, 'store_id' => 1, 'data' => null],
            ['product_id' => 6, 'store_id' => 1, 'data' => '{"name":"abc"}'],
            ['product_id' => 7, 'store_id' => 1, 'data' => null],
        ]);
        $this->connection->insertMultiple($this->tableMaintainer->getMainTable(), [
            ['product_id' => 1, 'store_id' => 1, 'data' => '{"price":"10.00"}'],
            ['product_id' => 2, 'store_id' => 1, 'data' => '{"price":"20.00"}'],
            ['product_id' => 2, 'store_id' => 2, 'data' => '{"price":"25.00"}'],
            ['product_id' => 3, 'store_id' => 1, 'data' => '{"price":"30.00"}'],
            ['product_id' => 5, 'store_id' => 1, 'data' => '{"price":"50.00"}'],
            ['product_id' => 6, 'store_id' => 1, 'data' => '{"name":"ABC"}'],
            ['product_id' => 7, 'store_id' => 1, 'data' => null],
        ]);

        $changedProductIds = $this->tableMaintainer->getChangedProductIds();
        sort($changedProductIds);

        $this->assertSame([2, 3, 4, 5, 6], $changedProductIds);
    }
}
