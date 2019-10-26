<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2013 Amasty (http://www.amasty.com)
*/
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amfile/store')}`
        CHANGE `excluded` `visible` tinyint(1) NOT NULL DEFAULT 1,
        CHANGE `position` `position` int(10) NOT NULL,
        CHANGE `use_default_label` `use_default_label` char(10) NOT NULL,
        CHANGE `use_default_excluded` `use_default_excluded` char(10) NOT NULL;

    UPDATE `{$this->getTable('amfile/store')}`
        SET
            use_default_label = IF(use_default_label = 'on', 1, 0),
            use_default_excluded = IF(use_default_excluded = 'on', 1, 0);

    ALTER TABLE `{$this->getTable('amfile/store')}`
        CHANGE `use_default_label` `use_default_label` tinyint(1) NOT NULL DEFAULT 0,
        CHANGE `use_default_excluded` `use_default_visible` tinyint(1) NOT NULL DEFAULT 0;

    ALTER TABLE `{$this->getTable('amfile/file')}`
        CHANGE `file_url` `file_url` VARCHAR(255) NOT NULL,
        CHANGE `file_link` `file_link` VARCHAR(255) NOT NULL;

    UPDATE `{$this->getTable('amfile/file')}`
        SET
            file_link = IF(file_link='none', '', file_link),
            file_url = IF(file_url='none', '', file_url);

    ALTER TABLE `{$this->getTable('amfile/store')}`
        CHANGE `store_id` `store_id` SMALLINT(5) UNSIGNED NOT NULL,
        ADD UNIQUE (`file_id`, `store_id`);

    ALTER TABLE `{$this->getTable('amfile/store')}` ADD FOREIGN KEY (`file_id`)
        REFERENCES `{$this->getTable('amfile/file')}` (`file_id`)
        ON DELETE CASCADE ON UPDATE CASCADE;

    ALTER TABLE `{$this->getTable('amfile/store')}` ADD FOREIGN KEY (`store_id`)
        REFERENCES `{$this->getTable('core/store')}` (`store_id`)
        ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->endSetup();
