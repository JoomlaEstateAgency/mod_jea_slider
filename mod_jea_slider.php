<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_jea_slider
 * @copyright   Copyright (C) 2008-2020 PHILIP Sylvain. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @var $params Joomla\Registry\Registry
 */

require_once (dirname(__FILE__) . '/helper.php');

// Load component language
JFactory::getLanguage()->load('com_jea', JPATH_BASE.'/components/com_jea');


// Declare JEA helpers
JHtml::addIncludePath(JPATH_BASE.'/components/com_jea/helpers/html');

modJeaSliderHelper::init($params);

$rows = modJeaSliderHelper::getItems();

require JModuleHelper::getLayoutPath('mod_jea_slider', $params->get('layout', 'default'));
