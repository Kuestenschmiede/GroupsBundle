<?php 
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Contao\Backend;
use Contao\DataContainer;

//___LOAD CUSTOM CSS___________________________________________
  // needed to properly display right lists side by side
if(System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))){
    $GLOBALS['TL_CSS'][] = 'bundles/con4gisgroups/dist/css/be_c4g_groups.css';
}

//___CONFIG____________________________________________________
  $GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][]   = array('tl_module_c4g_groups', 'updateDCA');


//___PALETTES__________________________________________________
	$GLOBALS['TL_DCA']['tl_module']['palettes']['c4g_groups'] = '{title_legend},name,headline,type;'.
                                                              '{c4g_groups_appearance_legend},c4g_groups_appearance_highlight_owner,c4g_groups_uitheme_css_select,c4g_groups_appearance_themeroller_css;'.
																															'{c4g_groups_groupdefaults_legend},c4g_groups_default_maximum_size,c4g_groups_default_displayname,c4g_groups_default_member_rights,c4g_groups_default_owner_rights;'.
																															'{c4g_groups_permissions_legend},c4g_groups_permission_creategroups_authorized_groups,c4g_groups_permission_deletegroups_authorized_groups;'.
																															'{protected_legend:hide},protected;'.
																															'{expert_legend:hide},c4g_groups_permission_applicationgroup,guests,cssID,space';


//___FIELDS____________________________________________________
  // appearance
  $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_appearance_highlight_owner'] = array
  (
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['appearance_highlight_owner'],
    'exclude'                 => true,
    'default'                 => true,
    'inputType'               => 'checkbox',
    'sql'                     => "char(1) NOT NULL default '1'"
  );
  $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_uitheme_css_select'] = array
  (
  'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_groups_uitheme_css_select'],
  'exclude'                 => true,
  'default'                 => 'settings',
  'inputType'               => 'radio',
  'options'                 => array('settings','base','black-tie','blitzer','cupertino','dark-hive','dot-luv','eggplant','excite-bike','flick','hot-sneaks','humanity','le-frog','mint-choc','overcast','pepper-grinder','redmond','smoothness','south-street','start','sunny','swanky-purse','trontastic','ui-darkness','ui-lightness','vader'),
  'eval'                    => array('mandatory'=>true, 'submitOnChange' => true),
  'reference'               => &$GLOBALS['TL_LANG']['tl_module']['c4g_references'],
  'sql'                     => "char(100) NOT NULL default 'settings'"
  );
  $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_appearance_themeroller_css'] = array
  (
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['appearance_themeroller_css'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'extensions'=>'css'),
    'sql'                     => "binary(16) NULL"
  );

  // group-defaults
	$GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_default_maximum_size'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_maximum_size'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'eval'                    => array('rgxp'=>'digit' ),
		'sql'											=> "int(10) NOT NULL default '0'"
	);

    $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_default_displayname'] = array
    (
        'label'                 => &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_displayname'],
        'exclude'               => true,
        'inputType'             => 'text',
        'default'               => '§f §l (§e)',
        'eval'                  => array('tl_class'=>'w50'),
        'sql'                   => "varchar(255) NOT NULL default '§f §l (§e)'"
    );

	$GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_default_member_rights'] = array
  (
    'label'                 	=> &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_member_rights'],
    'exclude'               	=> true,
    'inputType'             	=> 'checkbox',
    'options_callback'      	=> array('tl_module_c4g_groups','getRightList'),
    'eval'                  	=> array(
                              	    'multiple' => true,
                                	  'tl_class' => 'c4g_w50',
                              	  ),
    'sql'                   	=> "blob NULL"
  );
  $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_default_owner_rights'] = array
  (
    'label'                 	=> &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_owner_rights'],
    'exclude'               	=> true,
    'inputType'             	=> 'checkbox',
    'options_callback'      	=> array('tl_module_c4g_groups','getRightList'),
    'eval'                  	=> array(
                              	    'multiple' => true,
                              	    'tl_class' => 'c4g_w50',
                              	  ),
    'sql'                   	=> "blob NULL"
  );

  // permissions
  $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_permission_creategroups_authorized_groups'] = array
  (
    'label'                 	=> &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['permission_creategroups_authorized_groups'],
    'exclude'               	=> true,
    'inputType'             	=> 'checkbox',
    'foreignKey'      	      => 'tl_member_group.name',
    'relation'                => array(
    																'type' => 'hasMany',
    																'load' => 'lazy'
    															),
    'eval'                  	=> array(
                              	    'multiple' => true,
                              	    'tl_class' => 'c4g_w50',
                              	  ),
    'sql'                   	=> "blob NULL"
  );
  $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_permission_deletegroups_authorized_groups'] = array
  (
    'label'                 	=> &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['permission_deletegroups_authorized_groups'],
    'exclude'               	=> true,
    'inputType'             	=> 'checkbox',
    'foreignKey'      	      => 'tl_member_group.name',
    'relation'                => array(
    																'type' => 'hasMany',
    																'load' => 'lazy'
    															),
    'eval'                  	=> array(
                              	    'multiple' => true,
                              	    'tl_class' => 'c4g_w50',
                              	  ),
    'sql'                   	=> "blob NULL"
  );

  $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_permission_applicationgroup'] = array
  (
      'label'             => &$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['c4g_groups_permission_applicationgroup'],
      'exclude'           => true,
      'inputType'         => 'select',
      'foreignKey'        => 'tl_member_group.name',
      'eval'              => array('mandatory' => false),
      'sql'               => "int(10) NULL",
      'relation'          => array('type' => 'hasOne', 'load' => 'lazy')
  );




/**
 * Class tl_module_c4g_groups
 */
class tl_module_c4g_groups extends Backend
{
	/**
   * Update the DCA
   * @param  DataContainer $dc
   */
  public function updateDCA (DataContainer $dc)
  {
    // owners have all rights by default
    $GLOBALS['TL_DCA']['tl_module']['fields']['c4g_groups_default_owner_rights']['default'] = array_keys( $this->getRightList() );
  }

  /**
   * Get a list of all available rights
   * @return array()
   */
  public function getRightList ()
  {
  	// load languagefile, since this it not done automatically at this point
  	\Contao\System::loadLanguageFile('tl_member_group');

    $rights = $GLOBALS['TL_LANG']['tl_member_group']['cg_rights'];
    foreach ($rights as $right => $rightname) {
      if (trim($rightname) != '') {
          $return[$right] = $rightname;
      }
    }
    return $return;
  }
}