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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import necessary libraries.
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Site Reset model.
 *
 * @package  JSN_PowerAdmin_2
 * @since    1.0.0
 */
class JSNPowerAdmin2ModelReset extends JModelLegacy
{

	/**
	 * Define Joomla default extensions.
	 *
	 * @var  array
	 */
	protected static $joomla_default_extensions = array(
		'component' => array(
			'com_admin',
			'com_ajax',
			'com_associations',
			'com_banners',
			'com_cache',
			'com_categories',
			'com_checkin',
			'com_config',
			'com_contact',
			'com_content',
			'com_contenthistory',
			'com_cpanel',
			'com_fields',
			'com_finder',
			'com_mailto',
			'com_installer',
			'com_joomlaupdate',
			'com_languages',
			'com_login',
			'com_media',
			'com_menus',
			'com_messages',
			'com_modules',
			'com_newsfeeds',
			'com_plugins',
			'com_postinstall',
			'com_redirect',
			'com_search',
			'com_tags',
			'com_templates',
			'com_users',
			'com_wrapper',

			// Add JSN PowerAdmin component as default also.
			'com_poweradmin2'
		),
		'module' => array(
			'mod_custom',
			'mod_feed',
			'mod_latest',
			'mod_logged',
			'mod_login',
			'mod_menu',
			'mod_multilangstatus',
			'mod_popular',
			'mod_quickicon',
			'mod_sampledata',
			'mod_stats_admin',
			'mod_status',
			'mod_submenu',
			'mod_title',
			'mod_toolbar',
			'mod_version',
			'mod_articles_archive',
			'mod_articles_categories',
			'mod_articles_category',
			'mod_articles_latest',
			'mod_articles_news',
			'mod_articles_popular',
			'mod_banners',
			'mod_breadcrumbs',
			'mod_finder',
			'mod_footer',
			'mod_languages',
			'mod_random_image',
			'mod_related_items',
			'mod_search',
			'mod_stats',
			'mod_syndicate',
			'mod_tags_popular',
			'mod_tags_similar',
			'mod_users_latest',
			'mod_whosonline',
			'mod_wrapper',

			// Add JSN PowerAdmin module as default also.
			'mod_poweradminbar'
		),
		'plugin' => array(
			'authentication' => array(
				'cookie',
				'gmail',
				'joomla',
				'ldap'
			),
			'captcha' => array(
				'recaptcha'
			),
			'content' => array(
				'contact',
				'emailcloak',
				'fields',
				'finder',
				'joomla',
				'loadmodule',
				'pagebreak',
				'pagenavigation',
				'vote',

				// Add JSN PowerAdmin content plugins as default also.
				'pa2pagenavigation',
				'pa2vote'
			),
			'editors' => array(
				'codemirror',
				'none',
				'tinymce'
			),
			'editors-xtd' => array(
				'article',
				'contact',
				'fields',
				'image',
				'menu',
				'module',
				'pagebreak',
				'readmore'
			),
			'extension' => array(
				'joomla'
			),
			'fields' => array(
				'calendar',
				'checkboxes',
				'color',
				'editor',
				'imagelist',
				'integer',
				'list',
				'media',
				'radio',
				'sql',
				'text',
				'textarea',
				'url',
				'user',
				'usergrouplist'
			),
			'finder' => array(
				'categories',
				'contacts',
				'content',
				'newsfeeds',
				'tags'
			),
			'installer' => array(
				'folderinstaller',
				'packageinstaller',
				'urlinstaller'
			),
			'quickicon' => array(
				'extensionupdate',
				'joomlaupdate',
				'phpversioncheck'
			),
			'sampledata' => array(
				'blog'
			),
			'search' => array(
				'categories',
				'contacts',
				'content',
				'newsfeeds',
				'tags'
			),
			'system' => array(
				'cache',
				'debug',
				'fields',
				'highlight',
				'languagecode',
				'languagefilter',
				'log',
				'logout',
				'p3p',
				'redirect',
				'remember',
				'sef',
				'sessiongc',
				'stats',
				'updatenotification',

				// Add JSN PowerAdmin and JSN Ext. Framework system plugin as default also.
				'jsnextfw',
				'poweradmin2'
			),
			'twofactorauth' => array(
				'totp',
				'yubikey'
			),
			'user' => array(
				'contactcreator',
				'joomla',
				'profile'
			),

			// Add JSN PowerAdmin plugins as default also.
			'poweradmin' => array(
				'powerpack'
			)
		),
		'template' => array(
			'hathor',
			'isis',
			'system',
			'beez3',
			'protostar'
		)
	);

