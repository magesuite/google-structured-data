<?php
declare(strict_types=1);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $pageRepository \Magento\Cms\Api\PageRepositoryInterface */
$pageRepository = $objectManager->create(\Magento\Cms\Api\PageRepositoryInterface::class);
$contentConstructorContent = [
    [
        'type' => 'accordion',
        'name' => 'Accordion',
        'id' => 'component1',
        'section' => 'content',
        'data' => [
            'customCssClass' => '',
            'multiple_collapsible' => false,
            'expand_first' => false,
            'groups' => [
                [
                    'headline' => '',
                    'items' => [
                        [
                            'headline' => 'Dummy Question',
                            'content' => '<h1>Dummy Answer</h1>',
                            'isEditorOpened' => false,
                            'editiorId' => 'editior_1'
                        ]
                    ]
                ]
            ]
        ]
    ]
];
/** @var $page \Magento\Cms\Model\Page */
$page = $objectManager->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Cms Page with accordion component')
    ->setIdentifier('page-with-accordion-component')
    ->setStores([0])
    ->setIsActive(1)
    ->setContent('')
    ->setContentConstructorContent(json_encode($contentConstructorContent));
$pageRepository->save($page);
