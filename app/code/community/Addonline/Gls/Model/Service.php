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
class Addonline_Gls_Model_Service
{

    protected $_urlWsdl;

    public function getUrlWsdl ()
    {
        if (! $this->_urlWsdl) {
            $this->_urlWsdl = "http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/ParcelShopSearch/wsdl/2010_01_ParcelShopSearch.wsdl";
        }
        return $this->_urlWsdl;
    }

    public function getRelayPointsForZipCode ($zipcode, $country,$address='',$city='')
    {
        $login = Mage::getStoreConfig('carriers/gls/usernamews');
        $mdp = Mage::getStoreConfig('carriers/gls/passws');
        
        if (file_exists(dirname(__FILE__) . DS . 'Webservice' . DS . 'PointsRelaisWSService.php'))
            require_once dirname(__FILE__) . DS . 'Webservice' . DS . 'PointsRelaisWSService.php';
        else
            require_once dirname(__FILE__) . DS . 'Addonline_Gls_Model_Webservice_PointsRelaisWSService.php';
        
        try {
            $pointsRelaisWSService = new PointsRelaisWSService(
                array('trace' => TRUE), 
                $this->getUrlWsdl()
            );
            // $aParameters = array('UserName' =>$login,'Password' =>$mdp,'ZipCode' => $zipcode,'Country' => $country);
            
            $aParameters = array(
                    'Credentials' => array('UserName' => $login,'Password' => $mdp
                    ),
                    'Address' => array(
                            'Name1' => '',
                            'Name2' => '',
                            'Name3' => '',
                            'Street1' => $address,
                            'BlockNo1' => '',
                            'Street2' => '',
                            'BlockNo2' => '',
                            'ZipCode' => $zipcode,
                            'City' => $city,
                            'Province' => '',
                            'Country' => $country
                    )
            );
            
            $result = $pointsRelaisWSService->findRelayPoints($aParameters);
            return $result;
        } catch (SoapFault $fault) {
            /* On va flusher le cache wsdl, car parfois une mise à jour du WS peut nécéssiter un flush de ce cache */
            foreach (glob(sys_get_temp_dir() . DS . 'wsdl*') as $filename) {
                if (strpos(file_get_contents($filename), $this->getUrlWsdl()) !== false) {
                    unlink($filename);
                }
            }
            if (isset($pointRetraitServiceWSService)) {
                Mage::log('Request ' . $pointRetraitServiceWSService->__getLastRequest(), null, 'gls.log');
                Mage::log('Response ' . $pointRetraitServiceWSService->__getLastResponse(), null, 'gls.log');
            }
            Mage::log($fault, null, 'gls.log');
            $result = new Varien_Object();
            $result->exitCode = new Varien_Object();
            $result->exitCode->ErrorCode = 500;
            $result->exitCode->ErrorDscr = 'Erreur WebService : ' . $fault->faultcode . ' ' . $fault->faultstring;
            return $result;
        }
    }
}
