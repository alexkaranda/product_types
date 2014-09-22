<?php

class Ecg_Catalog_Block_Adminhtml_Sales_Items_Column_Name
extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    public function getOrderOptions()
    {
        /* @var $helper Ecg_Catalog_Helper_Catalog_Product_Custom */
        $helper = Mage::helper('ecg_catalog/catalog_product_custom');
        $options = $helper->getOrderCustomOptions($this->getItem());

        //rewrite custopn options which do not use in custom product type
        $this->getItem()->setProductOptions( array('options' => $options) );

        return parent::getOrderOptions();
    }

    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption($value)
    {
        $_remainder = '';
        $value = Mage::helper('core/string')->truncate($value, 100, '', $_remainder);
        $result = array(
            'value' => nl2br($value),
            'remainder' => nl2br($_remainder)
        );

        return $result;
    }
}