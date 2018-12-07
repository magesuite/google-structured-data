<?php
namespace MageSuite\GoogleStructuredData\Observer;

class AddStructuredDataToProduct implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider
     */
    private $structuredDataProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider $structuredDataProvider
    )
    {
        $this->structuredDataProvider = $structuredDataProvider;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $data = [
            "@context" => "http://schema.org/",
            "@type" => "Product",
            "name" => "Executive Anvil",
            "image" => [
                "https://example.com/photos/1x1/photo.jpg",
                "https://example.com/photos/4x3/photo.jpg",
                "https://example.com/photos/16x9/photo.jpg"
            ],
            "description" => "Sleeker than ACME\'s Classic Anvil, the Executive Anvil is perfect for the business traveler looking for something to drop from a height.",
            "mpn" => "925872",
            "brand" => [
                "@type" => "Thing",
                "name" => "ACME"
            ],
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "ratingValue" => "4.4",
                "reviewCount" => "89"
            ],
            "offers" => [
                "@type" => "Offer",
                "priceCurrency" => "USD",
                "price" => "119.99",
                "priceValidUntil" => "2020-11-05",
                "itemCondition" => "http://schema.org/UsedCondition",
                "availability" => "http://schema.org/InStock",
                "seller" => [
                    "@type" => "Organization",
                    "name" => "Executive Objects"
                ]
            ]
        ];


        $this->structuredDataProvider->add($data);
    }
}