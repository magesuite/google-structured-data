<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier;

class DeliveryData implements \MageSuite\GoogleStructuredData\Provider\Data\Product\ModifierInterface
{
    protected \Magento\Framework\Stdlib\ArrayManager $arrayManager;
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\BusinessDays $businessDays;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\CutoffTime $cutoffTime;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\HandlingTime $handlingTime;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\TransitTime $transitTime;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration;

    public function __construct(
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\BusinessDays $businessDays,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\CutoffTime $cutoffTime,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\HandlingTime $handlingTime,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\TransitTime $transitTime,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        \MageSuite\GoogleStructuredData\Helper\Configuration $configuration
    ) {
        $this->arrayManager = $arrayManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->businessDays = $businessDays;
        $this->cutoffTime = $cutoffTime;
        $this->handlingTime = $handlingTime;
        $this->transitTime = $transitTime;
        $this->productConfiguration = $productConfiguration;
        $this->configuration = $configuration;
    }

    public function execute(array $productData, \Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $dataObject = $this->dataObjectFactory->create();
        $dataObject->setData('store', $store);
        $dataObject->setData('product', $product);
        $dataObject->setData('currency_code', $store->getCurrentCurrencyCode());

        if ($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            foreach ($productData as $index => $associatedProductData) {
                $productData[$index]['offers'] = $this->addDeliveryDataToOffersData($associatedProductData['offers'], $dataObject);
            }
        } else {
            $productData['offers'] = $this->addDeliveryDataToOffersData($productData['offers'], $dataObject);
        }

        return $productData;
    }

    public function addDeliveryDataToOffersData(array $offersData, \Magento\Framework\DataObject $dataObject): array
    {
        $deliveryData = $this->getDeliveryData($dataObject);

        if ($deliveryData) {
            $offersData['shippingDetails'] = $deliveryData;
        }

        return $offersData;
    }

    public function getDeliveryData(\Magento\Framework\DataObject $dataObject): array
    {
        $store = $dataObject->getStore();

        if (!$this->productConfiguration->isDeliveryDataEnabled($store->getId())) {
            return [];
        }

        $availableCarriers = $this->getAvailableCarriers($store);

        if (!$availableCarriers) {
            return [];
        }

        $businessDaysData = $this->businessDays->getBusinessDaysData($dataObject);
        $handlingTimeData = $this->handlingTime->getHandlingTimeData($dataObject);
        $transitTimeData = $this->transitTime->getTransitTimeData($dataObject);
        $cutoffTimeData = $this->cutoffTime->getCutoffTimeData($dataObject);

        $deliveryTimeData = ['@type' => 'ShippingDeliveryTime'];
        $deliveryTimeData = array_merge($deliveryTimeData, $businessDaysData);
        $deliveryTimeData = array_merge($deliveryTimeData, $cutoffTimeData);
        $deliveryTimeData = array_merge($deliveryTimeData, $handlingTimeData);
        $deliveryTimeData = array_merge($deliveryTimeData, $transitTimeData);

        $shippingDestination = [
            '@type' => 'DefinedRegion',
            'addressCountry' => $this->configuration->getCountryByWebsite($store->getWebsite())
        ];

        $data = [];
        foreach ($availableCarriers as $carrier) {
            $shippingRateData = [
                '@type' => 'MonetaryAmount',
                'value' => $carrier['price'],
                'currency' => $dataObject->getCurrencyCode()
            ];

            $offerShippingDetails = [
                '@type' => 'OfferShippingDetails',
                'deliveryTime' => $deliveryTimeData,
                'shippingRate' => $shippingRateData,
                'shippingDestination' => $shippingDestination
            ];

            $data[] = $offerShippingDetails;
        }

        return $data;
    }

    public function getAvailableCarriers(\Magento\Store\Api\Data\StoreInterface $store): array
    {
        $allCarriers = $this->configuration->getCarriers($store);

        $availableCarriers = [];
        foreach ($allCarriers as $carrierCode => $carrier) {
            $isActive = $this->arrayManager->get('active', $carrier);
            if (!(bool)$isActive) {
                continue;
            }

            if (!$this->isShippingMethodAvailable($carrier, $store)) {
                continue;
            }

            $price = $this->arrayManager->get('price', $carrier);
            if ($price === null) {
                continue;
            }

            $availableCarriers[$carrierCode] = [
                'name' => $carrier['name'],
                'price' => $price
            ];
        }

        return $availableCarriers;
    }

    public function isShippingMethodAvailable(array $carrier, \Magento\Store\Api\Data\StoreInterface $store): bool
    {
        $allowSpecificCountries = $this->arrayManager->get('sallowspecific', $carrier);
        if ((bool)$allowSpecificCountries) {
            if ($this->isShippingMethodAvailableForWebsite($carrier, $store)) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function isShippingMethodAvailableForWebsite(array $carrier, \Magento\Store\Api\Data\StoreInterface $store): bool
    {
        $websiteCountry = $this->configuration->getCountryByWebsite($store->getWebsite());
        if (!$websiteCountry) {
            return false;
        }

        $specificCountries = $this->getSpecificCountries($carrier);
        if (in_array($websiteCountry, $specificCountries)) {
            return true;
        }

        return false;
    }

    public function getSpecificCountries(array $carrier): array
    {
        $allowedCountries = $this->arrayManager->get('specificcountry', $carrier);

        $specificCountries = [];
        if ($allowedCountries) {
            $specificCountries = explode(',', $allowedCountries);
        }

        return $specificCountries;
    }
}
