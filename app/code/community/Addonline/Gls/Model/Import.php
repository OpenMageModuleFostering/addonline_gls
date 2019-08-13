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
class Addonline_Gls_Model_Import
{

    const LOG_FILE = 'gls_import.log';

    public $filename;

    public $content;

    public $fileMimeType;

    public $fileCharset;

    public function run()
    {
        Mage::log('run GLS import', null, self::LOG_FILE);
        
        if (! Mage::getStoreConfig('carrier/gls/export')) {
            return;
        }
    }

    public function import()
    {
        $importFolder = Mage::helper('gls')->getImportFolder();
        if (! is_dir($importFolder)) {
            mkdir($importFolder);
        }
        $dir = opendir($importFolder);
        $count = 0;
        
        // Parcour du dossier
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..' && ! is_dir($importFolder . $file) && strpos($file, 'GlsWinExpe6_') !== FALSE) {
                $aOrdersUpdated = array();
                // Parcour du fichier
                if (($handle = fopen($importFolder . DS . $file, "r")) !== FALSE) {
                    $row = 0;
                    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                        $num = count($data);
                        $row ++;
                        if ($row > 1 && isset($data[4]) && $data[4]) {
                            
                            // On récupère le champ 5 qui contient le numéro de la commande
                            $order = Mage::getModel('sales/order')->getCollection()
                                ->addAttributeToFilter('increment_id', $data[4])
                                ->getFirstItem();
                            
                            // On met à jour le trackid avec le champ 18
                            if ($order && ! isset($aOrdersUpdated[$data[4]])) {
                                $order->setGlsTrackid($data[17]);
                                $order->save();
                                $aOrdersUpdated[$data[4]] = 1;
                                $count ++;
                                continue;
                            }
                            
                            if ($order && $aOrdersUpdated[$data[4]]) {
                                $order->setGlsTrackid($order->getGlsTrackid() . ',' . $data[17]);
                                $order->save();
                            }
                        }
                    }
                    fclose($handle);
                    
                    // Creation des expedition
                    foreach ($aOrdersUpdated as $key => $orderToShip) {
                        try {
                            $orderShipped = Mage::getModel('sales/order')->loadByIncrementId($key);
                            if ($this->_createShipment($orderShipped, $orderShipped->getGlsTrackid()) == 0) {
                                $count --;
                            }
                        } catch (Exception $e) {
                            Mage::log(Mage::helper('gls')->__('Shipment creation error for Order %s : %s', $key, $e->getMessage()), null, self::LOG_FILE);
                        }
                    }
                    
                    try {
                        unlink($importFolder . $file);
                    } catch (Exception $e) {
                        Mage::log("Import : unable to delete file : " . $importFolder . $file, null, self::LOG_FILE);
                    }
                }
            }
        }
        
        closedir($dir);
        return $count;
    }

    private function _createShipment($order, $trackcode)
    {
        if ($order->canShip()) {
            /**
             * Initialize the Mage_Sales_Model_Order_Shipment object
             */
            $convertor = Mage::getModel('sales/convert_order');
            $shipment = $convertor->toShipment($order);
            
            /**
             * Add the items to send
             */
            foreach ($order->getAllItems() as $orderItem) {
                if (! $orderItem->getQtyToShip()) {
                    continue;
                }
                if ($orderItem->getIsVirtual()) {
                    continue;
                }
                
                $item = $convertor->itemToShipmentItem($orderItem);
                $qty = $orderItem->getQtyToShip();
                $item->setQty($qty);
                
                $shipment->addItem($item);
            } // foreach
            
            $shipment->register();
            
            $arrTracking = array(
                'carrier_code' => $order->getShippingCarrier()->getCarrierCode(),
                'title' => $order->getShippingCarrier()->getConfigData('title'),
                'number' => $trackcode
            );
            
            $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
            $shipment->addTrack($track);
            
            // Sauvegarde de l'expedition
            $this->_saveShipment($shipment, $order);
            
            // Finally, Save the Order
            $this->_saveOrder($order);
            return 1;
        } else {
            $this->addError(Mage::helper('gls')->__('Order %s can not be shipped or has already been shipped', $order->getRealOrderId()));
            return 0;
        }
    }

    protected function _saveShipment(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order, $customerEmailComments = '')
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')->addObject($shipment)
            ->addObject($order)
            ->save();
        
        // $emailSentStatus = $shipment->getData('email_sent');
        // if (!is_null($customerEmail) && !$emailSentStatus) {
        // $shipment->sendEmail(true, $customerEmailComments);
        // $shipment->setEmailSent(true);
        // }
        
        return $this;
    }

    protected function _saveOrder(Mage_Sales_Model_Order $order)
    {
        // $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        // $order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
        $order->save();
        
        return $this;
    }

    protected function addError($message)
    {
        Mage::getSingleton('adminhtml/session')->addError($message);
    }
}