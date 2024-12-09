<?php

namespace MageSuite\GoogleStructuredData\Plugin\Adminhtml\Product\Attribuite\Edit\Tab\Front;

class AddGoogleStructuredData
{
    protected \MageSuite\GoogleStructuredData\Model\Config\Source\VariesByOptions $variesByOptions;

    public function __construct(\MageSuite\GoogleStructuredData\Model\Config\Source\VariesByOptions $variesByOptions)
    {
        $this->variesByOptions = $variesByOptions;
    }

    public function afterSetForm(
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject,
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $result,
        \Magento\Framework\Data\Form $form
    ): \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front {
        $fieldset = $this->createGoogleStructuredDataFieldset($form, $subject);
        $this->addVariesByField($fieldset);
        $this->addVaryAttributeCodeField($fieldset);
        return $result;
    }

    protected function createGoogleStructuredDataFieldset(
        \Magento\Framework\Data\Form $form,
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject
    ): \Magento\Framework\Data\Form\Element\Fieldset {
        $fieldset = $form->addFieldset(
            'magesuite_google_structured_data_fieldset',
            [
                'legend'      => __('Google Structured Data'),
                'collapsable' => $subject->getRequest()->has('popup'),
            ],
            'elasticsuite_catalog_attribute_advanced_fieldset'
        );

        $fieldset->addClass('cs-csfeature__logo');
        return $fieldset;
    }

    protected function addVariesByField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset): void
    {
        $description = __(
            'This attribute is used to specify the type of variation of the product. '
            . 'More details about available options can be found in %1.',
            '<a href="https://developers.google.com/search/docs/appearance/structured-data/product-variants#structured-data-type-definitions">the documentation</a>'
        );

        $config = [
            'name' => 'varies_by',
            'label' => __('Varies By'),
            'values' => $this->variesByOptions->toOptionArray(),
            'note' => $description,
        ];

        $fieldset->addField(
            'varies_by',
            'select',
            $config
        );
    }

    protected function addVaryAttributeCodeField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset): void
    {
        $description = __(
            'This field is used to map the variation attribute of the product with Google Structure supported schema types (ex. size, color).'
            . 'This field is optional. If you leave it empty, the original attribute code will be used.'
        );

        $config = [
            'name' => 'vary_attribute_code',
            'label' => __('Vary Attribute Code'),
            'note' => $description,
        ];

        $fieldset->addField(
            'vary_attribute_code',
            'text',
            $config
        );
    }
}
