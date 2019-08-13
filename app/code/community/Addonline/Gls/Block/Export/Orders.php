<?php
/**
 * Copyright (c) 2014 GLS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Addonline
 * @package     Addonline_Gls
 * @copyright   Copyright (c) 2014 GLS
 * @author 	    Addonline (http://www.addonline.fr)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Addonline_Gls
 *
 * @category    Addonline
 * @package     Addonline_Gls
 * @copyright   Copyright (c) 2014 GLS
 * @author 	    Addonline (http://www.addonline.fr)
 */
class Addonline_Gls_Block_Export_Orders extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Enter Description here
     */
    public function __construct()
    {
        $this->_blockGroup = 'gls';
        $this->_controller = 'export_orders';
        $this->_headerText = Mage::helper('gls')->__('Export');
        parent::__construct();
        $this->_removeButton('add');
    }
}
