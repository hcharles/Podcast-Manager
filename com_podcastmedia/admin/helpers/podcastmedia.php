<?php
/**
 * Podcast Manager for Joomla!
 *
 * @package     PodcastManager
 * @subpackage  com_podcastmedia
 *
 * @copyright   Copyright (C) 2011-2015 Michael Babker. All rights reserved.
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * Podcast Manager is based upon the ideas found in Podcast Suite created by Joe LeBlanc
 * Original copyright (c) 2005 - 2008 Joseph L. LeBlanc and released under the GPLv2 license
 */

defined('_JEXEC') or die;

/**
 * Podcast Media Helper Class
 *
 * @package     PodcastManager
 * @subpackage  com_podcastmedia
 * @since       1.6
 */
class PodcastMediaHelper extends JHelperMedia
{
	/**
	 * Checks if the file can be uploaded
	 *
	 * @param   array   $file       File information
	 * @param   string  $component  The option name for the component storing the parameters
	 *
	 * @return  boolean  True on success, false on error
	 *
	 * @since   1.6
	 */
	public function canUpload($file, $component = 'com_media')
	{
		$app          = JFactory::getApplication();
		$medmanparams = JComponentHelper::getParams('com_media');

		if (empty($file['name']))
		{
			$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_UPLOAD_INPUT'), 'notice');

			return false;
		}

		jimport('joomla.filesystem.file');

		if ($file['name'] !== JFile::makeSafe($file['name']))
		{
			$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_WARNFILENAME'), 'notice');

			return false;
		}

		$filetypes = explode('.', $file['name']);

		if (count($filetypes) < 2)
		{
			// There seems to be no extension
			$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_WARNFILETYPE'), 'notice');

			return false;
		}

		array_shift($filetypes);

		// Media file names should never have executable extensions buried in them.
		$executable = [
			'php', 'js', 'exe', 'phtml', 'java', 'perl', 'py', 'asp', 'dll', 'go', 'ade', 'adp', 'bat', 'chm', 'cmd', 'com', 'cpl', 'hta', 'ins',
			'isp', 'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb', 'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh'
		];

		$check = array_intersect($filetypes, $executable);

		if (!empty($check))
		{
			$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_WARNFILETYPE'), 'notice');

			return false;
		}

		$format    = array_pop($filetypes);
		$allowable = explode(',', 'mp3,m4a,mov,mp4,m4v');
		$ignored   = explode(',', $medmanparams->get('ignore_extensions'));

		if ($format == '' || $format == false || (!in_array($format, $allowable) && !in_array($format, $ignored)))
		{
			$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_WARNFILETYPE'), 'notice');

			return false;
		}

		$maxSize = (int) ($medmanparams->get('upload_maxsize', 0) * 1024 * 1024);

		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$app->enqueueMessage(JText::_('COM_PODCASTMEDIA_ERROR_WARNFILETOOLARGE'), 'notice');

			return false;
		}

		if ($medmanparams->get('restrict_uploads', 1))
		{
			if (!in_array($format, $ignored))
			{
				// If it's not an allowed file and we're not ignoring it
				$allowed_mime = explode(',', 'audio/mpeg,audio/x-m4a,video/mp4,video/x-m4v,video/quicktime');
				$illegal_mime = explode(',', $medmanparams->get('upload_mime_illegal'));

				if (function_exists('finfo_open') && $medmanparams->get('check_mime', 1))
				{
					// We have fileinfo
					$finfo = finfo_open(FILEINFO_MIME);
					$type  = finfo_file($finfo, $file['tmp_name']);

					if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime))
					{
						$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_WARNINVALID_MIME'), 'notice');

						return false;
					}

					finfo_close($finfo);
				}
				elseif (function_exists('mime_content_type') && $medmanparams->get('check_mime', 1))
				{
					// We have mime magic
					$type = mime_content_type($file['tmp_name']);

					if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime))
					{
						$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_WARNINVALID_MIME'), 'notice');

						return false;
					}
				}
				else
				{
					if (!JFactory::getUser()->authorise('core.manage', 'com_podcastmanager'))
					{
						$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_WARNNOTADMIN'), 'notice');

						return false;
					}
				}
			}
		}

		$xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);
		$html_tags = [
			'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink',
			'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del',
			'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
			'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext',
			'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object',
			'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar',
			'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead',
			'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--'
		];

		foreach ($html_tags as $tag)
		{
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag . '>'))
			{
				$app->enqueueMessage(JText::_('JLIB_MEDIA_ERROR_WARNIEXSS'), 'notice');

				return false;
			}
		}

		return true;
	}
}
