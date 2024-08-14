<?php

namespace MageSuite\GoogleStructuredData\Plugin\Adminhtml\Product\Attribuite\Edit\Tab\Front;

class AddGoogleStructuredData
{
    protected array $variesByTypes;

    public function __construct(array $variesByTypes)
    {
        $this->variesByTypes = $variesByTypes;
    }

    public function afterSetForm(
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject,
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $result,
        \Magento\Framework\Data\Form $form
    ) {
        $fieldset = $this->createGoogleStructuredDataFieldset($form, $subject);
        $this->addVariesByField($fieldset);
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
            'label' => __('VariesBy'),
            'values' => $this->getVariesByOptions(),
            'note' => $description,
        ];

        $fieldset->addField(
            'varies_by',
            'select',
            $config
        );
    }

    protected function getVariesByOptions(): array
    {
        $options = [['value' => '', 'label' => ' ']];
        foreach ($this->variesByTypes as $type) {
            $options[] = ['value' => $type, 'label' => $type];
        }

        return $options;
    }
}
