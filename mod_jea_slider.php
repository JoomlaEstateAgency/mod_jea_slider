<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_jea_slider
 * @copyright   Copyright (C) 2007-2012 PHILIP Sylvain. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die();


require_once (dirname(__FILE__) . '/helper.php');

// Load component language
JFactory::getLanguage()->load('com_jea', JPATH_BASE.'/components/com_jea');


// Declare JEA helpers
JHtml::addIncludePath(JPATH_BASE.'/components/com_jea/helpers/html');

modJeaSliderHelper::init($params);

$rows = modJeaSliderHelper::getItems();

$uid = uniqid();

require(JModuleHelper::getLayoutPath('mod_jea_slider', $params->get('layout', 'horizontal-carousel')));