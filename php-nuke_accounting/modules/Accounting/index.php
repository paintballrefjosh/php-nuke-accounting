<?
######################################################################
#
#	Accounting - A PHP-Nuke module for customers to use to view
#		their current account status. Admins can manage invoices,
#		services attached to each invoice, view paid/unpaid
#		invoices, manage users, and much more!
#
#	Copyright © 2004-2005 Joshua Scarbrough (JoshS@moahosting.com)
#
#	This program is free software; you can redistribute it and/or
#	modify it under the terms of the GNU General Public License
#	as published by the Free Software Foundation; either version 2
#	of the License, or (at your option) any later version.
#
#	This program is distributed in the hope that it will be useful,
#	but WITHOUT ANY WARRANTY; without even the implied warranty of
#	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#	GNU General Public License for more details.
#	You should have received a copy of the GNU General Public License
#	long with this program; if not, write to:
#			Free Software Foundation, Inc.
#			59 Temple Place - Suite 330
#			Boston, MA  02111-1307, USA.
#
######################################################################

if (!eregi("modules.php", $_SERVER['PHP_SELF']))
    die ("You can't access this file directly...");
	
require_once("mainfile.php");
$module_name = basename(dirname(__FILE__));
get_lang($module_name);
$index = 1;
$pagetitle = "- $module_name";

include("header.php");

function DoMenu()
{
?>
	<table align="center" width="100%" cellpadding="3" cellspacing="0" style="border: 1px solid;">
		<tr>
			<td width="25%" align="center" style="border-right: 1px solid;"><a href="modules.php?name=<? echo $_GET['name']?>&amp;op=ViewPaid">Paid Invoices</a></td>
			<td width="25%" align="center" style="border-right: 1px solid;"><a href="modules.php?name=<? echo $_GET['name']?>&amp;op=ViewUnpaid">Unpaid Invoices</a></td>
			<td width="25%" align="center" style="border-right: 1px solid;"><a href="modules.php?name=<? echo $_GET['name']?>&amp;op=Cancel">Cancellation Request</a></td>
			<td width="25%" align="center"><a href="modules.php?name=Your_Account&amp;op=logout">Logout</a></td>
		</tr>
	</table>
<?
}

function ViewPaid()
{
	global $prefix, $db, $cookie, $user;
	$config = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));
	OpenTable();
	DoMenu();
?>
	<br>
	<table width="100%" cellspacing="0" cellpadding="1" style="border: 1px solid; border-bottom: 0px;">
		<tr>
			<td width="33%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><b>Invoice ID</b></td>
			<td width="33%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><b>Date Paid</b></td>
			<td width="33%" align="center" style="border-bottom: 1px solid;"><b>Amount</b></td>
		</tr>
<?
	// Fetch the details of all of the user's paid invoices and display them
	$result = $db->sql_query("SELECT id, total, paydate FROM ".$prefix."_hosting_accounting WHERE uid='".$cookie[0]."' AND status='paid' ORDER BY paydate DESC");
	while($row = $db->sql_fetchrow($result))
	{
?>
		<tr>
			<td width="33%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"
				onClick="window.location.href='modules.php?name=<? echo $_GET['name'];?>&op=ViewInvoice&id=<? echo $row['id'];?>'">
				<a href="modules.php?name=<? echo $_GET['name'];?>&op=ViewInvoice&id=<? echo $row['id'];?>"><? echo $row['id'];?></a></td>
			<td width="33%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><? echo date("m/d/y", $row['paydate']);?></td>
			<td width="33%" align="center" style="border-bottom: 1px solid;"><? echo $config['currency'].number_format($row['total'], 2, ".", ",");?></td>
		</tr>
<?
	}
?>
	</table>
<?	
	CloseTable();
}

function ViewUnPaid()
{
	global $prefix, $db, $cookie, $user;
	$config = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));
	OpenTable();
	DoMenu();
