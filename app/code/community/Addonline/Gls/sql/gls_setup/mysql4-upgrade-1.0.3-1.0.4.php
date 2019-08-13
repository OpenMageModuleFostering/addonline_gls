<?php

$installer = $this;
$installer->startSetup();
$this->addAttribute('order', 'gls_exported', array(
    'type' => 'int',
    'label' => 'exported',
    'visible' => true,
    'required' => false
));
$this->addAttribute('order', 'gls_imported', array(
    'type' => 'int',
    'label' => 'Imported',
    'visible' => true,
    'required' => false
));
$installer->endSetup();
