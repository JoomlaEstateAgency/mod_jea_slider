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

JHtml::stylesheet('modules/mod_jea_slider/assets/vertical-carousel.css');
JHtml::_('behavior.framework');
JHtml::script('modules/mod_jea_slider/assets/slideitmoo.js');

$charset = strtoupper($document->getCharset());
$image_width = $params->get('image_width', 164);
$image_height = $params->get('image_height', 123);
$slide_margin = $params->get('slide_margin', 10);
$description_height = 80;
$item_height = $image_height + $description_height+ $slide_margin*2;
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
      itemsVisible : " . $params->get('visible_items', 4) . ",
      elemsSlide : " . $params->get('slide_items', 3) . ",
      duration : " . $params->get('duration', 100) . ",
      transition: Fx.Transitions.{$fxTransition},
      itemsSelector: '.vertical_slider_element',
      itemWidth : {$image_width},
      itemHeight : {$item_height},
      showControls: {$show_controls},
      startIndex:1,
      direction:1,
      slideVertical: true,
      autoSlide: {$autoplay},
      navs:{
        fwd:'.vertical_slider_back', /* forward button CSS selector */
        bk:'.vertical_slider_forward' /* back button CSS selector */
      },
      onChange: function(index){
      }
    });

    if ({$show_controls} == 2) {
      var fwd = document.id('jeaslider_outer_{$uid}').getElement('.vertical_slider_forward');
      var bkwd = document.id('jeaslider_outer_{$uid}').getElement('.vertical_slider_back');

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
});");

$document->addStyleDeclaration("
#jeaslider_outer_{$uid} {
	width: {$image_width}px;
}

#jeaslider_inner_{$uid} {
	width: {$image_width}px;
}

#jeaslider_outer_{$uid} div.vertical_slider_element {
  margin: {$slide_margin}px 0px;
  height: {$item_height}px;
  width: {$image_width}px;
}
");

?>

<div id="jeaslider_outer_<?php echo $uid ?>" class="vertical_slider_outer<?php echo $params->get('moduleclass_sfx') ?>">
  <?php if ($show_controls): ?>
  <div class="vertical_slider_back"></div>
  <?php endif ?>

  <div id="jeaslider_inner_<?php echo $uid ?>" class="vertical_slider_inner">
    <div id="jeaslider_items_<?php echo $uid ?>" class="vertical_slider_items">
    <?php foreach ($rows as $k => $row) : $url = modJeaSliderHelper::getPropertyRoute($row) ?>
    <?php
        if (empty($row->title)) {
            $title = JText::sprintf('COM_JEA_PROPERTY_TYPE_IN_TOWN', htmlspecialchars($row->type, ENT_COMPAT, $charset), htmlspecialchars($row->town, ENT_COMPAT, $charset));
        } else {
            $title = htmlspecialchars($row->title, ENT_COMPAT, $charset);
        } ?>
      <div class="vertical_slider_element">
        <?php if ($imgUrl = modJeaSliderHelper::getItemImg($row)): ?>
        <a href="<?php echo $url ?>" title="<?php echo $title ?>" class="image">
          <img src="<?php echo $imgUrl ?>" alt="<?php echo JText::_('COM_JEA_DETAIL') ?>" />
        </a>
        <?php endif ?>

        <a href="<?php echo $url ?>" title="<?php echo JText::_('COM_JEA_DETAIL') ?>"><?php echo $title ?></a>

        <?php if ($row->slogan): ?>
        <span class="slogan"><?php echo htmlspecialchars($row->slogan, ENT_COMPAT, $charset) ?> </span>
        <?php endif ?>

        <?php if ($params->get('show_price', 1)) :?>
        <?php echo $row->transaction_type == 'RENTING' ? JText::_('COM_JEA_FIELD_PRICE_RENT_LABEL') :  JText::_('COM_JEA_FIELD_PRICE_LABEL') ?>
        : <strong> <?php echo JHtml::_('utility.formatPrice', (float) $row->price , JText::_('COM_JEA_CONSULT_US') ) ?></strong>
        <?php if ($row->transaction_type == 'RENTING' && (float)$row->price != 0.0)
              echo JText::_('COM_JEA_PRICE_PER_FREQUENCY_'. $row->rate_frequency) ?>
        <?php endif ?>

        <?php if ($params->get('show_surfaces', 0)) :?>
        <?php if (!empty($row->living_space)): ?>
        <br /><?php echo  JText::_('COM_JEA_FIELD_LIVING_SPACE_LABEL') ?> :
        <strong><?php echo JHtml::_('utility.formatSurface', (float) $row->living_space , '-' ) ?></strong>
        <?php endif ?>

        <?php if (!empty($row->land_space)): ?>
        <br /><?php echo  JText::_('COM_JEA_FIELD_LAND_SPACE_LABEL') ?> :
        <strong> <?php echo JHtml::_('utility.formatSurface', (float) $row->land_space , '-' ) ?></strong>
        <?php endif ?>
        <?php endif ?>

        <?php if ($params->get('show_amenities', 0) && !empty($row->amenities)) : ?>
        <br /><strong><?php echo JText::_('COM_JEA_AMENITIES') ?> : </strong>
        <?php echo JHtml::_('amenities.bindList', $row->amenities) ?>
        <?php endif ?>
      </div>
      <?php endforeach ?>
    </div>
  </div>
  <?php if ($show_controls): ?>
  <div class="vertical_slider_forward"></div>
  <?php endif ?>
</div>

