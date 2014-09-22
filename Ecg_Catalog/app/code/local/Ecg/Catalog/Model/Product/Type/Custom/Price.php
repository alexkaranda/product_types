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
 * Custom Product Price Model
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */
class Ecg_Catalog_Model_Product_Type_Custom_Price extends Mage_Catalog_Model_Product_Type_Price
{
    /**
     * Default action to get price of product
     *
     * @return decimal
     */
    public function getPrice($product)
    {
        if (!$product->getData('price')) { //on product view page
            $product->setData('price', $this->calculateChildrenPrice($product, false));
        }
        return $product->getData('price');
    }

    /**
     * Get child product final price
     *
     * @param   Mage_Catalog_Model_Product $product
     * @param   double $productQty
     * @param   Mage_Catalog_Model_Product $childProduct
     * @param   double $childProductQty
     * @return  double
     */
    public function getChildFinalPrice($product, $productQty, $childProduct, $childProductQty)
    {
        return $childProduct->getPriceModel()->getFinalPrice($childProductQty, $childProduct);
    }

    /**
     * Returns product final price depending on options chosen
     *
     * @param   double $qty
     * @param   Mage_Catalog_Model_Product $product
     * @return  double
     */
    public function getFinalPrice($qty = null, $product)
    {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        //$finalPrice = Mage_Catalog_Model_Product_Type_Price::getFinalPrice($qty, $product);
        $finalPrice = $this->calculateChildrenPrice($product, true);

        $product->setFinalPrice($finalPrice);
        Mage::dispatchEvent('catalog_product_type_custom_price', array('product' => $product));

        return max(0, $product->getData('final_price'));
    }

    /**
     * Retrieve sum of children prices
     *
     * @param      $product
     * @param bool $isFinalPrice
     * @return double
     */
    public function calculateChildrenPrice($product, $isFinalPrice = false)
    {
        /* @var $typeInstance Mage_Catalog_Model_Product_Type_Custom */
        $typeInstance = $product->getTypeInstance(true);

        $associatedProducts = $typeInstance->setStoreFilter($product->getStore(), $product)
            ->getAssociatedProducts($product);

        $price = 0.0;
        /* @var $childProduct Mage_Catalog_Model_Product */
        foreach ($associatedProducts as $childProduct) {
            if (!$childProduct->isSalable()) {
                continue;
            }

            $qty = $this->_getQty($childProduct, $product);
            if (empty($qty) || !is_numeric($qty)) {
                continue;
            }

            if ($isFinalPrice) {
                $price += $childProduct->getFinalPrice($qty) * $qty;
            } else {
                $price += $childProduct->getPrice($qty) * $qty;
            }
        }

        return $price;
    }

    /**
     * Retrieve product QTY
     *
     * @param Mage_Catalog_Model_Product $subProduct
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    protected function _getQty(Mage_Catalog_Model_Product $subProduct, Mage_Catalog_Model_Product $product)
    {
        $qty = 0;
        $option = $product->getCustomOption('associated_product_' . $subProduct->getId());
        if($option) {
            $qty = $option->getValue();
        } elseif($subProduct->getQty()) {
            $qty = $subProduct->getQty();
        }

        return $qty;
    }
}
