<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\ConfigurableProduct\Model\Product\Type\Configurable;

class AddAttributesToUsedProducts
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider
    ) {}

    public function afterGetUsedProductCollection(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection $result
    ): \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection {
        $result->addAttributeToSelect($this->compositeAttributeDataProvider->getAdditionalPropertyAttributeCodes());

        return $result;
    }
}
