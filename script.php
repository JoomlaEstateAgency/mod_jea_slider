<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_jea_emphasis
 * @copyright   Copyright (C) 2012 PHILIP Sylvain. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');

class mod_jea_sliderInstallerScript
{

    /**
     * method to run before an install/update/uninstall method
     *
     * @return boolean
     */
    function preflight($type, $parent)
    {
        /*$jea = JFactory::getXML(JPATH_ROOT.'/administrator/components/com_jea/jea.xml');
        if ((float) $jea->version < 2.2) {
            JError::raiseWarning(500, 'JEA version must be >= 2.2. Please upgrade before JEA component.');
            return false;
        }*/
        return true;
    }


}

