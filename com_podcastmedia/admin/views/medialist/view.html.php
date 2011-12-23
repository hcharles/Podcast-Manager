<?php
/**
 * Podcast Manager for Joomla!
 *
 * @package     PodcastManager
 * @subpackage  com_podcastmedia
 *
 * @copyright   Copyright (C) 2011 Michael Babker. All rights reserved.
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * Podcast Manager is based upon the ideas found in Podcast Suite created by Joe LeBlanc
 * Original copyright (c) 2005 - 2008 Joseph L. LeBlanc and released under the GPLv2 license
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Podcast Media component
 *
 * @package     PodcastManager
 * @subpackage  com_podcastmedia
 * @since       1.6
 */
class PodcastMediaViewMediaList extends JView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   1.6
	 */
	function display($tpl = null)
	{
		// Do not allow cache
		JResponse::allowCache(false);

		$app = JFactory::getApplication();
		$style = $app->getUserStateFromRequest('podcastmedia.list.layout', 'layout', 'thumbs', 'word');

		$lang = JFactory::getLanguage();

		JHtml::_('behavior.framework', true);

		$document = JFactory::getDocument();
		$document->addStyleSheet('../media/media/css/medialist-' . $style . '.css');
		if ($lang->isRTL())
		{
			$document->addStyleSheet('../media/media/css/medialist-' . $style . '_rtl.css');
		}

		$document->addScriptDeclaration(
		"window.addEvent('domready', function() {
			window.parent.document.updateUploader();
			$$('a.img-preview').each(function(el) {
				el.addEvent('click', function(e) {
					new Event(e).stop();
					window.top.document.preview.fromElement(el);
				});
			});
		});"
		);

		$this->assign('baseURL', JURI::root());
		$this->assign('audio', $this->get('Audio'));
		$this->assign('folders', $this->get('Folders'));
		$this->assign('state', $this->get('State'));

		parent::display($tpl);
	}

	/**
	 * Function to set the current folder
	 *
	 * @param   integer  $index
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	function setFolder($index = 0)
	{
		if (isset($this->folders[$index]))
		{
			$this->_tmp_folder = $this->folders[$index];
		}
		else
		{
			$this->_tmp_folder = new JObject;
		}
	}

	/**
	 * Function to set the current audio
	 *
	 * @param   integer  $index
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	function setAudio($index = 0)
	{
		if (isset($this->audio[$index]))
		{
			$this->_tmp_audio = $this->audio[$index];
		}
		else
		{
			$this->_tmp_audio = new JObject;
		}
	}
}