?>
	<br>
	<table width="100%" cellspacing="0" cellpadding="1" style="border: 1px solid; border-bottom: 0px;">
		<tr>
			<td width="33%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><b>Invoice ID</b></td>
			<td width="33%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><b>Date Paid</b></td>
			<td width="33%" align="center" style="border-bottom: 1px solid;"><b>Amount</b></td>
		</tr>
<?
	// Fetch the details of all of the user's unpaid invoices and display them
	$sql = "SELECT id, total, duedate FROM ".$prefix."_hosting_accounting WHERE uid='".$cookie[0]."' AND status='unpaid' ORDER BY duedate ASC";
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
?>
		<tr>
			<td width="33%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"
				onClick="window.location.href='modules.php?name=<? echo $_GET['name'];?>&op=ViewInvoice&id=<? echo $row['id'];?>'">
				<a href="modules.php?name=<? echo $_GET['name'];?>&op=ViewInvoice&id=<? echo $row['id'];?>"><? echo $row['id'];?></a></td>
			<td width="33%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><? echo date("m/d/y", $row['paydate']);?></td>
			<td width="33%" align="center" style="border-bottom: 1px solid;"><? echo $config['currency'].number_format($row['total'], 2, ".", ",");?></td>
		</tr>
<?
	}
?>
	</table>
<?	
	CloseTable();
}

function ViewInvoice()
{
	global $prefix, $db, $cookie, $user;
	$config = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));
	OpenTable();
	DoMenu();

	// Check to make sure the user can only view their invoices, security
	$row = $db->sql_fetchrow($db->sql_query("SELECT uid FROM ".$prefix."_hosting_accounting WHERE id='".$_GET['id']."'"));
	if($row['uid'] == $cookie[0])
	{ 
?>
		<br>
		<table width="100%" cellspacing="0" cellpadding="1" style="border: 1px solid;">
			<tr>
				<td width="55%" style="border-right: 1px solid; border-bottom: 1px solid;"><b>Description</b></td>
				<td width="15%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><b>Unit Price</b></td>
				<td width="15%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><b>Quantity</b></td>
				<td width="15%" align="center" style="border-bottom: 1px solid;"><b>Ext. Price</b></td>
			</tr>
<?
		// Fetch the services for this invoice
		$sql = "SELECT id, description, unitprice, quantity FROM ".$prefix."_hosting_accounting_details WHERE parent_id='".$_GET['id']."'";
		$result = $db->sql_query($sql);
		$total = 0;
		while($row = $db->sql_fetchrow($result))
		{
?>
			<tr>
				<td width="55%" style="border-right: 1px solid; border-bottom: 1px solid;"><? echo $row['description'];?></td>
				<td width="15%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><? echo $config['currency'].number_format($row['unitprice'], 2, ".", ",");?></td>
				<td width="15%" align="center" style="border-right: 1px solid; border-bottom: 1px solid;"><? echo $row['quantity'];?></td>
				<td width="15%" align="center" style="border-bottom: 1px solid;"><? $total += $row['quantity'] * $row['unitprice']; echo $config['currency'].number_format($row['quantity'] * $row['unitprice'], 2, ".", ",");?></td>
			</tr>
<?
		}
?>
			<tr>
				<td colspan="4" align="right"><br><b>Total: <? echo $config['currency'].number_format($total, 2, ".", ","); ?>&nbsp;</b></td>
			</tr>
		</table>
		<br>
<?
	}
	else
		echo "Access Denied!";	
	CloseTable();
}

