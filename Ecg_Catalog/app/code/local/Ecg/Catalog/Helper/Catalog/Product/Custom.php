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
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper for fetching properties by product custom item
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */
class Ecg_Catalog_Helper_Catalog_Product_Custom extends Mage_Catalog_Helper_Product_Configuration
{
    /**
     * Retrieves configuration options for custom product
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return array
     */
    public function getCustomOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId != Ecg_Catalog_Model_Product_Type_Custom::TYPE_CODE) {
            Mage::throwException($this->__('Wrong product type to extract configurable options.'));
        }

        $options = array();
        /**
         * @var Ecg_Catalog_Model_Product_Type_Custom
         */
        $typeInstance = $product->getTypeInstance(true);
        $associatedProducts = $typeInstance->getAssociatedProducts($product);
        if ($associatedProducts) {
            foreach ($associatedProducts as $associatedProduct) {
                $qty = $item->getOptionByCode('associated_product_' . $associatedProduct->getId());

                //add continue if empty QTY
                $qty = ($qty && $qty->getValue()) ? $qty->getValue() : 0;
                $price = $associatedProduct->getFinalPrice($qty, $product);
                $options[] = array(
                    'label' => $associatedProduct->getName(),
                    'value' => $this->getValueHtml($qty, $price,  $associatedProduct->getSku()),
                );
            }
        }
        return $options;
    }

    /**
     * Retrieves custom order options for custom product
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    public function getOrderCustomOptions(Mage_Sales_Model_Order_Item $item)
    {
        $options = array();
        if ($item->getChildrenItems()) {
            /** @var $quoteItem Mage_Sales_Model_Order_Item */
            foreach ($item->getChildrenItems() as $quoteItem) {
                $options[] = array(
                    'label' => $quoteItem->getName(),
                    'value' => $this->getValueHtml($quoteItem->getQtyOrdered(), $quoteItem->getPrice(), $quoteItem->getSku()),
                );
            }
        }
        return $options;
    }

    /**
     * @param $qty
     * @param $price
     * @return string
     */
    public function getValueHtml($qty, $price, $sku = '')
    {
        if (!empty($sku)) {
            $sku .= ' ';
        }
        return sprintf('%d', $qty) . ' x ' . $sku . Mage::helper('core')->currency($price, true, false);
    }
}
