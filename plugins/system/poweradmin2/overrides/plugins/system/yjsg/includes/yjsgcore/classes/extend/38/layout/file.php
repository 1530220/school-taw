<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die();

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
 * @see         http://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since       3.0
 */
class JLayoutFile extends YjsgJLayoutFileDefault
{

	/**
	 * Get the default array of include paths
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getDefaultIncludePaths()
	{
		// Reset includePaths
		$paths = array();

		// (1 - highest priority) Received a custom high priority path
		if ($this->basePath !== null)
		{
			$paths[] = rtrim($this->basePath, DIRECTORY_SEPARATOR);
		}

		// Component layouts & overrides if exist
		$component = $this->options->get('component', null);

		if (!empty($component))
		{

			// (2) Component template overrides path
			$paths[] = JPATH_THEMES . '/' . \JFactory::getApplication()->getTemplate() . '/html/layouts/' . $component;

			// (3) Component path
			if ($this->options->get('client') == 0)
			{
				$paths[] = JPATH_SITE . '/components/' . $component . '/layouts';
			}
			else
			{
				$paths[] = JPATH_ADMINISTRATOR . '/components/' . $component . '/layouts';
			}
		}

		// (!) Check for YJSG overrides
		$paths[] = YJSGPATH . 'includes/html/layouts';

		// (4) Standard Joomla! layouts overriden
		$paths[] = JPATH_THEMES . '/' . \JFactory::getApplication()->getTemplate() . '/html/layouts';

		// (5 - lower priority) Frontend base layouts
		$paths[] = JPATH_ROOT . '/layouts';

		return $paths;
	}

	/**
	 * Method to finds the full real file path, checking possible overrides
	 *
	 * @return  string  The full path to the layout file
	 *
	 * @since   3.0
	 */
	protected function getPath()
	{
		\JLoader::import('joomla.filesystem.path');

		$layoutId = $this->getLayoutId();
		$includePaths = $this->getIncludePaths();
		$suffixes = $this->getSuffixes();

		$this->addDebugMessage('<strong>Layout:</strong> ' . $this->layoutId);

		if (!$layoutId)
		{
			$this->addDebugMessage('<strong>There is no active layout</strong>');

			return;
		}

		if (!$includePaths)
		{
			$this->addDebugMessage('<strong>There are no folders to search for layouts:</strong> ' . $layoutId);

			return;
		}

		$hash = md5(json_encode(array(
			'paths' => $includePaths,
			'suffixes' => $suffixes
		)));

		if (!empty(static::$cache[$layoutId][$hash]))
		{
			$this->addDebugMessage('<strong>Cached path:</strong> ' . static::$cache[$layoutId][$hash]);

			return static::$cache[$layoutId][$hash];
		}

		$this->addDebugMessage('<strong>Include Paths:</strong> ' . print_r($includePaths, true));

		// Search for suffixed versions. Example: tags.j31.php
		if ($suffixes)
		{
			$this->addDebugMessage('<strong>Suffixes:</strong> ' . print_r($suffixes, true));

			foreach ($suffixes as $suffix)
			{
				$rawPath = str_replace('.', '/', $this->layoutId) . '.' . $suffix . '.php';
				$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

				// <!-- Modified to support Site Manager feature of JSN PowerAdmin :: Begin
				if (JFactory::getApplication()->input->getInt('poweradmin-preview'))
				{
					JFactory::getApplication()->triggerEvent('onBeforeLoadLayoutFile',
						array(
							&$this->includePaths,
							$rawPath
						));
				}
				// Modified to support Site Manager feature of JSN PowerAdmin :: End -->

				if ($foundLayout = \JPath::find($this->includePaths, $rawPath))
				{
					$this->addDebugMessage('<strong>Found layout:</strong> ' . $this->fullPath);

					static::$cache[$layoutId][$hash] = $foundLayout;

					return static::$cache[$layoutId][$hash];
				}
			}
		}

		// Standard version
		$rawPath = str_replace('.', '/', $this->layoutId) . '.php';
		$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

		// <!-- Modified to support Site Manager feature of JSN PowerAdmin :: Begin
		if (JFactory::getApplication()->input->getInt('poweradmin-preview'))
		{
			JFactory::getApplication()->triggerEvent('onBeforeLoadLayoutFile', array(
				&$this->includePaths,
				$rawPath
			));
		}
		// Modified to support Site Manager feature of JSN PowerAdmin :: End -->

		$foundLayout = \JPath::find($this->includePaths, $rawPath);

		if (!$foundLayout)
		{
			$this->addDebugMessage('<strong>Unable to find layout: </strong> ' . $layoutId);

			return;
		}

		$this->addDebugMessage('<strong>Found layout:</strong> ' . $foundLayout);

		static::$cache[$layoutId][$hash] = $foundLayout;

		return static::$cache[$layoutId][$hash];
	}
}
