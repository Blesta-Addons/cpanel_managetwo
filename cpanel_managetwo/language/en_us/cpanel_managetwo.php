<?php
/**
 * en_us language for the cpanel module
 */
// Basics
$lang['CpanelManagetwo.name'] = "cPanel Manage2";
$lang['CpanelManagetwo.order_options.first'] = "First";
$lang['CpanelManagetwo.please_select'] = "-- Please Select --";
$lang['CpanelManagetwo.tab_ip'] = "Licence Info";

// Module management
$lang['CpanelManagetwo.!error.json.unavailable'] = "The JSON extension is required by cPanelLicensing.";
$lang['CpanelManagetwo.!error.simplexml.unavailable'] = "The SIMPLEXML extension is required by cPanelLicensing.";
$lang['CpanelManagetwo.!error.curl_init.unavailable'] = "cPanelLicensing requires that curl+ssl support is compiled into the PHP interpreter.";
$lang['CpanelManagetwo.module_row.name'] = "Account";
$lang['CpanelManagetwo.module_rows.name'] = "Accounts";
$lang['CpanelManagetwo.module_group.name'] = "Account Group";
$lang['CpanelManagetwo.module_row'] = "Account";
$lang['CpanelManagetwo.module_row_plural'] = "Accounts";


// Manage
$lang['CpanelManagetwo.add_module_row'] = "Add Account";
$lang['CpanelManagetwo.return_to_manage'] = "Back To Manage Page";

$lang['CpanelManagetwo.manage.module_rows_title'] = "Accounts";
$lang['CpanelManagetwo.manage.module_listip_title'] = "Active IP";

$lang['CpanelManagetwo.manage.module_rows.edit'] = "Edit";
$lang['CpanelManagetwo.manage.module_rows.delete'] = "Delete";
$lang['CpanelManagetwo.manage.module_rows.confirm_delete'] = "Are you sure you want to delete this Account?";

$lang['CpanelManagetwo.manage.module_rows_no_results'] = "There are no Account.";
$lang['CpanelManagetwo.manage.module_listip_no_results'] = "There are no ip licenced in this account.";

$lang['CpanelManagetwo.manage.module_rows_heading.name'] = "Server Label";
$lang['CpanelManagetwo.manage.module_rows_heading.options'] = "Options";
$lang['CpanelManagetwo.manage.module_listip_heading.name'] = " Active Licences ";

$lang['CpanelManagetwo.manage.module_listip.ip'] = "License IP";
$lang['CpanelManagetwo.manage.module_listip.name'] = "ID";
$lang['CpanelManagetwo.manage.module_listip.groupid'] = "Group Id";
$lang['CpanelManagetwo.manage.module_listip.packageid'] = "Package Id";
$lang['CpanelManagetwo.manage.module_listip.hostname'] = "Hostname";
$lang['CpanelManagetwo.manage.module_listip.envtype'] = "Env Type";
$lang['CpanelManagetwo.manage.module_listip.adddate'] = "Add Date";


// Add row
$lang['CpanelManagetwo.row_meta.account_name'] = "Account Label";
$lang['CpanelManagetwo.row_meta.user_name'] = "User Name";
$lang['CpanelManagetwo.row_meta.key'] = "Password ";
$lang['CpanelManagetwo.add_row.notes_title'] = "Notes";
$lang['CpanelManagetwo.add_row.add_btn'] = "Add Account";

$lang['CpanelManagetwo.edit_row.notes_title'] = "Notes";
$lang['CpanelManagetwo.edit_row.add_btn'] = "Edit Account";

// Errors
$lang['CpanelManagetwo.!error.account_name_valid'] = "You must enter a Account Label.";
$lang['CpanelManagetwo.!error.user_name_valid'] = "The User Name appears to be invalid.";
$lang['CpanelManagetwo.!error.remote_key_valid'] = "The Password appears to be invalid.";
$lang['CpanelManagetwo.!error.remote_key_valid_connection'] = "A connection to Manage2 Account could not be established. Please check to ensure that the User Name, and password are correct.";
$lang['CpanelManagetwo.!error.meta[license_type].valid'] = "Please select a valid license type.";
$lang['CpanelManagetwo.!error.meta[groupid].valid'] = "Please select a valid Group ID.";
$lang['CpanelManagetwo.!error.manage2_ipaddress.format'] = "Please enter a valid IP address.";
$lang['CpanelManagetwo.!error.no_valid_licence.for_ip'] = "There is no valid license for this ip \n.";

// Package fields
$lang['CpanelManagetwo.package_fields.please_select'] = "-- Please Select --";
$lang['CpanelManagetwo.package_fields.license_type'] = "License Type";
$lang['CpanelManagetwo.package_fields.groupid'] = "Group ID";

// Tooltips
$lang['CpanelManagetwo.service_field.tooltip.ipaddress'] = "Enter the IP address that the license will apply to";

// Service fields
$lang['CpanelManagetwo.service_field.ipaddress'] = "IP Address";


// Service info
$lang['CpanelManagetwo.service_info.info_heading.field'] = "Field";
$lang['CpanelManagetwo.service_info.info_heading.value'] = "Value";
$lang['CpanelManagetwo.service_info.info_title'] = "Information";

$lang['CpanelManagetwo.service_info.ip_address'] = "IP Address";
$lang['CpanelManagetwo.service_info.license_type'] = "License Type";

$lang['CpanelManagetwo.service_info.adddate'] = "Date Added";
$lang['CpanelManagetwo.service_info.company'] = "Belong To Company";
$lang['CpanelManagetwo.service_info.distro'] = "Distribution";
$lang['CpanelManagetwo.service_info.envtype'] = "Environment Type";
$lang['CpanelManagetwo.service_info.expiredon'] = "Expired On";
$lang['CpanelManagetwo.service_info.expirereason'] = "Expire Reason";
$lang['CpanelManagetwo.service_info.groupid'] = "Group ID";
$lang['CpanelManagetwo.service_info.hostname'] = "Hostname";
$lang['CpanelManagetwo.service_info.ip'] = "IP Address";
$lang['CpanelManagetwo.service_info.isenkompass'] = "Enkompass Licence";
$lang['CpanelManagetwo.service_info.licenseid'] = "license ID";
$lang['CpanelManagetwo.service_info.maxusers'] = "Maximum Users";
$lang['CpanelManagetwo.service_info.os'] = "OS";
$lang['CpanelManagetwo.service_info.osver'] = "OS Ver/kernel";
$lang['CpanelManagetwo.service_info.package'] = "Package";
$lang['CpanelManagetwo.service_info.packageqty'] = "Package Quantity";
$lang['CpanelManagetwo.service_info.updateexpiretime'] = "Update Expire Time";
$lang['CpanelManagetwo.service_info.status'] = "Status";
$lang['CpanelManagetwo.service_info.version'] = "Cpanel Version";

?>