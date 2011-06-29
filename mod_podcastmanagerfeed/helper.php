<?php
/**
* Podcast Manager for Joomla!
*
* @copyright	Copyright (C) 2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*
* Podcast Manager is based upon the ideas found in Podcast Suite created by Joe LeBlanc
* Original copyright (c) 2005 - 2008 Joseph L. LeBlanc and released under the GPLv2 license
*/

// Restricted access
defined('_JEXEC') or die;

// Import the external requirements
require_once JPATH_SITE.'/components/com_podcastmanager/helpers/route.php';
jimport('joomla.application.component.model');
JModel::addIncludePath(JPATH_SITE.'/components/com_podcastmanager/models', 'PodcastManagerModel');

abstract class modPodcastManagerFeedHelper
{
	public static function getList(&$params)
	{
		// Get the dbo
		$db = JFactory::getDbo();

		// Get an instance of the generic feed model
		$model = JModel::getInstance('Feed', 'PodcastManagerModel', array('ignore_request' => true));

		// Set application parameters in model
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));
		$model->setState('filter.published', 1);

		// Feed filter
		$model->setState('feed.id', $params->get('feed', array()));

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

		// Set ordering
		$model->setState('list.ordering', 'a.publish_up');
		$model->setState('list.direction', 'DESC');

		$items = $model->getItems();

		foreach ($items as &$item) {
			$item->link = JURI::base().$item->filename;
		}

		return $items;
	}
}