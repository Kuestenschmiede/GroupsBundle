<?php

/**
 * con4gis - the gis-kit
 *
 * @version   php 5
 * @package   con4gis
 * @author    con4gis contributors (see "authors.txt")
 * @license   GNU/LGPL http://opensource.org/licenses/lgpl-3.0.html
 * @copyright Küstenschmiede GmbH Software & Design 2011 - 2017.
 * @link      https://www.kuestenschmiede.de
 */

/**
 * Global settings
 */
$GLOBALS['con4gis_groups_extension']['installed'] = true;
$GLOBALS['con4gis_groups_extension']['version'] = "1.4.3-snapshot";


/**
 * FRONT END MODULES
 */
array_insert( $GLOBALS['FE_MOD']['con4gis'], 7, array
  (
    'c4g_groups' => 'C4GGroups'
  )
);


/**
 * API MODULES
 */
$GLOBALS['TL_API']['c4g_groups_ajax'] = 'C4gGroupsAjaxApi';


/**
 * ACTIVATIONPAGE-FUNCTION
 */
$GLOBALS['C4G_ACTIVATIONACTION']['c4g_joingroup'] = 'c4g\CGController';