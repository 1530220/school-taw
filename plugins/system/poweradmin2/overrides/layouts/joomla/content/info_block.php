<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$blockPosition = $displayData['params']->get('info_block_position', 0);

?>
	<dl class="article-info muted">

		<?php if ($displayData['position'] === 'above' && ($blockPosition == 0 || $blockPosition == 2)
				|| $displayData['position'] === 'below' && ($blockPosition == 1)
				) : ?>

			<dt class="article-info-term" data-option data-visibility="info_block_show_title" data-visibility-value="<?php echo $displayData['params']->get('info_block_show_title', 1); ?>">
				<?php //if ($displayData['params']->get('info_block_show_title', 1)) : ?>
					<?php echo JText::_('COM_CONTENT_ARTICLE_INFO'); ?>
				<?php //endif; ?>
			</dt>

			<?php //if ($displayData['params']->get('show_author') && !empty($displayData['item']->author )) : ?>
				<?php echo preg_replace(
					'#^<([^\s]+)([^>]*)>#',
					'<\\1\\2 data-option data-visibility="show_author" data-visibility-value="' . $displayData['params']->get('show_author') . '">',
					trim( $this->sublayout('author', $displayData) )
				); ?>
			<?php //endif; ?>

			<?php //if ($displayData['params']->get('show_parent_category') && !empty($displayData['item']->parent_slug)) : ?>
				<?php echo preg_replace(
					'#^<([^s]+)([^>]*)>#',
					'<\\1\\2 data-option data-visibility="show_parent_category" data-visibility-value="' . $displayData['params']->get('show_parent_category') . '">',
					trim( $this->sublayout('parent_category', $displayData) )
				); ?>
			<?php //endif; ?>

			<?php //if ($displayData['params']->get('show_category')) : ?>
				<?php echo preg_replace(
					'#^<([^s]+)([^>]*)>#',
					'<\\1\\2 data-option data-visibility="show_category" data-visibility-value="' . $displayData['params']->get('show_category') . '">',
					trim( $this->sublayout('category', $displayData) )
				); ?>
			<?php //endif; ?>

			<?php //if ($displayData['params']->get('show_associations')) : ?>
				<?php echo preg_replace(
					'#^<([^s]+)([^>]*)>#',
					'<\\1\\2 data-option data-visibility="show_associations" data-visibility-value="' . $displayData['params']->get('show_associations') . '">',
					trim( $this->sublayout('associations', $displayData) )
				); ?>
			<?php //endif; ?>

			<?php //if ($displayData['params']->get('show_publish_date')) : ?>
				<?php echo preg_replace(
					'#^<([^s]+)([^>]*)>#',
					'<\\1\\2 data-option data-visibility="show_publish_date" data-visibility-value="' . $displayData['params']->get('show_publish_date') . '">',
					trim( $this->sublayout('publish_date', $displayData) )
				); ?>
			<?php //endif; ?>

		<?php endif; ?>

		<?php if ($displayData['position'] === 'above' && ($blockPosition == 0)
				|| $displayData['position'] === 'below' && ($blockPosition == 1 || $blockPosition == 2)
				) : ?>
			<?php //if ($displayData['params']->get('show_create_date')) : ?>
				<?php echo preg_replace(
					'#^<([^s]+)([^>]*)>#',
					'<\\1\\2 data-option data-visibility="show_create_date" data-visibility-value="' . $displayData['params']->get('show_create_date') . '">',
					trim( $this->sublayout('create_date', $displayData) )
				); ?>
			<?php //endif; ?>

			<?php //if ($displayData['params']->get('show_modify_date')) : ?>
				<?php echo preg_replace(
					'#^<([^s]+)([^>]*)>#',
					'<\\1\\2 data-option data-visibility="show_modify_date" data-visibility-value="' . $displayData['params']->get('show_modify_date') . '">',
					trim( $this->sublayout('modify_date', $displayData) )
				); ?>
			<?php //endif; ?>

			<?php //if ($displayData['params']->get('show_hits')) : ?>
				<?php echo preg_replace(
					'#^<([^s]+)([^>]*)>#',
					'<\\1\\2 data-option data-visibility="show_hits" data-visibility-value="' . $displayData['params']->get('show_hits') . '">',
					trim( $this->sublayout('hits', $displayData) )
				); ?>
			<?php //endif; ?>
		<?php endif; ?>
	</dl>
