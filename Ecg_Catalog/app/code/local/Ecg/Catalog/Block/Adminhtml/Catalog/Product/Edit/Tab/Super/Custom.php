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
 * Tab at custom product type
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */
class Ecg_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Custom
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group
{
    /**
     * @return Ecg_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Custom
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('can_edit_qty', array(
            'header'    => Mage::helper('ecg_catalog')->__('Can Edit Qty'),
            'type'      => 'checkbox',
            'index'     => 'can_edit_qty',
            'field_name'=> 'can_edit_qty',
            'header_css_class' => 'a-center',
            'align'     => 'center',
            //specific options
            'renderer'  => 'ecg_catalog/adminhtml_widget_grid_column_renderer_boolean',
            'disabled_value' => false,
            'value'     => '0',
            'values'    => array(1),
            'filter'    => false,
            'use_index' => true,
        ));

        return $this;
    }

    public function getTabLabel()
    {
        return Mage::helper('ecg_catalog')->__('Associated Products');
    }

    public function getTabTitle()
    {
        return Mage::helper('ecg_catalog')->__('Child Products for Custom Product Type');
    }

    public function getTabUrl()
    {
        return $this->getUrl('*/*/superCustom', array('_current'=>true));
    }

    /**
     * Get Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url')
            ? $this->_getData('grid_url') : $this->getUrl('*/*/superCustomGridOnly', array('_current'=>true));
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group
     */
    protected function _prepareCollection()
    {
        $allowProductTypes = array();
        $allowProductTypeNodes = Mage::getConfig()
            ->getNode('global/catalog/product/type/custom/allow_product_types')->children();
        foreach ($allowProductTypeNodes as $type) {
            $allowProductTypes[] = $type->getName();
        }


        /** @var $collection Mage_Catalog_Model_Resource_Product_Link_Product_Collection */
        $collection = Mage::getModel('ecg_catalog/product_link')->useCustomLinks($this->_getProduct()->getTypeId())
            ->getProductCollection()
            ->setProduct($this->_getProduct())
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions()
            ->addAttributeToFilter('type_id', $allowProductTypes);

        if ($this->getIsReadonly() === true) {
            $collection->addFieldToFilter('entity_id', array('in' => $this->_getSelectedProducts()));
        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Retrieve array with selected associated products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getProductsCustom();//#
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedCustomProducts());
        }
        return $products;
    }

    /**
     * Retrieve array with associated products
     *
     * @return array
     */
    public function getSelectedCustomProducts()
    {
        $associatedProducts = Mage::registry('current_product')->getTypeInstance(true)
            ->getAssociatedProducts(Mage::registry('current_product'));
        $products = array();
        foreach ($associatedProducts as $product) {
            $products[$product->getId()] = array(
                'qty'          => $product->getQty(),
                'position'     => $product->getPosition(),
                'can_edit_qty' => $product->getCanEditQty(),
            );
        }
        return $products;
    }
}
