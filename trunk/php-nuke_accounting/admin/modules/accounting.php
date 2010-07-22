<?
######################################################################
#
#	Accounting - A PHP-Nuke module for customers to use to view
#		their current account status. Admins can manage invoices,
#		services attached to each invoice, view paid/unpaid
#		invoices, manage users, and much more!
#
#	Copyright © 2004-2006 Joshua Scarbrough (JoshS@moahosting.com)
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

function DoHead()
{
	include("header.php");
	
	OpenTable();
?>
	<center><b><strong>
		&middot; <a href="<? echo $_SERVER['PHP_SELF'];?>">Administration Home</a> &middot;<br>
		&middot; <a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting">Accounting Admin</a> &middot;
<?
	include("modules/Accounting/updater.php");
	if(isUpdate())
	{
?>
		<br>&middot;&middot;&middot; <a href="http://moahosting.com/modules.php?name=Downloads&d_op=viewdownload&cid=2">An update is available!</a> &middot;&middot;&middot;<br>
<?
	}
?>
	</strong></b></center>
<?
	CloseTable();
	echo "<br>";
	OpenTable();
	
	// Create the accounting menu
	DoMenu();
}

function DoMenu()
{
	global $db, $prefix;
	$config = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));
?>
	<table align="center" width="100%" cellpadding="1" cellspacing="0" border="1" style="border-collapse: collapse">
		<tr>
			<td align="center"><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=Settings">Settings</a></td>
			<td align="center"><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=Search">Search</a></td>
			<td align="center"><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=AddInvoice">New Invoice</a></td>
			<td align="center"><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=ViewPaid">Paid Invoices</a></td>
			<td align="center"><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=ViewUnpaid">Unpaid Invoices</a></td>
			<td align="center"><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=Templates">Invoice Templates</a></td>
		</tr>
	</table>
<?
}

