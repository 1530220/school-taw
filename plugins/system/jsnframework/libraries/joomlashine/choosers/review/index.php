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

// Get Joomla root directory
$jRoot = realpath(__DIR__ . '/../../../../../../../');

define('_JEXEC', 1);

if ( file_exists("{$jRoot}/administrator/defines.php") )
{
	include_once "{$jRoot}/administrator/defines.php";
}

if ( ! defined('_JDEFINES') )
{
	define('JPATH_BASE', "{$jRoot}/administrator");

	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_BASE . '/includes/helper.php';

// Instantiate the application
$app = JFactory::getApplication('administrator');

// Access check
if ( ! JFactory::getUser()->authorise('core.manage', $app->input->getCmd('component')))
{
	jexit('Please login to administration panel first!');
}

// Import necessary Joomla libraries
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

// Get Joomla version
$JVersion = new JVersion;

// Initialize JSN Framework
require_once JPATH_ROOT . '/plugins/system/jsnframework/jsnframework.php';

$dispatcher		= version_compare($JVersion->RELEASE, '3.0', '<') ? JDispatcher::getInstance() : JEventDispatcher::getInstance();
$jsnframework	= new PlgSystemJSNFramework($dispatcher);

$jsnframework->onAfterInitialise();

// Get necessary variables
$component = $app->input->get('component');
$status = $app->input->get('status');

// Validate required variable
! empty($component) OR die(JText::_('JSN_EXTFW_CHOOSERS_REVIEW_ON_JED_MISSING_VAR'));

// Define some common constants
define('JPATH_COMPONENT', JPATH_ROOT . '/administrator/components/' . $component);
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_COMPONENT);

// Load component's constant definition file
file_exists($define = JPATH_COMPONENT_ADMINISTRATOR . '/' . substr($component, 4) . '.defines.php')
	OR file_exists($define = JPATH_COMPONENT_ADMINISTRATOR . '/defines.' . substr($component, 4) . '.php')
	OR $define = null;

$define == null OR require_once $define;

// Get product configuration
$config = JSNConfigHelper::get($component);
$token =  JSession::getFormToken();

// Execute requested task
if ($app->input->getCmd('task') == 'switch')
{
	JSession::checkToken('get') or die( 'Invalid Token' );

	// Set 'option' variable to request
	$app->input->set('option', $component);

	// Get config model
	$model = new JSNConfigModel;

	// Turn on/off periodical popup asking for review
	$form = $model->getForm(array(), true, JPATH_ROOT . '/administrator/components/' . $component . '/config.xml');
	$data = array('review_popup' => (int) $status);

	try
	{
		// Save new configuration
		$model->save($form, $data);
	}
	catch (Exception $e)
	{
		// Do nothing as this is a background process
		jexit($e->getMessage());
	}

	jexit('SUCCESS');
}
else
{
	// Get product info
	$product = JSNUtilsXml::loadManifestCache($component, 'component');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo JText::_('JSN_EXTFW_CHOOSERS_REVIEW_ON_JED'); ?></title>
	<meta name="author" content="JoomlaShine Team">
	<?php
	if (JSNVersion::isJoomlaCompatible('3.0'))
	{
	?>
	<link href="../../../../../../../media/jui/css/bootstrap.min.css" rel="stylesheet" />
	<?php
	}
	else
	{
	?>
	<link href="../../../../../../../plugins/system/jsnframework/assets/3rd-party/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<?php
	}
	?>
	<link href="../../../../../../../plugins/system/jsnframework/assets/joomlashine/css/jsn-gui.css" rel="stylesheet" />
	<!-- Load HTML5 elements support for IE6-8 -->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<?php
	if (JSNVersion::isJoomlaCompatible('3.0'))
	{
	?>
	<script src="../../../../../../../<?php echo (JSNVersion::isJoomlaCompatible('3.2') ? 'plugins/system/jsnframework/assets/3rd-party/jquery/jquery.min.js' : 'media/jui/js/jquery.js'); ?>"></script>
	<?php
	}
	else
	{
	?>
	<script src="../../../../../../../plugins/system/jsnframework/assets/3rd-party/jquery/jquery-1.7.1.min.js"></script>
	<?php
	}
	?>
	<style type="text/css">
		#jsn-ask-for-review {
			padding: 9px 12px;
		}
		#jsn-ask-for-review .form-actions {
			margin: 0;
		}
		#jsn-review-on-jed-switcher {
			float: none;
			vertical-align: top;
		}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			var switcher = document.getElementById('jsn-review-on-jed-switcher'),
				root_url = '<?php echo JUri::root(); ?>';

			// Setup event handler to switch the popup on/off
			$(switcher).bind('change', function(event) {
				if (event.target.nodeName == 'INPUT') {
					if (root_url.indexOf('/review/') < 0) {
						root_url += 'review/';
					}

					$.ajax(root_url + 'index.php?task=switch&component=<?php echo $component; ?>&<?php echo $token; ?>=1&status=' + (switcher.checked ? 0 : 1));
				}
			});
		});
	</script>
</head>

<body class="jsn-master">
	<div class="jsn-bootstrap">
		<div id="jsn-ask-for-review">
<?php
echo JText::sprintf('JSN_EXTFW_CHOOSERS_REVIEW_ON_JED_MESSAGE', preg_replace('/JSN\s*/i', '', JText::_($product->name)));
?>
			<div class="form-actions">
				<p><a class="btn btn-primary" href="<?php echo JSNUtilsText::getConstant('REVIEW_LINK', $component); ?>" target="_blank" rel="noopener noreferrer"><?php echo JText::_('JSN_EXTFW_CHOOSERS_REVIEW_ON_JED'); ?></a></p>
				<div class="control-group">
					<label class="checkbox" for="jsn-review-on-jed-switcher">
						<input type="checkbox" value="1" name="status" id="jsn-review-on-jed-switcher"<?php echo $config->get('review_popup') ? '' : ' checked="checked"'; ?> />
						<span><?php echo JText::_('JSN_EXTFW_CHOOSERS_REVIEW_ON_JED_DISABLE'); ?></span>
					</label>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
