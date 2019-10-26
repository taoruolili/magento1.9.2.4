<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amfile/icon')}` CHANGE `active` `active` TINYINT( 1 ) NOT NULL DEFAULT '1';
");

$this->endSetup();