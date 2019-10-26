<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2008-2014 Amasty (http://www.amasty.com)
 */
$this->startSetup();

$this->run("
  ALTER TABLE `{$this->getTable('amfile/file')}`
    CHANGE `product_id` `product_id` INT(10) UNSIGNED NOT NULL,
    ADD INDEX(`product_id`);
");

$this->endSetup();
