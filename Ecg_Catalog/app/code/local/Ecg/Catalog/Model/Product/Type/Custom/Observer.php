<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer for adding custom post data to the product
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */


class Ecg_Catalog_Model_Product_Type_Custom_Observer
{
    /**
     * Add custom links data to the product
     *
     * @param Varien_Event_Observer $observer
     * @return Ecg_Catalog_Model_Product_Type_Custom_Observer
     */
    public function catalogProductPrepareSave(Varien_Event_Observer $observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        //check product type
        $typeInstance = $product->getTypeInstance(true);
        if (!($typeInstance instanceof Ecg_Catalog_Model_Product_Type_Custom)) {
            return $this;
        }

        /** @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getEvent()->getRequest();
        /**
         * Init product links data (customized)
         */
        $links = $request->getPost('links');
        if (isset($links['custom']) && !$product->getCustomReadonly()) {
            $product->setCustomLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['custom']));
        }
        return $this;
    }

    /**
     * Add custom links data to the new product from original product
     *
     * @param Varien_Event_Observer $observer
     * @return Ecg_Catalog_Model_Product_Type_Custom_Observer
     */
    public function catalogProductDuplicate(Varien_Event_Observer $observer)
    {
        /** @var $currentProduct Mage_Catalog_Model_Product */
        $currentProduct = $observer->getEvent()->getCurrentProduct();

        //check product type
        $typeInstance = $currentProduct->getTypeInstance(true);
        if (!($typeInstance instanceof Ecg_Catalog_Model_Product_Type_Custom)) {
            return $this;
        }

        /** @var $newProduct Mage_Catalog_Model_Product */
        $newProduct = $observer->getEvent()->getNewProduct();

        /* Prepare Grouped */
        $data = array();

        $linkInstance = Mage::getSingleton('ecg_catalog/product_link')->useCustomLinks($currentProduct->getTypeId());
        $attributes = array();
        foreach ($linkInstance->getAttributes() as $_attribute) {
            if (isset($_attribute['code'])) {
                $attributes[]=$_attribute['code'];
            }
        }

        foreach ($this->_getCustomLinkCollection($currentProduct) as $_link) {
            $data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
        }

        $newProduct->setCustomLinkData($data);
        return $this;
    }

    /**
     * Retrieve collection custom link
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getCustomLinkCollection(Mage_Catalog_Model_Product $product)
    {
        $collection = Mage::getSingleton('ecg_catalog/product_link')->useCustomLinks($product->getTypeId())
            ->getLinkCollection();

        $collection->setProduct($product);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();

        return $collection;
    }
}
