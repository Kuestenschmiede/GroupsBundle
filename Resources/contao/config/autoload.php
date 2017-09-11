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
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'c4g',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'c4g\CGController'     => 'system/modules/con4gis_groups/classes/CGController.php',
	'c4g\ViewDialogs'      => 'system/modules/con4gis_groups/classes/ViewDialogs.php',
	'c4g\ViewLists'        => 'system/modules/con4gis_groups/classes/ViewLists.php',
	'c4g\Views'            => 'system/modules/con4gis_groups/classes/Views.php',

	// Models
	'c4g\MemberGroupModel' => 'system/modules/con4gis_groups/models/MemberGroupModel.php',
	'c4g\MemberModel'      => 'system/modules/con4gis_groups/models/MemberModel.php',

	// Modules
	'C4gGroupsAjaxApi'     => 'system/modules/con4gis_groups/modules/api/C4gGroupsAjaxApi.php',
	'c4g\C4GGroups'        => 'system/modules/con4gis_groups/modules/C4GGroups.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_c4g_groups' => 'system/modules/con4gis_groups/templates',
));
