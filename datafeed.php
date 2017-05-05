<?php
/**
 * datafeed.php
 *
 * Magento Product Export. 
 *
 * @author     Dhimant
 * @version    0.0.1
 * 
 */


error_reporting(E_ALL | E_STRICT);
define('MAGENTO_ROOT', getcwd());
ini_set('max_execution_time', 600000);
$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
Mage::app();

$productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(array(
    'id',
    'name',
    'product_url',
    'price',
    'description',
    'image'
));

$productCollection->setPageSize(100);
$pages       = $productCollection->getLastPageNumber();
$currentPage = 1;
$write       = fopen('datafeed.csv', 'w');
fwrite($write, implode(",", array(
    'id',
    'title',
    'link',
    'image_link',
    'price',
    'description'
)) . "\r\n");

do {
    $productCollection->setCurPage($currentPage);
    
    foreach ($productCollection as $_product) {
        // write our comma-delimited line of data to our file
        $description = $_product->getDescription();
        $description = preg_replace('/[\n\r]+/', ',', trim($description));
        $description = strip_tags($description);
        
        fwrite($write, implode(",", array(
            $_product->getId(),
            $_product->getName(),
            $_product->getProductUrl(),
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getImage(),
            $_product->getFinalPrice(),
            $description
        )) . "\r\n");
    }
    $currentPage++;
    $productCollection->clear();
} while ($currentPage <= $pages);
fclose($write);
?>