function CancelReq()
{
	global $prefix, $db, $cookie, $user;
	$config = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));
	OpenTable();
	DoMenu();

	if(isset($_POST['submit']))
	{
		// Build the email sent to the admin about the user's cancellation request
		$msg = "Dear Admin,\nPlease cancel the following services from " . $cookie[1] . "'s account:\n\n";
		$msg .= "Reason for Cancel: ".$_POST['reason']."\n";
		$msg .= "Comments: ".$_POST['comments']."\n\n";
		foreach($_POST['item'] as $item_id)
		{
			// Loop through all of the services that were cancelled
			$row = $db->sql_fetchrow($db->sql_query("SELECT parent_id, description FROM ".$prefix."_hosting_accounting_details WHERE id='".$item_id."'"));
			$msg .= "Invoice ID: " . $row['parent_id'] . "\n";
			$msg .= "Description: " . $row['description'] . "\n\n";
			$chk = 1;
		}

		// If a service was selected and the user entered a cancellation reason then email the admin, else display an error
		if($chk == 1 && $_POST['reason'] != "")
		{
			$row = $db->sql_fetchrow($db->sql_query("SELECT user_id, username, user_email FROM ".$prefix."_users WHERE user_id='".$cookie[0]."'"));
			$mailheaders = "From: ".$row['user_email']."\n";
			$mailheaders .= "Reply-To: ".$row['user_email']."\n\n";
			
			// Create the email subject
			$subject = "Cancellation Request Submitted by User: ".$cookie[1];
			
			// Send the email to the admin
			mail($config['adminemail'], $subject, $msg, $mailheaders);
?>
			<br><table width="100%" cellspacing="0" cellpadding="1" style="border: 1px solid;"><tr><td>
			&nbsp;Your cancellation request has been sent.</td></tr></table><br>
<?
		}
		else
		{
?>			<br><table width="100%" cellspacing="0" cellpadding="1" style="border: 1px solid;"><tr><td>
			&nbsp;Error: Please fill in all required fields!</td></tr></table><br>	
<?
		}
	}
	else
	{
?>
		<br>
		<table width="100%" cellspacing="2" cellpadding="1" style="border: 1px solid;">
			<form method="post">
			<tr>
				<td style="border-bottom: 1px solid;" colspan="2">&nbsp;Select service(s) to cancel:</td>
			</tr>
<?
		// Fetch all of the invoices for this user
		$result = $db->sql_query("SELECT id FROM ".$prefix."_hosting_accounting WHERE uid='".$cookie[0]."' AND status='unpaid'");
		while($row = $db->sql_fetchrow($result))
		{
			// Fetch all of the services for each invoice and display them
			$result2 = $db->sql_query("SELECT id, description FROM ".$prefix."_hosting_accounting_details WHERE parent_id='".$row['id']."'");
			while($row2 = $db->sql_fetchrow($result2))
			{
?>
				<tr>
					<td colspan="2" align="left">&nbsp;&nbsp;&nbsp;<input type="checkbox" name="item[]" value="<? echo $row2['id'];?>">&nbsp;<? echo $row2['description'];?></td>
				</tr>
<?
			}
		}
?>		<tr>
			<td width="30%" class="tblcolor1">&nbsp;Reason for Cancel: </td>
			<td align="left"><input type="text" name="reason" style="width:200;"> *</td>
		</tr>
		<tr>
			<td valign="top" width="30%" class="tblcolor1">&nbsp;Additional Comments: </td>
			<td align="left"><textarea name="comments" style="width:200; height:75;"></textarea></td>
		</tr>
		<tr><td colspan="2" align="center"><input type="submit" name="submit" value="Submit" style="width:100;"></td></tr></form>
		</table>
<?
	}	
	CloseTable();
}

// Check to see if the user is logged in or not, if not then redirect the user to the login page
if(!is_user($user))
{
	header("Location: modules.php?name=Your_Account");
}
else
{	
	switch($_GET['op'])
	{
		case 'ViewPaid':
			ViewPaid();
		break;
		
		case 'ViewUnpaid':
			ViewUnpaid();
		break;
		
		case 'Cancel':
			CancelReq();
		break;
		
		case 'ViewInvoice':
			ViewInvoice();
		break;

		default:
			ViewUnpaid();
		break;
	}
}

include("footer.php");

?>