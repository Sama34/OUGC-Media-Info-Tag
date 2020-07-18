<?php

/***************************************************************************
 *
 *	OUGC Media Info Tag plugin (/inc/plugins/ougc_media_info_tag/forum_hooks.php)
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

namespace OUGCMediaInfoTag\ForumHooks;

function global_start()
{
	global $templatelist;

	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	else
	{
		$templatelist = '';
	}

	$templatelist .= ',ougcmediainfotag';

	if(defined('THIS_SCRIPT'))
	{
		if(THIS_SCRIPT == 'newpoints.php')
		{
			$templatelist .= ',';
		}
	}
}

function pre_output_page(&$page)
{
	if(my_strpos($page, '<!--ougc_media_info_tag_FORM-->') === false)
	{
		return;
	}

	global $mybb, $lang, $templates, $theme, $gobutton;

	\OUGCMediaInfoTag\Core\load_language();

	$form = eval($templates->render('OUGCMediaInfoTag_form'));

	$page = str_replace('<!--ougc_media_info_tag_FORM-->', $form, $page);
}


function datahandler_post_validate_post(&$dh)
{
	global $mybb, $lang, $db, $plugins, $thread;

	if(
		!is_member($mybb->settings['ougc_media_info_tag_forums'], ['usergroup' => $dh->data['fid']]) ||
		!isset($dh->data['message']) ||
		$mybb->settings['ougc_media_info_tag_firstpost'] && !$dh->first_post && $plugins->current_hook != 'datahandler_post_validate_thread'
	)
	{
		return;
	}

	preg_match_all(
		'#\['.\OUGCMediaInfoTag\Core\get_tag().'\](.+?)\[\/'.\OUGCMediaInfoTag\Core\get_tag().'\](\r\n?|\n?)#si',
		$dh->data['message'],
		$matches
	);

	\OUGCMediaInfoTag\Core\load_language();

	if(empty($matches[0]))
	{
		if($mybb->settings['ougc_media_info_tag_firstpost'])
		{
			$dh->set_error($lang->ougc_media_info_tag_error_forced);
		}
	
		return;
	}

	require_once OUGC_MEDIA_INFO_TAG_ROOT.'/PHPMediaInfoParser.php';

	$parser = new \Bhutanio\MediaInfo\Parser;

	foreach($matches[1] as $match)
	{
		$info = \OUGCMediaInfoTag\Core\parse($match);

		if(empty($info['general']))
		{
			$dh->set_error($lang->ougc_media_info_tag_error_invalid);

			break;
		}
	}
}

function datahandler_post_validate_thread(&$dh)
{
	datahandler_post_validate_post($dh);
}

function parse_message(&$message)
{
	global $mybb, $parser, $fid, $thread, $post, $thread, $templates, $pid, $forum;

	if(empty($fid))
	{
		// for quick edit $post isn't working so meh
		$post = get_post($mybb->get_input('pid', \MyBB::INPUT_INT));

		$fid = (int)$post['fid'];
	}

	if(empty($fid) || empty($parser->options['allow_mycode']) || empty($message))
	{
		return;
	}

	if(
		!is_member($mybb->settings['ougc_media_info_tag_forums'], ['usergroup' => $fid]) ||
		$mybb->settings['ougc_media_info_tag_firstpost'] && $post['pid'] != $thread['firstpost'] && THIS_SCRIPT != 'newthread.php'
	)
	{
		return;
	}

	\OUGCMediaInfoTag\Core\load_language();

	$message = preg_replace_callback(
		'#\['.\OUGCMediaInfoTag\Core\get_tag().'\](.+?)\[\/'.\OUGCMediaInfoTag\Core\get_tag().'\](\r\n?|\n?)#si',
		function ($match)
		{
			global $lang, $theme, $templates;

			$message = $match[1];

			$info = \OUGCMediaInfoTag\Core\parse($message);

			if(empty($info['general']))
			{
				return $match[0];
			}

			foreach($info as $type => $data)
			{
				if(!in_array($type, ['general', 'video', 'audio', 'text']))
				{
					continue;
				}

				$count = 1;

				if($type == 'text' && empty($data))
				{
					$data = [['language' => $lang->ougc_media_info_tag_nosubtitles, 'format' => '']];
				}

				foreach($data as $key => $value)
				{
					if($type == 'text')
					{
						$language = htmlspecialchars_uni(ucfirst($value['language']));

						$format = htmlspecialchars_uni(my_strtoupper($value['format']));

						$languages .= eval($templates->render('ougcmediainfotag_text_language', true, false));

						++$count;

						continue;
					}

					if($type == 'video' || $type == 'audio')
					{
						foreach($value as $k => $v)
						{
							$ckey = $type.'_'.$k;

							if($k == 'stream_size')
							{
								${$ckey} = get_friendly_size((int)$v);
							}
							else
							{
								${$ckey} = htmlspecialchars_uni($v);
							}
						}

						continue;
					}

					$ckey = $type.'_'.$key;

					if($key == 'file_size')
					{
						${$ckey} = get_friendly_size((int)$value);
					}
					else
					{
						${$ckey} = htmlspecialchars_uni($value);
					}
				}

				${$type} = eval($templates->render('ougcmediainfotag_'.$type, true, false));
			}

			return eval($templates->render('ougcmediainfotag', true, false));
        },
		$message
	);
}