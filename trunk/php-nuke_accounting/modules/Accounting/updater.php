<?

$version = "3.0";

$row = $db->sql_fetchrow($db->sql_query("SELECT version FROM ".$prefix."_hosting_accounting_config"));

if($version != $row['version'])
{
	if($row['version'] == "3.0")
	{
		u3_0__3_0b();
	}
	else
	{
		u2_2b__3_0();
//		u3_0__3_0b();
	}
}

function u2_2b__3_0()
{
	global $db, $prefix;
	$sql = "
	ALTER TABLE `nuke_hosting_invoices` RENAME `nuke_hosting_accounting` ;
	ALTER TABLE `nuke_hosting_invoices_details` RENAME `nuke_hosting_accounting_details` ;
	ALTER TABLE `nuke_hosting_templates` RENAME `nuke_hosting_accounting_templates` ;
	ALTER TABLE `nuke_hosting_accounting` CHANGE `paydate` `paydate` INT( 255 ) NOT NULL DEFAULT '0' ; 
	ALTER TABLE `nuke_hosting_accounting` CHANGE `duedate` `duedate` INT( 255 ) NOT NULL DEFAULT '0' ;
	ALTER TABLE `nuke_hosting_accounting_details` CHANGE `iid` `parent_id` INT( 255 ) NOT NULL DEFAULT '0' ; 
	ALTER TABLE `nuke_hosting_accounting_details` CHANGE `id` `id` INT( 255 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		CHANGE `parent_id` `parent_id` INT( 255 ) NOT NULL DEFAULT '0',
		CHANGE `quantity` `quantity` INT( 255 ) NOT NULL DEFAULT '0', 
		CHANGE `unitprice` `unitprice` DECIMAL( 253, 2 ) NOT NULL DEFAULT '0.00' ;
	ALTER TABLE `nuke_hosting_accounting` CHANGE `id` `id` INT( 255 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		CHANGE `uid` `uid` INT( 255 ) NOT NULL DEFAULT '0',
		CHANGE `total` `total` DECIMAL( 253, 2 ) NOT NULL DEFAULT '0.00' ; 
	ALTER TABLE `nuke_hosting_accounting_templates` CHANGE `id` `id` INT( 255 ) UNSIGNED NOT NULL AUTO_INCREMENT ;
	";
	$db->sql_query($sql);
	
	$res = $db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting");
	while($row = $db->sql_fetchrow($res))
	{
		$db->sql_query("UPDATE ".$prefix."_hosting_accounting SET duedate = '".mktime(11, 59, 59, $row['duemonth'], $row['duedate'], $row['dueyear'])."' WHERE id = '".$row['id']."'");
		$db->sql_query("UPDATE ".$prefix."_hosting_accounting SET paydate = '".mktime(11, 59, 59, $row['paymonth'], $row['paydate'], $row['payyear'])."' WHERE id = '".$row['id']."'");
	}
	
	$sql = "
	ALTER TABLE `nuke_hosting_accounting` DROP `duemonth`, `dueyear`, `paymonth`, `payyear` ;
	
	CREATE TABLE `nuke_hosting_accounting_config` (
		`currency` VARCHAR( 255 ) NOT NULL ,
		`adminemail` VARCHAR( 255 ) NOT NULL ,
		`version` VARCHAR( 255 ) NOT NULL , 
		`emailsubject` VARCHAR( 255 ) NOT NULL
	) TYPE = MYISAM ;
	
	INSERT INTO `nuke_hosting_accounting_config` ( `currency`, `adminemail` , `version` , `emailsubject`) 
		VALUES ('$', 'accounting@moaohsting.com', '3.0' , 'Moa Hosting Invoice');
		
	ALTER TABLE `nuke_hosting_accounting` ADD `invoice_sent` VARCHAR( 3 ) DEFAULT 'no' NOT NULL AFTER `status` ";
	$db->sql_query($sql);
}

?>