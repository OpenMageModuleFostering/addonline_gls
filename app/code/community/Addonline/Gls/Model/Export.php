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
class Addonline_Gls_Model_Export
{

    const LOG_FILE = 'gls_export.log';

    public $filename;

    public $content;

    public $fileMimeType;

    public $fileCharset;
    
    private $_aProductnoCorrespondance = array(
            'ls_tohome' => 02,
            'ls_tohome_international' => 01,
            'ls_fds' => 18,
            'ls_fds_international' => 19,
            'ls_relay' => 17
    );

    public function run ()
    {
        Mage::log('run GLS export', null, self::LOG_FILE);
        if (! Mage::getStoreConfig('carrier/gls/export')) {
            return;
        }
    }

    public function export ($collection, $local = false)
    {
        if ($collection->getSize() > 0) {
            
            /*
             * Csv export configuration
             */
            $delimiter = ';';
            $encloser = '"';
            $this->filename = 'GlsCmd_' . $this->udate('YmdHisu') . '.csv';
            
            /*
             * Get the export Folder
             */
            $exportFolder = Mage::helper('gls')->getExportFolder();
            
            /*
             * Populate orders array
             */
            $aOrdersToExport = array();
            
            // HEADERS of the file
            $aheaders = array(
                    'ORDERID',
                    'ORDERNAME',
                    'PRODUCTNO',
                    'ORDERWEIGHTTOT',
                    'CONSID',
                    'CONTACT',
                    'CONTACTMAIL',
                    'CONTACTMOBILE',
                    'CONTACTPHONE',
                    'STREET1',
                    'STREET2',
                    'STREET3',
                    'COUNTRYCODE',
                    'CITY',
                    'ZIPCODE',
                    'REFPR'
            );
            $aOrdersToExport[] = $aheaders;
            
            // Parsing of the orders
            foreach ($collection as $order) {
                $aRow = array();
                
                // Getting the addresses of the order
                $billingAddress = $order->getBillingAddress();
                $shippingAddress = $order->getShippingAddress();
                
                // ORDERID
                $aRow[] = $order->getIncrementId();
                
                // ORDERNAME                
                if($shippingAddress->getCompany()){
                    $aRow[] = mb_strtoupper($shippingAddress->getCompany());
                }else{                
                    $aRow[] = mb_strtoupper(
                        $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(), 
                        'UTF-8'
                    );
                }
                
                // PRODUCTNO
                $shippingMethod = $order->getShippingMethod();                                

                //On regarde si la livraison est en France
                $country_code = mb_strtoupper($shippingAddress->getCountry(), 'UTF-8');
                if($country_code != 'FR'){ 
                	$international = true;
                }else{
                	$international = false;
                }
                
                if (strpos($shippingMethod, 'ls_tohome') > 0) {                    
                    if($international){
                        $aRow[] = $this->_aProductnoCorrespondance['ls_tohome_international'];
                    }else{
                        $aRow[] = $this->_aProductnoCorrespondance['ls_tohome'];
                    }
                }
                if (strpos($shippingMethod, 'ls_fds') > 0) {
                    if($international){
                        $aRow[] = $this->_aProductnoCorrespondance['ls_fds_international'];
                    }else{
                        $aRow[] = $this->_aProductnoCorrespondance['ls_fds'];
                    }
                }
                if (strpos($shippingMethod, 'ls_relay') > 0) {
                    $aRow[] = $this->_aProductnoCorrespondance['ls_relay'];
                }                
                
                // ORDERWEIGHTTOT
                $totalWeight = 0;
                $items = $order->getAllItems();
                foreach ($items as $item) {
                    $totalWeight += $item->getRowWeight();
                }
                $aRow[] = str_pad(number_format($totalWeight, 2, '.', ''), 5, "0", STR_PAD_LEFT);
                
                // CONSID
                $aRow[] = $order->getCustomerId();
                
                // CONTACT
                if($shippingAddress->getCompany()){
                    $aRow[] = mb_strtoupper(
                        $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(), 
                        'UTF-8'
                    );
                }else{
                    $aRow[] = '';                   
                }
                
                // CONTACTMAIL
                $aRow[] = $shippingAddress->getEmail();
                
                // CONTACTMOBILE
                $aRow[] = $order->getGlsWarnByPhone() ? $shippingAddress->getTelephone() : '';
                
                // CONTACTPHONE
                $aRow[] = $shippingAddress->getTelephone();
                
                // Repartition de l'adresse en fonction des tailles.
                if (strlen($shippingAddress->getStreet(1)) > 35 || strlen($shippingAddress->getStreet(2)) > 35 ||
                     strlen($shippingAddress->getStreet(3)) > 35) {
                    $street = $shippingAddress->getStreet(1) . ' ' . $shippingAddress->getStreet(2) . ' ' .
                        $shippingAddress->getStreet(3);
                    $street = wordwrap($street, 35, ';', true);
                    $aStreet = explode(';', $street);
                    
                    // STREET1
                    $aRow[] = mb_strtoupper($aStreet[0], 'UTF-8');
                    // STREET2
                    $aRow[] = mb_strtoupper($aStreet[1], 'UTF-8');
                    // STREET3
                    $aRow[] = mb_strtoupper($aStreet[2], 'UTF-8');
                } else {
                    // STREET1
                    $aRow[] = mb_strtoupper($shippingAddress->getStreet(1), 'UTF-8');
                    
                    // STREET2
                    $aRow[] = mb_strtoupper($shippingAddress->getStreet(2), 'UTF-8');
                    
                    // STREET3
                    $aRow[] = mb_strtoupper($shippingAddress->getStreet(3), 'UTF-8');
                }
                
                // COUNTRYCODE
                $aRow[] = mb_strtoupper($shippingAddress->getCountry(), 'UTF-8');
                
                // CITY
                $aRow[] = mb_strtoupper($shippingAddress->getCity(), 'UTF-8');
                
                // ZIPCODE
                $aRow[] = mb_strtoupper($shippingAddress->getPostcode(), 'UTF-8');
                
                // REFPR (identifiant du point relais)
                $aRow[] = $order->getGlsRelayPointId();
                
                // Adding the order to the export array
                $aOrdersToExport[] = $aRow;
                
                //On flag la commande comme exportée
                $order->setGlsExported(1);
                $order->save();
            }
        
            
            if(!$local){
                /*
                 * Save the file
                 */
                $this->array2csv($aOrdersToExport, $this->filename, $delimiter, $encloser, $exportFolder);
            }else{
                return $this->printCsv($aOrdersToExport, $this->filename, $delimiter, $encloser);
            }
        } else {
            Mage::log("Export : " . Mage::helper('gls')->__('No Order has been selected'), null, self::LOG_FILE);
        }
    }
    