function Search()
{
	global $db, $prefix;
	if(isset($_POST['submit']) || $_GET['username'])
	{
		$sql = "SELECT * FROM ".$prefix."_hosting_accounting WHERE";
		$i = 0;
		if($_POST['Username'] != "" || $_GET['username'] != "")
		{
			if($_POST['Username'])
				$username = $_POST['Username'];
			else
				$username = $_GET['username'];
				
			$result2 = $db->sql_query("SELECT user_id FROM ".$prefix."_users WHERE username LIKE '%%".$username."%%'");
			$i = 0;
			while($row2= $db->sql_fetchrow($result2))
			{
				if($i)	
					$sql .= " OR";
				$sql .= " uid='".$row2['user_id']."'";
				$i = 1;
			}
		}
		
		if($_POST['UserId'] != "")
		{
			if($i)	
				$sql .= " AND";
			$sql .= " uid='".$_POST['UserId']."'";
			$i = 1;
		}

		if($_POST['InvoiceID'] != "")
		{
			if($i)	
				$sql .= " AND";
			$sql .= " id='".$_POST['InvoiceID']."'";
			$i = 1;
		}
		
		if($_POST['ItemDesc'] != "")
		{
			$result2 = $db->sql_query("SELECT parent_id FROM ".$prefix."_hosting_accounting_details WHERE description LIKE '%%".$_POST['ItemDesc']."%%'");
			if($i)	
				$sql .= " AND (";
			else
				$sql .= " (";
			$i = 0;
			while($row2= $db->sql_fetchrow($result2))
			{
				if($i)	
					$sql .= " OR ";
				$sql .= "id='".$row2['parent_id']."'";
				$i = 1;
			}
			if($i)	
				$sql .= ")";
		}
		
		if($_POST['Status'] == 'Unpaid')
			$sql .= " AND status='unpaid'";
		elseif($_POST['Status'] == 'Paid')
			$sql .= " AND status='paid'";

		$sql .= " ORDER BY status DESC";
		
		DoHead();

		// Loop through and see if the search returns results
		$row = $db->sql_fetchrow($db->sql_query($sql));
		
		if($row['id'])
		{
?>
			<br><table width="100%" cellspacing="0" cellpadding="1" border="1" style="border-collapse: collapse" align="center">
				<tr>
					<td align="center"><b>ID</b></td>
					<td align="center"><b>Status</b></td>
					<td align="center"><b>Username</b></td>
					<td align="center"><b>Date Due</b></td>
					<td align="center"><b>Price</b></td>
					<td align="center"><b>Action</b></td>
				</tr>
<?
			// Fetch the invoices that currently have not been paid and display them
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{				
				// Fetch the user's user id and username
				$row2 = $db->sql_fetchrow($db->sql_query("SELECT user_id, username FROM ".$prefix."_users WHERE user_id = '".$row['uid']."'"));
	
				if($row['duedate'] - time() < $config['notifydays'] && $row['status'] == 'unpaid')
				{
					// If the payment is due within $config['notifydays']; days then make the background GREEN
?>
					<tr bgcolor="#00FF00">
<?
				}
				elseif($row['duedate'] - time() < 0 && $row['status'] == 'unpaid')
				{
					// If the payment is past due make the background RED
?>
					<tr bgcolor="#FF0000">
<?	
				}
				else
				{
					// Else make the background color default
?>
					<tr>
<?
				}
?>
					<td align="center"><? echo $row['id'];?></td>
					<td align="center"><? echo $row['status'];?></td>
					<td><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&action=Search&username=<? echo $row2['username'];?>"><? echo $row2['username'];?></a></td>
					<td align="center"><? echo date("m/d/y", $row['duedate']);?></td>
					<td align="right"><? echo $config['currency'].number_format($row['total'], 2, ".", ",");?></td>
					<td align="center">
						[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=<? echo $_GET['op'];?>&amp;action=MarkPaid&amp;id=<? echo $row['id'];?>">Mark as Paid</a> | 
						<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=EditInvoice&id=<? echo $row['id'];?>">Edit</a> | 
						<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=DelInvoice&id=<? echo $row['id'];?>">Delete</a>]
					</td>
				</tr>
<?
			}
?>
			</table>
<?
		}
		else
		{
?>
			No searches met your criteria.
<?
		}
	}
	else
	{
		DoHead();
?>
		<br>
		<table width="100%" style="border: 1px solid;">
		<form method="post">
		<input type="hidden" name="parent_id" value="<? echo $_GET['parent_id'];?>">
			<tr>
				<td align="center" colspan="2"><b><strong>&middot; Search for Invoice &middot;</strong></b></td>
			</tr><tr>
				<td width="50%" align="right">By Username: </td>
				<td align="left"><input type="text" name="Username" style="width:200"></td>
			</tr><tr>
				<td align="right">By User ID: </td>
				<td align="left"><input type="text" name="UserId" style="width:200"></td>
			</tr><tr>
				<td align="right">By Invoice ID: </td>
				<td align="left"><input type="text" name="InvoiceID" style="width:200"></td>
			</tr><tr>
				<td align="right">By Item Description: </td>
				<td align="left"><input type="text" name="ItemDesc" style="width:200"></td>
			</tr><tr>
				<td align="right">By Status: </td>
				<td align="left"><select name="Status"><option value="Unpaid">Unpaid Invoices</option><option value="Paid">Paid Invoices</option><option value="Both">Both</option></select></td>
			</tr><tr>
				<td colspan="2" align="center"><input type="submit" name="submit" value="Search" style="width:70;"></td>
			</tr>
		</form>
		</table>
		
<?
		CloseTable();
		include("footer.php");
	}	
}

function AddItem()
{
	global $db, $prefix;
	if(isset($_POST['submit']))
	{
		// Update the total price of the invoice
		$db->sql_query("UPDATE ".$prefix."_hosting_accounting SET total=total+".($_POST['unit_price'] * $_POST['qty'])." WHERE id=".$_POST['parent_id']);
		
		// Insert the details of the new item
		$sql = "INSERT INTO ".$prefix."_hosting_accounting_details SET parent_id='".$_POST['parent_id']."', description='".$_POST['description']."',
			quantity='".$_POST['qty']."', unitprice='".$_POST['unit_price']."'";
		if($result = $db->sql_query($sql))
		
			header("Location: ".$_SERVER['PHP_SELF']."?op=".$_GET['op']."&action=EditInvoice&id=".$_POST['parent_id']);
		else
			echo "Error: This could not be done.";
	}
	else
	{
		DoHead();
		$date = getdate();
?>
		<br>
		<table width="100%" style="border: 1px solid;">
		<form action="<? echo $_SERVER['PHP_SELF'];?>?op=<? echo $_GET['op'];?>&amp;action=AddItem" method="post">
		<input type="hidden" name="parent_id" value="<? echo $_GET['parent_id'];?>">
			<tr>
				<td align="center" colspan="2"><b><strong>&middot; Add Item to Invoice &middot;</strong></b></td>
			</tr><tr>
				<td width="50%" align="right">Description: </td>
				<td align="left"><input type="text" name="description" style="width:200"></td>
			</tr><tr>
				<td align="right">Quantity: </td>
				<td align="left"><input type="text" name="qty" style="width:200"></td>
			</tr><tr>
				<td align="right">Unit Price: </td>
				<td align="left"><input type="text" name="unit_price" style="width:200"></td>
			</tr><tr>
				<td colspan="2" align="center"><input type="submit" name="submit" value="Submit" style="width:70;"></td>
			</tr>
		</form>
		</table>
<?
		CloseTable();
		include("footer.php");
	}	
}

function EditItem()
{
	global $db, $prefix;
	if(isset($_POST['submit']))
	{
		// Fetch the current total of the service
		$row = $db->sql_fetchrow($db->sql_query("SELECT parent_id, quantity, unitprice FROM ".$prefix."_hosting_accounting_details WHERE id='".$_POST['id']."'"));
		
		// Find the difference between the original price and the new price
		$diff = ($row['quantity'] * $row['unitprice']) - ($_POST['qty'] * $_POST['unit_price']);
		
		// Update the change in pricing of the invoice
		$db->sql_query("UPDATE ".$prefix."_hosting_accounting SET total=total-".$diff." WHERE id=".$row['parent_id']);
		
		// Update the details of the service
		$sql = "UPDATE ".$prefix."_hosting_accounting_details SET description='".$_POST['description']."',
			quantity='".$_POST['qty']."', unitprice='".$_POST['unit_price']."' WHERE id='".$_POST['id']."'";

		if($result = $db->sql_query($sql))
			header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting&action=EditInvoice&id=".$row['parent_id']);
		else
			echo "Error: This could not be done.";
	}
	else
	{
		DoHead();
		$row = $db->sql_fetchrow($db->sql_query("SELECT id, description, quantity, unitprice FROM ".$prefix."_hosting_accounting_details WHERE id='".$_GET['id']."'"));
?>
		<br>
		<table width="100%" style="border: 1px solid;">
		<form action="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=EditItem" method="post">
		<input type="hidden" name="id" value="<? echo $_GET['id'];?>">
			<tr>
				<td align="center" colspan="2"><b><strong>&middot; Edit Item &middot;</strong></b></td>
			</tr><tr>
				<td width="50%" align="right">Description: </td>
				<td align="left"><input type="text" name="description" value="<? echo $row['description'];?>" style="width:200"></td>
			</tr><tr>
				<td align="right">Quantity: </td>
				<td align="left"><input type="text" name="qty" value="<? echo $row['quantity'];?>" style="width:200"></td>
			</tr><tr>
				<td align="right">Unit Price: </td>
				<td align="left"><input type="text" name="unit_price" value="<? echo $row['unitprice'];?>" style="width:200"></td>
			</tr><tr>
				<td colspan="2" align="center"><input type="submit" name="submit" value="Submit" style="width:70;"></td>
			</tr>
		</form>
		</table>
<?
		CloseTable();
		include("footer.php");
	}	
}

function DelItem()
{
	global $db, $prefix;
	if(isset($_GET['value']))
	{
		// Fetch the price of the service so we can update the invoice amount when deleting
		$row = $db->sql_fetchrow($db->sql_query("SELECT parent_id, quantity, unitprice FROM ".$prefix."_hosting_accounting_details WHERE id='".$_GET['id']."'"));
		
		// Update the invoice total
		$db->sql_query("UPDATE ".$prefix."_hosting_accounting SET total=total-".($row['unitprice'] * $row['quantity'])." WHERE id='".$row['parent_id']."'");
		
		// Delete the service
		$sql = "DELETE FROM ".$prefix."_hosting_accounting_details WHERE id='".$_GET['id']."'";
		
		$db->sql_query($sql);
		header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting&action=EditInvoice&id=".$row['parent_id']);
	}
	else
	{
		DoHead();
?>
		<br>
		<table width="100%" style="border: 1px solid;" cellpadding="5">
			<tr>
				<td>
					Really delete the item from this invoice?
					[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=DelItem&id=<? echo $_GET['id'];?>&amp;value=1">Yes</a> | 
					<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting">No</a>]
				</td>
			</tr>
		</table>
<?
	}
	CloseTable();
	include("footer.php");
}

function AddInvoice()
{
	global $db, $prefix;
	if(isset($_POST['submit']))
	{
		// Insert the details of the invoice
		$sql = "INSERT INTO ".$prefix."_hosting_accounting SET duedate = '".mktime(11, 59, 59, $_POST['month'], $_POST['date'], $_POST['year'])."',
			uid = '".$_POST['user_id']."'";
			
		if($result = $db->sql_query($sql))
			header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting&action=ViewUnPaid");
		else
			echo "Error: This could not be done.";
	}
	else
	{
		DoHead();
		$date = getdate();
?>
		<br>
		<table width="100%" style="border: 1px solid;">
		<form method="post">
			<tr>
				<td colspan="2" align="center"><b>&middot; Add a New Invoice &middot;</b></td>
			</tr><tr>
				<td align="right">Assign to User: </td>
				<td align="left">
					<select name="user_id" style="width:200">
<?
				$result = $db->sql_query("SELECT user_id, username FROM ".$prefix."_users ORDER BY username");
				while($row = $db->sql_fetchrow($result))
				{
					if($row['username'] != "Anonymous")
					{
?>
						<option value="<? echo $row['user_id'];?>"><? echo $row['username'];?></option>		
<?
					}
				}
?>
					</select>
				</td>
			</tr><tr>
				<td align="right">Payment Due On: </td>
				<td align="left">
					<select name="month">
						<option value="1">January</option>
						<option value="2">February</option>
						<option value="3">March</option>
						<option value="4">April</option>
						<option value="5">May</option>
						<option value="6">June</option>
						<option value="7">July</option>
						<option value="8">August</option>
						<option value="9">September</option>
						<option value="10">October</option>
						<option value="11">November</option>
						<option value="12">December</option>
					</select> - 
					<select name="date">
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select> - 
					<select name="year">
						<option value="<? echo $date['year'];?>"><? echo $date['year'];?></option>
						<option value="<? echo $date['year'] + 1;?>"><? echo $date['year'] + 1;?></option>
						<option value="<? echo $date['year'] + 2;?>"><? echo $date['year'] + 2;?></option>
						<option value="<? echo $date['year'] + 3;?>"><? echo $date['year'] + 3;?></option>
						<option value="<? echo $date['year'] + 4;?>"><? echo $date['year'] + 4;?></option>
					</select>
				</td>
			</tr><tr>
				<td colspan="2" align="center"><br><input type="submit" name="submit" value="Submit" style="width:75;"></td>
			</tr>
		</form>
		</table>
		
<?
		CloseTable();
		include("footer.php");
	}	
}

function EditInvoice()
{
	global $db, $prefix;
	if(isset($_POST['submit']))
	{
		// Update the invoice details
		$sql = "UPDATE ".$prefix."_hosting_accounting SET duedate = '".mktime(11, 59, 59, $_POST['month'], $_POST['date'], $_POST['year'])."',
			uid = '".$_POST['user_id']."' WHERE id='".$_GET['id']."'";

		if($db->sql_query($sql))
			header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting&action=ViewUnPaid");
		else
			echo "Error: This could not be done.";
	}
	else
	{
		DoHead();
		$sql = "SELECT uid, total, duedate FROM ".$prefix."_hosting_accounting WHERE id='".$_GET['id']."'";
		$row = $db->sql_fetchrow($db->sql_query($sql));
		$due_month = date("m", $row['duedate']);
		$due_date = date("d", $row['duedate']);
		$due_year = date("y", $row['duedate']);
?>
		<center>
		<form method="post">
		<br><table width="100%" border="1" style="border-collapse: collapse" cellpadding="0" cellspacing="0"><tr><td>
		<table width="100%" cellpadding="0">
			<tr>
				<td align="right">Invoice ID: </td>
				<td align="left"><? echo $_GET['id']; ?></td>
			</tr><tr>
				<td align="right" >Assign to User: </td><td align="left"><select name="user_id" style="width:200">
<?
				$result2 = $db->sql_query("SELECT user_id, username FROM ".$prefix."_users ORDER BY username");
				while($row2 = $db->sql_fetchrow($result2))
				{
?>
					<option <? if($row2['user_id'] == $row['uid']){echo " selected ";}?> value="<? echo $row2['user_id'];?>"><? echo $row2['username'];?></option>
<?
				}
?>
				</select></td>
			</tr><tr>
				<td align="right" >Payment Due Date: </td>
				<td align="left">
					<select name="month">
						<option <? if($due_month == 1){echo " selected ";}?> value="1">January</option>
						<option <? if($due_month == 2){echo " selected ";}?> value="2">February</option>
						<option <? if($due_month == 3){echo " selected ";}?> value="3">March</option>
						<option <? if($due_month == 4){echo " selected ";}?> value="4">April</option>
						<option <? if($due_month == 5){echo " selected ";}?> value="5">May</option>
						<option <? if($due_month == 6){echo " selected ";}?> value="6">June</option>
						<option <? if($due_month == 7){echo " selected ";}?> value="7">July</option>
						<option <? if($due_month == 8){echo " selected ";}?> value="8">August</option>
						<option <? if($due_month == 9){echo " selected ";}?> value="9">September</option>
						<option <? if($due_month == 10){echo " selected ";}?> value="10">October</option>
						<option <? if($due_month == 11){echo " selected ";}?> value="11">November</option>
						<option <? if($due_month == 12){echo " selected ";}?> value="12">December</option>
					</select> - 
					<select name="date">
						<option <? if($due_date == 1){echo " selected ";}?> value="1">1</option>
						<option <? if($due_date == 2){echo " selected ";}?> value="2">2</option>
						<option <? if($due_date == 3){echo " selected ";}?> value="3">3</option>
						<option <? if($due_date == 4){echo " selected ";}?> value="4">4</option>
						<option <? if($due_date == 5){echo " selected ";}?> value="5">5</option>
						<option <? if($due_date == 6){echo " selected ";}?> value="6">6</option>
						<option <? if($due_date == 7){echo " selected ";}?> value="7">7</option>
						<option <? if($due_date == 8){echo " selected ";}?> value="8">8</option>
						<option <? if($due_date == 9){echo " selected ";}?> value="9">9</option>
						<option <? if($due_date == 10){echo " selected ";}?> value="10">10</option>
						<option <? if($due_date == 11){echo " selected ";}?> value="11">11</option>
						<option <? if($due_date == 12){echo " selected ";}?> value="12">12</option>
						<option <? if($due_date == 13){echo " selected ";}?> value="13">13</option>
						<option <? if($due_date == 14){echo " selected ";}?> value="14">14</option>
						<option <? if($due_date == 15){echo " selected ";}?> value="15">15</option>
						<option <? if($due_date == 16){echo " selected ";}?> value="16">16</option>
						<option <? if($due_date == 17){echo " selected ";}?> value="17">17</option>
						<option <? if($due_date == 18){echo " selected ";}?> value="18">18</option>
						<option <? if($due_date == 19){echo " selected ";}?> value="19">19</option>
						<option <? if($due_date == 20){echo " selected ";}?> value="20">20</option>
						<option <? if($due_date == 21){echo " selected ";}?> value="21">21</option>
						<option <? if($due_date == 22){echo " selected ";}?> value="22">22</option>
						<option <? if($due_date == 23){echo " selected ";}?> value="23">23</option>
						<option <? if($due_date == 24){echo " selected ";}?> value="24">24</option>
						<option <? if($due_date == 25){echo " selected ";}?> value="25">25</option>
						<option <? if($due_date == 26){echo " selected ";}?> value="26">26</option>
						<option <? if($due_date == 27){echo " selected ";}?> value="27">27</option>
						<option <? if($due_date == 28){echo " selected ";}?> value="28">28</option>
						<option <? if($due_date == 29){echo " selected ";}?> value="29">29</option>
						<option <? if($due_date == 30){echo " selected ";}?> value="30">30</option>
						<option <? if($due_date == 31){echo " selected ";}?> value="31">31</option>
					</select> - 
					<select name="year">
						<option value="<? echo $due_year - 1;?>"><? echo $due_year - 1;?></option>
						<option selected value="<? echo $due_year;?>"><? echo $due_year;?></option>
						<option value="<? echo $due_year + 1;?>"><? echo $due_year + 1;?></option>
						<option value="<? echo $due_year + 2;?>"><? echo $due_year + 2;?></option>
						<option value="<? echo $due_year + 3;?>"><? echo $due_year + 3;?></option>
					</select>
				</td>
			</tr><tr>
				<td align="center" colspan="2"><input type="submit" name="submit" value="Save" style="width:75;"></td>
			</tr>
		</table></td></tr></table>
		</form></center>
<?
		ViewInvoice();
	}
	CloseTable();
	include("footer.php");
}

function DelInvoice()
{
	global $db, $prefix;
	if(isset($_GET['value']))
	{
		// Delete the invoice
		$db->sql_query("DELETE FROM ".$prefix."_hosting_accounting WHERE id='".$_GET['id']."'");

		// Delete any services attached to this invoice
		$db->sql_query("DELETE FROM ".$prefix."_hosting_accounting_details WHERE parent_id='".$_GET['id']."'");
		
		header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting");
	}
	else
	{
		DoHead();
?>
		<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
		<tr><td><br>Really delete this invoice?  [<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=DelInvoice&id=<? echo $_GET['id'];?>&amp;value=1">Yes</a> | <a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting">No</a>]<br><br></td></tr></table>
<?
	}
	CloseTable();
	include("footer.php");
}

function ViewInvoice()
{
	// Here is where the contents of each invoice are displayed
	
	global $prefix, $db;
	$config = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));
	
	// Fetch the invoice details
	$row = $db->sql_fetchrow($db->sql_query("SELECT status, total FROM ".$prefix."_hosting_accounting WHERE id='".$_GET['id']."'"));
	
	// Set some variables for use later
	$total = $row['total'];
	$status = $row['status'];
?>
	<br>
	<table width="100%" cellspacing="0" cellpadding="1" border="1" style="border-collapse: collapse" align="center">
		<tr>
			<td align="center"><b>Description</b></td>
			<td align="center"><b>Unit Price</b></td>
			<td align="center"><b>Quantity</b></td>
			<td align="center"><b>Ext. Price</b></td>
<?
		if($status=='unpaid')
		{
?>
			<td width="100"><b>Action</b></td>
<?
		}
?>
		</tr>
<?
	// Fetch the details of each service on this invoice and display them
	$result = $db->sql_query("SELECT id, description, unitprice, quantity FROM ".$prefix."_hosting_accounting_details WHERE parent_id='".$_GET['id']."'");
	while($row = $db->sql_fetchrow($result))
	{
?>
		<tr>
			<td><? echo $row['description'];?></td>
			<td align="center"><? echo $config['currency'].number_format($row['unitprice'], 2, ".", ",");?></td>
			<td align="center"><? echo $row['quantity'];?></td>
			<td align="center"><? echo $config['currency'].number_format($row['unitprice'] * $row['quantity'], 2, ".", ",");?></td>
<?
		if($status=='unpaid')
		{
?>
			<td align="center">
				[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=EditItem&id=<? echo $row['id'];?>">Edit</a> | 
				<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=DelItem&id=<? echo $row['id'];?>">Delete</a>]
			</td>
<?
		}
?>
		</tr>
<?
	}
?>
	<tr><td colspan="5" align="right"><b>Total: <? echo $config['currency'].number_format($total, 2, ".", ",");?></b></td></tr></table><table width="100%"><tr><td align="right">
<?
	if($status=='unpaid')
	{
?>
		[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=AddItem&parent_id=<? echo $_GET['id'];?>">Add Item to Invoice</a>]
<?	
	}
?>
	</td></tr></table>
<?	
	CloseTable();
	include("footer.php");
}

function SendInvoice()
{
	// Here is where we send the invoices via email to the customer
	
	global $db, $prefix;
	$config = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));

	if(isset($_POST['invoice']))
	{
		// Loop through and send the invoices that were checked
		foreach($_POST['invoice'] as $invoiceid)
		{
			// Fetch the info from the invoice
			$row = $db->sql_fetchrow($db->sql_query("SELECT duedate, uid FROM ".$prefix."_hosting_accounting WHERE id = '".$invoiceid."'"));
			$user_id = $row['uid'];
			$due_date = date("F j, Y", $row['duedate']);
			
			// Fetch the user's email address and username
			$row = $db->sql_fetchrow($db->sql_query("SELECT username, user_email FROM ".$prefix."_users WHERE user_id = '".$user_id."'"));
			$user_email = $row['user_email'];
			$username = $row['username'];
			
			// Fetch the invoice template selected
			$row = $db->sql_fetchrow($db->sql_query("SELECT content FROM ".$prefix."_hosting_accounting_templates WHERE id = '".$_POST['template']."'"));
			$tcontent = $row['content'];
			
			// Fetch the services on this invoice
			$result = $db->sql_query("SELECT description, quantity, unitprice FROM ".$prefix."_hosting_accounting_details WHERE parent_id='".$invoiceid."'");
			$tdetail = "";
			$ttotal = 0;
			while($row = $db->sql_fetchrow($result))
			{
				// Generate the html to enter into the invoice template
				$tdetail .= "<tr><td align=\"center\"><font color=\"#000000\" size=\"3\">".$row['description']."</font></td>
							<td align=\"center\"><font color=\"#000000\" size=\"3\">".$row['quantity']."</font></td>
							<td align=\"center\"><font color=\"#000000\" size=\"3\">".$config['currency'].number_format($row['unitprice'], 2, '.', ',')."</font></td></tr>";
				
				// Update the total for this invoice
				$ttotal += ($row['unitprice'] * $row['quantity']);
			}
			
			// Replace some variables within the template
			$tcontent = str_replace("_CLIENTNAME_", $username, $tcontent);
			$tcontent = str_replace("_DETAILS_", $tdetail, $tcontent);
			$tcontent = str_replace("_TOTAL_", $config['currency'].number_format($ttotal, 2, '.', ','), $tcontent);
			$tcontent = str_replace("_INVOICEID_", $invoiceid, $tcontent);
			$tcontent = str_replace("_DUEDATE_", $due_date, $tcontent);
			
			$mailheaders  = "MIME-Version: 1.0\r\n";
			$mailheaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$mailheaders .= "From: ".$config['adminemail']."\n";
			$mailheaders .= "Reply-To: ".$config['adminemail']."\n\n";
			
			mail($user_email, $config['emailsubject'], $tcontent, $mailheaders);
			
			// Update the invoice as "invoice_sent = 'yes'"
			$db->sql_query("UPDATE ".$prefix."_hosting_accounting SET invoice_sent = '1' WHERE id = '".$invoiceid."'");
		}
		header("Location: ".$_SERVER['PHP_SELF']."?op=".$_GET['op']);
	}
	else
		echo "Please select an Invoice.";
}

