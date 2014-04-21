<?php
/***************************************************************************
 *
 *   NewPoints plugin (/inc/plugins/newpoints/core/plugin.php)
 *	 Author: Pirata Nervo
 *   Copyright: © 2009-2011 Pirata Nervo
 *   
 *   Website: http://www.mybb-plugins.com
 *
 *   NewPoints plugin for MyBB - A complex but efficient points system for MyBB.
 *
 ***************************************************************************/
 
/****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

if(!defined("IN_MYBB"))
	die("This file cannot be accessed directly.");

function newpoints_plugin_info()
{
	return array(
		"name"			=> "امتیازات",
		"description"	=> "پلاگین امتیازات یک پلاگین پیچیده اما کارآمد برای مای‌بی‌بی است.<br /> ترجمه به‌دست: <a href=\"http://my-bb.ir\" target=\"_blank\">گروه مای‌بی‌بی فارسی</a>",
		"website"		=> "http://www.consoleaddicted.com",
		"author"		=> "Pirata Nervo",
		"authorsite"	=> "http://www.mybb-plugins.com",
		"version"		=> "1.9.9",
		"guid" 			=> "152e7f9f32fadb777d58fda000eb7a9e",
		"compatibility" => "16*"
	);
}

function newpoints_plugin_install()
{
	global $db, $mybb;
	
	$collation = $db->build_create_table_collation();
	
	// create tables
	if(!$db->table_exists("newpoints_settings"))
    {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_settings` (
		  `sid` int(10) UNSIGNED NOT NULL auto_increment,
		  `plugin` varchar(100) NOT NULL default '',
		  `name` varchar(100) NOT NULL default '',
		  `title` varchar(100) NOT NULL default '',
		  `description` text NOT NULL,
		  `type` text NOT NULL,
		  `value` text NOT NULL,
		  `disporder` smallint(5) UNSIGNED NOT NULL default '0',
		  PRIMARY KEY  (`sid`)
			) ENGINE=MyISAM{$collation}");
	}
	
	if(!$db->table_exists("newpoints_log"))
    {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_log` (
		  `lid` bigint(30) UNSIGNED NOT NULL auto_increment,
		  `action` varchar(100) NOT NULL default '',
		  `data` text NOT NULL,
		  `date` bigint(30) UNSIGNED NOT NULL default '0',
		  `uid` bigint(30) UNSIGNED NOT NULL default '0',
		  `username` varchar(100) NOT NULL default '',
		  PRIMARY KEY  (`lid`)
			) ENGINE=MyISAM{$collation}");
	}
	
	if(!$db->table_exists("newpoints_forumrules"))
    {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_forumrules` (
		  `rid` bigint(30) UNSIGNED NOT NULL auto_increment,
		  `fid` int(10) UNSIGNED NOT NULL default '0',
		  `name` varchar(100) NOT NULL default '',
		  `description` text NOT NULL,
		  `rate` float NOT NULL default '1',
		  `pointsview` DECIMAL(16,2) NOT NULL default '0',
		  `pointspost` DECIMAL(16,2) NOT NULL default '0',
		  PRIMARY KEY  (`rid`)
			) ENGINE=MyISAM{$collation}");
	}
	
	if(!$db->table_exists("newpoints_grouprules"))
    {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_grouprules` (
		  `rid` bigint(30) UNSIGNED NOT NULL auto_increment,
		  `gid` int(10) UNSIGNED NOT NULL default '0',
		  `name` varchar(100) NOT NULL default '',
		  `description` text NOT NULL,
		  `rate` float NOT NULL default '1',
		  `pointsearn` DECIMAL(16,2) UNSIGNED NOT NULL default '0',
		  `period` bigint(30) UNSIGNED NOT NULL default '0',
		  `lastpay` bigint(30) UNSIGNED NOT NULL default '0',
		  PRIMARY KEY  (`rid`)
			) ENGINE=MyISAM{$collation}");
	}
	
	// add settings
	newpoints_add_setting('newpoints_main_enabled', 'main', 'سیستم امتیازات فعال باشد؟', 'اگر می‌خواهید سیستم امتیازات را غیرفعال کنید روی خیر قرار دهید.', 'yesno', 1, 1);
	newpoints_add_setting('newpoints_main_curname', 'main', 'نام پول', 'نامی را برای پول وارد کنید.', 'text', 'امتیاز', 2);
	newpoints_add_setting('newpoints_main_curprefix', 'main', 'پیشوند پول', 'چیزی که می‌خواهید قبل از عدد پول نمایش داده شود را وارد کنید', 'text', '', 3);
	newpoints_add_setting('newpoints_main_cursuffix', 'main', 'پسوند پول', 'چیزی که می‌خواهید بعد از عدد پول نمایش داده شود را وارد کنید.', 'text', '﷼', 4);
	newpoints_add_setting('newpoints_main_decimal', 'main', 'رقم اعشار', 'تعداد رقم اعشار مورد استفاده را وارد کنید.', 'text', '2', 5);
	newpoints_add_setting('newpoints_main_statsvisible', 'main', 'آمار و ارقام برای کاربران نمایش داده شود؟', 'اگر نمی‌خواهید کاربران آمار را ببینید روی خیر قرار دهید.', 'yesno', 1, 6);
	newpoints_add_setting('newpoints_main_donationsenabled', 'main', 'اهدا هدیه فعال باشد؟', 'اگر می‌خواهید اهدا هدیه غیرفعال باشد روی خیر قرار دهید', 'yesno', 1, 7);
	newpoints_add_setting('newpoints_main_donationspm', 'main', 'ارسال پیام خصوصی برای اهدا هدیه؟', 'آیا می‌خواهید که بعد از هر اهدا هدیه به کاربری که هدیه دریافت کرده‌است یک پیام خصوصی ارسال شود؟', 'yesno', 1, 8);
	newpoints_add_setting('newpoints_main_stats_lastdonations', 'main', 'آخرین هدیه‌ها', 'چندتا از آخرین هدیه‌ها را نمایش دهد؟', 'text', 10, 9);
	newpoints_add_setting('newpoints_main_stats_richestusers', 'main', 'ثروتمندترین کاربران', 'چندتا از ثروت‌مندترین کاربران را نمایش دهد؟', 'text', 10, 9);
	
	// income settings
	newpoints_add_setting('newpoints_income_newpost', 'income', 'ارسال جدید', 'مقدار امتیازی که برای ارسال جدید داده شود.', 'text', '10', 1);
	newpoints_add_setting('newpoints_income_newthread', 'income', 'موضوع جدید', 'مقدار امتیازی که برای موضوع جدید داده شود.', 'text', '20', 2);
	newpoints_add_setting('newpoints_income_newpoll', 'income', 'نظرسنجی جدید', 'مقدار امتیازی که برای نظرسنجی جدید داده شود.', 'text', '15', 3);
	newpoints_add_setting('newpoints_income_perchar', 'income', 'هر کاراکتر', 'مقدار امتیازی که برای هر حرف داده شود (در پاسخ یا موضوع جدید)', 'text', '0.01', 4);
	newpoints_add_setting('newpoints_income_minchar', 'income', 'حداقل کاراکتر', 'حداقل کاراکتری که نیاز است تا بعداز آن به هر کاراکتر امتیاز تعلق گیرد.', 'text', '15', 5);
	newpoints_add_setting('newpoints_income_newreg', 'income', 'عضویت جدید', 'مقدار امتیازی که برای عضویت داده شود.', 'text', '50', 6);
	newpoints_add_setting('newpoints_income_pervote', 'income', 'هر رای در نظرسنجی', 'مقدار امتیازی که برای رای‌های دریافت شده توسط کاربران در نظر سنجی‌هایش دریافت کد.', 'text', '5', 7);
	newpoints_add_setting('newpoints_income_perreply', 'income', 'هر پاسخ', 'مقدار امتیازی که سازنده تاپیک در صورت پاسخ به تاپیکش دریافت کند.', 'text', '2', 8);
	newpoints_add_setting('newpoints_income_pmsent', 'income', 'هر ارسال پیام‌خصوصی', 'مقدار امتیازی که برای ارسال پیام خصوصی دریافت کند.', 'text', '1', 9);
	newpoints_add_setting('newpoints_income_perrate', 'income', 'هر رتبه', 'مقدار امتیازی که کاربر برای رتبه‌دادن به موضوعات دریافت کند.', 'text', '0.05', 9);
	newpoints_add_setting('newpoints_income_pageview', 'income', 'هر بازدید از صفحه', 'مقدار امتیازی که یک کاربر برای بازدید از یک صفحه دریافت کند.', 'text', '0', 10);
	newpoints_add_setting('newpoints_income_visit', 'income', 'هر بازدید', 'مقدار امتیازی که کاربر بعد از هر بازدید دریافت کند. ("بازدید‌کردن" = یک جلسه‌ی جدید در مای‌بی‌بی (که بعد از ۱۵ دقیقه تمام می‌شود))', 'text', '0.1', 11);
	newpoints_add_setting('newpoints_income_referral', 'income', 'هر معرفی', 'مقدار امتیازی که یک کاربر بعد از هر معرفی می‌گیرد. (کاربر معرفی شده کسی است که امتیاز را کسب می‌کند)', 'text', '5', 12);
	
	//rebuild_settings();
	
	newpoints_rebuild_settings_cache();
	newpoints_rebuild_rules_cache();
	
	// add points field
	if (!$db->field_exists('newpoints', 'users'))
		$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` ADD `newpoints` DECIMAL(16,2) NOT NULL DEFAULT '0';");
	
	// create task
	$new_task = array(
		"title" => "Backup NewPoints",
		"description" => "Creates a backup of NewPoints default tables and users\'s points.",
		"file" => "backupnewpoints",
		"minute" => '0',
		"hour" => '0',
		"day" => '*',
		"month" => '*',
		"weekday" => '0',
		"enabled" => '0',
		"logging" => '1'
	);
	
	$new_task['nextrun'] = 0; // once the task is enabled, it will generate a nextrun date
	$tid = $db->insert_query("tasks", $new_task);
}

function newpoints_plugin_is_installed()
{
	global $db;
	
	if($db->table_exists('newpoints_settings'))
		return true;
	else
		return false;
}

function newpoints_plugin_uninstall()
{
	global $db, $mybb, $cache, $plugins, $theme, $templates, $lang;
	
	// uninstall plugins
	$plugins_cache = $cache->read("newpoints_plugins");
	$active_plugins = $plugins_cache['active'];
	
	if (!empty($active_plugins))
	{
		foreach($active_plugins as $plugin)
		{
			// Ignore missing plugins
			if(!file_exists(MYBB_ROOT."inc/plugins/newpoints/".$plugin.".php"))
				continue;
		
			require_once MYBB_ROOT."inc/plugins/newpoints/".$plugin.".php";
		
			if(function_exists("{$plugin}_deactivate"))
			{
				call_user_func("{$plugin}_deactivate");
			}
	
			if(function_exists("{$plugin}_uninstall"))
			{
				call_user_func("{$plugin}_uninstall");
			}
		}
	}
	
	// delete plugins cache
	$db->delete_query('datacache', 'title=\'newpoints_plugins\''); 
		
	if ($db->field_exists('newpoints', 'users'))
		$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` DROP `newpoints`;");
	
	// delete default main settings
	newpoints_remove_settings("'newpoints_main_enabled','newpoints_main_curname','newpoints_main_curprefix','newpoints_main_cursuffix','newpoints_main_decimal','newpoints_main_statsvisible','newpoints_main_donationsenabled','newpoints_main_donationspm','newpoints_main_stats_lastdonations','newpoints_main_stats_richestusers'");
	
	// delete default income settings
	newpoints_remove_settings("'newpoints_income_newpost','newpoints_income_newthread','newpoints_income_newpoll','newpoints_income_perchar','newpoints_income_minchar','newpoints_income_newreg','newpoints_income_pervote','newpoints_income_perreply','newpoints_income_pmsent','newpoints_income_perrate','newpoints_income_pageview','newpoints_income_visit','newpoints_income_referral'");
	
	// drop tables
	if($db->table_exists('newpoints_settings'))
		$db->drop_table('newpoints_settings');
		
	if($db->table_exists('newpoints_log'))
		$db->drop_table('newpoints_log');
		
	if($db->table_exists('newpoints_forumrules'))
		$db->drop_table('newpoints_forumrules');
		
	if($db->table_exists('newpoints_grouprules'))
		$db->drop_table('newpoints_grouprules');
	
	//rebuild_settings();
	
	$db->delete_query('tasks', 'file=\'backupnewpoints\''); 
}

function newpoints_plugin_do_template_edits()
{
	// do edits
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'user_details\']}').'#', '{$post[\'user_details\']}'.'{$post[\'newpoints_postbit\']}');
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'user_details\']}').'#', '{$post[\'user_details\']}'.'{$post[\'newpoints_postbit\']}');
	find_replace_templatesets("member_profile", '#'.preg_quote('{$warning_level}').'#', '{$warning_level}'.'{$newpoints_profile}');
}

function newpoints_plugin_undo_template_edits()
{
	// undo edits
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'newpoints_postbit\']}').'#', '', 0);
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'newpoints_postbit\']}').'#', '', 0);
	find_replace_templatesets("member_profile", '#'.preg_quote('{$newpoints_profile}').'#', '', 0);
}

function newpoints_plugin_activate()
{
	global $db, $lang;
	
	newpoints_add_template('newpoints_postbit', '<br /><span class="smalltext">{$currency}: <a href="{$mybb->settings[\'bburl\']}/newpoints.php">{$points}</a></span>{$donate}');
	newpoints_add_template('newpoints_profile', '<tr>
	<td class="trow2"><strong>{$currency}:</strong></td>
	<td class="trow2"><a href="{$mybb->settings[\'bburl\']}/newpoints.php">{$points}</a>{$donate}</td>
</tr>');
	
	newpoints_add_template('newpoints_donate_inline', ' <span class="smalltext">[<a href="{$mybb->settings[\'bburl\']}/newpoints.php?action=donate&amp;uid={$uid}">{$lang->newpoints_donate}</a>]</span>');
	
	newpoints_add_template('newpoints_donate', '
<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->newpoints} - {$lang->newpoints_donate}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="180">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
</tr>
{$options}
</table>
</td>
<td valign="top">
<form action="newpoints.php" method="POST">
<input type="hidden" name="postcode" value="{$mybb->post_code}" />
<input type="hidden" name="action" value="do_donate" />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->newpoints_donate}</strong></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_user}:</strong><br /><span class="smalltext">{$lang->newpoints_user_desc}</span></td>
<td class="trow1" width="50%"><input type="text" name="username" value="{$user[\'username\']}" class="textbox" /></td>
</tr>
<tr>
<td class="trow2" width="50%"><strong>{$lang->newpoints_amount}:</strong><br /><span class="smalltext">{$lang->newpoints_amount_desc}</span></td>
<td class="trow2" width="50%"><input type="text" name="amount" value="" class="textbox" /></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_reason}:</strong><br /><span class="smalltext">{$lang->newpoints_reason_desc}</span></td>
<td class="trow1" width="50%"><input type="text" name="reason" value="" class="textbox" /></td>
</tr>
<tr>
<td class="tfoot" width="100%" colspan="2" align="center"><input type="submit" name="submit" value="{$lang->newpoints_submit}" /></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
{$footer}
</body>
</html>');

	newpoints_add_template('newpoints_statistics', '
<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->newpoints} - {$lang->newpoints_statistics}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
    <tr>
        <td valign="top" width="180">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                <td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
                </tr>
                {$options}
            </table>
        </td>
        <td valign="top">
            <table width="100%" border="0" align="center">
                <tr>
                    <td valign="top" width="40%">
                        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                            <tr>
                                <td class="thead" colspan="2"><strong>{$lang->newpoints_richest_users}</strong></td>
                            </tr>
                            <tr>
                                <td class="tcat" width="50%"><strong>{$lang->newpoints_user}</strong></td>
                                <td class="tcat" width="50%" align="center"><strong>{$lang->newpoints_amount}</strong></td>
                            </tr>
                            {$richest_users}
                        </table>
                    </td>
                </tr>
            </table>
        </td>
        <td valign="top" width="60%">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                    <td class="thead" colspan="4"><strong>{$lang->newpoints_last_donations}</strong></td>
                </tr>
                <tr>
                    <td class="tcat" width="30%"><strong>{$lang->newpoints_from}</strong></td>
                    <td class="tcat" width="30%"><strong>{$lang->newpoints_to}</strong></td>
                    <td class="tcat" width="20%" align="center"><strong>{$lang->newpoints_amount}</strong></td>
                    <td class="tcat" width="20%" align="center"><strong>{$lang->newpoints_date}</strong></td>
                </tr>
                {$last_donations}
            </table>
        </td>
    </tr>
</table>
{$footer}
</body>
</html>');
	
	newpoints_add_template('newpoints_statistics_richest_user', '
<tr>
<td class="{$bgcolor}" width="50%">{$user[\'username\']}</td>
<td class="{$bgcolor}" width="50%" align="center">{$user[\'newpoints\']}</td>
</tr>');
	
	newpoints_add_template('newpoints_statistics_donation', '
<tr>
<td class="{$bgcolor}" width="30%">{$donation[\'from\']}</td>
<td class="{$bgcolor}" width="30%">{$donation[\'to\']}</td>
<td class="{$bgcolor}" width="20%" align="center">{$donation[\'amount\']}</td>
<td class="{$bgcolor}" width="20%" align="center">{$donation[\'date\']}</td>
</tr>');
	
	newpoints_add_template('newpoints_no_results', '
<tr>
<td class="{$bgcolor}" width="100%" colspan="{$colspan}">{$no_results}</td>
</tr>');
	
	newpoints_add_template('newpoints_option', '
<tr>
<td class="{$bgcolor}" width="100%">{$option}</td>
</tr>');
	
	newpoints_add_template('newpoints_home', '
<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->newpoints}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="180">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
</tr>
{$options}
</table>
</td>
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints}</strong></td>
</tr>
<tr>
<td class="trow1">{$lang->newpoints_home_desc}</td>
</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>');
	
	newpoints_do_template_edits();
	
	//Change admin permissions
	change_admin_permission("newpoints", false, 1);
	change_admin_permission("newpoints", "plugins", 1);
	change_admin_permission("newpoints", "settings", 1);
	change_admin_permission("newpoints", "log", 1);
	change_admin_permission("newpoints", "maintenance", 1);
	change_admin_permission("newpoints", "forumrules", 1);
	change_admin_permission("newpoints", "grouprules", 1);
	change_admin_permission("newpoints", "stats", 1);
	change_admin_permission("newpoints", "upgrades", 1);
}

function newpoints_plugin_deactivate()
{
	global $db, $mybb;
	
	newpoints_remove_templates("'newpoints_postbit','newpoints_profile','newpoints_donate','newpoints_donate_inline','newpoints_statistics','newpoints_statistics_richest_user','newpoints_statistics_donation','newpoints_no_results','newpoints_option','newpoints_home'");
	
	newpoints_undo_template_edits();
	
	//Change admin permissions
	change_admin_permission("newpoints", false, -1);
	change_admin_permission("newpoints", "plugins", -1);
	change_admin_permission("newpoints", "settings", -1);
	change_admin_permission("newpoints", "log", -1);
	change_admin_permission("newpoints", "maintenance", -1);
	change_admin_permission("newpoints", "forumrules", -1);
	change_admin_permission("newpoints", "grouprules", -1);
	change_admin_permission("newpoints", "stats", -1);
	change_admin_permission("newpoints", "upgrades", -1);
}

?>
