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
class Addonline_Gls_Block_Listrelay extends Mage_Core_Block_Template
{

    /**
     *
     * @var array
     */
    private $_listRelay = array();

    /**
     * getter listRelay
     *
     * @return array
     */
    public function getListRelay ()
    {
        return $this->_listRelay;
    }

    /**
     * setter listRelay
     *
     * @param array $list            
     */
    public function setListRelay ($list)
    {
        $this->_listRelay = $list;
    }

    /**
     * Constructor
     */
    public function __construct ()
    {
        parent::__construct();
        $this->setTemplate('gls/listrelais.phtml');
    }
}