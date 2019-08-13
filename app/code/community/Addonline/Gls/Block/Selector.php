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
class Addonline_Gls_Block_Selector extends Mage_Core_Block_Template
{

    /**
     * adresse de livraison
     */
    private function _getShippingAddress ()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
    }

    /**
     * methode de livraison
     * 
     * @return string
     */
    public function getAddressShippingMethod ()
    {
        if ($adress = $this->_getShippingAddress()) {
            return $adress->getShippingMethod();
        } else {
            return '';
        }
    }

    /**
     * rue
     */
    public function getShippingStreet ()
    {
        return $this->_getShippingAddress()->getStreetFull();
    }

    /**
     * code postal
     */
    public function getShippingPostcode ()
    {
        return $this->_getShippingAddress()->getPostcode();
    }

    /**
     * ville
     */
    public function getShippingCity ()
    {
        return $this->_getShippingAddress()->getCity();
    }

    /**
     * pays
     */
    public function getShippingCountry ()
    {
        return $this->_getShippingAddress()->getCountry();
    }

    /**
     * telephone
     */
    public function getTelephone ()
    {
        return $this->_getShippingAddress()->getTelephone();
    }
}