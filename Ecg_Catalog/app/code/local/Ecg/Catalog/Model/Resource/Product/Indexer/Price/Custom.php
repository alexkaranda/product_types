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
 * Price Indexer Model for Custom Product Type
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */
class Ecg_Catalog_Model_Resource_Product_Indexer_Price_Custom
    extends Mage_Catalog_Model_Resource_Product_Indexer_Price_Default
{
    /**
     * @var int
     */
    protected $_callCounter = 0;

    /**
     * Calculate minimal and maximal prices for Customized products
     * Use calculated price for relation products
     *
     * @param int|array $entityIds  the parent entity ids limitation
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Custom
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $select = $this->_getWriteAdapter()->select();

        $this->_addInitJoinToSelect($select);
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $this->_addInventoryJoinToSelect($select);

        $this->_addSummFieldsJoinToSelect(
            $select,
            $this->_addAttributeFieldToSelect($select, 'qty')
            //, $this->_addAttributeFieldToSelect($select, 'can_edit_qty')
        );

        $select->where('e.type_id=?', $this->getTypeId());
        if (!is_null($entityIds)) {
            $select->where('l.product_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('catalog_product_prepare_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        //die($select);
        $query = $select->insertFromSelect($this->getIdxTable());
        $this->_getWriteAdapter()->query($query);

        return $this;
    }

    /**
     * Add main join to select
     *
     * @param Varien_Db_Select $select
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Custom
     */
    public function _addInitJoinToSelect(Varien_Db_Select $select)
    {
        $select->from(array('e' => $this->getTable('catalog/product')), 'entity_id')
            ->joinLeft(
                array('l' => $this->getTable('catalog/product_link')),
                'e.entity_id = l.product_id AND l.link_type_id=' . $this->_getLinkTypeId(),
                array()
            )
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id')
            );

        $select->group(array('e.entity_id', 'cg.customer_group_id'));

        return $this;
    }

    /**
     * Add join to select only in stock products
     *
     * @param Varien_Db_Select $select
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Custom
    */
    public function _addInventoryJoinToSelect(Varien_Db_Select $select)
    {
        $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $cond = array(
            "`cis`.use_config_manage_stock = 0 AND `cis`.manage_stock=1 AND `cis`.is_in_stock=1",
            "`cis`.use_config_manage_stock = 0 AND `cis`.manage_stock=0",
        );

        if ($manageStock) {
            $cond[] = "`cis`.use_config_manage_stock = 1 AND `cis`.is_in_stock=1";
        } else {
            $cond[] = "`cis`.use_config_manage_stock = 1";
        }
        $condition = '(' . join(') OR (', $cond) . ')';
        $condition = '(cis.product_id=l.linked_product_id) AND (' . $condition . ')';

        $select->join(
            array('cis' => $this->getTable('cataloginventory/stock_item')),
            $condition,
            array()
        );

        return $this;
    }

    /**
     * Add join to select summarized values (calc. by `SUM` function)
     *
     * @param Varien_Db_Select $select
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Custom
     */
    public function _addSummFieldsJoinToSelect(Varien_Db_Select $select, $qtyTable = false /*, $canEditQtyTable = false*/)
    {
        $write = $this->_getWriteAdapter();

        $qtySql = '';
        if ($qtyTable) {
            $qtySql = " * {$qtyTable}.`value`";
        }

        /*
        $localCanEditQtySql = $globalCanEditQtySqlNegative = $globalCanEditQtySqlPositive = '';
        if ($canEditQtyTable) {
            $_max = new Zend_Db_Expr("MAX({$canEditQtyTable}.`value`)");
            $globalCanEditQtySqlNegative = ' * ' . $write->getCheckSql("{$_max} > 0", 0, 1);
            $globalCanEditQtySqlPositive = ' * ' . $write->getCheckSql("{$_max} > 0", 1, 0);
            $localCanEditQtySql = ' * ' . $write->getCheckSql("`{$canEditQtyTable}`.`value` > 0", 0, 1);
        }
        */

        $sumPriceCheckSql = $write->getCheckSql('le.required_options = 0', 'i.price', 0) . $qtySql;
        $sumFinalPriceCheckSql = $write->getCheckSql('le.required_options = 0', 'i.final_price', 0) . $qtySql;

        /*
        $sumMinPriceCheckSql = $write->getCheckSql('le.required_options = 0', 'i.min_price', 0);
        $sumMaxPriceCheckSql = $write->getCheckSql('le.required_options = 0', 'i.max_price', 0);
        $fields = array(
            'tax_class_id'=> $this->_getReadAdapter()
                ->getCheckSql('MIN(i.tax_class_id) IS NULL', '0', 'MIN(i.tax_class_id)'),
            'price'       => new Zend_Db_Expr("SUM({$sumPriceCheckSql}{$localCanEditQtySql})"),
            'final_price' => new Zend_Db_Expr("SUM({$sumFinalPriceCheckSql}{$localCanEditQtySql})"),
            'min_price'   => new Zend_Db_Expr("MIN({$sumMinPriceCheckSql}{$localCanEditQtySql}){$globalCanEditQtySqlPositive}"),
            'max_price'   => new Zend_Db_Expr("MAX({$sumMaxPriceCheckSql}{$localCanEditQtySql}){$globalCanEditQtySqlPositive}"),
            'tier_price'  => new Zend_Db_Expr('NULL'),
        );
        */

        $fields = array(
            'tax_class_id'=> $this->_getReadAdapter()
                ->getCheckSql('MIN(i.tax_class_id) IS NULL', '0', 'MIN(i.tax_class_id)'),
            'price'       => new Zend_Db_Expr("SUM({$sumPriceCheckSql})"),
            'final_price' => new Zend_Db_Expr("SUM({$sumFinalPriceCheckSql})"),
            'min_price'   => new Zend_Db_Expr('NULL'),
            'max_price'   => new Zend_Db_Expr('NULL'),
            'tier_price'  => new Zend_Db_Expr('NULL'),
        );

        $select->columns('website_id', 'cw')
            ->joinLeft(
            array('le' => $this->getTable('catalog/product')),
            'le.entity_id = l.linked_product_id',
            array())
            ->joinLeft(
            array('i' => $this->getIdxTable()),
            'i.entity_id = l.linked_product_id AND i.website_id = cw.website_id'
                . ' AND i.customer_group_id = cg.customer_group_id',
            $fields
        );

        $select->group(array('cw.website_id'));

        return $this;
    }


    /**
     * Return tmpTable name that will be joined to select for retrieving custom link attribute value
     *
     * @param      $select
     * @param      $attributeCode
     * @param bool $addToSelect
     * @return bool|string
     */
    protected function _addAttributeFieldToSelect($select, $attributeCode, $addToSelect = false)
    {
        $_linkTypeId = $this->_getLinkTypeId();
        $row = $this->_getAttributeRowData($_linkTypeId, $attributeCode);
        if (!$row) {
            return false;
        }

        $_eavType = $row['data_type'];
        $linkAttributeId = (int) $row['product_link_attribute_id'];
        $tableEav = $this->getTable('catalog/product_link_attribute_' . $_eavType);

        $tmpName = 'lvt_' . ($this->_callCounter++);
        if ($addToSelect) {
            $fields = array($attributeCode => $tmpName . '.value');
        } else {
            $fields = array();
        }

        $select->joinLeft(
            array($tmpName => $tableEav),
            $tmpName . '.link_id = l.link_id AND ' . $tmpName . '.product_link_attribute_id =' . $linkAttributeId,
            $fields
        );
        return $tmpName;
    }

    /**
     * Retrieve information about attribute
     *
     * @param $linkTypeId
     * @param $attributeCode
     * @return array
     */
    protected function _getAttributeRowData($linkTypeId, $attributeCode)
    {
        $fields = array(
            'product_link_attribute_id',
            'data_type',
        );

        $_select = $this->_getReadAdapter()->select();
        $_select->from( $this->getTable('catalog/product_link_attribute'), $fields)
            ->where('link_type_id = ?', $linkTypeId)
            ->where('product_link_attribute_code = ?', $attributeCode);

        return $this->_getReadAdapter()->fetchRow($_select);
    }

    /**
     * @return mixed
     */
    protected function _getLinkTypeId()
    {
        return Mage::getSingleton('ecg_catalog/product_link')
            ->useCustomLinks($this->getTypeId())
            ->getLinkTypeId();
    }
}
