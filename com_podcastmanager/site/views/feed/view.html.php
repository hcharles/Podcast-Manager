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

jimport('joomla.application.component.view');

class PodcastManagerViewFeed extends JView
{
	protected $state;
	protected $items;
	protected $category;
	protected $children;
	protected $pagination;

	function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		// Get some data from the models
		$state	= $this->get('State');
		$items	= $this->get('Items');
		$feed	= $this->get('Feed');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',	$state);
		$this->assignRef('items',	$items);
		$this->assignRef('feed',	$feed);
		$this->assignRef('params',	$params);

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active	= $app->getMenu()->getActive();
		if ((!$active) || ((strpos($active->link, 'view=feed') === false) || (strpos($active->link, '&id=' . (string) $this->feed->id) === false))) {
			//if ($layout = $category->params->get('category_layout')) {
			//$this->setLayout($layout);
			//}
		}
		elseif (isset($active->query['layout'])) {
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('COM_PODCASTMANAGER_DEFAULT_PAGE_TITLE'));
		}

		$id = (int) @$menu->query['id'];

		if ($menu && ($menu->query['option'] != 'com_podcastmanager' || $id != $this->feed->id)) {
			$this->params->set('page_subheading', $this->feed->name);
			//$path = array(array('title' => $this->feed->name, 'link' => ''));
			//$feed = $this->feed;

			//while (($menu->query['option'] != 'com_podcastmanager' || $id != $feed->id) && $feed->id > 1)
			//{
				//$path[] = array('title' => $feed->name, 'link' => WeblinksHelperRoute::getCategoryRoute($feed->id));
			//}

			//$path = array_reverse($path);

			//foreach($path as $item)
			//{
				//$pathway->addItem($item['title'], $item['link']);
			//}
		}

		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		//if ($this->category->metadesc)
		//{
			//$this->document->setDescription($this->category->metadesc);
		//}
		//elseif (!$this->category->metadesc && $this->params->get('menu-meta_description'))
		//{
			//$this->document->setDescription($this->params->get('menu-meta_description'));
		//}

		//if ($this->category->metakey)
		//{
			//$this->document->setMetadata('keywords', $this->category->metakey);
		//}
		//elseif (!$this->category->metakey && $this->params->get('menu-meta_keywords'))
		//{
			//$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		//}

		//if ($this->params->get('robots'))
		//{
			//$this->document->setMetadata('robots', $this->params->get('robots'));
		//}

		//if ($app->getCfg('MetaAuthor') == '1') {
			//$this->document->setMetaData('author', $this->category->getMetadata()->get('author'));
		//}

		//$mdata = $this->category->getMetadata()->toArray();

		//foreach ($mdata as $k => $v)
		//{
			//if ($v) {
				//$this->document->setMetadata($k, $v);
			//}
		//}

		// Add alternative feed link
		//if ($this->params->get('show_feed_link', 1) == 1)
		//{
			//$link	= '&format=feed&limitstart=';
			//$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			//$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			//$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			//$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		//}
	}
}