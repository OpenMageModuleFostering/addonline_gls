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

public function update_gls_agencies() {

$ftp = 'ftp.gls-france.com';
$user = 'addonline';
$password = '-mAfXmTqC';
$pattern = '/^tbzipdeltimes_(\d{8}).csv$/i';
$forceupdate = true;

/* Connects to FTP server */
$conn_id = ftp_connect($ftp);
$login_result = ftp_login($conn_id, $user, $password);

/* Use passive mode for downloads */
ftp_pasv($conn_id, true);

// Récupération du contenu d'un dossier
$contents = ftp_nlist($conn_id, ".");

// Récupération de la date du fichier distant
$remoteFileDate = 0;
foreach ($contents as $key => $filename) {
    if (preg_match($pattern, $filename, $matches)) {
        $local_file = $filename;
        $remoteFileDate = ($matches[1]);
    }
}

/* Get last import date */
$dbInstance = Mage::getSingleton('core/resource')->getConnection('core_read');
$lastImportQuery = $dbInstance->select()->from('gls_agencies_list', array('last_import_date'));
$row = $dbInstance->fetchRow($lastImportQuery);
$lastImportDate = $row['last_import_date'];

//Si on est en force update ou qu'on a un nouveau fichier disponible
if ($forceupdate || $remoteFileDate > $lastImportDate) {

    ob_start();
    $result = ftp_get($conn_id, "php://output", $local_file, FTP_BINARY);
    $data = ob_get_contents();
    $aAgencies = explode("\r\n", $data);
    ob_end_clean();

    if (count($aAgencies) > 1) {

        //On vide la table
        $dbInstance->query('TRUNCATE gls_agencies_list');

        //On remplit la table avec le nouveau fichier
        foreach ($aAgencies as $key => $agency) {

            $aData = explode(';', $agency);

            $dbInstance = Mage::getSingleton('core/resource')->getConnection('core_write');
            $dbInstance->beginTransaction();
            $fields = array();
            $fields['agencycode'] = $aData[0];
            $fields['zipcode_start'] = $aData[1];
            $fields['zipcode_end'] = $aData[2];
            $fields['validity_date_start'] = $aData[3];
            $fields['validity_date_end'] = $aData[4];
            $fields['last_import_date'] = $remoteFileDate;
            $fields['last_check_date'] = date('Ymd');
            $dbInstance->insert('gls_agencies_list', $fields);
            $dbInstance->commit();
        }
    }
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
