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
 * Order item render block
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ecg_Catalog_Block_Sales_Order_Items_Renderer extends Mage_Sales_Block_Order_Item_Renderer_Default
{
    public function getItemOptions()
    {
        /* @var $helper Ecg_Catalog_Helper_Catalog_Product_Custom */
        $helper = Mage::helper('ecg_catalog/catalog_product_custom');
        $options = $helper->getOrderCustomOptions($this->getItem());

        //rewrite custopn options which do not use in custom product type
        $this->getOrderItem()->setProductOptions( array('options' => $options) );

        return parent::getItemOptions();
    }
}
