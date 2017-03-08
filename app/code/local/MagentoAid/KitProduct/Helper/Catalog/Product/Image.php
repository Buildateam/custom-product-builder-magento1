<?php
class MagentoAid_KitProduct_Helper_Catalog_Product_Image 
extends Mage_Core_Helper_Abstract
{
    public function getImageUrl($kitProduct)
    {
        $imgDir = trim($kitProduct->getName());
        $imgPath = "kitproduct/{$imgDir}/";
		return $imgPath;
    }

    public function getImagePath($kitProduct)
    {
        $imgDir = trim($kitProduct->getName());
        $imgPath = "kitproduct" . DS . "{$imgDir}" . DS;
        return $imgPath;
    }

    public function getCompositeImagePath($kitProduct)
    {
        $imgDir = trim($kitProduct->getName());
        $imgPath = "kitproduct" . DS . "{$imgDir}_composite" . DS;
        return $imgPath;
    }

    public function createImageFoldersIfNeed($kitProduct)
    {
        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        $imgPath = $baseDir . DS . $this->getImagePath( $kitProduct );
        $compositeImgPath = $baseDir . DS . $this->getCompositeImagePath( $kitProduct );
        if ( !is_dir($imgPath) )
        {
            if ( !mkdir( $imgPath ) )
            {
                //throw new Exception('Failed to create Kit images folder.');
            }
        }
        if ( !is_dir($compositeImgPath) )
        {
            if ( !mkdir( $compositeImgPath ) )
            {
                //throw new Exception('Failed to create Kit composite images folder.');
            }
        }
    }

    protected function _getCompositeFileName($kitProduct)
    {
        $simpleIds = $this->_getSimpleIdsFromKit( $kitProduct );
        $filename = md5( implode('|', $simpleIds) ) . '.png';
        return $filename;
    }

    protected function _getSimpleIdsFromKit($kitProduct)
    {
        if ( $kitProduct->getCustomOption('kitproduct_simple_product_ids') )
        {
            $simpleIds = unserialize( $kitProduct->getCustomOption('kitproduct_simple_product_ids')->getValue() );
            return $simpleIds;
        }
        return null;
    }

    protected function _composite($fromImgPaths, $toImgPath)
    {
        $im = new Imagick();
        foreach( $fromImgPaths as $fromImgPath)
        {
            try
            {
                $im2 = new Imagick( $fromImgPath );
                $im->addImage($im2);
            }
            catch(Exception $e)
            {
                Mage::logException($e);
            }
        }
        $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_COMPOSITE);
        if ( $im->writeImage( $toImgPath ) )
        {
            return true;
        }

        return false;
    }

    protected function _generateCompositeImage($kitProduct)
    {
        //Mage::log('_generateCompositeImage');
        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        $simpleIds = $this->_getSimpleIdsFromKit( $kitProduct );

        // to keep order !!!
        $fromImgPaths = array();
        foreach( $simpleIds as $simpleId)
        {
            $s = Mage::getModel('catalog/product')->load( $simpleId );
            $img = $s->getSku() . '_smallimage.png';
            $imgPath = $baseDir . DS . $this->getImagePath( $kitProduct ) . $img;
            $fromImgPaths[]= $imgPath;
        }

        $imgComposite = $this->_getCompositeFileName( $kitProduct );
        $toImgPath = $baseDir . DS . $this->getCompositeImagePath( $kitProduct ) . $imgComposite;
        if ( $this->_composite($fromImgPaths, $toImgPath) )
        {
            return true;
        }

        return false;
    }

    public function getCompositeImage($kitProduct)
    {
        //Mage::log('getCompositeImage');
        if ( $kitProduct->getCustomOption('kitproduct_simple_product_ids') )
        {
            $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
            $imgComposite = $this->_getCompositeFileName( $kitProduct );

            $imgCompositePath = $baseDir . DS . $this->getCompositeImagePath( $kitProduct ) . $imgComposite;
            if ( file_exists($imgCompositePath) )
            {
                //Mage::log('composite load');
                return $this->getCompositeImagePath( $kitProduct ) . $imgComposite;
            }
            else
            {
                //Mage::log('composite generate');
                if ( $this->_generateCompositeImage( $kitProduct ) )
                {
                    return $this->getCompositeImagePath( $kitProduct ) . $imgComposite;
                }
            }
        }

        return null;
    }
}
