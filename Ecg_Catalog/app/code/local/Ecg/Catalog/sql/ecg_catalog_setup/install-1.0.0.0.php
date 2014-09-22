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
 * Installation script
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */

/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

//add new product link type
$installer->getConnection()->insert(
    $this->getTable('catalog_product_link_type'), array(
        'code' => 'custom',
    )
);
$linkTypeId = $installer->getConnection()->lastInsertId($this->getTable('catalog_product_link_type'));
//add attributes wich will save to links tables
$data = array(
    //Can Edit QTY
    array(
        'product_link_attribute_code' => 'can_edit_qty',
        'link_type_id' => $linkTypeId,
        'data_type' => 'int',
    ),
    //Qty
    array(
        'product_link_attribute_code' => 'qty',
        'link_type_id' => $linkTypeId,
        'data_type' => 'int',
    ),
    //Position
    array(
        'product_link_attribute_code' => 'position',
        'link_type_id' => $linkTypeId,
        'data_type' => 'int',
    ),
);
$installer->getConnection()->insertMultiple($this->getTable('catalog_product_link_attribute'), $data);
$installer->endSetup();