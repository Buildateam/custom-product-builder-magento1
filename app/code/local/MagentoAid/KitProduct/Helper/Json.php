<?php
class MagentoAid_KitProduct_Helper_Json extends Mage_Core_Helper_Abstract
{
    const JSON_KEY_PREFIX = 'key';

    protected $_mapper;

    public function __construct()
    {
        $this->_mapper = Mage::helper('kitproduct/json_mapper');
        $this->_mapper->setKitProduct( Mage::registry('current_product') );
    }

	public function getJsonKeyPrefix()
	{
	    return self::JSON_KEY_PREFIX;
	}

    public function prepareArrayForKit($product)
    {
        if ( $product->getTypeId() != MagentoAid_KitProduct_Model_Product_Type::TYPE_KITPRODUCT )
        {
            throw new Exception('Attempt to generate JSON on a non-Kit product.');
        }

        $a = array();

        $optionsCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
        foreach ($optionsCollection as $option)
        {
            $g = $this->_mapper->prepare($option, 'group');
            $g['subgroups']= array();

            $selectionsCollection = $option->getSelectionsCollection();
            foreach($selectionsCollection as $selection)
            {
                if ( ( $selection->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED ) || (!$selection->getIsInStock()) )
                {
                    continue;
                }

                $sg = $this->_mapper->prepare($selection, 'subgroup');
                $sg['options']= array();

                $simples = Mage::helper('kitproduct/option_product')->getSortedSimples( $selection );
                foreach($simples as $s)
                {
                    $o = $this->_mapper->prepare($s, 'option');
                    $o['configurableProductId']= $selection->getEntityId();
                    $sg['options'][]= $o;
                }

                $this->groupDependentOptions( $sg['options'] );

                $g['subgroups'][]= $sg;
            }

            $a[]= $g;
        }

        return $a;
    }

    protected function _isNeedToGroupOptions($option1, $option2)
    {
        if ( $option1['name'] == $option2['name'] )
        {
            return true;
        }

        return false;
    }

    protected function _groupOptions(&$option1, &$option2)
    {
        if ( !$this->_isGroupedOptions($option1) )
        {
            $dependsOn1 = $option1['depends_on'];
            $option1['id']          = array($dependsOn1 => $option1['id']);
            $option1['order']       = array($dependsOn1 => $option1['order']);
            $option1['price']       = array($dependsOn1 => $option1['price']);
            $option1['description'] = array($dependsOn1 => $option1['description']);
            $option1['depends_on']  = array($dependsOn1);
            $option1['zoomUrl']     = array($dependsOn1 => $option1['zoomUrl']);
            $option1['imageUrl']    = array($dependsOn1 => $option1['imageUrl']);
            $option1['iconUrl']     = array($dependsOn1 => $option1['iconUrl']);
            $option1['is_salable']  = array($dependsOn1 => $option1['is_salable']);
        }

        $dependsOn2 = $option2['depends_on'];
        $option1['id'][$dependsOn2]          = $option2['id'];
        $option1['order'][$dependsOn2]       = $option2['order'];
        $option1['price'][$dependsOn2]       = $option2['price'];
        $option1['description'][$dependsOn2] = $option2['description'];
        $option1['depends_on'][]             = $dependsOn2;
        $option1['zoomUrl'][$dependsOn2]     = $option2['zoomUrl'];
        $option1['imageUrl'][$dependsOn2]    = $option2['imageUrl'];
        $option1['iconUrl'][$dependsOn2]     = $option2['iconUrl'];
        $option1['is_salable'][$dependsOn2]  = $option2['is_salable'];

        $option2['deleted_after_combining'] = 1;
    }

    protected function _isGroupedOptions($option)
    {
        $res = is_array($option['depends_on']) ? true : false;
        return $res;
    }

    public function groupDependentOptions(&$options)
    {
        for($i = 0; $i < count($options); $i++)
        {
            $oi = &$options[$i];

            for($j = $i; $j < count($options); $j++)
            {
                $oj = &$options[$j];

                if ( $i != $j )
                if ( $this->_isNeedToGroupOptions($oi, $oj) )
                {
                    $this->_groupOptions($oi, $oj);
                }
            }
        }

        $this->_processDeletedOptions( $options );
    }

    protected function _processDeletedOptions(&$options)
    {
        foreach($options as $key => $o)
        if ( isset($o['deleted_after_combining']) )
        {
            unset($options[$key]);
        }

        # bypassing of JSON generating native bugs
        $optionsCopy = array();
        foreach($options as $o)
        {
            $optionsCopy[]= $o;
        }
        $options = $optionsCopy;
    }

    public function prepareForKit($product)
    {
        $a = $this->prepareArrayForKit($product);
        $json = Zend_Json::encode($a);
        $json = Zend_Json::prettyPrint($json, array('indent' => '  '));

        return $json;
    }
}
