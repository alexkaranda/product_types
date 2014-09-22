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
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Custom Product Type Model
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */
class Ecg_Catalog_Model_Product_Type_Custom extends Mage_Catalog_Model_Product_Type_Grouped
{
    const TYPE_CODE = 'custom';

    /**
     * Retrieve catalog product object
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($product = null)
    {
        $product = parent::getProduct($product);

        //set flag for saving all info about child products into quote items table
        $product->setPriceType(Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD);
        return $product;
    }

    /**
     * Return relation info about used products
     *
     * @return Varien_Object Object with information data
     */
    public function getRelationInfo()
    {
        $info = parent::getRelationInfo();
        //rewrite link type info
        $info->setWhere('link_type_id=' . $this->_getLinkTypeId());

        return $info;
    }

    /**
     * Save type related data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Grouped
     */
    public function save($product = null)
    {
        //Mage_Catalog_Model_Product_Type_Abstract::save($product);
        /** @var $model Ecg_Catalog_Model_Product_Link */
        $model = Mage::getSingleton('ecg_catalog/product_link');//@override
        $model->saveCustomLinks($this->getProduct($product));

        return $this;
    }

    /**
     * Retrieve collection of associated products
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
     */
    public function getAssociatedProductCollection($product = null)
    {
        /** @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection */
        $collection = Mage::getModel('ecg_catalog/product_link')
            ->useCustomLinks( $this->getProduct($product)->getTypeId() )
            ->getProductCollection()
            ->setFlag('require_stock_items', true)
            ->setFlag('product_children', true)
            ->setIsStrongMode();
        $collection->setProduct($this->getProduct($product));

        return $collection;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and add logic specific to Custom product type.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        if (!$buyRequest->getQty()) {
            Mage::helper('catalog')->__('Please specify the quantity of product(s).');
        }

        $product = $this->getProduct($product);
        $productsInfo = $buyRequest->getSuperGroup();
        $result = Mage_Catalog_Model_Product_Type_Abstract::_prepareProduct($buyRequest, $product, $processMode);

        $associatedProducts = $this->setStoreFilter($product->getStore(), $product)//@fix: for adminhtml
            ->getAssociatedProducts($product);

        if ($associatedProducts) {
            foreach ($associatedProducts as $subProduct) {
                if (!$subProduct->isSalable()) {
                    continue;
                }

                $subProductId = $subProduct->getId();
                $qty = $this->_getQty($subProduct, $productsInfo);
                if (empty($qty) || !is_numeric($qty)) {
                    continue;
                }

                $product->addCustomOption('product_qty_' . $subProductId, $qty, $subProduct);//@required
                $product->addCustomOption('associated_product_' . $subProductId, $qty, $subProduct);//@required

                $_result = $subProduct->getTypeInstance(true)
                    ->_prepareProduct($buyRequest, $subProduct, $processMode);

                if (is_string($_result) && !is_array($_result)) {
                    return $_result;
                }

                if (!isset($_result[0])) {
                    return Mage::helper('checkout')->__('Cannot process the item.');
                } else {
                    $childProduct = $_result[0];
                }

                // add custom option to simple product for protection of process
                //when we add simple product separately
                /** @var $childProduct Mage_Catalog_Model_Product */
                $childProduct->setCartQty($qty);
                $childProduct->addCustomOption('product_type', $product->getTypeId(), $product);
                $childProduct->setParentProductId($product->getId())
                    ->addCustomOption('parent_product_id', $product->getId());

                $result[] = $childProduct;
            }
            //foreach
        }

        if (count($result)) {
            return $result;
        }

        return Mage::helper('catalog')->__('Please specify the quantity of product(s).');
    }

    public function getChildrenIds($parentId, $required = true)
    {
        return Mage::getResourceSingleton('ecg_catalog/product_link')
            ->getChildrenIds($parentId, $this->_getLinkTypeId());
    }

    public function getParentIdsByChild($childId)
    {
        return Mage::getResourceSingleton('ecg_catalog/product_link')
            ->getParentIdsByChild($childId, $this->_getLinkTypeId());
    }

    protected function _getLinkTypeId()
    {
        return Mage::getSingleton('ecg_catalog/product_link')
            ->useCustomLinks($this->getProduct()->getTypeId())
            ->getLinkTypeId();
    }

    protected function _getQty($subProduct, $productsInfo)
    {
        if ($subProduct->getCanEditQty() && is_array($productsInfo) && isset($productsInfo[$subProduct->getId()])) {
            $qty = $productsInfo[$subProduct->getId()];
        } else {
            $qty = $subProduct->getQty();
        }
        return $qty;
    }
}
