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
 * KitProduct Selection Model
 *
 * @method MagentoAid_KitProduct_Model_Resource_Selection _getResource()
 * @method MagentoAid_KitProduct_Model_Resource_Selection getResource()
 * @method int getOptionId()
 * @method MagentoAid_KitProduct_Model_Selection setOptionId(int $value)
 * @method int getParentProductId()
 * @method MagentoAid_KitProduct_Model_Selection setParentProductId(int $value)
 * @method int getProductId()
 * @method MagentoAid_KitProduct_Model_Selection setProductId(int $value)
 * @method int getPosition()
 * @method MagentoAid_KitProduct_Model_Selection setPosition(int $value)
 * @method int getIsDefault()
 * @method MagentoAid_KitProduct_Model_Selection setIsDefault(int $value)
 * @method int getSelectionPriceType()
 * @method MagentoAid_KitProduct_Model_Selection setSelectionPriceType(int $value)
 * @method float getSelectionPriceValue()
 * @method MagentoAid_KitProduct_Model_Selection setSelectionPriceValue(float $value)
 * @method float getSelectionQty()
 * @method MagentoAid_KitProduct_Model_Selection setSelectionQty(float $value)
 * @method int getSelectionCanChangeQty()
 * @method MagentoAid_KitProduct_Model_Selection setSelectionCanChangeQty(int $value)
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MagentoAid_KitProduct_Model_Selection extends Mage_Core_Model_Abstract
{
    protected $_product;
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('kitproduct/selection');
        parent::_construct();
    }

    public function getProduct()
    {
        if( ! $this->_product) {
            $this->_product = Mage::getModel('catalog/product')->load($this->getProductId());
        }
        return $this->_product;
    }
    /**
     * Processing object before save data
     *
     * @return MagentoAid_KitProduct_Model_Selection
     */
    protected function _beforeSave()
    {
        $storeId = Mage::registry('product')->getStoreId();
        if (!Mage::helper('catalog')->isPriceGlobal() && $storeId) {
            $this->setWebsiteId(Mage::app()->getStore($storeId)->getWebsiteId());
            $this->getResource()->saveSelectionPrice($this);

            if (!$this->getDefaultPriceScope()) {
                $this->unsSelectionPriceValue();
                $this->unsSelectionPriceType();
            }
        }
        $this->uploadImage();
        parent::_beforeSave();
    }

    public function uploadImage()
    {
        if (isset($_FILES['kitproduct_selections']['name'][$this->getParentIndex()][$this->getIndex()])) {
            $path = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/catalog/kitproduct/';
            $ext = pathinfo($_FILES['kitproduct_selections']['name'][$this->getParentIndex()][$this->getIndex()]['image'], PATHINFO_EXTENSION);
            $destinationFile = $path.$this->getImageFileName().'.'.$ext;
            if(!file_exists($path.$this->getData('orig_image'))) {
                $this->setData('image','');
            }
            try {
                move_uploaded_file($_FILES['kitproduct_selections']['tmp_name'][$this->getParentIndex()][$this->getIndex()]['image'], $destinationFile);
                chmod($destinationFile, 0777);
            } catch (Exception $e) {
                Mage::logException($e->getMessage());
            }
            if($ext)
            $this->setData('image',$this->getImageFileName().'.'.$ext);
        }
        if(!file_exists($path.$this->getData('image'))) {
            $this->setData('image','');
        }


    }

    public function getImageFileName()
    {
        return $this->getOptionId() . '_' . $this->getId();
    }
}
