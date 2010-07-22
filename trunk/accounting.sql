-- 
-- Table structure for table `nuke_hosting_accounting`
-- 

CREATE TABLE `nuke_hosting_accounting` (
  `id` int(255) unsigned NOT NULL auto_increment,
  `uid` int(255) NOT NULL default '0',
  `paydate` int(255) default NULL,
  `duedate` int(255) default NULL,
  `status` varchar(6) NOT NULL default 'unpaid',
  `invoice_sent` tinyint(1) NOT NULL default '0',
  `total` decimal(253,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`),
  KEY `status` (`status`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `nuke_hosting_accounting`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `nuke_hosting_accounting_config`
-- 

CREATE TABLE `nuke_hosting_accounting_config` (
  `adminemail` varchar(255) NOT NULL default '',
  `emailsubject` varchar(255) NOT NULL default '',
  `version` varchar(255) NOT NULL default '',
  `notifydays` int(255) NOT NULL default '0',
  `currency` varchar(255) NOT NULL default ''
) TYPE=MyISAM;

-- 
-- Dumping data for table `nuke_hosting_accounting_config`
-- 

INSERT INTO `nuke_hosting_accounting_config` VALUES ('accounting@moahosting.com', 'Moa Hosting Invoice', '3.0', 7, '$');

-- --------------------------------------------------------

-- 
-- Table structure for table `nuke_hosting_accounting_details`
-- 

CREATE TABLE `nuke_hosting_accounting_details` (
  `id` int(255) unsigned NOT NULL auto_increment,
  `parent_id` int(255) NOT NULL default '0',
  `description` text NOT NULL,
  `unitprice` decimal(253,2) NOT NULL default '0.00',
  `quantity` int(255) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `nuke_hosting_accounting_details`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `nuke_hosting_accounting_templates`
-- 

CREATE TABLE `nuke_hosting_accounting_templates` (
  `id` int(255) unsigned NOT NULL auto_increment,
  `name` text NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `nuke_hosting_accounting_templates`
-- 

INSERT INTO `nuke_hosting_accounting_templates` VALUES (1, 'Default Invoice', '<html>\r\n\r\n<head>\r\n</head>\r\n\r\n<body>\r\n\r\nDear _CLIENTNAME_,<br>\r\nYour invoice appears below. Please remit payment at your earliest convenience. \r\nThank you for your business - we appreciate it very much.<br>\r\n<br>\r\n<b>Payment Due On: _DUEDATE_</b><br><br>\r\n<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#0098E1" width="100%" id="AutoNumber1" height="89">\r\n  <tr> \r\n    <td width="60%" align="center" bgcolor="#0080C1" bordercolor="#0098E1"><font color="#FFFFFF" size="4">Description</font></td>\r\n    <td width="20%" height="19" align="center" bgcolor="#0080C1" bordercolor="#0098E1"><font color="#FFFFFF" size="4">Quantity</font></td>\r\n    <td width="20%" height="19" align="center" bgcolor="#0080C1" bordercolor="#0098E1"> \r\n      <font color="#FFFFFF" size="4">Price</font></td>\r\n  </tr>\r\n_DETAILS_\r\n</table>\r\n<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#0098E1" width="24%" id="AutoNumber2" align="right" height="29">\r\n  <tr>\r\n    <td width="36%" height="29" bgcolor="#0080C1" bordercolor="#0098E1">\r\n    <p align="left"><b><font size="4" color="#FFFFFF">  </font></b><font size="4" color="#FFFFFF">Total<b>:</b></font></td>\r\n    <td width="64%" height="29">\r\n    <p align="center">_TOTAL_</td>\r\n  </tr>\r\n</table>\r\n<p>If you are paying via paypal, you may click the following link:<BR>\r\n\r\n<a href="https://www.paypal.com/xclick/business=payments%40moahosting.com&item_name=Invoice%20Id:%20_INVOICEID_&amount=_TOTAL_&no_shipping=1&no_note=1&currency_code=USD">Click here to pay!</a>\r\n\r\n</p>\r\nSincerely,<br>\r\n=|MoA|= Hosting<br>\r\n<a href="http://moahosting.com">www.moahosting.com </a>\r\n\r\n</body>\r\n\r\n</html>');
