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
$installer = $this;

$installer->startSetup();

$this->addAttribute('order', 'gls_relay_point_id', array(
    'type' => 'varchar',
    'label' => 'Id du point relay GLS',
    'visible' => true,
    'required' => false,
    'input' => 'text'
));

$this->addAttribute('order', 'gls_warn_by_phone', array(
    'type' => 'varchar',
    'label' => 'Prévenir par téléphone',
    'visible' => true,
    'required' => false,
    'input' => 'text'
));

$this->addAttribute('order', 'gls_trackid', array(
    'type' => 'varchar',
    'label' => 'Trackid',
    'visible' => true,
    'required' => false,
    'input' => 'text'
));

$installer->endSetup();