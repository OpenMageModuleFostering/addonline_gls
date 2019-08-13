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
class Addonline_Gls_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * seulement les relay XL configuré en BO
     */
    public function isOnlyXLRelay()
    {
        return Mage::getStoreConfig('carriers/gls/relay_xl_only');
    }

    /**
     * répertoire d'export configuré en BO
     */
    public function getExportFolder()
    {
        return Mage::getStoreConfig('carriers/gls/export_folder');
    }

    /**
     * répertoire d'import configuré en BO
     */
    public function getImportFolder()
    {
        return Mage::getStoreConfig('carriers/gls/import_folder');
    }

    /**
     * @var $_translate_inline
     */
    protected $_translate_inline;

    /* (non-PHPdoc)
     * @see Mage_Core_Helper_Abstract::__()
     */
    public function __()
    {
        $args = func_get_args();
        if (isset($args[0]) && is_array($args[0]) && count($args) == 1) {
            $args = $args[0];
        }
        $message = array_shift($args);
        if ($message instanceof OS_Message) {
            $args = $message->args;
            $message = $message->message;
        }
        
        $output = parent::__($message);
        
        /*
         * if (true) { $translations = @file_get_contents('translations.os2'); $translations = eval('return '.$translations.';'); if (!is_array($translations)) $translations = array(); $file = 'NC'; $line = 'NC'; $backtrace = debug_backtrace(); foreach ($backtrace as $trace) { if (!isset($trace['function'])) continue; if (substr($trace['function'], strlen($trace['function'])-2, strlen($trace['function']))=='__') { $file = ltrim(str_replace(Mage::getBaseDir(), '', $trace['file']), '/'); $line = $trace['line']; continue; } //$file = ltrim(str_replace(Mage::getBaseDir(), '', $trace['file']), '/'); //echo $file.', '.$trace['function'].'(), '.$line.', '.$message.'<br/>'; break; } $translations[Mage::app()->getLocale()->getLocaleCode()][$file][$message] = $output; ksort($translations[Mage::app()->getLocale()->getLocaleCode()]); file_put_contents('translations.os2', var_export($translations, true)); }
         */
        
        if (count($args) == 0) {
            $result = $output;
        } else {
            if (! isset($this->_translate_inline))
                $this->_translate_inline = Mage::getSingleton('core/translate')->getTranslateInline();
            if ($this->_translate_inline) {
                $parts = explode('}}{{', $output);
                $parts[0] = vsprintf($parts[0], $args);
                $result = implode('}}{{', $parts);
            } else {
                $result = vsprintf($output, $args);
            }
        }
        return $result;
    }

    /**
     * Construit le nom de la méthode de livraison affiché
     * @param unknown $helper
     * @param unknown $process
     * @param unknown $row
     * @param unknown $property
     * @return string
     */
    public function getMethodText($helper, $process, $row, $property)
    {
        if (! isset($row[$property]))
            return '';
        
        $output = '';
        $cart = $process['data']['cart'];
        return $helper->evalInput($process, $row, $property, str_replace(array(
            '{cart.weight}',
            '{cart.price-tax+discount}',
            '{cart.price-tax-discount}',
            '{cart.price+tax+discount}',
            '{cart.price+tax-discount}'
        ), array(
            $cart->weight . $cart->weight_unit,
            $this->currency($cart->price_including_tax),
            $this->currency($cart->price_excluding_tax),
            $this->currency($cart->{'price-tax+discount'}),
            $this->currency($cart->{'price-tax-discount'}),
            $this->currency($cart->{'price+tax+discount'}),
            $this->currency($cart->{'price+tax-discount'})
        ), $helper->getRowProperty($row, $property)));
    }

    /**
     * Construit la mpa de données
     * @param unknown $helper
     * @param unknown $carrier_code
     * @param unknown $request
     * @return multitype:NULL 
     */
    public function getDataModelMap($helper, $carrier_code, $request)
    {
        $mage_config = Mage::getConfig();
        return array(
            'info' => Mage::getModel('gls/Os2_Data_Info', array_merge($helper->getInfos(), array(
                'magento_version' => Mage::getVersion(),
                'module_version' => (string) $mage_config->getNode('modules/Addonline_Gls/version'),
                'carrier_code' => $carrier_code
            ))),
            'cart' => Mage::getModel('gls/Os2_Data_Cart', array(
                'request' => $request,
                'options' => array(
                    'bundle' => array(
                        'process_children' => (boolean) Mage::getStoreConfig('gls/bundle_product/process_children'),
                        'load_item_options_on_parent' => (boolean) Mage::getStoreConfig('gls/bundle_product/load_item_options_on_parent'),
                        'load_item_data_on_parent' => (boolean) Mage::getStoreConfig('gls/bundle_product/load_item_data_on_parent'),
                        'load_product_data_on_parent' => (boolean) Mage::getStoreConfig('gls/bundle_product/load_product_data_on_parent')
                    ),
                    'configurable' => array(
                        'load_item_options_on_parent' => (boolean) Mage::getStoreConfig('gls/configurable_product/load_item_options_on_parent'),
                        'load_item_data_on_parent' => (boolean) Mage::getStoreConfig('gls/configurable_product/load_item_data_on_parent'),
                        'load_product_data_on_parent' => (boolean) Mage::getStoreConfig('gls/configurable_product/load_product_data_on_parent')
                    )
                )
            )),
            'quote' => Mage::getModel('gls/Os2_Data_Quote'),
            'selection' => Mage::getModel('gls/Os2_Data_Selection'),
            'customer' => Mage::getModel('gls/Os2_Data_Customer'),
            'customer_group' => Mage::getModel('gls/Os2_Data_CustomerGroup'),
            'customvar' => Mage::getModel('gls/Os2_Data_Customvar'),
            'date' => Mage::getModel('gls/Os2_Data_Date'),
            'address_filter' => Mage::getModel('gls/Os2_Data_AddressFilter'),
            'origin' => Mage::getModel('gls/Os2_Data_Address', $this->_extract($request->getData(), array(
                'country_id' => 'country_id',
                'region_id' => 'region_id',
                'postcode' => 'postcode',
                'city' => 'city'
            ))),
            'shipto' => Mage::getModel('gls/Os2_Data_Address', $this->_extract($request->getData(), array(
                'country_id' => 'dest_country_id',
                'region_id' => 'dest_region_id',
                'region_code' => 'dest_region_code',
                'street' => 'dest_street',
                'city' => 'dest_city',
                'postcode' => 'dest_postcode'
            ))),
            'billto' => Mage::getModel('gls/Os2_Data_Billto'),
            'store' => Mage::getModel('gls/Os2_Data_Store', array(
                'id' => $request->getData('store_id')
            )),
            'request' => Mage::getModel('gls/Os2_Data_Abstract', $request->getData())
        );
    }

    /**
     * extrait les données du tableau
     * @param unknown $data
     * @param unknown $attributes
     * @return multitype:Ambigous <NULL, unknown> 
     */
    protected function _extract($data, $attributes)
    {
        $extract = array();
        foreach ($attributes as $to => $from) {
            $extract[$to] = isset($data[$from]) ? $data[$from] : null;
        }
        return $extract;
    }
}
