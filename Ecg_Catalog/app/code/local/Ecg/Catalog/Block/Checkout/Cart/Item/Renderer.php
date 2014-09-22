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
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart custom item render block
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */
class Ecg_Catalog_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * Get product customize options
     *
     * @return array || false
     */
    public function getProductOptions()
    {
        /* @var $helper Ecg_Catalog_Helper_Catalog_Product_Custom */
        $helper = Mage::helper('ecg_catalog/catalog_product_custom');
        return $helper->getCustomOptions($this->getItem());
    }
}
