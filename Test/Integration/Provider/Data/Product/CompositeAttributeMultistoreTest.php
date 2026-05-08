<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data\Product;

#[\Magento\TestFramework\Annotation\AppIsolation(true)]
#[\Magento\TestFramework\Annotation\DbIsolation(true)]
class CompositeAttributeMultistoreTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
    }

    /**
     * Verifies that getEavAttributeCodes() resolves preload_only attributes per store scope.
     *
     * Before the fix, the result was cached without a store key, so the first call's
     * scope (often global/default during CLI indexing) silently poisoned all subsequent
     * store-specific lookups. The test sets suggested_min_age_attribute only on the
     * second store and asserts that each store ID returns its own resolved attribute list.
     *
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/test_age_attr.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoConfigFixture fixturestore_store structured_data/product_page/audience/suggested_min_age_attribute test_age_attr
     */
    public function testGetEavAttributeCodesRespectsStoreScope(): void
    {
        $defaultStore = $this->storeManager->getStore('default');
        $secondStore = $this->storeManager->getStore('fixturestore');

        $compositeAttribute = $this->objectManager->create(
            \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute::class
        );

        $defaultStoreCodes = $compositeAttribute->getEavAttributeCodes((int)$defaultStore->getId());
        $secondStoreCodes = $compositeAttribute->getEavAttributeCodes((int)$secondStore->getId());

        $this->assertArrayNotHasKey(
            'suggested_min_age_attribute',
            $defaultStoreCodes,
            'Default store should not resolve suggested_min_age_attribute when not configured'
        );

        $this->assertArrayHasKey(
            'suggested_min_age_attribute',
            $secondStoreCodes,
            'Second store should resolve suggested_min_age_attribute from its own store config'
        );

        $this->assertEquals('test_age_attr', $secondStoreCodes['suggested_min_age_attribute']);
    }
}