	/**
	 * Get all available Joomla sample data.
	 *
	 * @return  array
	 */
	public function getJoomlaSampleData()
	{
		// Get current Joomla version.
		$version = new JVersion();

		// Detect database driver.
		$db_driver = JFactory::getConfig()->get('dbtype');

		// Some database drivers share DDLs; point these drivers to the correct parent.
		if ($db_driver == 'mysqli' || $db_driver == 'pdomysql')
		{
			$db_driver = 'mysql';
		}
		elseif ($db_driver == 'sqlsrv')
		{
			$db_driver = 'sqlazure';
		}

		// Build temporary path to get available samples.
		$tmp = JFactory::getConfig()->get('tmp_path') . '/joomla/' . $version->getShortVersion() . "/samples/{$db_driver}.json";

		if (!JFile::exists($tmp))
		{
			// Build link to get all available samples for the active database driver.
			$url = 'https://github.com/joomla/joomla-cms/tree/' . $version->getShortVersion() . "/installation/sql/{$db_driver}";

			// Fetch all available samples from Joomla repository.
			$http = new JHttp();

			try
			{
				$response = $http->get($url);

				if ($response->code != 200)
				{
					throw new Exception($response->body);
				}

				// Parse response for available samples.
				if (preg_match_all('#<a[^>]+>([^\.]+\.sql)</a>#', $response->body, $matches, PREG_SET_ORDER))
				{
					// Load language file from Joomla repository.
					$lang = JFactory::getLanguage()->getTag();

					$url = 'https://raw.githubusercontent.com/joomla/joomla-cms/' . $version->getShortVersion() .
						 "/installation/language/{$lang}/{$lang}.ini";

					$response = $http->get($url);

					if ($response->code == 200)
					{
						// Build temporary path to store language data.
						$lng = JFactory::getConfig()->get('tmp_path') . "/language/{$lang}";

						// Write retrieve language data to a temporary file.
						if (JFolder::create($lng))
						{
							JFile::write("{$lng}/{$lang}.ini", $response->body);
						}

						// Load language data.
						JFactory::getLanguage()->load('joomla', dirname(dirname($lng)));
					}

					// Builder sample data array.
					$samples = array();

					foreach ($matches as $match)
					{
						if ($match[1] == 'sample_testing.sql')
						{
							continue;
						}

						if ($match[1] == 'joomla.sql')
						{
							$text = JText::_('INSTL_SITE_INSTALL_SAMPLE_NONE');
						}
						else
						{
							$text = JText::_('INSTL_' . strtoupper(JFile::stripExt($match[1])) . '_SET');
						}

						$samples[$match[1]] = $text;
					}
				}
				else
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_FAILED_TO_GET_JOOMLA_SAMPLES_FROM_GIT'));
				}
			}
			catch (Exception $e)
			{
				throw $e;
			}

			// Save temporary file.
			if (JFolder::create(dirname($tmp)))
			{
				$json = json_encode($samples);

				JFile::write($tmp, $json);
			}
		}
		else
		{
			$samples = json_decode(JFile::read($tmp), true);
		}

