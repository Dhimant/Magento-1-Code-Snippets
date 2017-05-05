<?php
error_reporting(E_ALL | E_STRICT);
define('MAGENTO_ROOT', getcwd());
ini_set('max_execution_time', 600000);
$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
Mage::app();

// Pull product collection
$productCollection = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToSelect(array('id','name','product_url','price','description','image'));
// Divide the collection into "pages" that contain 100 products each
$productCollection->setPageSize(100);

/* Get the page number of the last page, so we know how many pages of        
 * products we need to iterate through.
 *
 * pages = total items in collection / page size
 */
$pages = $productCollection->getLastPageNumber();

// Start on page 1
$currentPage = 1;

// Open our file for writing
$write = fopen('datafeed.csv', 'w');

// Create our first row which is the columns for our data
fwrite($write, implode(",", array('id','title','link','image_link','price','description')) . "\r\n");
 
// Iterate until $currentPage reaches the total number of pages.

do {
    $productCollection->setCurPage($currentPage);
      
    /* When passing a collection into a foreach loop
      * load() is automagically called on the collection.
     */
    foreach ($productCollection as $_product) {
        // write our comma-delimited line of data to our file

    	//echo $_product->getId();
    	/*echo $_product->getName() ;
    	echo $_product->getProductUrl();
        echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$_product->getImage();
        echo $_product->getFinalPrice();
        echo $_product->getDescription();*/

        $description = $_product->getDescription();
        //str_replace(array("\r\n", "\n\r", "\n", "\r"), '|', $description);
        $description = preg_replace('/[\n\r]+/', ',', trim($description));
        $description = strip_tags($description);


        fwrite($write, implode(",", array(
        	$_product->getId(),
            $_product->getName() ,
            $_product->getProductUrl(),
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$_product->getImage(),
            $_product->getFinalPrice(),
            $description
            //strip_tags(str_replace(',','|',$description))
        )) . "\r\n");
    }

    // Proceed to the next page
    echo $currentPage."<br />";
    $currentPage++;

    /* Here we take advantage of the fact that we are only
     * loading 100 products at a time. Once we finished processing
     * the first page of 100, we can clear the collection data
     * which frees up memory in the system. 
     */
    $productCollection->clear();
} while ($currentPage <= $pages);

// Close file stream
fclose($write);

?>