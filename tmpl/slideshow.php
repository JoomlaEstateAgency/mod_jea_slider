<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_jea_slider
 * @copyright   Copyright (C) 2007-2012 PHILIP Sylvain. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die();

$document = JFactory::getDocument();

JHtml::stylesheet('modules/mod_jea_slider/assets/slideshow.css');
JHtml::_('behavior.framework');
JHtml::script('modules/mod_jea_slider/assets/slideitmoo.js');

$charset = strtoupper($document->getCharset());
$image_width = (int) $params->get('image_width', 600);
$image_height = (int) $params->get('image_height', 300);
$autoplay = (int) $params->get('autoplay', 7000);
$show_controls = (int) $params->get('show_controls', 1);
$fxTransition = $params->get('transition_effect', 'Linear');

$document->addScriptDeclaration("
window.addEvents({
	'domready': function(){

		var slideMoo = new SlideItMoo({
			overallContainer : 'jeaslider_outer_{$uid}',
			elementScrolled : 'jeaslider_inner_{$uid}',
			thumbsContainer : 'jeaslider_items_{$uid}',
			itemsVisible : 1,
			elemsSlide : 1,
			duration : " . $params->get('duration', 300) . ",
			transition: Fx.Transitions.{$fxTransition},
			itemsSelector: '.slideshow_element',
			itemWidth : {$image_width},
			showControls: 0, // deactivate the default behavior
			startIndex:1,
			autoSlide: {$autoplay},
			navs:{
				fwd:'.slideshow_forward', /* forward button CSS selector */
				bk:'.slideshow_back' /* back button CSS selector */
			},
			onChange: function(index){
			}
		});

		if (slideMoo.elements.length > 1 && {$show_controls}) {
			var fwd = document.id('jeaslider_outer_{$uid}').getElement('.slideshow_forward');
			var bkwd = document.id('jeaslider_outer_{$uid}').getElement('.slideshow_back');
			fwd.addEvent('click', slideMoo.slide.pass(1, slideMoo));
			bkwd.addEvent('click', slideMoo.slide.pass(-1, slideMoo));

			if ({$show_controls} == 2) {
				fwd.setStyle('opacity', 0);
				bkwd.setStyle('opacity', 0);
				document.id('jeaslider_outer_{$uid}').addEvent('mouseenter', function(event){
					fwd.morph({'opacity':[0,1], duration:500});
					bkwd.morph({'opacity':[0,1], duration:400});
				});

				document.id('jeaslider_outer_{$uid}').addEvent('mouseleave', function(event){
					fwd.morph({'opacity':[1,0], duration:1000});
					bkwd.morph({'opacity':[1,0], duration:1000});
				});
			}
		}
	}
});");

$document->addStyleDeclaration("
#jeaslider_outer_{$uid} div.slideshow_element {
	margin: 0;
	width: {$image_width}px;
	height: {$image_height}px;
	background: #000;
}

#jeaslider_outer_{$uid} div.infos {
	/* 10px => 2 x 5px paddings */
	width: ".($image_width - 10)."px;
}
");

?>

<div id="jeaslider_outer_<?php echo $uid ?>" class="slideshow_outer<?php echo $params->get('moduleclass_sfx') ?>">

  <?php if ($show_controls): ?>
  <div class="slideshow_back"></div>
  <?php endif ?>

  <div id="jeaslider_inner_<?php echo $uid ?>" class="slideshow_inner">
    <div id="jeaslider_items_<?php echo $uid ?>" class="slideshow_items">
    <?php foreach ($rows as $k => $row) : $url = modJeaSliderHelper::getPropertyRoute($row) ?>
      <div class="slideshow_element">
        <?php if ($imgUrl = modJeaSliderHelper::getItemImg($row)): ?>
        <a href="<?php echo $url ?>" title="<?php echo JText::_('COM_JEA_DETAIL') ?>" class="image">
          <img src="<?php echo $imgUrl ?>" alt="<?php echo JText::_('COM_JEA_DETAIL') ?>" />
        </a>
        <?php endif ?>

        <?php if ($row->slogan): ?>
        <span class="slogan"><?php echo htmlspecialchars($row->slogan, ENT_COMPAT, $charset) ?> </span>
        <?php endif ?>

        <div class="infos">
          <a href="<?php echo $url ?>" title="<?php echo JText::_('COM_JEA_DETAIL') ?>" class="title">
          <?php if (empty($row->title)) {
                echo JText::sprintf('COM_JEA_PROPERTY_TYPE_IN_TOWN',
                htmlspecialchars($row->type, ENT_COMPAT, $charset),
                htmlspecialchars($row->town, ENT_COMPAT, $charset));
            } else {
                echo htmlspecialchars($row->title, ENT_COMPAT, $charset);
            } ?></a>

          <?php if ($params->get('show_price', 1)) :?>
          <span class="price">
            <strong><?php echo JHtml::_('utility.formatPrice', (float) $row->price , JText::_('COM_JEA_CONSULT_US') ) ?></strong>
            <?php if ($row->transaction_type == 'RENTING' && (float)$row->price != 0.0) echo JText::_('COM_JEA_PRICE_PER_FREQUENCY_'. $row->rate_frequency) ?>
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
  <?php if ($show_controls): ?>
  <div class="slideshow_forward"></div>
  <?php endif ?>
</div>

