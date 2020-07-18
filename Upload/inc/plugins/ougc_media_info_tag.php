<?php

/***************************************************************************
 *
 *	OUGC Media Info Tag plugin (/inc/plugins/ougc_media_info_tag.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2020 Omar Gonzalez
 *
 *	Website: https://ougc.network
 *
 *	Allow users to paste Media Info in posts using a tag that renders gracefully.
 *
 ***************************************************************************
 
****************************************************************************
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
 
// Die if IN_MYBB is not defined, for security reasons.
if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}

define('OUGC_MEDIA_INFO_TAG_ROOT', MYBB_ROOT . 'inc/plugins/ougc_media_info_tag');

require_once OUGC_MEDIA_INFO_TAG_ROOT.'/core.php';

// Add our hooks
if(defined('IN_ADMINCP'))
{
	require_once OUGC_MEDIA_INFO_TAG_ROOT.'/admin.php';
}
else
{
	require_once OUGC_MEDIA_INFO_TAG_ROOT.'/forum_hooks.php';

	\OUGCMediaInfoTag\Core\addHooks('OUGCMediaInfoTag\ForumHooks');
}

// Plugin API
function ougc_media_info_tag_info()
{
	return \OUGCMediaInfoTag\Admin\_info();
}

// Activate the plugin.
function ougc_media_info_tag_activate()
{
	\OUGCMediaInfoTag\Admin\_activate();
}

// Deactivate the plugin.
function ougc_media_info_tag_deactivate()
{
	\OUGCMediaInfoTag\Admin\_deactivate();
}

// Install the plugin.
function ougc_media_info_tag_install()
{
	\OUGCMediaInfoTag\Admin\_install();
}

// Check if installed.
function ougc_media_info_tag_is_installed()
{
	return \OUGCMediaInfoTag\Admin\_is_installed();
}

// Unnstall the plugin.
function ougc_media_info_tag_uninstall()
{
	\OUGCMediaInfoTag\Admin\_uninstall();
}