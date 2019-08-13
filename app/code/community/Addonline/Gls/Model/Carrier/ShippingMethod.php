<?php

/**
 * Addonline_Gls
 *
 * @category    Addonline
 * @package     Addonline_Gls
 * @copyright   Copyright (c) 2014 GLS
 * @author 	    Addonline (http://www.addonline.fr)
 */
/**
 * Copyright (c) 2008-13 Owebia
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @website    http://www.owebia.com/
 * @project    Magento Owebia Shipping 2 module
 * @author     Antoine Lemoine
 * @license    http://www.opensource.org/licenses/MIT  The MIT License (MIT)
 **/


class Addonline_Gls_Model_Carrier_ShippingMethod extends Addonline_Gls_Model_Carrier_Abstract
{

  protected $_code = 'gls';

// 	public function getTrackingInfo($tracking_number) {
// 		$tracking_url = $this->__getConfigData('tracking_view_url').$tracking_number;

// 		$tracking_status = Mage::getModel('shipping/tracking_result_status')
// 		->setCarrier($this->_code)
// 		->setCarrierTitle($this->__getConfigData('title'))
// 		->setTracking($tracking_number)
// 		->addData(
// 				array(
// 						'status'=>'<a target="_blank" href="'.str_replace('{tracking_number}',$tracking_number,$tracking_url).'">'.__('track the package').'</a>'
// 				)
// 		)
// 		;
// 		$tracking_result = Mage::getModel('shipping/tracking_result')
// 		->append($tracking_status)
// 		;

// 		if ($trackings = $tracking_result->getAllTrackings()) return $trackings[0];
// 		return false;
// 	}


}
