<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2008-2014 Amasty (http://www.amasty.com)
 */
$this->startSetup();

$this->run("
    ALTER TABLE  `{$this->getTable('amfile/stat')}`
        CHANGE  `date`  `date` DATETIME NOT NULL,
        CHANGE  `rating`  `rating` INT( 10 ) NOT NULL DEFAULT '1',
        ADD  `customer_id` INT( 10 ) UNSIGNED NOT NULL;

    ALTER TABLE `{$this->getTable('amfile/file')}`
        DROP `rating`;
");

$this->endSetup();