function Templates()
{
	global $db, $prefix;
	if(isset($_POST['submit']))
	{
		// Update the invoice
		$sql = "UPDATE ".$prefix."_hosting_accounting_templates SET name = '".$_POST['PostName']."', content = '".$_POST['PostContent']."' WHERE id = '".$_POST['id']."'";
		
		if($result = $db->sql_query($sql))
			header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting&action=Templates");
		else
			echo "Error: This could not be done.";
	}
	elseif(isset($_GET['previewid']))
	{
		// Fetch the template
		$row = $db->sql_fetchrow($db->sql_query("SELECT content FROM ".$prefix."_hosting_accounting_templates WHERE id='".$_GET['previewid']."'"));
		
		// Display the template
		echo $row['content'];
	}
	elseif(isset($_GET['id']))
	{
		DoHead();
		
		// Fetch the invoice details and display them for editing
		$row = $db->sql_fetchrow($db->sql_query("SELECT id, name, content FROM ".$prefix."_hosting_accounting_templates WHERE id='".$_GET['id']."'"));
		
?>
		<center><br>
		<table width="100%" border="1" style="border-collapse: collapse">
			<tr>
				<td colspan="2" align="center" ><b><strong>&middot; Edit Template &middot;</strong></b></td>
			</tr><tr>
				<td>
					<form action="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=Templates" method="post">
						<input type="hidden" name="id" value="<? echo $row['id']; ?>">
						<table width="100%" border="0">
							<tr>
								<td align="right" >Template Name: </td>
								<td align="left"><input type="text" name="PostName" value="<? echo $row['name'];?>" style="width:200;"></td>
							</tr><tr>
								<td align="right" valign="top" >Template Content: </td>
								<td align="left"><textarea name="PostContent" style="width:100%; height:200"><? echo $row['content'];?></textarea></td>
							</tr><tr>
								<td align="center" colspan="2"><input type="submit" name="submit" value="Save" style="width=75;"></td>
							</tr>
						</table>
					</form>
				</td>
			</tr>
		</table>
		</center>
<?		
		CloseTable();
		include("footer.php");
	}
	else
	{
		DoHead();
?>
		<br><center><table width="100%" cellspacing="0" cellpadding="1" border="1" style="border-collapse: collapse" align="center">
		<tr >
			<td align="center"><b>Name</b></td>
			<td align="center"><b>Action</b></td>
		</tr>
<?
		$sql = "SELECT id, name, content FROM ".$prefix."_hosting_accounting_templates ORDER BY name";
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
?>
			<tr>
				<td width="50%" align="center"><? echo $row['name'];?></td>
				<td align="center">[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=<? echo $_GET['op'];?>&amp;action=Templates&amp;id=<? echo $row['id'];?>">Edit</a>
				 | <a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=DelTemplate&amp;id=<? echo $row['id'];?>">Delete</a>
				 | <a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=Templates&amp;previewid=<? echo $row['id'];?>" target="_blank">Preview</a>]</td>
			</tr>
<?
		}
?>
		</table><table width="100%"><tr><td align="right">[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=AddTemplate">Add Template</a>]</td></tr></table></center><br>
<?
		CloseTable();
		include("footer.php");
	}
}

function DelTemplate()
{
	global $db, $prefix;
	if(isset($_GET['value']))
	{
		// Delete the invoice template
		$db->sql_query("DELETE FROM ".$prefix."_hosting_accounting_templates WHERE id='".$_GET['id']."'");
		header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting&action=Templates");
	}
	else
	{
		// Create the deletion comfirmation
		DoHead();
?>
		<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
			<tr>
				<td>
					<br>Really delete this template?  
					[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=DelTemplate&id=<? echo $_GET['id'];?>&amp;value=1">Yes</a> | 
					<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=Templates">No</a>]<br><br>
				</td>
			</tr>
		</table>
<?
	}
	CloseTable();
	include("footer.php");
}

function AddTemplate()
{
	global $db, $prefix;
	if(isset($_POST['submit']))
	{
		$date = getdate();
		$sql = "INSERT INTO ".$prefix."_hosting_accounting_templates SET name = '".$_POST['PostName']."', content = '".$_POST['PostContent']."'";
		if($result = $db->sql_query($sql))
			header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting&action=Templates");
		else
			echo "Error: This could not be done.";
	}
	else
	{
		DoHead();
?>
		<br>
		<table width="100%" style="border: 1px solid;">
		<form action="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=AddTemplate" method="post">
			<tr>
				<td colspan="2" align="center"><b>&middot; Edit Template &middot;</b></td>
			</tr><tr>
				<td align="right">Template Name: </td>
				<td align="left"><input type="text" name="PostName" style="width:200;"></td>
			</tr><tr>
				<td align="right" valign="top">Template Content: </td>
				<td align="left"><textarea name="PostContent" style="width:100%; height:200"></textarea></td>
			</tr><tr>
				<td align="center" colspan="2"><input type="submit" name="submit" value="Submit" style="width=75;"></td>
			</tr>
		</form>
		</table>
<?
		CloseTable();
		include("footer.php");
	}
}

function MarkPaid()
{
	// Here we will update the invoice as paid and give the option to create a new one for next month
	global $db, $prefix;
	if(isset($_POST['submit']))
	{
		$iserror = 0;
		
		// Update the invoice and mark it paid
		$sql = "UPDATE ".$prefix."_hosting_accounting SET status = 'paid', total = '".$_POST['total']."', paydate = '".time()."' WHERE id = '".$_POST['id']."'";
		if(!$db->sql_query($sql))
			$iserror = 1;
		
		// If the admin checked for a new invoice then let's create it
		if(isset($_POST['new_invoice']))
		{
			// Create the new invoice
			$db->sql_query("INSERT INTO ".$prefix."_hosting_accounting SET total='".$_POST['new_total']."', uid='".$_POST['uid']."',
			duedate='".mktime(11, 59, 59, $_POST['new_month'], $_POST['new_date'], $_POST['new_year'])."'");

			// Get the ID for the newly inserted row
			$result = $db->sql_query("SELECT id FROM ".$prefix."_hosting_accounting WHERE status='unpaid' ORDER BY id ASC");
			while($row = $db->sql_fetchrow($result))
				$insert_id = $row['id'];

			// Fetch the old services attached to the invoice
			$result = $db->sql_query("SELECT unitprice, description, quantity FROM ".$prefix."_hosting_accounting_details WHERE parent_id = '".$_POST['id']."'");
			while($row = $db->sql_fetchrow($result))
			{
				// Insert the services into the new invoice
				$db->sql_query("INSERT INTO ".$prefix."_hosting_accounting_details SET parent_id='".$insert_id."',
					unitprice=".$row['unitprice'].", description='".$row['description']."', quantity='".$row['quantity']."'");
			}
		}
		
		if(!$iserror)
			header("Location: ".$_SERVER['PHP_SELF']."?op=Accounting&action=ViewUnpaid");
		else
			echo "Error: This could not be done.";
	}
	else
	{
		DoHead();
		
		// Fetch the details of the invoice
		$row = $db->sql_fetchrow($db->sql_query("SELECT uid, total, duedate FROM ".$prefix."_hosting_accounting WHERE id='".$_GET['id']."'"));

		// By default we will make the date for the new invoice option +1 month from the old invoice
		$next_month = date("m", $row['duedate']) + 1;
		$next_date = date("d", $row['duedate']);
		$next_year = date("y", $row['duedate']);
		
		if($this_month > 12)
		{
			$next_month = 1;
			$next_year++;
		}
?>
		<br><center><form action="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=MarkPaid" method="post">
		<input type="hidden" name="id" value="<? echo $_GET['id']; ?>">
		<input type="hidden" name="uid" value="<? echo $row['uid']; ?>">
		<table width="100%" style="border: 1px solid;">
			<tr>
				<td align="right">Invoice ID: </td>
				<td align="left"><? echo $_GET['id']; ?></td>
			</tr><tr>
				<td align="right" valign="top">Total Paid: </td>
				<td align="left"><input type="text" name="total" value="<? echo $row['total'];?>" style="width:200;"></td>
			</tr><tr>
				<td align="right" valign="top"><br>Create New Invoice?: </td>
				<td align="left"><br><input type="checkbox" checked name="new_invoice"></td>
			</tr><tr>
				<td align="right">New Due Date: </td>
				<td align="left">
					<select name="new_month">
						<option <? if($next_month == 1){echo " selected ";}?> value="1">January</option>
						<option <? if($next_month == 2){echo " selected ";}?> value="2">February</option>
						<option <? if($next_month == 3){echo " selected ";}?> value="3">March</option>
						<option <? if($next_month == 4){echo " selected ";}?> value="4">April</option>
						<option <? if($next_month == 5){echo " selected ";}?> value="5">May</option>
						<option <? if($next_month == 6){echo " selected ";}?> value="6">June</option>
						<option <? if($next_month == 7){echo " selected ";}?> value="7">July</option>
						<option <? if($next_month == 8){echo " selected ";}?> value="8">August</option>
						<option <? if($next_month == 9){echo " selected ";}?> value="9">September</option>
						<option <? if($next_month == 10){echo " selected ";}?> value="10">October</option>
						<option <? if($next_month == 11){echo " selected ";}?> value="11">November</option>
						<option <? if($next_month == 12){echo " selected ";}?> value="12">December</option>
					</select> - 
					<select name="new_date">
						<option <? if($next_date == 1){echo " selected ";}?> value="1">1</option>
						<option <? if($next_date == 2){echo " selected ";}?> value="2">2</option>
						<option <? if($next_date == 3){echo " selected ";}?> value="3">3</option>
						<option <? if($next_date == 4){echo " selected ";}?> value="4">4</option>
						<option <? if($next_date == 5){echo " selected ";}?> value="5">5</option>
						<option <? if($next_date == 6){echo " selected ";}?> value="6">6</option>
						<option <? if($next_date == 7){echo " selected ";}?> value="7">7</option>
						<option <? if($next_date == 8){echo " selected ";}?> value="8">8</option>
						<option <? if($next_date == 9){echo " selected ";}?> value="9">9</option>
						<option <? if($next_date == 10){echo " selected ";}?> value="10">10</option>
						<option <? if($next_date == 11){echo " selected ";}?> value="11">11</option>
						<option <? if($next_date == 12){echo " selected ";}?> value="12">12</option>
						<option <? if($next_date == 13){echo " selected ";}?> value="13">13</option>
						<option <? if($next_date == 14){echo " selected ";}?> value="14">14</option>
						<option <? if($next_date == 15){echo " selected ";}?> value="15">15</option>
						<option <? if($next_date == 16){echo " selected ";}?> value="16">16</option>
						<option <? if($next_date == 17){echo " selected ";}?> value="17">17</option>
						<option <? if($next_date == 18){echo " selected ";}?> value="18">18</option>
						<option <? if($next_date == 19){echo " selected ";}?> value="19">19</option>
						<option <? if($next_date == 20){echo " selected ";}?> value="20">20</option>
						<option <? if($next_date == 21){echo " selected ";}?> value="21">21</option>
						<option <? if($next_date == 22){echo " selected ";}?> value="22">22</option>
						<option <? if($next_date == 23){echo " selected ";}?> value="23">23</option>
						<option <? if($next_date == 24){echo " selected ";}?> value="24">24</option>
						<option <? if($next_date == 25){echo " selected ";}?> value="25">25</option>
						<option <? if($next_date == 26){echo " selected ";}?> value="26">26</option>
						<option <? if($next_date == 27){echo " selected ";}?> value="27">27</option>
						<option <? if($next_date == 28){echo " selected ";}?> value="28">28</option>
						<option <? if($next_date == 29){echo " selected ";}?> value="29">29</option>
						<option <? if($next_date == 30){echo " selected ";}?> value="30">30</option>
						<option <? if($next_date == 31){echo " selected ";}?> value="31">31</option>
					</select> - 
					<select name="new_year">
						<option value="<? echo $next_year - 1;?>"><? echo $next_year + 1;?></option>
						<option value="<? echo $next_year;?>" selected><? echo $next_year;?></option>
						<option value="<? echo $next_year + 1;?>"><? echo $next_year + 1;?></option>
						<option value="<? echo $next_year + 2;?>"><? echo $next_year + 2;?></option>
						<option value="<? echo $next_year + 3;?>"><? echo $next_year + 3;?></option>
					</select>
				</td>
			</tr><tr>
				<td align="center" colspan="2"><input type="hidden" name="new_total" value="<? echo $row['total'];?>">
					<input type="submit" name="submit" value="Submit" style="width:75;"></td>
			</tr>
		</table>
		</form></center>
<?
		CloseTable();
		include("footer.php");
	}
}

function ViewUnpaid()
{
	global $prefix, $db;
	DoHead();
	$config = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));
?>
	<br><table width="100%" cellspacing="0" cellpadding="1" border="1" style="border-collapse: collapse" align="center">
	<form action="<? echo $_SERVER['PHP_SELF'];?>?op=<? echo $_GET['op'];?>&amp;action=SendInvoice" method="post">
		<tr>
			<td>&nbsp;</td>
			<td align="center"><b>Invoice ID</b></td>
			<td align="center"><b>Username</b></td>
			<td align="center"><b>Date Due</b></td>
			<td align="center"><b>Amount Due</b></td>
			<td align="center"><b>Action</b></td>
		</tr>
<?
	// Fetch the invoices that currently have not been paid and display them
	$result = $db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting WHERE status = 'unpaid' ORDER BY duedate ASC");
	while($row = $db->sql_fetchrow($result))
	{
		// Fetch the user's user id and username
		$row2 = $db->sql_fetchrow($db->sql_query("SELECT user_id, username FROM ".$prefix."_users WHERE user_id = '".$row['uid']."'"));

		if($row['duedate'] - time() < 0)
		{
			// If the payment is past due make the background RED
?>
			<tr bgcolor="#FF0000">
<?	
		}
		elseif($row['duedate'] - time() < $config['notifydays'] * 60 * 60 * 24)
		{
			// If the payment is due within $config[notifydays] then make the background GREEN
?>
			<tr bgcolor="#00FF00">
<?
		}
		else
		{
			// Else make the background color default
?>
			<tr>
<?
		}
?>
				<td align="center">
<?
			if(!$row['invoice_sent'])				
			{
?>
				<input type="checkbox" name="invoice[]" value="<? echo $row['id'];?>">
<?
			}
			else
				echo "&nbsp;";
?>
				</td>
				<td align="center"><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=EditInvoice&id=<? echo $row['id'];?>"><? echo $row['id'];?></a></td>
				<td><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&action=Search&username=<? echo $row2['username'];?>"><? echo $row2['username'];?></a></td>
				<td align="center"><? echo date("m/d/y", $row['duedate']);?></td>
				<td align="center"><? echo $config['currency'].number_format($row['total'], 2, ".", ",");?></td>
				<td align="center">[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=<? echo $_GET['op'];?>&amp;action=MarkPaid&amp;id=<? echo $row['id'];?>">Mark as Paid</a> | 
					<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=EditInvoice&id=<? echo $row['id'];?>">Edit</a> | 
					<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=DelInvoice&id=<? echo $row['id'];?>">Delete</a>]</td>
			</tr>
<?
	}
?>
	</table>
	<table width="100%" cellspacing="1" cellpadding="1" border="0" align="center"><tr><td align="left">
	With Selected: <select name="template">
<?
	// Fetch the list of invoice templates and display them in the drop-down menu
	$result = $db->sql_query("SELECT id, name FROM ".$prefix."_hosting_accounting_templates ORDER BY name ASC");
	while($row = $db->sql_fetchrow($result))
	{
?>
		<option value="<? echo $row['id'];?>"><? echo $row['name'];?></option>
<?
	}
?>
	</select>
	<input type="submit" name="SendInvoice" value="Send Invoice"></td><td width="50%" align="center">[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&action=AddInvoice">Add Invoice</a>]</td></tr></form></table>
<?	
	CloseTable();
	include("footer.php");
}

function ViewPaid()
{
	global $prefix, $db;
	DoHead();
?>
	<br><table width="100%" cellspacing="0" cellpadding="1" border="1" style="border-collapse: collapse" align="center">
		<tr>
			<td align="center"><b>Invoice ID</b></td>
			<td align="center"><b>Username</b></td>
			<td align="center"><b>Date Paid / Due</b></td>
			<td align="center"><b>Total</b></td>
			<td align="center"><b>Action</b></td>
		</tr>
<?
	// Fetch the invoices which have already been paid
	$result = $db->sql_query("SELECT id, uid, duedate, paydate, total FROM ".$prefix."_hosting_accounting WHERE status = 'paid' ORDER BY duedate DESC");
	while($row = $db->sql_fetchrow($result))
	{
		// Fetch the user's user id and username who owns this specific invoice
		$row2 = $db->sql_fetchrow($db->sql_query("SELECT user_id, username FROM ".$prefix."_users WHERE user_id = '".$row['uid']."'"));

?>
		<tr>
			<td align="center"><font style="text-decoration: underline"><a href="<? echo $_SERVER['PHP_SELF'];?>?op=<? echo $_GET['op'];?>&amp;action=ViewInvoice&amp;id=<? echo $row['id'];?>"><? echo $row['id'];?></a></font></td>
			<td><a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&action=Search&username=<? echo $row2['username'];?>"><? echo $row2['username'];?></a></td>
			<td align="center"><? echo date("m/d/y", $row['paydate'])." <b>/</b> ".date("m/d/y", $row['duedate']);?></td>
			<td align="right"><? echo $config['currency'].number_format($row['total'], 2, ".", ",");?></td>
			<td align="center">[<a href="<? echo $_SERVER['PHP_SELF'];?>?op=Accounting&amp;action=DelInvoice&id=<? echo $row['id'];?>">Delete</a>]</td>
		</tr>
<?
	}
?>
	</table>
<?	
	CloseTable();
	include("footer.php");
}

function Settings()
{
	global $db, $prefix;
	DoHead();
	
	if(isset($_POST['submit']))
	{
		$sql = "UPDATE ".$prefix."_hosting_accounting_config SET adminemail = '".$_POST['adminemail']."', notifydays = '".$_POST['notifydays']."', 
			emailsubject = '".$_POST['emailsubject']."', currency = '".$_POST['currency']."'";
		$db->sql_query($sql);
?>
		<br><table width="100%" style="border: 1px solid;"><tr><td><b>Settings Saved!</b></td></tr></table>
<?
	}
	$row = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_hosting_accounting_config"));
?>
	<br>
	<table width="100%" style="border: 1px solid;">
	<form method="post">
		<tr>
			<td colspan="2" align="center"><b>Accounting Configuration</b></td>
		</tr><tr>
			<td>Admin Email:</td>
			<td><input type="text" name="adminemail" style="width: 200px;" value="<? echo $row['adminemail'];?>"></td>
		</tr><tr>
			<td>Email Subject:</td>
			<td><input type="text" name="emailsubject" value="<? echo $row['emailsubject'];?>"></td>
		</tr><tr>
			<td>Notify Days:</td>
			<td><input type="text" name="notifydays" value="<? echo $row['notifydays'];?>"></td>
		</tr><tr>
			<td>Currency Symbol:</td>
			<td><input type="text" name="currency" style="width: 200px;" value="<? echo $row['currency'];?>"></td>
		</tr><tr>
			<td colspan="2" align="center"><input type="submit" name="submit" value="Save Settings"></td>
		</tr>
	</form>
	</table>
<?
	CloseTable();
}

if (!eregi("admin.php", $_SERVER['PHP_SELF'])) { die ("Access Denied"); }
global $prefix, $db;
$aid = substr("$aid", 0,25);
$row = $db->sql_fetchrow($db->sql_query("SELECT radminsuper FROM " . $prefix . "_authors WHERE aid='$aid'"));
if ($row['radminsuper'] == 1)
{
	switch($_GET['action'])
	{
		case "ViewPaid": ViewPaid(); break;
	
		case "ViewUnpaid": ViewUnpaid(); break;
	
		case "ViewInvoice": ViewInvoice(); break;
	
		case "SendInvoice": SendInvoice(); break;
	
		case "MarkPaid": MarkPaid(); break;
	
		case "MarkUnpaid": MarkUnpaid(); break;
	
		case "Templates": Templates(); break;
	
		case "AddTemplate": AddTemplate(); break;
	
		case "DelTemplate": DelTemplate(); break;
	
		case "AddInvoice": AddInvoice(); break;
	
		case "DelInvoice": DelInvoice(); break;
	
		case "EditInvoice": EditInvoice(); break;
	
		case "AddItem": AddItem(); break;
	
		case "DelItem": DelItem(); break;
	
		case "EditItem": EditItem(); break;
		
		case "Search": Search(); break;

		case "Settings": Settings(); break;
		
		default: ViewUnPaid(); break;
	}
}
else 
    echo "Access Denied";

function isUpdate()
{
	global $db, $prefix;
	$ver = $db->sql_fetchrow($db->sql_query("SELECT version FROM ".$prefix."_hosting_accounting_config"));
	$fp = fopen("http://moahosting.com/files/php-nuke_accounting/current.txt", "r");
	$current_version = fgets($fp, 4096);
	fclose($fp);
	$current_version = explode(":", $current_version);
	
	if($current_version[1] != $ver['version'])
		return true;
	
	return false;
}
?>