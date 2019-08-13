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
class Addonline_Gls_Model_Observer extends Varien_Object
{

    public function __construct()
    {}

    public function checkoutEventGlsdata($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $request = Mage::app()->getRequest();
        
        // si on n'a pas le paramètre shipping_method c'est qu'on n'est pas sur la requête de mise à jour du mode de livraison
        // dans ce cas on ne change rien
        if (! $request->getParam('shipping_method')) {
            return $this;
        }
        
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $shippingAddress->getShippingMethod();
        
        return $this;
    }

    public function setShippingRelayAddress($observer)
    {
        $shipping_data = Mage::getSingleton('checkout/session')->getData('gls_shipping_relay_data');
        $quote = $observer->getEvent()->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $shippingMethod = $shippingAddress->getShippingMethod();
        if (strpos($shippingMethod, 'gls_relay') !== false) {
            $request = Mage::app()->getRequest();
            // si on a le paramètre shipping_method c'est qu'on n'est pas sur la requête de mise à jour du mode de livraison :
            // il faut mettre à jour l'addresse de livraison si on a le mode de livraison relais
            if ($shipping_data && $request->getParam('shipping_method')) {
                Mage::getSingleton('checkout/session')->setData('gls_shipping_warnbyphone', $shipping_data['warnbyphone']);
                Mage::getSingleton('checkout/session')->setData('gls_relay_id', $shipping_data['relayId']);
                $shippingAddress->setData('company', $shipping_data['name']);
                $shippingAddress->setData('street', $shipping_data['address']);
                $shippingAddress->setData('city', $shipping_data['city']);
                $shippingAddress->setData('postcode', $shipping_data['zipcode']);
                $shippingAddress->setData('save_in_address_book', 0);
                if ($shipping_data['phone']) {
                    $shippingAddress->setData('telephone', $shipping_data['phone']);
                } else {
                    $shippingAddress->setData('telephone', $shippingAddress->getData('telephone'));
                }
            }
        } else {
            if ($shipping_data) {
                // Si l'adresse était une adresse de relais colis (on a les données en session) :
                // on réinitialise l'adresse de livraison avec l'adresse de facturation
                $shippingAddress->setData('prefix', $billingAddress->getData('prefix'));
                $shippingAddress->setData('firstname', $billingAddress->getData('firstname'));
                $shippingAddress->setData('company', $billingAddress->getData('company'));
                $shippingAddress->setData('lastname', $billingAddress->getData('lastname'));
                $shippingAddress->setData('street', $billingAddress->getData('street'));
                $shippingAddress->setData('city', $billingAddress->getData('city'));
                $shippingAddress->setData('postcode', $billingAddress->getData('postcode'));
                $shippingAddress->setData('telephone', $billingAddress->getData('telephone'));
                $shippingAddress->setData('save_in_address_book', 0);
            }
        }
    }

    public function addGlsInformationsToOrder($observer)
    {
        try {
            // puis on vide les données en session
            Mage::getSingleton('checkout/session')->setData('gls_shipping_relay_data', null);
            $quote = $observer->getEvent()->getQuote();
            $shippingAddress = $quote->getShippingAddress();
            $shippingMethod = $shippingAddress->getShippingMethod();
            if (strpos($shippingMethod, 'gls_relay') !== false) {
                $observer->getEvent()
                    ->getOrder()
                    ->setGlsRelayPointId(Mage::getSingleton('checkout/session')->getData('gls_relay_id'));
                $observer->getEvent()
                    ->getOrder()
                    ->setGlsWarnByPhone(Mage::getSingleton('checkout/session')->getData('gls_shipping_warnbyphone'));
                $observer->getEvent()
                    ->getOrder()
                    ->save();
            }
        } catch (Exception $e) {
            Mage::Log('Failed to save GLS data : ' . print_r($shippingData, true));
        }
    }
}
