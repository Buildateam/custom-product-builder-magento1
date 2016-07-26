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
 * KitProduct Option Model
 *
 * @method MagentoAid_KitProduct_Model_Resource_Option _getResource()
 * @method MagentoAid_KitProduct_Model_Resource_Option getResource()
 * @method int getParentId()
 * @method MagentoAid_KitProduct_Model_Option setParentId(int $value)
 * @method int getRequired()
 * @method MagentoAid_KitProduct_Model_Option setRequired(int $value)
 * @method int getPosition()
 * @method MagentoAid_KitProduct_Model_Option setPosition(int $value)
 * @method string getType()
 * @method MagentoAid_KitProduct_Model_Option setType(string $value)
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MagentoAid_KitProduct_Model_Option extends Mage_Core_Model_Abstract
{
    /**
     * Default selection object
     *
     * @var MagentoAid_KitProduct_Model_Selection
     */
    protected $_defaultSelection = null;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('kitproduct/option');
        parent::_construct();
    }

    /**
     * Add selection to option
     *
     * @param MagentoAid_KitProduct_Model_Selection $selection
     * @return MagentoAid_KitProduct_Model_Option
     */
    public function addSelection($selection)
    {
        if (!$selection) {
            return false;
        }
        if (!$selections = $this->getData('selections')) {
            $selections = array();
        }
        array_push($selections, $selection);
        $this->setSelections($selections);
        return $this;
    }

    /**
     * Check Is Saleable Option
     *
     * @return bool
     */
    public function isSaleable()
    {

        $saleable = 0;
        if ($this->getSelections()) {
            foreach ($this->getSelections() as $selection) {
                if ($selection->isSaleable()) {
                    $saleable++;
                }
            }
            return (bool)$saleable;
        }
        else {
            return false;
        }
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->getItems();
    }

    /**
     * Retrieve default Selection object
     *
     * @return MagentoAid_KitProduct_Model_Selection
     */
    public function getDefaultSelection()
    {
        if (!$this->_defaultSelection && $this->getSelections()) {
            foreach ($this->getSelections() as $selection) {
                if ($selection->getIsDefault()) {
                    $this->_defaultSelection = $selection;
                    break;
                }
            }
        }
        return $this->_defaultSelection;
        /**
         *         if (!$this->_defaultSelection && $this->getSelections()) {
            $_selections = array();
            foreach ($this->getSelections() as $selection) {
                if ($selection->getIsDefault()) {
                    $_selections[] = $selection;
                }
            }
            if (!empty($_selections)) {
                $this->_defaultSelection = $_selections;
            } else {
                return null;
            }
        }
        return $this->_defaultSelection;
         */
    }

    public function getSelections()
    {
        if(!$this->getData('selections')) {
            $this->getItems();
        }
        return $this->getData('selections');
    }

    public function getSelectionsCollection()
    {
        $selectionCollection = Mage::getResourceModel('kitproduct/selection_collection')
            ->setOptionIdsFilter(array($this->getId()))
            ->addAttributeToSelect('name')
        ;
        $selectionCollection->getSelect()->order("position ASC");

        return $selectionCollection;
    }

    public function getItems()
    {
        $store = Mage::app()->getStore()->getId();
        $locale = Mage::getSingleton('core/locale');
        $locale->emulate(0);
        Mage::app()->getStore()->setId(0);
        $selectionCollection = Mage::getResourceModel('kitproduct/selection_collection')
            ->setOptionIdsFilter(array($this->getId()));
        foreach($selectionCollection as $selection) {
            $this->addSelection($selection);
        }
        //$this->setSelections($selectionCollection->getItems());
        $locale->revert();
        Mage::app()->getStore()->setId($store);
        //var_dump($selectionCollection->getFirstItem());
        return $selectionCollection->getData();

    }
    /**
     * Check is multi Option selection
     *
     * @return bool
     */
    public function isMultiSelection()
    {
        if ($this->getType() == 'checkbox' || $this->getType() == 'multi') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve options searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        return $this->_getResource()
            ->getSearchableData($productId, $storeId);
    }

    /**
     * Return selection by it's id
     *
     * @param int $selectionId
     * @return MagentoAid_KitProduct_Model_Selection
     */
    public function getSelectionById($selectionId)
    {
        $selections = $this->getSelections();
        $i = count($selections);

        while ($i-- && $selections[$i]->getSelectionId() != $selectionId);

        return $i == -1 ? false : $selections[$i];
    }
}
