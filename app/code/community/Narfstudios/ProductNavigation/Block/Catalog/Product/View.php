<?php
/**
 * Product View block
 *
 * @package    Narfstudios
 * @module     ProductNavigation
 */
class Narfstudios_ProductNavigation_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * Return the Navigation urls from the XML file for the current category
     * @return <type>
     */
    public function getNavigationUrls() {
    	// get the current category
        $burl = $this->helper('core/url')->getCurrentUrl();
        $layer = Mage::getSingleton('catalog/layer');
        $_category = $layer->getCurrentCategory();

		// load the url array
        $navigateProducts =      Mage::helper('productnavigation')->getCategoryProductUrls($this->getProduct()->getId(), $_category->getId());
        if(!empty($navigateProducts['currentPosition'])) {
            return $navigateProducts;
        } else {
            return array();
        }
    }
}