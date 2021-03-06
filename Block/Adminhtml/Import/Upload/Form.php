<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_CmsImportExport
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\CmsImportExport\Block\Adminhtml\Import\Upload;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Store\Api\StoreRepositoryInterface;
use MSP\CmsImportExport\Api\ContentInterface;
use MSP\CmsImportExport\Model\Source\CmsMode;
use MSP\CmsImportExport\Model\Source\MediaMode;

class Form extends Generic
{
    protected $mediaMode;
    protected $cmsMode;
    protected $storeRepositoryInterface;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        StoreRepositoryInterface $storeRepositoryInterface,
        MediaMode $mediaMode,
        CmsMode $cmsMode,
        array $data = []
    ) {
        $this->mediaMode = $mediaMode;
        $this->cmsMode = $cmsMode;
        $this->storeRepositoryInterface = $storeRepositoryInterface;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'enctype' => 'multipart/form-data',
                    'action' => $this->getUrl('*/*/post'),
                    'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('import_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Upload'),
                'class' => 'fieldset-wide',
            ]
        );

        $fieldsetMode = $form->addFieldset(
            'mode_fieldset',
            [
                'legend' => __('Import mode'),
                'class' => 'fieldset-wide',
            ]
        );

        $fieldsetStores = $form->addFieldset(
            'store_fieldset',
            [
                'legend' => __('Store mapping'),
                'class' => 'fieldset-wide',
            ]
        );


        $fieldset->addField(
            'zipfile',
            'file',
            [
                'name' => 'zipfile',
                'label' => __('ZIP File'),
                'title' => __('ZIP File'),
                'required' => true,
            ]
        );

        $fieldsetMode->addField(
            'cms_mode',
            'select',
            [
                'name' => 'cms_mode',
                'label' => __('CMS import mode'),
                'title' => __('CMS import mode'),
                'required' => true,
                'values' => $this->cmsMode->toOptionArray(),
            ]
        );

        $fieldsetMode->addField(
            'media_mode',
            'select',
            [
                'name' => 'media_mode',
                'label' => __('Media import mode'),
                'title' => __('Media import mode'),
                'required' => true,
                'values' => $this->mediaMode->toOptionArray(),
            ]
        );

        $stores = $this->storeRepositoryInterface->getList();
        foreach ($stores as $storeInterface) {
            $fieldsetStores->addField(
                'store_map:'.$storeInterface->getCode(),
                'text',
                [
                    'name' => 'store_map['.$storeInterface->getCode().']',
                    'label' => __('Store "%1"', $storeInterface->getCode()),
                    'title' => __('Store "%1"', $storeInterface->getCode()),
                    'required' => false,
                ]
            );
        }

        $values = [
            'cms_mode' => ContentInterface::CMS_MODE_UPDATE,
            'media_mode' => ContentInterface::MEDIA_MODE_UPDATE,
        ];
        foreach ($stores as $storeInterface) {
            $values['store_map:'.$storeInterface->getCode()] = $storeInterface->getCode();
        }

        // Set defaults
        $form->setValues($values);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
