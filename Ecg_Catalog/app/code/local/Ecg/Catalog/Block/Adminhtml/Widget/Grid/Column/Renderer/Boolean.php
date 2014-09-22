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
 * Custom Input Checkbox
 *
 * @category   Ecg
 * @package    Ecg_Catalog
 * @author     Magento Ecg Team
 */
class Ecg_Catalog_Block_Adminhtml_Widget_Grid_Column_Renderer_Boolean
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{
    /**
     * Override parent method
     * Retrieve custom checkbox html
     *
     * @param $value
     * @param $checked
     * @return string
     */
    protected function _getCheckboxHtml($value, $checked)
    {
        //create jsevent on change
        $onchange = 'onchange="this.value = this.checked ? 1 : 0;"';
        return '<input type="checkbox" name="'.$this->getColumn()->getFieldName().'" value="' . $value . '"
            class="input-boolean '. ($this->getColumn()->getInlineCss() ? $this->getColumn()->getInlineCss() : 'checkbox' ).'"'.
            $checked.$this->getDisabled(). ' '. $onchange . '/>';
    }
}