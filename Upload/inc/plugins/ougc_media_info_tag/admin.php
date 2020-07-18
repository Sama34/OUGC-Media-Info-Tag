<?php

/***************************************************************************
 *
 *	OUGC Media Info Tag plugin (/inc/plugins/ougc_media_info_tag/admin.php)
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

namespace OUGCMediaInfoTag\Admin;

function _info()
{
	global $lang;

	\OUGCMediaInfoTag\Core\load_language();

	return [
		'name'			=> 'OUGC Media Info Tag',
		'description'	=> $lang->setting_group_ougc_media_info_tag_desc,
		'website'		=> 'https://ougc.network',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'https://ougc.network',
		'version'		=> '1.8.0',
		'versioncode'	=> 1800,
		'compatibility'	=> '18*',
		'codename'		=> 'ougc_media_info_tag',
		'pl'			=> [
			'version'	=> 13,
			'url'		=> 'https://community.mybb.com/mods.php?action=view&pid=573'
		]
	];
}

function _activate()
{
	global $PL, $lang, $cache;

	\OUGCMediaInfoTag\Core\load_pluginlibrary();

	$PL->settings('ougc_media_info_tag', $lang->setting_group_ougc_media_info_tag, $lang->setting_group_ougc_media_info_tag_desc, [
		'forums' => [
			'title' => $lang->setting_ougc_media_info_tag_forums,
			'description' => $lang->setting_ougc_media_info_tag_forums_desc,
			'optionscode' => 'forumselect',
			'value' =>	-1,
		],
		'force' => [
			'title' => $lang->setting_ougc_media_info_tag_force,
			'description' => $lang->setting_ougc_media_info_tag_force_desc,
			'optionscode' => 'yesno',
			'value' =>	0,
		],
		'firstpost' => [
			'title' => $lang->setting_ougc_media_info_tag_firstpost,
			'description' => $lang->setting_ougc_media_info_tag_firstpost_desc,
			'optionscode' => 'yesno',
			'value' =>	0,
		],
		'tag' => [
			'title' => $lang->setting_ougc_media_info_tag_tag,
			'description' => $lang->setting_ougc_media_info_tag_tag_desc,
			'optionscode' => 'text',
			'value' =>	'mediainfo',
		],
		/*'button' => [
			'title' => $lang->setting_ougc_media_info_tag_button,
			'description' => $lang->setting_ougc_media_info_tag_button_desc,
			'optionscode' => 'yesno',
			'value' =>	1,
		]*/
	]);

	// Add templates
    $templatesDirIterator = new \DirectoryIterator(OUGC_MEDIA_INFO_TAG_ROOT.'/templates');

	$templates = [];

    foreach($templatesDirIterator as $template)
    {
		if(!$template->isFile())
		{
			continue;
		}

		$pathName = $template->getPathname();

        $pathInfo = pathinfo($pathName);

		if($pathInfo['extension'] === 'html')
		{
            $templates[$pathInfo['filename']] = file_get_contents($pathName);
		}
    }

	if($templates)
	{
		$PL->templates('ougcmediainfotag', 'OUGC Media Info Tag', $templates);
	}

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');

	if(!$plugins)
	{
		$plugins = [];
	}

	$_info = \OUGCMediaInfoTag\Admin\_info();

	if(!isset($plugins['mediainfotag']))
	{
		$plugins['mediainfotag'] = $_info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['mediainfotag'] = $_info['versioncode'];

	$cache->update('ougc_plugins', $plugins);
}

function _deactivate()
{
}

function _install()
{
}

function _is_installed()
{
	global $cache;

	$plugins = $cache->read('ougc_plugins');

	return isset($plugins['mediainfotag']);
}

function _uninstall()
{
	global $db, $PL, $cache;

	\OUGCMediaInfoTag\Core\load_pluginlibrary();

	$PL->settings_delete('ougc_media_info_tag');

	$PL->templates_delete('ougcmediainfotag');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['mediainfotag']))
	{
		unset($plugins['mediainfotag']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$cache->delete('ougc_plugins');
	}
}