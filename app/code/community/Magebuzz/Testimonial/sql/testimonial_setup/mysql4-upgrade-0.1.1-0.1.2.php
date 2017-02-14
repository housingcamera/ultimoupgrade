<?php
/*
* Copyright (c) 2014 www.magebuzz.com
*/
$installer = $this;
$installer->startSetup();
	$installer->run("
		ALTER TABLE simple_testimonial ADD avatar_name varchar(255) NULL default '' after email;
		ALTER TABLE simple_testimonial ADD avatar_path varchar(255) NULL default '' after avatar_name;
	");
$installer->endSetup(); 