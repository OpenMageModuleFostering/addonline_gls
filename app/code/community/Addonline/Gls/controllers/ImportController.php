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
 * @category Addonline
 * @package Addonline_Gls
 * @copyright Copyright (c) 2014 GLS
 * @author Addonline (http://www.addonline.fr)
 */
class Addonline_Gls_ImportController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Constructor
     */
    protected function _construct ()
    {
        $this->setUsedModuleName('Addonline_Gls');
    }

    /**
     * Main action : show import form
     */
    public function indexAction ()
    {
        $this->loadLayout()
            ->_setActiveMenu('gls/import')
            ->_addContent(
                $this->getLayout()->createBlock('gls/import_form')
            )->renderLayout();
    }

    /**
     * Import Action
     */
    public function importAction ()
    {
        $import = Mage::getModel('gls/import');
        $nbrImported = $import->import();
        
        if ($nbrImported) {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $nbrImported . ' ' . $this->__('Orders have been imported')
            );
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('No orders to import in the folder ') . Mage::helper('gls')->getImportFolder()
            );
        }
        $this->_redirect('*/*/');
    }
}