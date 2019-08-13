<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('gls_agencies_list')} ;

    CREATE TABLE IF NOT EXISTS {$this->getTable('gls_agencies_list')} (
  `id_agency_entry` int(10) NOT NULL AUTO_INCREMENT,
  `agencycode` varchar(255) DEFAULT NULL,
  `zipcode_start` int(5) DEFAULT NULL,
  `zipcode_end` int(5) DEFAULT NULL,
  `validity_date_start` varchar(20) DEFAULT NULL,
  `validity_date_end` varchar(20) DEFAULT NULL,
  `last_import_date` varchar(20) DEFAULT NULL,
  `last_check_date` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_agency_entry`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
  ");

$installer->endSetup();



