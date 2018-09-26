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
defined('_JEXEC') or die;

/**
 * Helper class that provides support for history tracking.
 */
final class JSNPowerAdmin2HistoryHelper
{
	/**
	 * Initialization.
	 *
	 * @return  void
	 */
	public static function onAfterInitialise()
	{
		$isAjax = ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

		if ($isAjax)
		{
			return;
		}

		// Load history table class.
		JLoader::register('JSNPowerAdmin2TableHistory', JPATH_ADMINISTRATOR . '/components/com_poweradmin2/tables/history.php');
		JLoader::load('JSNPowerAdmin2TableHistory');

		// Handle global form post.
		if (JFactory::getApplication()->input->getMethod() == 'POST')
		{
			return self::handlePostRequest();
		}

		return self::handleGetRequest();
	}

	/**
	 * Save history for form submission.
	 *
	 * @return  void
	 */
	protected static function handlePostRequest()
	{
		//$post = JFactory::getApplication()->input->getArray();
		$post = JRequest::get('post');

		if ( ! isset($post['task']) )
		{
			return;
		}

		// If 'remove' task is submitted, delete appropriated history entry.
		if ( preg_match('/^([a-zA-Z0-9]+)\.?(delete|remove|trash|publish)$/i', $post['task'])
			&& ( isset($post['cid']) || isset($post['id']) ) )
		{
			return self::updateHistoryState($post);
		}

		// If 'save' task is submitted, update appropriated history entry.
		if ( preg_match('/\.?(apply|save)$/i', $post['task'])
			&& isset($post['jsn_history_id']) && isset($post['jsn_history_title']) )
		{
			return self::updateHistoryTitle($post['jsn_history_id'], $post['jsn_history_title']);
		}

		// Otherwise, create new history entry.
		if ( ! preg_match('/\.?edit/i', $post['task']) ||
			! isset($post['boxchecked']) || intval($post['boxchecked']) == 0 || ! isset($post['cid']) || empty($post['cid']) )
		{
			return;
		}

		$cid = $post['cid'];

		if ( is_array($cid) )
		{
			$cid = array_shift($cid);
		}

		if ( ! is_numeric($cid) )
		{
			return;
		}

		if ( ! isset($post['option']) )
		{
			$post['option'] = JFactory::getApplication()->input->getCmd('option');
		}

		$sessionKey = md5( 'post.' . time() . mt_rand(1, 1000) );

		if ( isset($post['view']) )
		{
			$formData['view'] = $post['view'];
		}

		if ( isset($post['layout']) )
		{
			$formData['layout'] = $post['layout'];
		}

		if ( isset($post['extension']) )
		{
			$formData['extension'] = $post['extension'];
		}

		$session = JFactory::getSession();

		$session->set( $sessionKey, json_encode($post) );

		// Send session key to client via cookie.
		setcookie('jsn-poweradmin-post-session', $sessionKey);
	}

	/**
	 * Update a history entry.
	 *
	 * @param   int     $id     ID of history item.
	 * @param   string  $title  New title of history.
	 *
	 * @return  void
	 */
	protected static function updateHistoryTitle($id, $title)
	{
		$history = JTable::getInstance('History', 'JSNPowerAdmin2Table');

		$history->load($id);

		$history->title = $title;

		$history->store();
	}

