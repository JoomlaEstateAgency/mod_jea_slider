<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_jea_slider
 * @copyright   Copyright (C) 2008-2020 PHILIP Sylvain. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @var $uid string
 * @var $rows stdClass[]
 * @var $module stdClass
 * @var $params Joomla\Registry\Registry
 */

$document = JFactory::getDocument();

JHtml::stylesheet('mod_jea_slider/slick.css', array('relative' => true));
JHtml::stylesheet('mod_jea_slider/slick-theme.css', array('relative' => true));
JHtml::stylesheet('mod_jea_slider/mod_jea_slider.css', array('relative' => true));

JHtml::_('jquery.framework');
JHtml::script('mod_jea_slider/slick.min.js', array('relative' => true));

$charset = strtoupper($document->getCharset());
$autoplay = (int) $params->get('autoplay', 7000);
$showArrows = (int) $params->get('show_arrows', 1);

$slickOptions = array(
	'arrows' => $showArrows > 0 ? true : false,
	'dots'=> $params->get('show_dots', 1) ? true : false,
	'infinite'=> true,
	'speed' => (int) $params->get('duration', 300),
	'slidesToShow' => (int) $params->get('visible_items', 3),
	'slidesToScroll' => (int) $params->get('slide_items', 3),
	'adaptiveHeight' => true,
	'cssEase' => $params->get('transition_effect', 'ease'),
	'autoplay' => $autoplay > 0 ? true : false,
	'autoplaySpeed' => $autoplay,
	'fade' => $params->get('fade', 0) ? true : false,
	'vertical' => $params->get('vertical', 0) ? true : false,
	'verticalSwiping' => $params->get('vertical', 0) ? true : false,
);

$type = 'horizontal';

if ($slickOptions['slidesToShow'] == 1)
{
	$type = 'slideshow';
}

if ($slickOptions['vertical'])
{
	$type = 'vertical';
}

$showArrowsScript = '';

if ($showArrows == 2)
{
	$showArrowsScript = <<<JS

	$('#jea-slider-{$module->id} .slick-arrow').css('opacity', 0)
	
	$('#jea-slider-{$module->id}').on('mouseenter', function() {
		$('#jea-slider-{$module->id} .slick-arrow').show().css({
			'opacity': 100,
			'transition': 'opacity 0.5s linear'
		});
	});
	
	$('#jea-slider-{$module->id}').on('mouseleave', function() {
		$('#jea-slider-{$module->id} .slick-arrow').css('opacity', 0);
	});
JS;
}

$options = json_encode($slickOptions, JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);

$script = <<<JS
jQuery(function($) {
	$('#jea-slider-{$module->id} .jea-slider-items').slick($options);
	$showArrowsScript
});
JS;

$document->addScriptDeclaration($script);

if ($margin = $params->get('slide_margin', 0)) {

	$margin = $slickOptions['vertical'] ? $margin . 'px 0' : '0 ' . $margin . 'px';

	$document->addStyleDeclaration("
		#jea-slider-{$module->id} .jea-slider-item {
			padding: {$margin};
		}
	");
}
?>
<div id="jea-slider-<?php echo $module->id ?>" class="jea-slider <?php echo $type . ' ' . $params->get('moduleclass_sfx') ?>">
	<div class="jea-slider-items">
		<?php foreach ($rows as $row) : $url = modJeaSliderHelper::getPropertyRoute($row) ?>
		<div class="jea-slider-item">
			<?php if ($row->slogan): ?>
			<span class="slogan"><?php echo htmlspecialchars($row->slogan, ENT_COMPAT, $charset) ?> </span>
			<?php endif ?>

			<?php if ($imgUrl = modJeaSliderHelper::getItemImg($row)): ?>
			<a href="<?php echo $url ?>" title="<?php echo JText::_('COM_JEA_DETAIL') ?>" class="image">
				<img src="<?php echo $imgUrl ?>" alt="<?php echo JText::_('COM_JEA_DETAIL') ?>" />
			</a>
			<?php endif ?>

			<div class="infos">

				<a href="<?php echo $url ?>" title="<?php echo JText::_('COM_JEA_DETAIL') ?>" class="title">
				<?php echo empty($row->title) ?
					JText::sprintf('COM_JEA_PROPERTY_TYPE_IN_TOWN',
						htmlspecialchars($row->type, ENT_COMPAT, $charset),
						htmlspecialchars($row->town, ENT_COMPAT, $charset)
					) :
					htmlspecialchars($row->title, ENT_COMPAT, $charset);
				?></a>

				<?php if ($params->get('show_price', 1)) :?>
				<span class="price">
				<strong><?php echo JHtml::_('utility.formatPrice', (float) $row->price , JText::_('COM_JEA_CONSULT_US') ) ?></strong>
				<?php if ($row->transaction_type == 'RENTING' && (float)$row->price != 0.0)
					echo JText::_('COM_JEA_PRICE_PER_FREQUENCY_'. $row->rate_frequency) ?>
				</span>
				<?php endif ?>

				<?php if ($params->get('show_surfaces', 0) || $params->get('show_amenities', 0)) :?>
				<div class="details">
					<?php if ($params->get('show_surfaces', 0)) :?>
					<?php if (!empty($row->living_space)): ?>
					<?php echo JText::_('COM_JEA_FIELD_LIVING_SPACE_LABEL') ?> :
					<strong><?php echo JHtml::_('utility.formatSurface', (float) $row->living_space , '-' ) ?></strong><br />
					<?php endif ?>

					<?php if (!empty($row->land_space)): ?>
					<?php echo JText::_('COM_JEA_FIELD_LAND_SPACE_LABEL') ?> : <strong>
					<?php echo JHtml::_('utility.formatSurface', (float) $row->land_space , '-' ) ?></strong><br />
					<?php endif ?>

					<?php endif ?>

					<?php if ($params->get('show_amenities', 0) && !empty($row->amenities)) : ?>
					<strong><?php echo JText::_('COM_JEA_AMENITIES') ?> : </strong>
					<?php echo JHtml::_('amenities.bindList', $row->amenities) ?>
					<?php endif ?>
				</div>
				<?php endif ?>

				<?php if ($params->get('show_description', 1)) :?>
				<div class="description">
				<?php echo $row->description ?>
				</div>
				<?php endif ?>
			</div>

		</div>
	<?php endforeach ?>
	</div>
</div>
