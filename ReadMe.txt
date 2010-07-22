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

Check out www.moahosting.com for updates!
Find a bug? Submit it to us via:
   Email: JoshS@moahosting.com
   Forums: www.moahosting.com/modules.php?name=Forums


Installation:

1) To install this simply copy over all of the files within the zip
2) Insert the new tables provided in accounting.sql file.
3) Goto your admin panel and click on the Accounting icon to administer
   admin the accounting options and other info.


Administration:

1) The first page you will see in the admin section will display
   all of the current unpaid invoices listed from the date closest
   to today. When invoices become late, the background will turn red
   for that invoice in the list.
   -> You have several options on this page, you can mark an invoice
      as paid, edit the invoice details, or delete the invoice. You
      can also use the checkboxes on the left side to send invoices
      via email.
   -> If you click on the invoice ID it will display the
      current services on this invoice.

2) [View Invoice] Here is where you can view all of the services
   attached to a specific invoice. You can add/edit/delete them as
   needed. These actions will also update the total appearing on
   the invoice.

3) [Mark Paid] Here is where you mark each invoice as paid. On this
   page you are also prompted with the option of automatically
   creating a new invoice with the same details. By default 1 month
   in the future is the selected date, this can be changed easily.

4) [New Invoice] This is a simple page, you simply create an invoice
   with a payment due date and assign it to a user, both of these can
   be changed at any time.
   -> Once you add an invoice you'll need to add service(s) to it.
      To do this simply click on the invoice ID and add away!

5) [Users] This area is for managing the current users. These users
   are not connected to the PHP-Nuke users.

6) [Templates] This page is for managing your invoice templates that
   will be sent out in emails to users. You can add/edit/delete and
   preview each template.
   -> Creating new templates: Ok this is very simple to do, however
      there are a couple things that must be done in order for it to
      work correctly. First you must have the following in your html
      somewhere which will be replaced with data, _DETAILS_ & _TOTAL_
   -> _DETAILS_ is a tricky one, this one needs to be inserted in a 
      table. The _DETAILS_ will be replaced with something similar to:

	<tr><td>Description</td><td>Quantity</td><td>price</td></tr>

	So in order for this to work you'll need to make sure you have
	it within a table. You can look at the default table for an
	example.
   -> _TOTAL_ is replaced with the total for the current invoice.
   -> _INVOICEID_ is replaced with the ID of the invoice
   -> _DUEDATE_ is replaced with the date this invoice is due


ToDo:

 1) Add custom subject title's for emailed invoices, each title is
    attached to a specific template. Ex: Invoice, Late Payment, etc


ChangeLog:

v3.0 :: 03.23.2006
 - Changed date format to use the unix timestamp instead of storing
   3 separate values for date, month, year.
 - Added update manager feature.
 - SQL updates now done by simply clicking on a link.
 - Took out config.php and put all config values in the database.
 - Cleaned up user interface and admin interface.

v2.1b :: 07.22.2005
 - Fixed cancellation request bug where emails were not being sent.

v2.1 :: 05.07.2005
 - Added a detailed search engine for searching invoices.
 - Fixed bug with the $notifydays feature where the background would
   be green even if the payment was late.

v2.0c :: 03.17.2005
 - Went back through all of the php files and added comments to help
   understand what we are doing throughout the coding
 - Background color of invoices is now GREEN when the payment is
   due within $notifydays days (set in config.php)

v2.0b :: 02.10.2005
 - Fixed bug where invoices would not be sent from admin panel

v2.0 :: 01.31.2005
 - Module now uses the PHP-Nuke user database instead of a standalone
   user database. This makes it much easier for clients simply
   because now they only need to register at your site once :)
 - Fixed bug when emailing invoices some clients would receive
   other clients invoices.

v1.0d :: 01.11.2005
 - Fixed bug when marking invoice as paid and creating a new one
 - Add search functions for invoices
 - Add more variables to the templates, Dear _CLIENTNAME_, replaced
   with the client's first and last name

v1.0c :: 01.01.2005
 - Added _INVOICEID_ into invoice templates
 - Added _DUEDATE_ into invoice templates
 - Can now delete paid invoices
 - Added ability to change currency symbols ($currency) in config.php
 - Added links to edit user details when viewing paid/unpaid
 - Added username to list of paid invoices
 - Admins can view/edit/add all components of users' details

v1.0b :: 12.31.2004
 - First Release available!

