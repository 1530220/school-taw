<?php
/**
 * @version    $Id$
 * @package    JSN_Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import necessary Joomla libraries.
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Handle Ajax requests related to media management.
 *
 * @package  JSN_Framework
 * @since    2.0.3
 */
class JSNAjaxMedia extends JSNAjax
{
	public function indexAction($return = false)
	{
		// Get Joomla document object.
		$doc = JFactory::getDocument();

		// Load required stylesheets.
		$doc->addStyleSheet(JUri::root(true) . '/plugins/system/jsnframework/assets/3rd-party/font-awesome/css/font-awesome.min.css');
		$doc->addStyleSheet(JUri::root(true) . '/plugins/system/jsnframework/assets/3rd-party/bootstrap4/css/bootstrap.min.css');
		$doc->addStyleSheet(JUri::root(true) . '/plugins/system/jsnframework/assets/joomlashine/css/jsn-override.css');

		// Load required scripts.
		$doc->addScript(JUri::root(true) . '/media/jui/js/jquery.min.js');
		$doc->addScript(JUri::root(true) . '/plugins/system/jsnframework/assets/3rd-party/react/react.min.js');
		$doc->addScript(JUri::root(true) . '/plugins/system/jsnframework/assets/3rd-party/react/react-dom.min.js');
		$doc->addScript(JUri::root(true) . '/plugins/system/jsnframework/assets/3rd-party/underscore/underscore-min.js');
		$doc->addScript(JUri::root(true) . '/plugins/system/jsnframework/assets/bravebits/bb-media-selector.js');

		// Render index template.
		$this->render('index');

		if ($return) {
			return $this->getResponse();
		}
	}

	/**
	 * Send list of file in a directory.
	 *
	 * @return  void
	 */
	public function getListFilesAction()
	{
		// Get requested variables.
		$d    = $this->app->input->getString('dir');
		$type = $this->app->input->getString('type');

		// Send response.
		echo json_encode( $this->listFiles($d, $type) );

		exit;
	}

	/**
	 * Send list of file and folder in a directory.
	 *
	 * @return  void
	 */
	public function getFullDirectoryAction()
	{
		$type  = $this->app->input->getString('type', '');
		$files = $this->listDir(JPATH_ROOT . '/images', $type);

		sort($files, SORT_LOCALE_STRING);

		$lists = array();

		foreach ($files as $f)
		{
			$lists[] = str_replace(JPATH_ROOT . '/images/', '', $f);
		}

		// Send response.
		echo json_encode($lists);

		exit;
	}

	/**
	 * Handle uploading file.
	 *
	 * @return  void
	 */
	public function uploadFileAction()
	{
		$d    = $this->app->input->getString('dir', '');
		$root = JPATH_ROOT . '/images/' . $d;

		if ($_POST['data_uri'])
		{
			$uri = $root . $_POST['filename'];

			if ( is_file($uri) )
			{
				jexit( json_encode( array(
					'message' => JText::_('JSN_EXTFW_FILE_ALREADY_EXISTS'),
					'uri'     => $_POST['filename'],
					'list'    => $this->listFiles($d)
				) ) );
			}

			$data = $_POST['data_uri'];

			list($type, $data) = explode(';', $data);
			list($tmp, $data) = explode(',', $data);

			$data = base64_decode($data);

			file_put_contents($uri, $data);

			// Verify uploaded file.
			if ( ! JSNUtilsFile::check_upload($uri) || ! JSNUtilsFile::check_xss($uri) )
			{
				unlink($uri);

				jexit( json_encode( array(
					'success' => false,
					'message' => JText::_('JSN_EXTFW_UPLOADED_FILE_IS_NOT_ALLOWED')
				) ) );
			}
		}

		jexit( json_encode( array(
			'message' => 'done',
			'uri'     => $_POST['filename'],
			'list'    => $this->listFiles($d)
		) ) );
	}

