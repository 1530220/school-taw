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

/**
 * Site Reset controller.
 *
 * @package  JSN_PowerAdmin_2
 * @since    1.0.0
 */
class JSNPowerAdmin2ControllerReset extends JControllerLegacy
{

	/**
	 * Get list of 3rd-party extensions.
	 *
	 * @return  void
	 */
	public function getExtensions()
	{
		// Get list of installed extensions.
		try
		{
			$results = $this->getModel('Reset', 'JSNPowerAdmin2Model')->get3rdPartyExtensions();

			echo json_encode(array(
				'success' => true,
				'content' => $results
			));
		}
		catch (Exception $e)
		{
			echo json_encode(array(
				'success' => false,
				'content' => $e->getMessage()
			));
		}

		exit();
	}

	/**
	 * Uninstall an extension.
	 *
	 * @return  void
	 */
	public function uninstallExtension()
	{
		// Get extension info.
		$ext = $this->input->getArray(
			array(
				'extension_id' => 'NUMBER',
				'name' => 'STRING',
				'type' => 'STRING',
				'element' => 'STRING',
				'folder' => 'STRING'
			), $_POST['extension']);

		// Try to uninstall the specified extension.
		try
		{
			// If uninstalling a template, clear default state.
			$dbo = JFactory::getDbo();

			if ($ext['type'] == 'template')
			{
				$qry = $dbo->getQuery(true);

				$qry->update('#__template_styles')
					->set('home = 0')
					->where('template = ' . $dbo->quote($ext['element']));

				$dbo->setQuery($qry);
				$dbo->execute();
			}

			// Let Joomla uninstalls the extension.
			if (JInstaller::getInstance()->uninstall($ext['type'], $ext['extension_id']))
			{
				echo json_encode(array(
					'success' => true
				));
			}
			else
			{
				// Check if the extension is REALLY not installed?
				$qry = $dbo->getQuery(true);

				$qry->select('protected')
					->from('#__extensions')
					->where('extension_id = ' . intval($ext['extension_id']));

				$dbo->setQuery($qry);

				if (!is_null($result = $dbo->loadResult()))
				{
					if (intval($result))
					{
						// Remove protection then try to uninstall the extension again.
						$qry = $dbo->getQuery(true);

						$qry->update('#__extensions')
							->set('protected = 0')
							->where('extension_id = ' . intval($ext['extension_id']));

						$dbo->setQuery($qry);

						if ($dbo->execute())
						{
							$this->uninstallExtension();
						}
						else
						{
							echo json_encode(
								array(
									'success' => false,
									'content' => JText::sprintf('JSN_JOOMLARESET_FAILED_TO_UNINSTALL_EXTENSION', $ext['type'],
										$ext['name'])
								));
						}
					}
					else
					{
						echo json_encode(
							array(
								'success' => false,
								'content' => JText::sprintf('JSN_JOOMLARESET_FAILED_TO_UNINSTALL_EXTENSION', $ext['type'], $ext['name'])
							));
					}
				}
				else
				{
					echo json_encode(array(
						'success' => true
					));
				}
			}
		}
		catch (Exception $e)
		{
			echo json_encode(array(
				'success' => false,
				'content' => $e->getMessage()
			));
		}

		exit();
	}

	/**
	 * Drop unused tables.
	 *
	 * @return  void
	 */
	public function dropUnusedTables()
	{
		// Get selected sample data.
		$sample = $this->input->getString('sample_data');

		// Check if user want to keep JSN PowerAdmin.
		$keep_reset = $this->input->getCmd('keep_reset', 'yes');

		try
		{
			if (empty($sample))
			{
				throw new Exception(JText::_('JSN_JOOMLARESET_NO_SAMPLE_DATA_SELECTED'));
			}

			// Drop all unused tables.
			$this->getModel('Reset', 'JSNPowerAdmin2Model')->dropUnusedTables($sample, $keep_reset);

			echo json_encode(array(
				'success' => true
			));
		}
		catch (Exception $e)
		{
			echo json_encode(array(
				'success' => false,
				'content' => $e->getMessage()
			));
		}

		exit();
	}

	/**
	 * Import Joomla sample data file.
	 *
	 * @return  void
	 */
	public function importSampleData()
	{
		// Get selected sample data.
		$sample = $this->input->getString('sample_data');

		// Check if user want to keep JSN PowerAdmin.
		$keep_reset = $this->input->getCmd('keep_reset', 'yes');

		// Check if user want to keep the current admin account.
		$keep_user = $this->input->getCmd('keep_user', 'yes');

		// Get info for creating new admin account.
		$admin_email = $this->input->getString('admin_email', '');
		$admin_username = $this->input->getString('admin_username', '');
		$admin_password = $this->input->getString('admin_password', '');

		try
		{
			if (empty($sample))
			{
				throw new Exception(JText::_('JSN_JOOMLARESET_NO_SAMPLE_DATA_SELECTED'));
			}

			// Check if user don't want to keep the current admin account.
			if ($keep_user != 'yes')
			{
				// Verify admin account info.
				if (empty($admin_email))
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_MISSING_ADMIN_EMAIL'));
				}

				if (empty($admin_username))
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_MISSING_ADMIN_USERNAME'));
				}

				if (empty($admin_password))
				{
					throw new Exception(JText::_('JSN_JOOMLARESET_MISSING_ADMIN_PASSWORD'));
				}
			}

			// Drop all unused tables.
			$this->getModel('Reset', 'JSNPowerAdmin2Model')->importSampleData($sample, $keep_reset, $keep_user, $admin_email,
				$admin_username, $admin_password);

			echo json_encode(array(
				'success' => true
			));
		}
		catch (Exception $e)
		{
			echo json_encode(array(
				'success' => false,
				'content' => $e->getMessage()
			));
		}

		exit();
	}
}
