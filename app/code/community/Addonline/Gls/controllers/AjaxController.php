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
class Addonline_Gls_AjaxController extends Mage_Core_Controller_Front_Action
{

    /**
     * Affiche le bloc selector
     */
    public function selectorAction ()
    {
        // Creation du block
        $this->loadLayout();
        $block = $this->getLayout()->createBlock(
            'Addonline_Gls_Block_Selector', 'root', 
            array('template' => 'gls/selector.phtml')
        );
        $this->getLayout()
            ->getBlock('content')
            ->append($block);
        $this->renderLayout();
    }

    /**
     * affiche la liste des Point relais
     */
    public function listPointsRelaisAction ()
    {
    	
    	
        $aPointsRelais = array();
        $response = new Varien_Object();
        
        $address = $this->getRequest()->getParam('address', false);
        $zipcode = $this->getRequest()->getParam('zipcode', false);
        $country = $this->getRequest()->getParam('country', false);
        $city = $this->getRequest()->getParam('city', false);
        
        $listrelais = Mage::getSingleton('gls/service')->getRelayPointsForZipCode($zipcode, $country,$address,$city);
        
        if (! isset($listrelais->exitCode->ErrorCode)) {
            $aPointsRelais['error'] = $this->__('Error call GLS webservice, it might be down, see var/log/gls.log');
        } else {
            if ($listrelais->exitCode->ErrorCode == 0) {
                $productMaxWeight = 0;
                $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
                foreach ($items as $item) {
                    $productMaxWeight = (($productMaxWeight > $item->getWeight()) ? 
                        $productMaxWeight : $item->getWeight());
                }
                
                $onlyxlrelay = Mage::getStoreConfig('carriers/gls/onlyxlrelay') ||
                     ($productMaxWeight > Mage::getStoreConfig('carriers/gls/maxrelayweight'));
                
                //Si on retourne aucun relais pour ce code postal
                if(is_array($listrelais->SearchResults)){
                    foreach ($listrelais->SearchResults as $key => $pointRelais) {
                        
                        $endName = substr(
                            $pointRelais->Parcelshop->Address->Name1, 
                            strlen($pointRelais->Parcelshop->Address->Name1) - 2, 
                            strlen($pointRelais->Parcelshop->Address->Name1)
                        );
                        
                        if ($onlyxlrelay && $endName != 'XL') {
                            continue;
                        }
                        $aRelay = array();
                        $aRelay['relayId'] = $pointRelais->Parcelshop->ParcelShopId;
                        $aRelay['relayName'] = $pointRelais->Parcelshop->Address->Name1 . ' ' .
                            $pointRelais->Parcelshop->Address->Name2 . ' ' . 
                            $pointRelais->Parcelshop->Address->Name3;
                        $aRelay['relayAddress'] = $pointRelais->Parcelshop->Address->Street1 . ' ' .
                            $pointRelais->Parcelshop->Address->BlockNo1 . ' ' . 
                            $pointRelais->Parcelshop->Address->Street2 . ' ' . 
                            $pointRelais->Parcelshop->Address->BlockNo2;
                        $aRelay['relayZipCode'] = $pointRelais->Parcelshop->Address->ZipCode;
                        $aRelay['relayCity'] = $pointRelais->Parcelshop->Address->City;
                        $aRelay['relayCountry'] = $pointRelais->Parcelshop->Address->Country;
                        $aRelay['relayLatitude'] = $pointRelais->Parcelshop->GLSCoordinates->Latitude;
                        $aRelay['relayLongitude'] = $pointRelais->Parcelshop->GLSCoordinates->Longitude;
                        
                        $relayWorkingDays = array();
                        for ($i = 0; $i < 7; $i ++) {                                                
                            if(is_array($pointRelais->Parcelshop->GLSWorkingDay)){
                                if (isset($pointRelais->Parcelshop->GLSWorkingDay[$i])) {
                                    $relayWorkingDays[$i]['hours']['from'] = 
                                        $pointRelais->Parcelshop->GLSWorkingDay[$i]->OpeningHours->Hours->From;
                                    $relayWorkingDays[$i]['hours']['to'] = 
                                        $pointRelais->Parcelshop->GLSWorkingDay[$i]->OpeningHours->Hours->To;
                                    $relayWorkingDays[$i]['breaks']['from'] = 
                                        $pointRelais->Parcelshop->GLSWorkingDay[$i]->Breaks->Hours->From;
                                    $relayWorkingDays[$i]['breaks']['to'] = 
                                        $pointRelais->Parcelshop->GLSWorkingDay[$i]->Breaks->Hours->To;
                                }
                            }
                        }
                        $aRelay['relayWorkingDays'] = $relayWorkingDays;
                        $aPointsRelais[$pointRelais->Parcelshop->ParcelShopId] = $aRelay;
                    }
                //Sinon de si on retourne aucun relais pour ce code postal
                }else{
                    // Aucune donnée ne correspond à la recherche. La requête est formulée correctement mais aucun
                    // résultat dans la base de données points relais GLS.
                    $aPointsRelais['error'] = $this->__('Aucun relais ne correspond à votre recherche');
                }
            } elseif ($listrelais->exitCode->ErrorCode == 502) {
                $aPointsRelais['error'] = $this->__('Authentification error GLS webservice, login or password might be wrong');
            } elseif ($listrelais->exitCode->ErrorCode == 999) {
                // Aucune donnée ne correspond à la recherche. La requête est formulée correctement mais aucun
                // résultat dans la base de données points relais GLS.
                $aPointsRelais['error'] = $this->__('Aucun relais ne correspond à votre recherche');
            } else {
                $aPointsRelais['error'] = $listrelais->exitCode->ErrorDscr;
            }
        }
        // Creation du block
        $this->loadLayout();
        $block = $this->getLayout()->createBlock(
            'Addonline_Gls_Block_Listrelay', 
            'root', 
            array('template' => 'gls/listrelais.phtml')
        );
        $block->setListRelay($aPointsRelais);
        $this->getLayout()
            ->getBlock('content')
            ->append($block);
        $this->renderLayout();
    }

    /**
     * enregsitre en session
     */
    public function saveInSessionRelayInformationsAction ()
    {
        if (count($_GET)) {
            Mage::getSingleton('checkout/session')->setData('gls_shipping_relay_data', $_GET);
        }
    }
    
    /**
     * Vide les informations GLS en session
     */
    public function clearSessionRelayInformationsAction ()
    {        
        Mage::getSingleton('checkout/session')->setData('gls_shipping_relay_data', null);       
        Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setShippingMethod(null)->save();
    }
}
