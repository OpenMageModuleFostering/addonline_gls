<?php

/**
 * Log Cron Backend Model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Addonline_Gls_Model_Cron extends Mage_Core_Model_Config_Data
{
    
    
    const CRON_STRING_PATH  = 'gls/import_export/cron_expression';    

    /**
     * Cron settings after save
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_Log_Cron
     */
    protected function _afterSave()        
    {        
        $helperGls = Mage::helper('gls');
        $enabled    = $helperGls->getImportExportActive();        
        $frequncy   = $helperGls->getFrequence();                        
        
        if ($enabled && $frequncy) {           
            $cronExprString = '*/'.$frequncy.' * * * *';
        }
        else {
            $cronExprString = '';
        }       

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('adminhtml')->__('Unable to save the cron expression.'));
        }
    }
    
    public function import_export(){                
        
        /*
         * Import des commandes
         */        
        
        //Lancement de l'import des commandes 
        $import = Mage::getModel('gls/import');
        $import->import();
        
        /*
         * Export des commandes
         */

        //Récupération du statut des commandes à exporter
        $export_status = Mage::helper('gls')->getExportStatus();
        
        Mage::log('import_export : '.$export_status,null,'gls.log');
        
        //Récupération des commandes non encore exportée
        $order_collection = Mage::getModel('sales/order')->getCollection()
        ->addFieldToFilter('status', array('eq' => $export_status))
        ->addAttributeToFilter('shipping_method',array('like' => 'gls_%'))
        //->addFieldToFilter('gls_exported', array('neq' => 1))
        ->addFieldToFilter('gls_exported', array('null' => true));
                           
        
        Mage::log('import_export : '.$order_collection->getSelect()->__toString(),null,'gls.log');
        
        //Lancement de l'export des commandes en passant la collection des commandes
        $export = Mage::getModel('gls/export');
        $export->export($order_collection);
    }
}
