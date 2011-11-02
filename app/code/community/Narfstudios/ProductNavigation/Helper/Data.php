<?php
/**
 * Helper
 *
 * @package    Narfstudios
 * @module     ProductNavigation
 */
class Narfstudios_ProductNavigation_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    /**
     * returns the optimizer xml path
	 * @return string	Path to the file
     */
	function getXmlFilepath() {
		// prepare model and path	
		$sitemap = Mage::getModel('ProductNavigation/sitemap');
		$path = $_SERVER['DOCUMENT_ROOT'].'/sitemap/';
		
		// Get the filename of the xml
		if($sitemap) {
			return $path.$sitemap->getOptimizedSitemapFilename();
		}
		// default
		return $path.'sitemap-optimizer.xml';
	}
	
	
    /**
     * Looking for the current position of the product/category combination in the xml an return
	 * whole url array with the pointer to the current position 
     * @param $productid
	 * @param $categoryid
     * @return array The url array 
     */
    public function getCategoryProductUrls($productid, $categoryid) {
		
		// get the data from the sitemap-optimizer.xml
        $file = $this->getXmlFilepath();
        if (file_exists($file)) {
            $xml = simplexml_load_file($file);
            
            // variables to prepare navigation
            $i=0;
            $next = false;
            
            // check each element
            foreach($xml as $index => $elem) {
                $ns_dc = $elem->children('http://www.narf-studios.de');                
                $locurl = (string)$elem->loc;
                $type = (string)$ns_dc->type;
                $catid = (int)$ns_dc->catid;
                $pid = (int)$ns_dc->pid;

                if( $catid == $categoryid && $type == 'category_product' ) {
                    $i++;

                    // save the next
                    if($next == true) {
                        $navigateProducts[1] = $locurl;
                        $next = false;
                    }
                    
                    // current element
                    if($productid == $pid) {
                       // get the one before
                       $navigateProducts[0] = $before;
                       // save the current
                       $navigateProducts['currentPosition'] = $i;
                       // make sure next will be loaded
                       $next = true;
                    }
                    
                    // element before
                    $before = $locurl;
                }
            }
            $navigateProducts['count'] = $i;
            return $navigateProducts;

        } else {
            Mage::log('Sitemap not found in '.$file);
        }
        return array();
    }
}
?>