		return $samples;
	}

	/**
	 * Get all 3rd-party extensions.
	 *
	 * @return  array
	 */
	public function get3rdPartyExtensions()
	{
		// Get list of installed extensions.
		$dbo = JFactory::getDbo();
		$qry = $dbo->getQuery(true);

		$qry->select('extension_id, name, type, element, folder')
			->from('#__extensions')
			->where("type IN ('component', 'module', 'plugin', 'template')")
			->where('manifest_cache NOT LIKE \'%"author":"Joomla! Project"%\'')
			->order('type DESC')
			->order('extension_id DESC');

		$dbo->setQuery($qry);

		try
		{
			$results = $dbo->loadObjectList();

			// Remove default Joomla extensions from results.
			foreach ($results as $k => $v)
			{
				$is_default = false;

				if ($v->type == 'plugin')
				{
					if (isset(self::$joomla_default_extensions[$v->type][$v->folder]))
					{
						$is_default = in_array($v->element, self::$joomla_default_extensions[$v->type][$v->folder]);
					}
				}
				else
				{
					$is_default = in_array($v->element, self::$joomla_default_extensions[$v->type]);
				}

				if ($is_default)
				{
					unset($results[$k]);
				}
			}

			return array_values($results);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Drop unused tables.
	 *
	 * @param   string  $sample      Name of Joomla sample data file to be imported.
	 * @param   string  $keep_reset  'yes' to exclude the JSN PowerAdmin component from the list of 3rd-party extensions.
	 *
	 * @return  void
	 */
	public function dropUnusedTables($sample, $keep_reset)
	{
		try
		{
			// Get Joomla sample data.
			$sample = $this->getJoomlaSampleDataFile($sample);

			// Get all tables in database.
			$dbo = JFactory::getDbo();

			if ($results = $dbo->getTableList())
			{
				foreach ($results as $table)
				{
					// Drop table if not used in sample data.
					$table = str_replace($dbo->getPrefix(), '#__', $table);

					if (false === strpos($sample, "`{$table}`"))
					{
						if ($keep_reset != 'yes' || 0 !== strpos($table, '#__jsn_poweradmin2_'))
						{
							$dbo->dropTable($table);
						}
					}
				}
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Import Joomla sample data file to database.
	 *
	 * @param   string  $sample          Name of Joomla sample data file to be imported.
	 * @param   string  $keep_reset      'yes' to keep the JSN PowerAdmin component.
	 * @param   string  $keep_user       'yes' to keep the current admin account.
	 * @param   string  $admin_email     Required if $keep_user is not 'yes'.
	 * @param   string  $admin_username  Required if $keep_user is not 'yes'.
	 * @param   string  $admin_password  Required if $keep_user is not 'yes'.
	 *
	 * @return  void
	 */
	public function importSampleData($sample, $keep_reset, $keep_user, $admin_email = '', $admin_username = '', $admin_password = '')
	{
		try
		{
			// Get Joomla sample data.
			$sample = $this->getJoomlaSampleDataFile($sample);

			// Get Joomla database object.
			$dbo = JFactory::getDbo();

			if ($keep_reset == 'yes')
			{
				// Query database for the raw extension data of JSN PowerAdmin component.
				$qry = $dbo->getQuery(true);

				$qry->select('*')
					->from('#__extensions')
					->where("element LIKE '%poweradmin%' OR element LIKE 'pa2%' OR folder LIKE 'poweradmin' OR element LIKE 'jsnextfw'");
				$dbo->setQuery($qry);

				try
				{
					$extensions = $dbo->loadAssocList();
				}
				catch (Exception $e)
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_FAILED_TO_GET_EXTENSION_DATA') . "\n" . $e->getMessage());
				}

				// Query database for the raw assets data of JSN PowerAdmin component.
				$qry = $dbo->getQuery(true);

				$qry->select('*')
					->from('#__assets')
					->where("name LIKE '%poweradmin%' OR name LIKE 'pa2%'");
				$dbo->setQuery($qry);

				try
				{
					$assets = $dbo->loadAssocList();
				}
				catch (Exception $e)
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_FAILED_TO_GET_EXTENSION_ASSETS_DATA') . "\n" . $e->getMessage());
				}

				// Query database for the raw menu data of JSN PowerAdmin component.
				$qry = $dbo->getQuery(true);

				$qry->select('*')
					->from('#__menu')
					->where("link LIKE '%option=com_poweradmin2%'");
				$dbo->setQuery($qry);

				try
				{
					$menu = $dbo->loadAssocList();
				}
				catch (Exception $e)
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_FAILED_TO_GET_EXTENSION_MENU_DATA') . "\n" . $e->getMessage());
				}
			}

			if ($keep_user == 'yes')
			{
				// Get the current admin account.
				$user = JFactory::getUser();

				// Query database for the raw user data.
				$qry = $dbo->getQuery(true);

				$qry->select('*')
					->from('#__users')
					->where('id = ' . intval($user->id));
				$dbo->setQuery($qry);

				try
				{
					$user = $dbo->loadAssoc();
				}
				catch (Exception $e)
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_FAILED_TO_GET_CURRENT_ADMIN_ACCOUNT') . "\n" . $e->getMessage());
				}

				// Query database for the raw user to user group mapping data.
				$qry = $dbo->getQuery(true);

				$qry->select('*')
					->from('#__user_usergroup_map')
					->where('user_id = ' . intval($user['id']));
				$dbo->setQuery($qry);

				try
				{
					$user_usergroup_map = $dbo->loadAssoc();
				}
				catch (Exception $e)
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_FAILED_TO_GET_CURRENT_ADMIN_ACCOUNT_GROUP') . "\n" . $e->getMessage());
				}
			}

			// Get all tables in database.
			if ($results = $dbo->getTableList())
			{
				foreach ($results as $table)
				{
					// Truncate table if used in sample data.
					$table = str_replace($dbo->getPrefix(), '#__', $table);

					if (false !== strpos($sample, "`{$table}`"))
					{
						$dbo->truncateTable($table);
					}
				}
			}

			// Parse sample data file.
			$buffer = array();
			$queries = array();
			$in_string = false;

			// Trim any whitespace.
			$sample = trim($sample);

			// Remove comment lines.
			$sample = preg_replace("/\n\#[^\n]*/", '', "\n" . $sample);

			// Remove PostgreSQL comment lines.
			$sample = preg_replace("/\n\--[^\n]*/", '', "\n" . $sample);

			// Find function.
			$funct = explode('CREATE OR REPLACE FUNCTION', $sample);

			// Save SQL before function and parse it.
			$sample = $funct[0];

			// Parse the sample data to break up queries.
			for ($i = 0; $i < strlen($sample) - 1; $i++)
			{
				if ($sample[$i] == ';' && !$in_string)
				{
					$queries[] = substr($sample, 0, $i);
					$sample = substr($sample, $i + 1);
					$i = 0;
				}

				if ($in_string && ( $sample[$i] == $in_string ) && $buffer[1] != '\\')
				{
					$in_string = false;
				}
				elseif (!$in_string && ( $sample[$i] == '"' || $sample[$i] == "'" ) && ( !isset($buffer[0]) || $buffer[0] != '\\' ))
				{
					$in_string = $sample[$i];
				}

				if (isset($buffer[1]))
				{
					$buffer[0] = $buffer[1];
				}

				$buffer[1] = $sample[$i];
			}

			// If the is anything left over, add it to the queries.
			if (!empty($sample))
			{
				$queries[] = $sample;
			}

			// Add function part as is.
			for ($f = 1; $f < count($funct); $f++)
			{
				$queries[] = 'CREATE OR REPLACE FUNCTION ' . $funct[$f];
			}

			// Import sample data queries.
			foreach ($queries as $query)
			{
				// Trim any whitespace.
				$query = trim($query);

				// If the query isn't empty and is not a MySQL or PostgreSQL comment, execute it.
				if (!empty($query) && ( $query{0} != '#' ) && ( $query{0} != '-' ))
				{
					/**
					 * If we don't have UTF-8 Multibyte support we'll have to convert queries to plain UTF-8.
					 *
					 * Note: the JDatabaseDriver::convertUtf8mb4QueryToUtf8 performs the conversion ONLY when
					 * necessary, so there's no need to check the conditions in JInstaller.
					 */
					$query = $dbo->convertUtf8mb4QueryToUtf8($query);

					/**
					 * This is a query which was supposed to convert tables to utf8mb4 charset but the server doesn't
					 * support utf8mb4. Therefore we don't have to run it, it has no effect and it's a mere waste of time.
					 */
					if (!$dbo->hasUTF8mb4Support() && stristr($query, 'CONVERT TO CHARACTER SET utf8 '))
					{
						continue;
					}

					// Execute the query.
					$dbo->setQuery($query);
					$dbo->execute();
				}
			}

			// Restore JSN PowerAdmin component if requested.
			if ($keep_reset == 'yes')
			{
				// Restore extension data for JSN PowerAdmin component.
				if ($extensions)
				{
					foreach ($extensions as $extension)
					{
						$qry = $dbo->getQuery(true);

						$qry->insert('#__extensions')
							->columns(array_keys($extension))
							->values(implode(',', array_map(array(
							$dbo,
							'quote'
						), array_values($extension))));

						$dbo->setQuery($qry);
						$dbo->execute();
					}
				}

				// Restore assets data for JSN PowerAdmin component.
				if ($assets)
				{
					foreach ($assets as $asset)
					{
						$qry = $dbo->getQuery(true);

						$qry->insert('#__assets')
							->columns(array_keys($asset))
							->values(implode(',', array_map(array(
							$dbo,
							'quote'
						), array_values($asset))));

						$dbo->setQuery($qry);
						$dbo->execute();
					}
				}

				// Restore menu data for JSN PowerAdmin component.
				if ($menu)
				{
					foreach ($menu as $item)
					{
						$qry = $dbo->getQuery(true);

						$qry->insert('#__menu')
							->columns(array_keys($item))
							->values(implode(',', array_map(array(
							$dbo,
							'quote'
						), array_values($item))));

						$dbo->setQuery($qry);
						$dbo->execute();
					}
				}

				// Re-initialize JSN PowerAdmin.
				$settings = JSNPowerAdmin2Helper::getConfig();

				JFactory::getApplication()->triggerEvent('onJsnExtFwAfterSaveComponentSettings',
					array(
						&$settings,
						'com_poweradmin2'
					));
			}
			// Otherwise, manually remove the JSN PowerAdmin component and JSN Extension Framework directory.
			else
			{
				// Make sure the admin menu module has at least 1 instance.
				$exist = $dbo->setQuery(
					$dbo->getQuery(true)
						->select('id')
						->from('#__modules')
						->where('client_id = 1')
						->where("position = 'menu'")
						->where("module = 'mod_menu'"))
					->loadResult();

				if (empty($exist))
				{
					// Get object to working with modules table.
					$module = JTable::getInstance('module');

					// Load module instance.
					$module->load(array(
						'module' => 'mod_menu'
					));

					// Update module instance.
					$module->title = 'Admin Menu';
					$module->access = 3;
					$module->ordering = 0;
					$module->published = 1;
					$module->client_id = 1;
					$module->module = 'mod_menu';
					$module->position = 'menu';

					// Store module instance.
					$module->store();

					// Set module instance to show in all page.
					if ((int) $module->id > 0)
					{
						try
						{
							// Remove all menu assignment records associated with this module instance.
							$dbo->setQuery(
								$dbo->getQuery(true)
									->delete('#__modules_menu')
									->where("moduleid = {$module->id}"))
								->execute();

							// Show this module instance in all page.
							$dbo->setQuery(
								$dbo->getQuery(true)
									->insert('#__modules_menu')
									->columns('moduleid, menuid')
									->values("{$module->id}, 0"))
								->execute();
						}
						catch (Exception $e)
						{
							throw $e;
						}
					}
				}

				// Toggle the default admin menu of Joomla.
				$dbo->setQuery(
					$dbo->getQuery(true)
						->update('#__modules')
						->set('published = 1')
						->where('client_id = 1')
						->where("position = 'menu'")
						->where("module = 'mod_menu'"))
					->execute();

				// Remove files and folders belonging JSN PowerAdmin.
				JFolder::delete(JPATH_ROOT . '/administrator/components/com_poweradmin2');
				JFolder::delete(JPATH_ROOT . '/administrator/modules/mod_poweradminbar');
				JFolder::delete(JPATH_ROOT . '/components/com_poweradmin2');
				JFolder::delete(JPATH_ROOT . '/plugins/content/pa2pagenavigation');
				JFolder::delete(JPATH_ROOT . '/plugins/content/pa2vote');
				JFolder::delete(JPATH_ROOT . '/plugins/system/poweradmin2');
				JFolder::delete(JPATH_ROOT . '/plugins/system/jsnextfw');
				JFolder::delete(JPATH_ROOT . '/plugins/jsnpoweradmin');
				JFolder::delete(JPATH_ROOT . '/plugins/poweradmin');
			}

			// Either restore the current admin account or create a new one.
			if ($keep_user == 'yes')
			{
				// Restore the current admin account.
				$qry = $dbo->getQuery(true);

				$qry->insert('#__users')
					->columns(array_keys($user))
					->values(implode(',', array_map(array(
					$dbo,
					'quote'
				), array_values($user))));

				$dbo->setQuery($qry);
				$dbo->execute();

				// Restore user to user group mapping for the current admin account.
				$qry = $dbo->getQuery(true);

				$qry->insert('#__user_usergroup_map')
					->columns(array_keys($user_usergroup_map))
					->values(implode(',', array_map(array(
					$dbo,
					'quote'
				), array_values($user_usergroup_map))));

				$dbo->setQuery($qry);
				$dbo->execute();
			}
			else
			{
				// Create a new admin account.
				$qry = $dbo->getQuery(true);

				$qry->insert('#__users')
					->columns(array(
					'name',
					'username',
					'email',
					'password'
				))
					->values("'Super User', '{$admin_username}', '{$admin_email}', MD5('{$admin_password}')");

				$dbo->setQuery($qry);
				$dbo->execute();

				// Create user to user group mapping for the new admin account.
				$qry = $dbo->getQuery(true);

				$qry->insert('#__user_usergroup_map')
					->columns(array(
					'user_id',
					'group_id'
				))
					->values($dbo->insertid() . ', 8');

				$dbo->setQuery($qry);
				$dbo->execute();
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Get Joomla sample data from repository.
	 *
	 * @param   string  $sample  Name of Joomla sample data file to get.
	 *
	 * @return  string
	 */
	protected function getJoomlaSampleDataFile($sample)
	{
		// Get current Joomla version.
		$version = new JVersion();

		// Detect database driver.
		$db_driver = JFactory::getConfig()->get('dbtype');

		// Some database drivers share DDLs; point these drivers to the correct parent.
		if ($db_driver == 'mysqli' || $db_driver == 'pdomysql')
		{
			$db_driver = 'mysql';
		}
		elseif ($db_driver == 'sqlsrv')
		{
			$db_driver = 'sqlazure';
		}

		// Build temporary path to get sample data file.
		$tmp = JFactory::getConfig()->get('tmp_path') . '/joomla/' . $version->getShortVersion() . "/samples/{$db_driver}/{$sample}";

		if (!JFile::exists($tmp))
		{
			// Build link to get database schema file from Joomla repository.
			$url = 'https://raw.githubusercontent.com/joomla/joomla-cms/' . $version->getShortVersion() .
				 "/installation/sql/{$db_driver}/joomla.sql";

			// Fetch sample data file content from Joomla repository.
			$http = new JHttp();

			try
			{
				$response = $http->get($url);

				if ($response->code != 200)
				{
					throw new Exception($response->body);
				}

				$queries = $response->body;
			}
			catch (Exception $e)
			{
				throw $e;
			}

			// Get sample data file if specified.
			if ($sample != 'joomla.sql')
			{
				// Build link to get sample data file from Joomla repository.
				$url = 'https://raw.githubusercontent.com/joomla/joomla-cms/' . $version->getShortVersion() .
					 "/installation/sql/{$db_driver}/{$sample}";

				// Fetch sample data file content from Joomla repository.
				$http = new JHttp();

				try
				{
					$response = $http->get($url);

					if ($response->code != 200)
					{
						throw new Exception($response->body);
					}

					$queries .= "\n" . $response->body;
				}
				catch (Exception $e)
				{
					throw $e;
				}
			}

			// Save temporary file.
			if (JFolder::create(dirname($tmp)))
			{
				JFile::write($tmp, $queries);
			}
		}
		else
		{
			$queries = JFile::read($tmp);
		}

		return $queries;
	}
}
