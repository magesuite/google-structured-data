<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\Adminhtml\Product\Attribuite\Edit\Tab\Front;

class AddGoogleStructuredData
{
    public function __construct(
        protected \Magento\Config\Model\Config\Source\Yesno $yesNoSource,
        protected \MageSuite\GoogleStructuredData\Model\Config\Source\VariesByOptions $variesByOptions
    ) {}

    public function afterSetForm(
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject,
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $result,
        \Magento\Framework\Data\Form $form
    ): \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front {
        $fieldset = $this->createGoogleStructuredDataFieldset($form, $subject);
        $this->addVariesByField($fieldset);
        $this->addVaryAttributeCodeField($fieldset);
        $this->addUseInStructuredDataField($fieldset);
        $this->addStructuredDataDescriptionField($fieldset);
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

        $fieldset->addField('varies_by', 'select', $config);
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

        $fieldset->addField('vary_attribute_code', 'text', $config);
    }

    protected function addUseInStructuredDataField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset): void
    {
        $config = [
            'name' => 'use_in_structured_data',
            'label' => __('Use in Structured Data'),
            'values' => $this->yesNoSource->toOptionArray(),
            'note' => __('Adds the attribute label and value to the product structured data as an additional property.'),
        ];

        $fieldset->addField('use_in_structured_data', 'select', $config);
    }

    protected function addStructuredDataDescriptionField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset): void
    {
        $description = __(
            'Optional context for search engines and AI assistants, added as the description of the additional property. '
            . 'Plain text applies to all store views. To set it per store view, use JSON with store view codes, for example %1. '
            . 'A store view without an entry uses the default store view of its website.',
            '{"de": "...", "fr": "..."}'
        );

        $config = [
            'name' => 'structured_data_description',
            'label' => __('Structured Data Description'),
            'note' => $description,
        ];

        $fieldset->addField('structured_data_description', 'textarea', $config);
    }
}
