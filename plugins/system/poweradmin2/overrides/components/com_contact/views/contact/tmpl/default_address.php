<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Marker_class: Class based on the selection of text, none, or icons
 * jicon-text, jicon-none, jicon-icon
 */
?>
<dl class="contact-address dl-horizontal" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
	<?php if (($this->params->get('address_check') > 0) &&
		($this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>
		<dt>
			<span class="<?php echo $this->params->get('marker_class'); ?>">
				<?php echo $this->params->get('marker_address'); ?>
			</span>
		</dt>

		<?php if ($this->contact->address/* && $this->params->get('show_street_address')*/) : ?>
		<dd data-option data-visibility="show_street_address" data-visibility-value="<?php echo $this->params->get('show_street_address'); ?>">
			<span class="contact-street" itemprop="streetAddress">
				<?php echo nl2br($this->contact->address); ?>
				<br />
			</span>
		</dd>
		<?php endif; ?>

		<?php if ($this->contact->suburb/* && $this->params->get('show_suburb')*/) : ?>
		<dd data-option data-visibility="show_suburb" data-visibility-value="<?php echo $this->params->get('show_suburb'); ?>">
			<span class="contact-suburb" itemprop="addressLocality">
				<?php echo $this->contact->suburb; ?>
				<br />
			</span>
		</dd>
		<?php endif; ?>
		<?php if ($this->contact->state/* && $this->params->get('show_state')*/) : ?>
		<dd data-option data-visibility="show_state" data-visibility-value="<?php echo $this->params->get('show_state'); ?>">
			<span class="contact-state" itemprop="addressRegion">
				<?php echo $this->contact->state; ?>
				<br />
			</span>
		</dd>
		<?php endif; ?>
		<?php if ($this->contact->postcode/* && $this->params->get('show_postcode')*/) : ?>
		<dd data-option data-visibility="show_postcode" data-visibility-value="<?php echo $this->params->get('show_postcode'); ?>">
			<span class="contact-postcode" itemprop="postalCode">
				<?php echo $this->contact->postcode; ?>
				<br />
			</span>
		</dd>
		<?php endif; ?>
		<?php if ($this->contact->country/* && $this->params->get('show_country')*/) : ?>
		<dd data-option data-visibility="show_country" data-visibility-value="<?php echo $this->params->get('show_country'); ?>">
			<span class="contact-country" itemprop="addressCountry">
				<?php echo $this->contact->country; ?>
				<br />
			</span>
		</dd>
		<?php endif; ?>
	<?php endif; ?>

<?php if ($this->contact->email_to/* && $this->params->get('show_email')*/) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" itemprop="email">
			<?php echo nl2br($this->params->get('marker_email')); ?>
		</span>
	</dt>
	<dd data-option data-visibility="show_email" data-visibility-value="<?php echo $this->params->get('show_email'); ?>">
		<span class="contact-emailto">
			<?php echo $this->contact->email_to; ?>
		</span>
	</dd>
<?php endif; ?>

<?php if ($this->contact->telephone/* && $this->params->get('show_telephone')*/) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_telephone'); ?>
		</span>
	</dt>
	<dd data-option data-visibility="show_telephone" data-visibility-value="<?php echo $this->params->get('show_telephone'); ?>">
		<span class="contact-telephone" itemprop="telephone">
			<?php echo $this->contact->telephone; ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->fax/* && $this->params->get('show_fax')*/) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_fax'); ?>
		</span>
	</dt>
	<dd data-option data-visibility="show_fax" data-visibility-value="<?php echo $this->params->get('show_fax'); ?>">
		<span class="contact-fax" itemprop="faxNumber">
		<?php echo $this->contact->fax; ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->mobile/* && $this->params->get('show_mobile')*/) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_mobile'); ?>
		</span>
	</dt>
	<dd data-option data-visibility="show_mobile" data-visibility-value="<?php echo $this->params->get('show_mobile'); ?>">
		<span class="contact-mobile" itemprop="telephone">
			<?php echo $this->contact->mobile; ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->webpage/* && $this->params->get('show_webpage')*/) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
		</span>
	</dt>
	<dd data-option data-visibility="show_webpage" data-visibility-value="<?php echo $this->params->get('show_webpage'); ?>">
		<span class="contact-webpage">
			<a href="<?php echo $this->contact->webpage; ?>" target="_blank" rel="noopener noreferrer" itemprop="url">
			<?php echo JStringPunycode::urlToUTF8($this->contact->webpage); ?></a>
		</span>
	</dd>
<?php endif; ?>
</dl>
