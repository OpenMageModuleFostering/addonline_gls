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
if ((string) Mage::getConfig()->getModuleConfig('Idev_OneStepCheckout')->active != 'true') {

    /**
     * Addonline_Gls
     *
     * @category Addonline
     * @package Addonline_Gls
     * @copyright Copyright (c) 2014 GLS
     * @author Addonline (http://www.addonline.fr)
     */
    class Idev_OneStepCheckout_Helper_Checkout extends Mage_Core_Helper_Abstract
    {
    }
}

/**
 * Addonline_Gls
 *
 * @category Addonline
 * @package Addonline_Gls
 * @copyright Copyright (c) 2014 GLS
 * @author Addonline (http://www.addonline.fr)
 */
class Addonline_Gls_Helper_OneStepCheckout_Checkout extends Idev_OneStepCheckout_Helper_Checkout
{
    
    /*
     * (non-PHPdoc) @see Idev_OneStepCheckout_Helper_Checkout::saveShipping()
     */
    public function saveShipping ($data, $customerAddressId)
    {
        $shippingData = Mage::getSingleton('checkout/session')->getData('gls_shipping_relay_data');
        if ($shippingData)
            return array();
        else
            return parent::saveShipping($data, $customerAddressId);
    }
}
