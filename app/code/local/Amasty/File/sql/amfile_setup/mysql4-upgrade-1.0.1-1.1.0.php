<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2013 Amasty (http://www.amasty.com)
*/
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amfile/file')}` ADD `file_link` varchar(255) NOT NULL AFTER `file_name`;
");

$this->endSetup();