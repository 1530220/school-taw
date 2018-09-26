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

class PlgContentPA2Vote extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.7.0
	 */
	protected $app;

	/**
	 * The position the voting data is displayed in relative to the article.
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $votingPosition;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   3.7.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Copy params from the default plugin of Joomla.
		if ($plugin = JPluginHelper::getPlugin('content', 'vote'))
		{
			$this->params = new JRegistry($plugin->params);
		}

		$this->votingPosition = $this->params->get('position', 'top');
	}

	/**
	 * Handle onPrepareContent event to change the execution order of this plugin.
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed   true if there is an error. Void otherwise.
	 *
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$this->app = JFactory::getApplication();

		if ($this->app->input->getInt('poweradmin-preview'))
		{
			// Register onContentBeforeDisplay event handler.
			$this->app->registerEvent('onContentBeforeDisplay', array(
				&$this,
				'onContentBeforeDisplay'
			));

			// Register onContentAfterDisplay event handler.
			$this->app->registerEvent('onContentAfterDisplay', array(
				&$this,
				'onContentAfterDisplay'
			));
		}
	}

	/**
	 * Displays the voting area when viewing an article and the voting section is displayed before the article
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  string|boolean  HTML string containing code for the votes if in com_content else boolean false
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		// Make sure this event handler is executed at last order.
		if (!isset($this->onContentBeforeDisplayReordered))
		{
			$this->onContentBeforeDisplayReordered = true;

			return;
		}

		if ($this->votingPosition !== 'top')
		{
			return '';
		}

		if ($this->app->input->getInt('poweradmin-preview') && !empty($row->event->beforeDisplayContent) &&
			 strpos($row->event->beforeDisplayContent, 'class="content_rating"') !== false)
		{
			$row->event->beforeDisplayContent = str_replace(
				array('class="content_rating"', 'class="content_vote"'),
				array(
					'class="content_rating" data-option data-visibility="show_vote" data-visibility-value="' . $params->get('show_vote') . '"',
					'class="content_vote" data-option data-visibility="show_vote" data-visibility-value="' . $params->get('show_vote') . '"'
				),
				$row->event->beforeDisplayContent
			);

			return;
		}

		return $this->displayVotingData($context, $row, $params, $page);
	}

	/**
	 * Displays the voting area when viewing an article and the voting section is displayed after the article
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  string|boolean  HTML string containing code for the votes if in com_content else boolean false
	 *
	 * @since   3.7.0
	 */
	public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
	{
		// Make sure this event handler is executed at last order.
		if (!isset($this->onContentAfterDisplayReordered))
		{
			$this->onContentAfterDisplayReordered = true;

			return;
		}

		if ($this->votingPosition !== 'bottom')
		{
			return '';
		}

		if ($this->app->input->getInt('poweradmin-preview') && !empty($row->event->afterDisplayContent) &&
			 strpos($row->event->afterDisplayContent, 'class="content_rating"') !== false)
		{
			$row->event->afterDisplayContent = str_replace(
				array('class="content_rating"', 'class="content_vote"'),
				array(
					'class="content_rating" data-option data-visibility="show_vote" data-visibility-value="' . $params->get('show_vote') . '"',
					'class="content_vote" data-option data-visibility="show_vote" data-visibility-value="' . $params->get('show_vote') . '"'
				),
				$row->event->afterDisplayContent
			);

			return;
		}

		return $this->displayVotingData($context, $row, $params, $page);
	}

	/**
	 * Displays the voting area
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  string|boolean  HTML string containing code for the votes if in com_content else boolean false
	 *
	 * @since   3.7.0
	 */
	private function displayVotingData($context, &$row, &$params, $page)
	{
		if (!$this->app->input->getInt('poweradmin-preview'))
		{
			return;
		}

		$parts = explode('.', $context);

		if ($parts[0] !== 'com_content')
		{
			return false;
		}

		// Load plugin language files only when needed (ex: they are not needed if show_vote is not active).
		$this->loadLanguage();

		// Get the path for the rating summary layout file
		$path = JPluginHelper::getLayoutPath('content', 'pa2vote', 'rating');

		// Render the layout
		ob_start();
		include $path;
		$html = ob_get_clean();

		if ($this->app->input->getString('view', '') === 'article' && $row->state == 1)
		{
			// Get the path for the voting form layout file
			$path = JPluginHelper::getLayoutPath('content', 'pa2vote', 'vote');

			// Render the layout
			ob_start();
			include $path;
			$html .= ob_get_clean();
		}

		return $html;
	}
}
