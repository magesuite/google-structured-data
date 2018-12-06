<?php
namespace MageSuite\GoogleStructuredData\Observer;


class AddAdditionalDataToRenderedPage implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getResponse();

    }
}