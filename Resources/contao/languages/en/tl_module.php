<?php

/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

/**
 * Legend
 */
$GLOBALS['TL_LANG']['tl_module']['c4g_groups_appearance_legend']            = 'Appearance';
$GLOBALS['TL_LANG']['tl_module']['c4g_groups_groupdefaults_legend']         = 'Group-defaults configuration';
$GLOBALS['TL_LANG']['tl_module']['c4g_groups_permissions_legend']           = 'Permissions';


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['appearance_highlight_owner']              = array('Highlight group-owner', 'Highlight the group-owner in the memberlist.');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['appearance_themeroller_css']              = array('jQuery UI ThemeRoller CSS file', 'Optionally: select the CSS file you created with the jQuery UI ThemeRoller.');

$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_maximum_size']              = array('Default maximum group-size', 'The number of Members a group can have by default. (0 = infinite)');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_displayname']               = array('Default displayname', 'Defines how members names should be displayed in the FE. You can use the following placeholders: "§f": firstname, "§l": lastname, "§u": username, "§e": emailaddress.');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_member_rights']             = array('Default member rights', 'The rights a member will have in this group, when it is newly created in the FE.');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_owner_rights']              = array('Default owner rights', 'The rights an owner will have in this group, when it is newly created in the FE.');

$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['permission_creategroups_authorized_groups']     = array('Authorized groups (create new groups)', 'Groups that are allowed to create new groups.');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['permission_deletegroups_authorized_groups']     = array('Authorized groups (delete other groups)', 'Groups that are allowed to delete other groups.');

$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['c4g_groups_permission_applicationgroup'] = array('Global membergroup', 'group for all con4gis-groups members. Useful for standard backend rights.');

$GLOBALS['TL_LANG']['tl_module']['c4g_groups_uitheme_css_select']     = array(
    'jQuery UI ThemeRoller CSS theme',
    'Select a standart UI-Theme.'
);

$GLOBALS['TL_LANG']['tl_module']['c4g_references']['settings']  = 'con4gis settings';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['base']      = 'base';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['black-tie'] = 'black-tie';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['blitzer']   = 'blitzer';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['cupertino'] = 'cupertino';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['dark-hive'] = 'dark-hive';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['dot-luv']   = 'dot-luv';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['eggplant']  = 'eggplant';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['excite-bike']   = 'excite-bike';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['flick']         = 'flick';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['hot-sneaks']    = 'hot-sneaks';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['humanity']      = 'humanity';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['le-frog']       = 'le-frog';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['mint-choc']     = 'mint-choc';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['overcast']      = 'overcast';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['pepper-grinder'] = 'pepper-grinder';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['redmond']       = 'redmond';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['smoothness']    = 'smoothness';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['south-street']  = 'south-street';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['start']         = 'start';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['sunny']         = 'sunny';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['swanky-purse']  = 'swanky-purse';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['trontastic']    = 'trontastic';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['ui-darkness']   = 'ui-darkness';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['ui-lightness']  = 'ui-lightness';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['vader']         = 'vader';
