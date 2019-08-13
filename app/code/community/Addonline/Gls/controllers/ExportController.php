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
class Addonline_Gls_ExportController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Addonline_Gls');
    }

    /**
     * Main action : show orders list
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('gls/export')
            ->_addContent($this->getLayout()
            ->createBlock('gls/export_orders'))
            ->renderLayout();
    }

    /**
     * Export Action : Generates a CSV file to download
     */
    public function exportAction()
    {
        /* get the orders */
        $orderIds = $this->getRequest()->getPost('order_ids');
        
        if (isset($orderIds) && ($orderIds[0] != "")) {
            
            $collection = Mage::getResourceModel('sales/order_collection');
            $collection->addAttributeToFilter('entity_id', $orderIds);
            
            $export = Mage::getModel('gls/export');
            $export->export($collection);
            
            /* download the file */
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Orders have been exported'));
            $this->_redirect('*/*/');
        } else {
            $this->_getSession()->addError($this->__('No Order has been selected'));
            $this->_redirect('*/*/');
        }
    }
}
