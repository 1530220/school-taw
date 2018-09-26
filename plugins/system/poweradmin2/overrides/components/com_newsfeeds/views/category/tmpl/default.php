<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JHtml::_('behavior.caption');
JHtml::_('formbehavior.chosen', 'select');

$pageClass = $this->params->get('pageclass_sfx');
?>
<div class="newsfeed-category<?php echo $this->pageclass_sfx; ?>">
	<?php //if ($this->params->get('show_page_heading')) : ?>
		<h1 data-option data-content data-visibility="show_page_heading" data-visibility-value="<?php echo $this->params->get('show_page_heading'); ?>">
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php //endif; ?>
	<?php //if ($this->params->get('show_category_title', 1)) : ?>
		<h2 data-option data-visibility="show_category_title" data-visibility-value="<?php echo $this->params->get('show_category_title'); ?>">
			<?php echo JHtml::_('content.prepare', $this->category->title, '', 'com_newsfeeds.category.title'); ?>
		</h2>
	<?php //endif; ?>

	<?php if (/*$this->params->get('show_tags', 1) && */!empty($this->category->tags->itemTags)) : ?>
		<?php $this->category->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
		<?php echo preg_replace(
			'#^<([^\s]+)([^>]*)>#',
			'<\\1\\2 data-option data-visibility="show_tags" data-visibility-value="' . $this->params->get('show_tags', 1) . '">',
			trim( $this->category->tagLayout->render($this->category->tags->itemTags) )
		); ?>
	<?php endif; ?>

	<?php //if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
		<div class="category-desc">
			<?php if (/*$this->params->get('show_description_image') && */$this->category->getParams()->get('image')) : ?>
			<div data-option data-visibility="show_description_image" data-visibility-value="<?php echo $this->params->get('show_description_image'); ?>">
				<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
			</div>
			<?php endif; ?>
			<?php if (/*$this->params->get('show_description') && */$this->category->description) : ?>
			<div data-option data-visibility="show_description" data-visibility-value="<?php echo $this->params->get('show_description'); ?>">
				<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_newsfeeds.category'); ?>
			</div>
			<?php endif; ?>
			<div class="clr"></div>
		</div>
	<?php //endif; ?>

	<?php echo $this->loadTemplate('items'); ?>

	<?php if ($this->maxLevel != 0 && !empty($this->children[$this->category->id])) : ?>
		<div class="cat-children">
			<h3><?php echo JText::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
			<?php echo $this->loadTemplate('children'); ?>
		</div>
	<?php endif; ?>
</div>
