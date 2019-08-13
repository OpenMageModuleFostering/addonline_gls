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

if (! class_exists("PointsRelaisWSService", false)) {

/**
 * Addonline_Gls
 *
 * @category    Addonline
 * @package     Addonline_Gls
 * @copyright   Copyright (c) 2014 GLS
 * @author 	    Addonline (http://www.addonline.fr)
 */
    class PointsRelaisWSService extends SoapClient
    {

        /**
         *
         * @param array $config
         *            A array of config values
         * @param string $wsdl
         *            The wsdl file to use
         * @access public
         */
        public function __construct(array $options = array(), $wsdl = 'http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/ParcelShopSearch/wsdl/2010_01_ParcelShopSearch.wsdl')
        {
            parent::__construct($wsdl, $options);
        }

        /**
         *
         * @param findRelayPoints $parameters            
         * @access public
         */
        public function findRelayPoints($parameters)
        {
            return $this->__soapCall('GetParcelShops', array(
                $parameters
            ));
        }
    }
}