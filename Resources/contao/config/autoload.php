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
	'con4gis/GroupsBundle',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'con4gis\GroupsBundle\CGController'     => 'src/con4gis/GroupsBundle/Resources/contao/classes/CGController.php',
	'con4gis\GroupsBundle\ViewDialogs'      => 'src/con4gis/GroupsBundle/Resources/contao/classes/ViewDialogs.php',
	'con4gis\GroupsBundle\ViewLists'        => 'src/con4gis/GroupsBundle/Resources/contao/classes/ViewLists.php',
	'con4gis\GroupsBundle\Views'            => 'src/con4gis/GroupsBundle/Resources/contao/classes/Views.php',

	// Models
	'con4gis\GroupsBundle\MemberGroupModel' => 'src/con4gis/GroupsBundle/Resources/contao/models/MemberGroupModel.php',
	'con4gis\GroupsBundle\MemberModel'      => 'src/con4gis/GroupsBundle/Resources/contao/models/MemberModel.php',

	// Modules
	'C4gGroupsAjaxApi'     => 'src/con4gis/GroupsBundle/Resources/contao/modules/api/C4gGroupsAjaxApi.php',
	'con4gis\GroupsBundle\C4GGroups'        => 'src/con4gis/GroupsBundle/Resources/contao/modules/C4GGroups.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_c4g_groups' => 'src/con4gis/GroupsBundle/Resources/contao/templates',
));
