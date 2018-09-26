<?php
/**
 * @version    $Id$
 * @package    JSN_PowerAdmin_2
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

class plgPowerAdminPowerPack extends JPlugin
{
	/**
	 * Joomla database object.
	 *
	 * @var  JDatabaseDriver
	 */
	protected $dbo;

	/**
	 * JSN PowerAdmin config object.
	 *
	 * @var  JObject
	 */
	protected $cfg;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @return  void
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Get Joomla database object.
		$this->dbo = JFactory::getDbo();

		// Get JSN PowerAdmin config object.
		$this->cfg = JSNPowerAdmin2Helper::getConfig();

		// Load language file.
		$result = JFactory::getLanguage()->load('plg_poweradmin_powerpack', JPATH_ADMINISTRATOR);
	}

	/**
	 * Handle onBeforeRender event.
	 *
	 * @return  void
	 */
	public function onBeforeRender()
	{
		if (JFactory::getApplication()->input->getInt('poweradmin-search') && class_exists('JsnExtFwAssets'))
		{
			JsnExtFwAssets::loadInlineStyle(
				'body.pa-search-results .jsn-drop-menu,
				body.pa-search-results .jsn-fieldset-filter,
				body.pa-search-results .jsn-page-footer,
				body.pa-search-results .app-sidebar,
				body.pa-search-results .app-content-head,
				body.pa-search-results .app-head,
				body.pa-search-results .app-filter-bar,
				body.pa-search-results .btn-float-wrap,
				body.pa-search-results .vmicon-show,
				body.pa-search-results .vmicon-show + .menu-wrapper,
				body.pa-search-results #adminForm > #header,
				body.pa-search-results #filter-bar,
				body.pa-search-results #filter-bar0,
				body.pa-search-results #jdfilter-bar,
				body.pa-search-results .k2AdminTableFilters,
				body.pa-search-results .k2LimitBox,
				body.pa-search-results #k2AdminFooter,
				body.pa-search-results #j-main-container > .alert,
				body.pa-search-results #j-main-container > .video-tutorial,
				body.pa-search-results .j2store-product-filters,
				body.pa-search-results #content > .row-fluid > .span2 {
					display: none !important;
				}
				body.pa-search-results #content > .row-fluid > .span10 {
					width: 100% !important;
				}');
		}
	}

	/**
	 * Get link to edit content item associated with the specified menu item.
	 *
	 * @param   object  $item     A menu item object.
	 * @param   array   &$action  Action declaration.
	 *
	 * @return  void
	 */
	public function onPowerAdminGetContentItemEditLink($item, &$action)
	{
		if (!$item || $item->type != 'component')
		{
			return;
		}

		// Parse item link.
		parse_str(substr($item->link, strpos($item->link, '?') + 1), $query);

		if (!isset($query['view']))
		{
			return;
		}

		// Generate edit link.
		switch ($query['option'])
		{
			case 'com_imageshow':
				if ($query['view'] == 'show')
				{
					$action['href'] = JRoute::_("index.php?option=com_imageshow&controller=showlist&task=edit&cid[]={$query['showlist_id']}", false);
				}
			break;

			case 'com_uniform':
				if ($query['view'] == 'form')
				{
					$action['href'] = JRoute::_("index.php?option=com_uniform&view=form&task=form.edit&form_id={$query['form_id']}", false);
				}
			break;

			case 'com_easyblog':
				switch ($query['view'])
				{
					case 'blogger':
						$action['href'] = JRoute::_("index.php?option=com_easyblog&view=bloggers&layout=form&id={$query['id']}", false);
					break;

					case 'categories':
						$action['href'] = JRoute::_("index.php?option=com_easyblog&view=categories&layout=form&id={$query['id']}", false);
					break;

					case 'entry':
						$action['href'] = JRoute::_("index.php?option=com_easyblog&view=composer&tmpl=component&uid={$query['id']}", false);
					break;
				}
			break;

			case 'com_easydiscuss':
				switch ($query['view'])
				{
					case 'post':
						$action['href'] = JRoute::_("index.php?option=com_easydiscuss&view=post&layout=edit&id={$query['id']}", false);
					break;

					case 'categories':
						if (isset($query['category_id']))
						{
							$action['href'] = JRoute::_("index.php?option=com_easydiscuss&view=categories&layout=form&id={$query['category_id']}", false);
						}
					break;
				}
			break;

			case 'com_easysocial':
				if (isset($query['layout']) && isset($query['id']))
				{
					if ($query['layout'] == 'category')
					{
						$action['href'] = JRoute::_("index.php?option=com_easysocial&view={$query['view']}&layout=categoryForm&id={$query['id']}", false);
					}
					elseif ($query['layout'] == 'item')
					{
						$action['href'] = JRoute::_("index.php?option=com_easysocial&view={$query['view']}&layout=form&id={$query['id']}", false);
					}
				}
			break;

			case 'com_virtuemart':
				if ($query['view'] == 'category' && (int) $query['virtuemart_category_id'])
				{
					$action['href'] = JRoute::_("index.php?option=com_virtuemart&view=category&task=edit&cid={$query['virtuemart_category_id']}", false);
				}
				elseif ($query['view'] == 'productdetails')
				{
					$action['href'] = JRoute::_("index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id={$query['virtuemart_product_id']}", false);
				}
			break;

			case 'com_jevents':
				if ($query['view'] == 'icalrepeat' && $query['layout'] == 'detail')
				{
					$action['href'] = JRoute::_("index.php?option=com_jevents&task=icalevent.edit&evid={$query['evid']}", false);
				}
			break;

			case 'com_phocagallery':
				if ($query['view'] == 'category')
				{
					$action['href'] = JRoute::_("index.php?option=com_phocagallery&task=phocagalleryc.edit&id={$query['id']}", false);
				}
			break;

			case 'com_k2':
				if ($query['view'] == 'item')
				{
					$action['href'] = JRoute::_("index.php?option=com_k2&view=item&cid={$query['id']}", false);
				}
			break;

			case 'com_kunena':
				if ($query['view'] == 'category')
				{
					$action['href'] = JRoute::_("index.php?option=com_kunena&view=categories&layout=edit&catid={$query['catid']}", false);
				}
			break;

			case 'com_jdownloads':
				if ($query['view'] == 'category')
				{
					$action['href'] = JRoute::_("index.php?option=com_jdownloads&task=category.edit&id={$query['catid']}", false);
				}
				elseif ($query['view'] == 'download')
				{
					$action['href'] = JRoute::_("index.php?option=com_jdownloads&task=download.edit&file_id={$query['id']}", false);
				}
			break;
		}
	}

	/**
	 * Add supported search coverages.
	 *
	 * @param   string  &$coverage  The coverage to search for results.
	 *
	 * @return  void
	 */
	public function onPowerAdminGetSearchCoverages(&$coverages)
	{
		// Define supported search coverages.
		$supportedCoverages = array(
			// JoomlaShine extensions.
			'com_easyslider' => JText::_('JSN_POWERADMIN_COVERAGE_JSN_EASYSLIDER_SLIDES'),
			'com_imageshow' => JText::_('JSN_POWERADMIN_COVERAGE_JSN_IMAGESHOW_SHOWLISTS'),
			'com_mobilize' => JText::_('JSN_POWERADMIN_COVERAGE_JSN_MOBILIZE_PROFILES'),
			'com_uniform' => JText::_('JSN_POWERADMIN_COVERAGE_JSN_UNIFORM_FORMS'),

			// StackIdeas extensions.
			'com_easyblog' => JText::_('JSN_POWERADMIN_COVERAGE_EASYBLOG_POSTS'),
			'com_easydiscuss' => JText::_('JSN_POWERADMIN_COVERAGE_EASYDISCUSS_POSTS'),
			'com_easysocial' => JText::_('JSN_POWERADMIN_COVERAGE_EASYSOCIAL_PAGES'),
			'com_komento' => JText::_('JSN_POWERADMIN_COVERAGE_KOMENTO_COMMENTS'),

			// VirtueMart.
			'com_virtuemart' => JText::_('JSN_POWERADMIN_COVERAGE_VIRTUEMART_PRODUCTS'),

			// JEvents.
			'com_jevents' => JText::_('JSN_POWERADMIN_COVERAGE_JEVENTS_EVENTS'),

			// JComments.
			'com_jcomments' => JText::_('JSN_POWERADMIN_COVERAGE_JCOMMENTS_COMMENTS'),

			// Phoca Gallery.
			'com_phocagallery' => JText::_('JSN_POWERADMIN_COVERAGE_PHOCAGALLERY_IMAGES'),

			// K2.
			'com_k2' => JText::_('JSN_POWERADMIN_COVERAGE_K2_ITEMS'),

			// Kunena.
			'com_kunena' => JText::_('JSN_POWERADMIN_COVERAGE_KUNENA_TOPICS'),

			// J2Store.
			'com_j2store' => JText::_('JSN_POWERADMIN_COVERAGE_J2STORE_PRODUCTS'),

			// jDownloads.
			'com_jdownloads' => JText::_('JSN_POWERADMIN_COVERAGE_JDOWNLOADS_DOWNLOADS'),
		);

		// Only add search coverages for installed components.
		foreach ($supportedCoverages as $coverage => $title)
		{
			// Check if component is installed?
			$this->dbo->setQuery("SELECT count(*) FROM #__extensions WHERE type = 'component' AND element = '{$coverage}';");

			if ($this->dbo->loadResult())
			{
				$coverages[$coverage] = $title;
			}
		}
	}

	/**
	 * Get search results for the specified coverage.
	 *
	 * @param   string  $coverage  The coverage to search for results.
	 * @param   string  $keyword   The keyword to search for results.
	 * @param   array   &$results  Array of search result.
	 */
	public function onPowerAdminGetSearchResultsForCoverage($coverage, $keyword, &$results)
	{
		// Build query to search database for results.
		$qry = $this->dbo->getQuery(true);

		switch ($coverage)
		{
			case 'com_easyslider':
				$qry->select('slider_id AS id, slider_title AS title')
					->from('#__jsn_easyslider_sliders')
					->where('slider_title LIKE ' . $qry->quote("%{$keyword}%"));
			break;

			case 'com_imageshow':
				$qry->select('showlist_id AS id, showlist_title AS title, description')
					->from('#__imageshow_showlist')
					->where('(' . implode( ' OR ', array(
							'showlist_title LIKE ' . $qry->quote("%{$keyword}%"),
						'description LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_mobilize':
				$qry->select('profile_id AS id, profile_title AS title, profile_description AS description')
					->from('#__jsn_mobilize_profiles')
					->where('(' . implode( ' OR ', array(
							'profile_title LIKE ' . $qry->quote("%{$keyword}%"),
						'profile_description LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_uniform':
				$qry->select('form_id AS id, form_title AS title, form_description AS description')
					->from('#__jsn_uniform_forms')
					->where('(' . implode( ' OR ', array(
							'form_title LIKE ' . $qry->quote("%{$keyword}%"),
						'form_description LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_easyblog':
				$qry->select('id, title, CONCAT(content, intro, excerpt) AS description')
					->from('#__easyblog_post')
					->where('(' . implode( ' OR ', array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
							'content LIKE ' . $qry->quote("%{$keyword}%"),
							'intro LIKE ' . $qry->quote("%{$keyword}%"),
						'excerpt LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_easydiscuss':
				$qry->select('id, title, content AS description')
					->from('#__discuss_posts')
					->where('(' . implode( ' OR ', array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
						'content LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_easysocial':
				$qry->select('id, title, description')
					->from('#__social_clusters')
					->where("cluster_type = 'page'")
					->where('(' . implode( ' OR ', array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
						'description LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_komento':
				$qry->select("id, CASE title WHEN '' THEN comment ELSE title END AS title, CASE title WHEN '' THEN title ELSE comment END AS description")
					->from('#__komento_comments')
				->where('(' . implode( ' OR ', array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
						'comment LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_virtuemart':
				$qry->select('virtuemart_product_id AS id, product_name AS title, product_desc AS description')
					->from('#__virtuemart_products_en_gb')
					->where('(' . implode( ' OR ', array(
							'product_name LIKE ' . $qry->quote("%{$keyword}%"),
						'product_desc LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_jevents':
				$qry->select('evdet_id AS id, summary AS title, description')
					->from('#__jevents_vevdetail')
					->where('(' . implode( ' OR ', array(
							'summary LIKE ' . $qry->quote("%{$keyword}%"),
						'description LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_jcomments':
				$qry->select("id, CASE title WHEN '' THEN comment ELSE title END AS title, CASE title WHEN '' THEN title ELSE comment END AS description")
					->from('#__jcomments')
					->where('(' . implode( ' OR ', array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
							'comment LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_phocagallery':
				$qry->select('id, title, description')
					->from('#__phocagallery')
					->where('(' . implode( ' OR ', array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
						'description LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_k2':
				$qry->select('id, title, CONCAT(introtext, `fulltext`) AS description')
					->from('#__k2_items')
					->where('(' . implode( ' OR ', array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
							'introtext LIKE ' . $qry->quote("%{$keyword}%"),
						'`fulltext` LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');

				// Filter trashed content?
				if (!(int) $this->cfg['search_trashed'])
				{
					$qry->where('trash = 0');
				}
			break;

			case 'com_kunena':
				$qry->select('id, subject AS title, CONCAT(first_post_message, last_post_message) AS description')
					->from('#__kunena_topics')
					->where('(' . implode( ' OR ', array(
							'subject LIKE ' . $qry->quote("%{$keyword}%"),
							'first_post_message LIKE ' . $qry->quote("%{$keyword}%"),
						'last_post_message LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_j2store':
				$qry->select('a.id, a.title, CONCAT(a.introtext, a.`fulltext`) AS description')
					->from('#__content AS a')
					->innerJoin("#__j2store_products AS p ON p.product_source = 'com_content' AND p.product_source_id = a.id")
					->where('(' . implode( ' OR ', array(
							'a.title LIKE ' . $qry->quote("%{$keyword}%"),
							'a.introtext LIKE ' . $qry->quote("%{$keyword}%"),
						'a.`fulltext` LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			case 'com_jdownloads':
				$qry->select('file_id AS id, file_title AS title, CONCAT(description, description_long) AS description')
					->from('#__jdownloads_files')
					->where('(' . implode( ' OR ', array(
							'file_title LIKE ' . $qry->quote("%{$keyword}%"),
							'description LIKE ' . $qry->quote("%{$keyword}%"),
						'description_long LIKE ' . $qry->quote("%{$keyword}%"),
						)) . ')');
			break;

			default:
				return;
			break;
		}

		// Set limitation.
		$this->dbo->setQuery($qry, 0, (int) $this->cfg['search_result_num']);

		// Get search results.
		$results = $this->dbo->loadObjectList();

		// Prepare results.
		foreach ($results as $k => $result)
		{
			switch ($coverage)
			{
				case 'com_easyslider':
					$result->link = JRoute::_("index.php?option=com_easyslider&task=slider.edit&slider_id={$result->id}", false);
				break;

				case 'com_imageshow':
					$result->link = JRoute::_("index.php?option=com_imageshow&controller=showlist&task=edit&cid[]={$result->id}", false);
				break;

				case 'com_mobilize':
					$result->link = JRoute::_("index.php?option=com_mobilize&view=profile&task=profile.edit&profile_id={$result->id}", false);
				break;

				case 'com_uniform':
					$result->link = JRoute::_("index.php?option=com_uniform&view=form&task=form.edit&form_id={$result->id}", false);
				break;

				case 'com_easyblog':
					$result->link = JRoute::_("index.php?option=com_easyblog&view=composer&tmpl=component&uid={$result->id}", false);
				break;

				case 'com_easydiscuss':
					$result->link = JRoute::_("index.php?option=com_easydiscuss&view=post&layout=edit&id={$result->id}", false);
				break;

				case 'com_easysocial':
					$result->link = JRoute::_("index.php?option=com_easysocial&view=pages&layout=form&id={$result->id}", false);
				break;

				case 'com_komento':
					$result->link = JRoute::_("index.php?option=com_komento&view=comments&layout=form&id={$result->id}", false);
				break;

				case 'com_virtuemart':
					$result->link = JRoute::_("index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id={$result->id}", false);
				break;

				case 'com_jevents':
					$result->link = JRoute::_("index.php?option=com_jevents&task=icalevent.edit&evid={$result->id}", false);
				break;

				case 'com_jcomments':
					$result->link = JRoute::_("index.php?option=com_jcomments&task=comment.edit&id={$result->id}", false);
				break;

				case 'com_phocagallery':
					$result->link = JRoute::_("index.php?option=com_phocagallery&task=phocagalleryimg.edit&id={$result->id}", false);
				break;

				case 'com_k2':
					$result->link = JRoute::_("index.php?option=com_k2&view=item&cid={$result->id}", false);
				break;

				case 'com_kunena':
					$result->link = JUri::root() . "index.php?option=com_kunena&view=topic&id={$result->id}";
				break;

				case 'com_j2store':
					$result->link = JRoute::_("index.php?option=com_content&task=article.edit&id={$result->id}", false);
				break;

				case 'com_jdownloads':
					$result->link = JRoute::_("index.php?option=com_jdownloads&task=download.edit&file_id={$result->id}", false);
				break;
			}
		}
	}

	/**
	 * Get search page and name of keyword input control for the specified search coverage.
	 *
	 * @param   string  $coverage  Coverage to get search page for.
	 * @param   string  &$page     Current search page.
	 * @param   string  &$name     Name of input control for entering keyword.
	 *
	 * @return  void
	 */
	public function onPowerAdminGetSearchPageForCoverage($coverage, &$page, &$name)
	{
		switch ($coverage)
		{
			case 'com_imageshow':
				$page = JRoute::_('index.php?option=com_imageshow&view=showlist', false);
				$name = 'showlist_stitle';
			break;

			case 'com_mobilize':
				$page = JRoute::_('index.php?option=com_mobilize&view=profiles', false);
				$name = 'filter_search';
			break;

			case 'com_uniform':
				$page = JRoute::_('index.php?option=com_uniform&view=forms', false);
				$name = 'filter_search';
			break;

			case 'com_easyblog':
				$page = JRoute::_('index.php?option=com_easyblog&view=blogs', false);
				$name = 'search';
			break;

			case 'com_easydiscuss':
				$page = JRoute::_('index.php?option=com_easydiscuss&view=posts', false);
				$name = 'search';
			break;

			case 'com_easysocial':
				$page = JRoute::_('index.php?option=com_easysocial&view=pages', false);
				$name = 'search';
			break;

			case 'com_komento':
				$page = JRoute::_('index.php?option=com_komento&view=comments', false);
				$name = 'search';
			break;

			case 'com_virtuemart':
				$page = JRoute::_('index.php?option=com_virtuemart&view=product', false);
				$name = 'filter_product';
			break;

			case 'com_jcomments':
				$page = JRoute::_('index.php?option=com_jcomments&view=comments', false);
				$name = 'filter_search';
			break;

			case 'com_phocagallery':
				$page = JRoute::_('index.php?option=com_phocagallery&view=phocagalleryimgs', false);
				$name = 'filter_search';
			break;

			case 'com_k2':
				$page = JRoute::_('index.php?option=com_k2&view=items', false);
				$name = 'search';
			break;

			case 'com_j2store':
				$page = JRoute::_('index.php?option=com_j2store&view=products', false);
				$name = 'search';
			break;

			case 'com_jdownloads':
				$page = JRoute::_('index.php?option=com_jdownloads&view=downloads', false);
				$name = 'filter_search';
			break;
		}
	}
}