	/**
	 * Delete a history entry.
	 *
	 * @param   array  $post  Post data array.
	 *
	 * @return  void
	 */
	protected static function updateHistoryState($post)
	{
		if ( ! isset($_COOKIE['jsn-poweradmin-list-page']) )
		{
			return;
		}

		$listPage = json_decode($_COOKIE['jsn-poweradmin-list-page']);

		if ( ! $listPage )
		{
			$listPage = json_decode( stripslashes($_COOKIE['jsn-poweradmin-list-page']) );
		}

		// Add @ before $listPage->params to disable the message `Warning: Creating default object from empty value` when publishing articles.
		@$listPage->params = isset($listPage->params) ? str_replace('&amp;', '&', $listPage->params) : '';
		$id = array();

		if ( isset($post['id']) && is_numeric($post['id']) )
		{
			$id[] = $post['id'];
		}
		elseif ( isset($post['id']) && is_array($post['id']) )
		{
			$id = array_merge($id, $post['id']);
		}

		if ( isset($post['cid']) && is_numeric($post['cid']) )
		{
			$id[] = $post['cid'];
		}
		elseif ( isset($post['cid']) && is_array($post['cid']) )
		{
			$id = array_merge($id, $post['cid']);
		}

		$isDelete = (int) preg_match('/\.?(delete|remove|trash)$/i', $post['task']);

		if ( count($id) && ( is_numeric($id) || is_array($id) ) )
		{
			// Bypass if any of ID list is not a number.
			if ( is_array($id) )
			{
				foreach ($id as $i)
				{
					if ( ! is_numeric($i) )
					{
						return;
					}
				}
			}

			// Clean-up obsolete history entries.
			try
			{
				$dbo = JFactory::getDbo();

				$dbo->setQuery(
					"UPDATE #__jsn_poweradmin2_history SET is_deleted={$isDelete} " .
					"WHERE list_page_params LIKE '{$listPage->params}' AND object_id IN (" . implode(', ', $id) . ')'
				);

				$dbo->execute();
			}
			catch (Exception $e)
			{
				// Do nothing.
			}
		}
	}

	/**
	 * Save history for edit link.
	 *
	 * @return  void
	 */
	protected static function handleGetRequest()
	{
		$input = JFactory::getApplication()->input;
		$task = $input->getCmd('task');
		$cid = $input->get('cid', null, 'raw');
		$id = $input->getInt('id');

		if ( empty($task) )
		{
			return;
		}

		if ( ! $id && ! empty($cid) )
		{
			$id = is_array($cid) ? array_shift($cid) : $cid;
		}

		$params = array(
			'queryString' => $_SERVER['QUERY_STRING'],
			'object_id'   => $id
		);

		$sessionKey = md5( 'get.' . time() . mt_rand(1, 1000) );
		$session = JFactory::getSession();

		$session->set( $sessionKey, json_encode($params) );

		if ( isset($_COOKIE['jsn-poweradmin-get-session']) && $session->has($_COOKIE['jsn-poweradmin-get-session']) )
		{
			$session->clear($_COOKIE['jsn-poweradmin-get-session']);
		}

		setcookie('jsn-poweradmin-get-session', $sessionKey);
	}

	/**
	 * Handle onAfterRender event to detect default view of the current component.
	 *
	 * @return  void
	 */
	public static function onAfterRender()
	{
		$input = JFactory::getApplication()->input;
		$option = $input->getCmd('option');
		$view = $input->getCmd('view');
		$task = $input->getCmd('task');
		$layout = $input->getCmd('layout');

		if ( ! isset($_SERVER['HTTP_REFERER']) )
		{
			$_SERVER['HTTP_REFERER'] = '';
		}

		// Find actual view of the current request.
		if ( ! empty($option) )
		{
			$includedFiles = get_included_files();
			$isMatchedView = false;
			$isMatchedLayout = false;

			foreach ($includedFiles as $file)
			{
				$file = str_replace('\\', '/', $file);

				if ( ! $isMatchedView && preg_match("/\/{$option}\/views\/([^\/]+)\/view\.html\.php$/i", $file, $matches) )
				{
					$view = $matches[1];
					$isMatchedView = true;
				}

				if ( $isMatchedLayout && preg_match("/\/{$option}\/views\/([^\/]+)\/tmpl\/(.*?)\.php$/i", $file, $matches) )
				{
					$layout = $matches[2];
					$isMatchedLayout = true;
				}

				if ($isMatchedLayout && $isMatchedView)
				{
					break;
				}
			}
		}

		$params = array();
		$params['option'] = $option;

		if ( ! empty($view) )
		{
			$params['view'] = $view;
		}

		if ( ! empty($layout) )
		{
			$params['layout'] = $layout;
		}

		if ( ! empty($task) )
		{
			$params['task'] = $task;
		}

		setcookie('jsn-poweradmin-page-key', http_build_query($params));
		setcookie('jsn-poweradmin-default-view', $view);
		setcookie('jsn-poweradmin-default-layout', $layout);
		setcookie('jsn-poweradmin-referer-page', $_SERVER['HTTP_REFERER']);
	}
}