	/**
	 * Create a directory.
	 *
	 * @return  void
	 */
	public function createFolderAction()
	{
		$d    = $this->app->input->getString('dir', '');
		$name = $this->app->input->getString('name', '');

		if ( file_exists(JPATH_ROOT . '/images' . $d . $name) )
		{
			$result = array(
				'success' => false,
				'message' => JText::_('JSN_EXTFW_FOLDER_ALREADY_EXISTS'),
				'path'    => $d . $name
			);
		}
		elseif ( JFolder::create(JPATH_ROOT . '/images' . $d . $name) )
		{
			$result = array(
				'success' => true,
				'message' => JText::_('JSN_EXTFW_NEW_FOLDER_SUCCESSFULLY_CREATED'),
				'path'    => $d . $name
			);
		}
		else
		{
			$result = array(
				'success' => false,
				'message' => JText::_('JSN_EXTFW_CREATING_NEW_FOLDER_FAILED'),
				'path'    => $d . $name
			);
		}

		echo json_encode($result);

		exit;
	}

	/**
	 * Delete a directory.
	 *
	 * @return  void
	 */
	public function deleteFolderAction()
	{
		$d = $this->app->input->getString('dir', '');

		if ( file_exists(JPATH_ROOT . '/images' . $d) )
		{
			if ( JFolder::delete(JPATH_ROOT . '/images' . $d) )
			{
				$result = array(
					'success' => true,
					'message' => JText::sprintf('JSN_EXTFW_THE_FOLDER_HAS_BEEN_DELETED', $d),
					'path'    => $d
				);
			}
			else
			{
				$result = array(
					'success' => false,
					'message' => JText::_('JSN_EXTFW_DELETING_FOLDER_FAILED'),
					'path'    => $d
				);
			}
		}
		else
		{
			$result = array(
				'success' => false,
				'message' => JText::_('JSN_EXTFW_FOLDER_DOES_NOT_EXIST'),
				'path'    => $d
			);
		}

		echo json_encode($result);

		exit;
	}

	/**
	 * Delete a file.
	 *
	 * @return  void
	 */
	public function deleteFileAction()
	{
		$d = $this->app->input->getString('dir', '');

		if ( file_exists(JPATH_ROOT . '/images' . $d) )
		{
			if ( JFile::delete(JPATH_ROOT . '/images' . $d) )
			{
				$result = array(
					'success' => true,
					'message' => JText::sprintf('JSN_EXTFW_THE_FILE_HAS_BEEN_DELETED', $d),
					'path'    => $d
				);
			}
			else
			{
				$result = array(
					'success' => false,
					'message' => JText::_('JSN_EXTFW_DELETING_FILE_FAILED'),
					'path'    => $d
				);
			}
		}
		else
		{
			$result = array(
				'success' => false,
				'message' => JText::_('JSN_EXTFW_FILE_DOES_NOT_EXIST'),
				'path'    => $d
			);
		}

		echo json_encode($result);

		exit;
	}

	/**
	 * Rename a directory.
	 *
	 * @return  void
	 */
	public function renameFolderAction()
	{
		$d       = $this->app->input->getString('dir', '');
		$newPath = $this->app->input->getString('newPath', '');

		if ( file_exists(JPATH_ROOT . '/images' . $newPath) )
		{
			$result = array(
				'success' => false,
				'message' => JText::_('JSN_EXTFW_FOLDER_ALREADY_EXISTS'),
				'path'    => $d,
				'newPath' => $newPath
			);
		}
		else
		{
			if ( JFolder::move(JPATH_ROOT . '/images' . $d, JPATH_ROOT . '/images' . $newPath) )
			{
				$result = array(
					'success' => true,
					'message' => JText::_('JSN_EXTFW_RENAMED_FOLDER_SUCCESSFULLY'),
					'path'    => $d,
					'newPath' => $newPath
				);
			}
			else
			{
				$result = array(
					'success' => false,
					'message' => JText::_('JSN_EXTFW_ERROR_OCCURRED_TRY_AGAIN'),
					'path'    => $d,
					'newPath' => $newPath
				);
			}
		}

		echo json_encode($result);

		exit;
	}

	/**
	 * Rename a file.
	 *
	 * @return  void
	 */
	public function renameFileAction()
	{
		$d       = $this->app->input->getString('dir', '');
		$newPath = $this->app->input->getString('newPath', '');

		if ( file_exists(JPATH_ROOT . '/images' . $newPath) )
		{
			$result = array(
				'success' => false,
				'message' => JText::_('JSN_EXTFW_FILE_ALREADY_EXISTS'),
				'path'    => $d,
				'newPath' => $newPath
			);
		}
		else
		{
			if ( JFile::move(JPATH_ROOT . '/images' . $d, JPATH_ROOT . '/images' . $newPath) )
			{
				$result = array(
					'success' => true,
					'message' => JText::_('JSN_EXTFW_RENAMED_FILE_SUCCESSFULLY'),
					'path'    => $d,
					'newPath' => $newPath
				);
			}
			else
			{
				$result = array(
					'success' => false,
					'message' => JText::_('JSN_EXTFW_ERROR_OCCURRED_TRY_AGAIN'),
					'path'    => $d,
					'newPath' => $newPath
				);
			}
		}

		echo json_encode($result);

		exit;
	}

