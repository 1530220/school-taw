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

/**
 * HTML helper class.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
class JSNHtmlHelper
{
	/**
	 * To valid W3C types.
	 *
	 * @param   string  &$tagName  Tag name
	 * @param   array   &$attrs    Attributes
	 *
	 * @return  void
	 */
	public static function W3CValid(&$tagName, &$attrs)
	{
		$tagName = strtolower(trim($tagName));

		switch ($tagName)
		{
			case 'img':
				if ( ! array_key_exists('alt', $attrs))
				{
					$attrs += array('alt' => '');
				}
			break;

			case 'a':
				if ( ! array_key_exists('title', $attrs))
				{
					$attrs += array('title' => '');
				}
			break;

			case 'link':
				if ( ! array_key_exists('rel', $attrs))
				{
					$attrs += array('rel' => 'stylesheet');
				}
			break;
		}
	}

	/**
	 * Open HTML tag and add attributes.
	 *
	 * @param   string  $tagName  Tag name
	 * @param   array   $attrs    Attributes
	 *
	 * @return  string
	 */
	public static function openTag($tagName, $attrs = array())
	{
		self::W3CValid($tagName, $attrs);

		$openTag = '<' . $tagName . ' ';

		if (count($attrs))
		{
			foreach ($attrs AS $key => $val)
			{
				$openTag .= $key . '="' . $val . '" ';
			}
		}

		return $openTag . '>';
	}

	/**
	 * Close HTML tag.
	 *
	 * @param   string  $tagName  Tag name
	 *
	 * @return  string
	 */
	public static function closeTag($tagName)
	{
		$tagName = strtolower(trim($tagName));

		return '</' . $tagName . '>';
	}

	/**
	 * Add an input tag and attributes.
	 *
	 * @param   string  $type   Input type
	 * @param   array   $attrs  Attributes
	 *
	 * @return  string
	 */
	public static function addInputTag($type, $attrs = array())
	{
		$tagName = 'input';

		self::W3CValid($tagName, $attrs);

		$inputTag = '<' . $tagName . ' type="' . $type . '" ';

		if (count($attrs))
		{
			foreach ($attrs AS $key => $val)
			{
				$inputTag .= $key . '="' . $val . '" ';
			}
		}

		return $inputTag . ' />';
	}

	/**
	 * Add an single HTML tag. <br />, <hr />,
	 *
	 * @param   string  $tagName  Tag name
	 * @param   array   $attrs    Attributes
	 *
	 * @return  string
	 */
	public static function addSingleTag($tagName, $attrs)
	{
		self::W3CValid($tagName, $attrs);

		$singleTag = '<' . $tagName . ' ';

		if (count($attrs))
		{
			foreach ($attrs AS $key => $val)
			{
				$singleTag .= $key . '="' . $val . '" ';
			}
		}

		return $singleTag . '/>';
	}

	/**
	 * Make an html select dropdown list
	 *
	 * @param   string  $items  Items for dropdown list generation.
	 * @param   array   $attrs  Attributes
	 *
	 * @return  void
	 */
	public static function makeDropDownList($items, $attrs = array())
	{
		$HTML  = self::openTag('select', $attrs);

		for ($i = 0; $i < count($items); $i++)
		{
			$HTML .= self::openTag('option', array('value' => $items[$i]->value)) . $items[$i]->text . self::closeTag('option');
		}

		$HTML .= self::closeTag('select');

		return $HTML;
	}

   /**
	 * Return javascript tag.
	 *
	 * @param   string  $base_url  The base url.
	 * @param   string  $filename  Script file name.
	 * @param   string  $code      Javascript code.
	 *
	 * @return  string
	 */
	public static function addCustomScript($base_url = '', $filename = '', $code = '')
	{
		$tagName = 'script';

		if ($code)
		{
			return self::openTag($tagName, array('type' => 'text/javascript')) . $code . self::closeTag($tagName);
		}
		else
		{
			return self::openTag($tagName, array('src' => $base_url . $filename, 'type' => 'text/javascript')) . self::closeTag($tagName);
		}
	}

	/**
	 * Return style tag and add css file to your page.
	 *
	 * @param   string  $base_url  The base url.
	 * @param   string  $filename  Stylesheet file name.
	 * @param   string  $code      CSS code.
	 *
	 * @return  string
	 */
	public static function addCustomStyle($base_url = '', $filename = '', $code = '')
	{
		if ($code)
		{
			$tagName = 'style';

			return self::openTag($tagName, array('type' => 'text/css')) . $code . self::closeTag($tagName);
		}
		else
		{
			$tagName = 'link';

			return self::addSingleTag($tagName, array('href' => $base_url . $filename, 'type' => 'text/css', 'rel' => 'stylesheet'));
		}
	}

	/**
	 * Load edition manager.
	 *
	 * @param   string  $ext     Extension's element name.
	 * @param   string  $script  Script file to load.
	 *
	 * @return  void
	 */
	public static function loadEditionManager($ext, $script)
	{
		// Verify extension.
		if ( empty($ext) )
		{
			$ext = JFactory::getApplication()->input->getCmd('option');
		}

		if ( ! in_array($ext, JSNVersion::$products) )
		{
			return;
		}

		// Generate URL to the assets directory.
		$base = JUri::root() . 'plugins/system/jsnframework/assets';

		// Load Bootstrap 3.
		JSNHtmlAsset::addStyle("{$base}/3rd-party/bootstrap/bootstrap.min.css");
		JSNHtmlAsset::addStyle("{$base}/3rd-party/bootstrap/bootstrap-theme.min.css");

		// Load FontAwesome.
		JSNHtmlAsset::addStyle("{$base}/3rd-party/font-awesome/css/font-awesome.min.css");

		// Load modal stylesheet.
		JSNHtmlAsset::addStyle("{$base}/joomlashine/css/libs/common/modal.css");

		// Load Base64 library.
		JSNHtmlAsset::addScript("{$base}/3rd-party/base64/base64.min.js");

		// Load edition manager.
		JSNHtmlAsset::loadScript(
			$script,
			array(
				'url' => JRoute::_('index.php?option=com_ajax&format=json&plugin=jsnframework&context=account', false),
				'token' => JSession::getFormToken(),
				'extension' => $ext,
				'textMapping' => JSNUtilsLanguage::getTranslated( array(
					strtoupper( substr($ext, 4) ),

					'JSN_EXTFW_USERNAME',
					'JSN_EXTFW_PASSWORD',
					'JSN_EXTFW_GENERAL_CLOSE',

					'JSN_EXTFW_USER_VERIFICATION_TITLE',
					'JSN_EXTFW_USER_VERIFICATION_SELECT_EXISTING_ACCOUNT',
					'JSN_EXTFW_USER_VERIFICATION_USE_ANOTHER_ACCOUNT',
					'JSN_EXTFW_USER_VERIFICATION_INPUT_CUSTOMER_ACCOUNT',
					'JSN_EXTFW_USER_VERIFICATION_ONE_TIME_REQUIREMENT',
					'JSN_EXTFW_USER_VERIFICATION_FORGOT_ACCOUNT',
					'JSN_EXTFW_USER_VERIFICATION_VERIFY_BUTTON',
					'JSN_EXTFW_USER_VERIFICATION_CANCEL_BUTTON',

					'JSN_EXTFW_PRODUCT_VERIFICATION_TITLE',
					'JSN_EXTFW_PRODUCT_VERIFICATION_FREE_EDITION',
					'JSN_EXTFW_PRODUCT_VERIFICATION_GOT_IT',
					'JSN_EXTFW_PRODUCT_VERIFICATION_ALL_DONE',
					'JSN_EXTFW_PRODUCT_VERIFICATION_INTRODUCTION',
					'JSN_EXTFW_PRODUCT_VERIFICATION_EDITION',
					'JSN_EXTFW_PRODUCT_VERIFICATION_EXPIRATION',
					'JSN_EXTFW_PRODUCT_VERIFICATION_NEVER_EXPIRE',
					'JSN_EXTFW_PRODUCT_VERIFICATION_THANK_YOU',
					'JSN_EXTFW_PRODUCT_VERIFICATION_LETS_GET_STARTED',

					'JSN_EXTFW_PRO_INTRODUCTION_TITLE',
					'JSN_EXTFW_PRO_INTRODUCTION_MESSAGE',
					'JSN_EXTFW_PRO_INTRODUCTION_TRY_BUTTON',
					'JSN_EXTFW_PRO_INTRODUCTION_BUY_BUTTON',

					'JSN_EXTFW_TRIAL_REGISTRATION_DONE_TITLE',
					'JSN_EXTFW_TRIAL_REGISTRATION_DONE_MESSAGE',
					'JSN_EXTFW_TRIAL_REGISTRATION_DONE_BUTTON',

					'JSN_EXTFW_TRIAL_REGISTRATION_FAIL_TITLE',
					'JSN_EXTFW_TRIAL_REGISTRATION_FAIL_BUTTON'
				) ),
				'forgotUsername' => 'https://www.joomlashine.com/username-reminder-request.html',
				'forgotPassword' => 'https://www.joomlashine.com/password-reset.html'
			)
		);
	}
}
