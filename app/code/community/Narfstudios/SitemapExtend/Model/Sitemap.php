<?php
class Narfstudios_SitemapExtend_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    private $urlkeys;
    
	/**
	 * Returns the name of the xml file
	 * TODO: get the value from systemconfig
	 */
	private function getOptimizedSitemapFilename() {
		return 'sitemap-optimizer.xml';
	}
	
    /**
     * Returns an xml file with contains the urls for
	 * - categories url
	 * - products in categories (with the ids of products and category)
	 * - products url
	 * - other cms pages
	 * This can e.g. be used to create a horizontal navigation in the product view
     * @return Mage_Sitemap_Model_Sitemap
     */
    public function generateXml()
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));


        if ($io->fileExists($this->getOptimizedSitemapFilename()) && !$io->isWriteable($this->getOptimizedSitemapFilename())) {
            Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->getOptimizedSitemapFilename(), $this->getPath()));
        }

        $io->streamOpen($this->getOptimizedSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:narfstudios="http://www.narf-studios.de">');

        $storeId = $this->getStoreId();
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        foreach ($collection as $item) {
            $url = $baseUrl . $item->getUrl();
            $url = str_replace('/index.php', '', $url);
            $xml = sprintf('<url><narfstudios:type>category</narfstudios:type><narfstudios:catid>'.$item->getId().'</narfstudios:catid><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($url),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate products in categories sitemap
         */
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        foreach ($collection as $item) {
            $category   = Mage::getModel('catalog/category')->load($item->getId());
            
            $pCollection= $category->getProductCollection();
            $pCollection->addAttributeToFilter('status', 1);
            $pCollection->addAttributeToFilter('visibility', 4); //catalog, search 
            $pCollection->addAttributeToSort('name', 'asc');
            foreach ($pCollection as $product)
            {
                $product = Mage::getModel('catalog/product')->load($product->getId());
                $burl = Mage::getBaseUrl().Mage::getModel('core/url_rewrite')->loadByIdPath('product/'.$product->getId().'/'.$category->getId())->getRequestPath();
                $url = str_replace('/index.php', '', $burl);

                $xml = sprintf('<url><narfstudios:type>category_product</narfstudios:type><narfstudios:catid>'.$category->getId().'</narfstudios:catid><narfstudios:pid>'.$product->getId().'</narfstudios:pid><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($url),
                $date,
                $changefreq,
                $priority);
                $io->streamWrite($xml);
            }
        }

        /**
         * Generate products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><narfstudios:type>product</narfstudios:type><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        $io->streamWrite('</urlset>');
        $io->streamClose();

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();
		
		parent::generateXml();

        return $this;
    }
}