	/**
	 * Get list of file in a directory.
	 *
	 * @param   string  $d     Directory to scan for list of file.
	 * @param   string  $type  Either 'file' or 'dir'. Leave empty to get both.
	 *
	 * @return  array
	 */
	protected function listFiles($d, $type = '')
	{
		$root = JPATH_ROOT . '/images/' . $d;

		$files       = array();
		$dirs        = array();
		$directories = array();
		$last_letter = $root[strlen($root) - 1];
		$root        = ($last_letter == '\\' || $last_letter == '/') ? $root : $root . DIRECTORY_SEPARATOR;

		$directories[] = $root;

		while ( sizeof($directories) )
		{
			$dir = array_pop($directories);

			if ( $handle = opendir($dir) )
			{
				$count  = 0;
				$ignore = array('cgi-bin', '.', '..', '._');

				while ( false !== ( $file = readdir($handle) ) )
				{
					if (in_array($file, $ignore) || substr($file, 0, 1) == '.')
					{
						continue;
					}

					$path = $dir . $file;

					if ( preg_match('/\.(bmp|gif|jpe?g|png)$/i', $file) )
					{
						list($width, $height) = getimagesize($path);
					}
					else
					{
						$width = $height = null;
					}

					$file_size = round(filesize($path) / 1024, 2);

					$obj = array(
						'name'         => $file,
						'key'          => $count++,
						'file_size'    => $file_size,
						'image_width'  => $width,
						'image_height' => $height
					);

					if ( is_dir($dir . $file) )
					{
						$obj['type'] = 'dir';

						$dirs[] = $obj;
					}
					else
					{
						$obj['type'] = 'file';

						$files[] = $obj;
					}
				}

				closedir($handle);
			}
		}

		if ($type == 'file')
		{
			return $files;
		}
		elseif ($type == 'dir')
		{
			return $dirs;
		}

		return array_merge($dirs, $files);
	}

	/**
	 * Get list of file in a directory.
	 *
	 * @param   string  $dir   Directory to scan for files.
	 * @param   string  $type  Either 'file' or 'dir'. Leave empty to get both.
	 *
	 * @return  array
	 */
	protected function listDir($dir, $type = '')
	{
		if ( ! is_dir($dir) )
		{
			return false;
		}

		$files = array();

		$this->listDirAux($dir, $files, $type);

		return $files;
	}

	/**
	 * Get list of file in a directory.
	 *
	 * @param   string  $dir    Directory to scan for files.
	 * @param   array   $files  Currently retrieved file list.
	 * @param   string  $type   Either 'file' or 'dir'. Leave empty to get both.
	 *
	 * @return  void
	 */
	protected function listDirAux($dir, &$files, $type = '')
	{
		$handle = opendir($dir);

		while ( ($file = readdir($handle) ) !== false )
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}

			$filepath = $dir == '.' ? $file : $dir . '/' . $file;

			if ( is_link($filepath) )
			{
				continue;
			}

			if ( is_file($filepath) && ($type == '' || $type == 'file') )
			{
				if ( preg_match('/\.(bmp|gif|jpe?g|png)$/i', $file) )
				{
					list($width, $height) = getimagesize($filepath);
				}
				else
				{
					$width = $height = null;
				}

				$file_size = round(filesize($filepath) / 1024, 2);

				$files[] = array(
					'type'         => 'file',
					'path'         => $filepath,
					'name'         => $file,
					'image_width'  => $width,
					'image_height' => $height,
					'file_size'    => $file_size
				);
			}
			elseif ( is_dir($filepath) && ($type === '' || $type === 'dir') )
			{
				$file_size = round(filesize($filepath) / 1024, 2);

				$files[] = array(
					'type'      => 'dir',
					'path'      => $filepath,
					'name'      => $file,
					'file_size' => $file_size
				);

				$this->listDirAux($filepath, $files, $type);
			}
		}

		closedir($handle);
	}
}
