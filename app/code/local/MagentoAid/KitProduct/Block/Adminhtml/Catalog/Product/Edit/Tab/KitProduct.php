<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is kitproductd with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml catalog product kitproduct items tab block
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MagentoAid_KitProduct_Block_Adminhtml_Catalog_Product_Edit_Tab_KitProduct extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_product = null;
    public function __construct()
    {
        parent::__construct();
        //$this->setSkipGenerateContent(true);
        $this->setTemplate('kitproduct/product/edit/kitproduct.phtml');
    }

    public function getTabUrl()
    {
        return '#';
        //return $this->getUrl('*/kitproduct_product_edit/form', array('_current' => true));
    }

    public function getTabClass()
    {
        return '';
        //return 'ajax';
    }

    /**
     * Prepare layout
     *
     * @return MagentoAid_KitProduct_Block_Adminhtml_Catalog_Product_Edit_Tab_KitProduct
     */
    protected function _prepareLayout()
    {



        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('kitproduct')->__('Add New Group'),
                    'class' => 'add',
                    'id'    => 'add_new_option',
                    'on_click' => 'bOption.add()'
                ))
        );

        $this->setChild('options_box',
            $this->getLayout()->createBlock('kitproduct/adminhtml_catalog_product_edit_tab_kitProduct_option',
                'adminhtml.catalog.product.edit.tab.kitproduct.option')
        );

        return parent::_prepareLayout();
    }

    /**
     * Check block readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getCompositeReadonly();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('options_box');
    }

    public function getFieldSuffix()
    {
        return 'product';
    }

    public function getProduct()
    {
        return Mage::registry('product');
    }

    public function getTabLabel()
    {
        return Mage::helper('kitproduct')->__('Configuration');
    }
    public function getTabTitle()
    {
        return Mage::helper('kitproduct')->__('Configuration');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
}
