<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_jea_slider
 * @copyright   Copyright (C) 2007-2012 PHILIP Sylvain. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die();
jimport('joomla.filesystem.folder');

class modJeaSliderHelper
{

    protected static $_params = null;

     public static function init(&$params)
     {
         self::$_params = $params;
     }

    public static function getItems()
    {
        $params = self::$_params;
        $orderby = $params->get('order_by', 'created');
        $selection = $params->get('selection', 'featured');
        $limit = (int) $params->get('number_items', 5);
        $transaction_type = $params->get('transaction_type', '');

        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query->select('p.*');
        $query->from('#__jea_properties AS p');

        // Join properties types
        $query->select('t.value AS `type`');
        $query->join('LEFT', '#__jea_types AS t ON t.id = p.type_id');

        // Join departments
        $query->select('d.value AS department');
        $query->join('LEFT', '#__jea_departments AS d ON d.id = p.department_id');

        // Join towns
        $query->select('town.value AS town');
        $query->join('LEFT', '#__jea_towns AS town ON town.id = p.town_id');

        // Join slogans
        $query->select('s.value AS slogan');
        $query->join('LEFT', '#__jea_slogans AS s ON s.id = p.slogan_id');

        $query->where('p.published=1');
        $query->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');

        // Filter by access level
        $user = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $query->where('p.access IN ('.$groups.')');

        // Filter by start and end dates.
        $nullDate = $db->Quote($db->getNullDate());
        $nowDate  = $db->Quote(JFactory::getDate()->toSql());

        $query->where('(p.publish_up = '.$nullDate.' OR p.publish_up <= '.$nowDate.')');
        $query->where('(p.publish_down = '.$nullDate.' OR p.publish_down >= '.$nowDate.')');

        switch($selection){
            case 'featured':
                $query->where('p.featured=1');
                break;
            case 'latest':
                $orderby = 'created';
                break;
            case 'random':
                $orderby = 'random';
                break;
        }

        if ($transaction_type == 'SELLING') {
            $query->where('p.transaction_type=\'SELLING\'');
        } elseif ($transaction_type == 'RENTING') {
            $query->where('p.transaction_type=\'RENTING\'');
        }

        switch($orderby){
            case 'created':
                $query->order('p.created DESC');
                break;
            case 'ordering':
                $query->order('p.ordering ASC');
                break;
            case 'price':
                $query->order('p.price ASC');
                break;
            case 'hits':
                $query->order('p.hits DESC');
                break;
            case 'random':
                $query->order('RAND()');
                break;
        }

        $db->setQuery($query, 0, $limit);
        return $db->loadObjectList();
    }

    public static function getPropertyRoute(&$row)
    {
        static $menuItems;

        if ($menuItems === null){
            $menuItems = array(
                'both'    => 0,
                'renting' => 0,
                'selling' => 0
            );
            $app  = JFactory::getApplication();
            $menu = $app->getMenu();
            $items = $menu->getItems('component', 'com_jea');
            $lang = JFactory::getLanguage()->getTag();
            $user = JFactory::getUser();
            $viewLevels = $user->getAuthorisedViewLevels();

            foreach ($items as $item) {
                if (!in_array($item->access, $viewLevels)) {
                    continue;
                }
                $layout = isset($item->query['layout']) ? $item->query['layout'] : 'default';
                $view = isset($item->query['view']) ? $item->query['view'] : '';

                if ($view == 'properties' && ($item->language == '*' || $item->language == $lang)) {

                    if ($layout == 'search' || $layout == 'searchmap') {
                        if (empty($menuItems['both'])) {
                            $menuItems['both'] = $item->id;
                        }
                    }

                    if ($layout == 'default') {

                        $type = $item->params->get('filter_transaction_type');

                        if ($type == 'SELLING' && empty($menuItems['selling'])) {

                            $menuItems['selling'] = $item->id;

                        } elseif ($type == 'RENTING' && empty($menuItems['renting'])) {

                            $menuItems['renting'] = $item->id;

                        } elseif (empty($menuItems['both'])) {

                            $menuItems['both'] = $item->id;
                        }
                    }
                }
            }
        }


        $slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

        $url = 'index.php?option=com_jea&view=property&id=' . $slug;

        if (!empty($menuItems['selling']) && $row->transaction_type == 'SELLING') {
            $url .= '&Itemid=' . $menuItems['selling'];
        } elseif (!empty($menuItems['renting']) && $row->transaction_type == 'RENTING') {
            $url .= '&Itemid=' . $menuItems['renting'];
        } elseif (!empty($menuItems['both'])) {
            $url .= '&Itemid=' . $menuItems['both'];
        }

        return JRoute::_($url);
    }

    public static function getItemImg(&$row)
    {
        $images = json_decode($row->images);
        $image  = null;

        if (!empty($images) && is_array($images)) {

            $image = array_shift($images);
            $imagePath = JPATH_ROOT . '/images/com_jea';

            $width = self::$_params->get('image_width', 164);
            $height = self::$_params->get('image_height', 123);

            $imageSliderName = $row->id. '-'. $width .'x'. $height .'-'. $image->name;
            $imgURL = JURI::root(true) . '/images/com_jea/slider/'.$imageSliderName;

            if (file_exists($imagePath . '/slider/' . $imageSliderName)) {
                // If the thumbnail already exists, return it directly
                return $imgURL;

            } elseif (file_exists($imagePath . '/images/' . $row->id . '/' . $image->name)) {
                // If the thumbnail doesn't exist, generate it and output it on the fly
                if (!JFolder::exists($imagePath . '/slider')) {
                    JFolder::create($imagePath . '/slider');
                }

                $JImage = new JImage($imagePath . '/images/'. $row->id . '/' . $image->name);
                $thumb = $JImage->resize($width, $height, true, JImage::SCALE_OUTSIDE);
                $left = $thumb->getWidth() > $width ? intval(($thumb->getWidth() - $width) / 2) : 0;
                $top = $thumb->getHeight() > $height ? intval(($thumb->getHeight() - $height) / 2) : 0;
                $thumb->crop($width, $height, $left, $top, false);
                $thumb->toFile($imagePath . '/slider/' . $imageSliderName, IMAGETYPE_JPEG, array('quality'=> 95));

                return $imgURL;
            }
        }

        return '';
    }


}