    private function udate ($format = 'u', $utimestamp = null)
    {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);
        
        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);
        $milliseconds = substr($milliseconds, 0, 2);
        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
    
    private function array2csv (array &$array, 
        $filename, 
        $delimiter = ';', 
        $encloser = '"', 
        $folder = 'var/export/gls/')
    {
        if (count($array) == 0) {
            return null;
        }
        
        if (! file_exists($folder) and ! is_dir($folder)) {
            mkdir($folder);
        }
        
        ob_start();
        $df = fopen($folder . $filename, 'w+');
        foreach ($array as $row) {
            // WINEXPE attends de l'ISO-8859-1
            foreach (array_keys($row) as $key) {
                $row[$key] = iconv('UTF-8', 'ISO-8859-9', $row[$key]);
            }
            
            fputcsv($df, $row, $delimiter, $encloser);
        }
        fclose($df);
        return ob_get_clean();
    }
    
    public function printCsv(array &$array, 
        $filename, 
        $delimiter = ';', 
        $encloser = '"')
    {
        if (count($array) == 0) {
            return null;
        }
        
        $csvData = '';
        foreach ($array as $row) {
            // WINEXPE attends de l'ISO-8859-1
            $rowData = '';
            foreach (array_keys($row) as $key) {
                $cellData = $encloser.iconv('UTF-8', 'ISO-8859-9', $row[$key]).$encloser.$delimiter;
                $rowData .= $cellData;
            }   
            $csvData .= $rowData."\r\n";
                
        }
        return $csvData;
    }
}
