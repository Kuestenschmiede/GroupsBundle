<?php

/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2022, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

//___LOAD CUSTOM CSS___________________________________________
  // needed to properly display right lists side by side
if(TL_MODE == "BE") {
    $GLOBALS['TL_CSS'][] = 'bundles/con4gisgroups/dist/css/be_c4g_groups.css';
}

//___CONFIG____________________________________________________
  $GLOBALS['TL_DCA']['tl_member_group']['config']['onload_callback'][]   = array('tl_member_group_c4g_groups', 'updateDCA');
  $GLOBALS['TL_DCA']['tl_member_group']['config']['ondelete_callback'][] = array('tl_member_group_c4g_groups', 'deleteGroupFromMembers');


//___PALETTES__________________________________________________
PaletteManipulator::create()
    ->addLegend('c4g_groups_legend', 'redirect_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(array('cg_owner_id','cg_max_member','cg_member_displayname','cg_member','cg_member_rights','cg_owner_rights'), 'c4g_groups_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member_group');

//___FIELDS____________________________________________________
  $GLOBALS['TL_DCA']['tl_member_group']['fields']['cg_pid'] = array
  (
    'sql'                   => "int(10) unsigned NULL"
  );

  $GLOBALS['TL_DCA']['tl_member_group']['fields']['cg_owner_id'] = array
  (
    'label'                 => &$GLOBALS['TL_LANG']['tl_member_group']['cg_owner_id'],
    'exclude'               => true,
    'filter'                => true,
    'inputType'             => 'select',
    'options_callback'      => array('tl_member_group_c4g_groups','getMemberList'),
    'eval'                  => array(
                                'includeBlankOption' => true,
                                'tl_class'=>'long',
                                'chosen'=>true,
                              ),
    'sql'                   => "int(10) unsigned NULL"
  );

  $GLOBALS['TL_DCA']['tl_member_group']['fields']['cg_max_member'] = array
  (
    'label'                 => &$GLOBALS['TL_LANG']['tl_member_group']['cg_max_member'],
    'exclude'               => true,
    'inputType'             => 'text',
    'default'               => 0,
    'eval'                  => array('rgxp' => 'digit', 'submitOnChange'=>true, 'tl_class'=>'w50'),
    'save_callback'         => array(array('tl_member_group_c4g_groups', 'checkSetSize')),
    'sql'                   => "int(255) unsigned NULL"
  );

  $GLOBALS['TL_DCA']['tl_member_group']['fields']['cg_member_displayname'] = array
  (
    'label'                 => &$GLOBALS['TL_LANG']['tl_member_group']['cg_member_displayname'],
    'exclude'               => true,
    'inputType'             => 'text',
    'default'               => '§f §l (§e)',
    'eval'                  => array('tl_class'=>'w50'),
    'sql'                   => "varchar(255) NOT NULL default '§f §l (§e)'"
  );

  $GLOBALS['TL_DCA']['tl_member_group']['fields']['cg_member'] = array
  (
    'label'                 => &$GLOBALS['TL_LANG']['tl_member_group']['cg_member'],
    'exclude'               => true,
    'filter'                => true,
    'inputType'             => 'select',
    'eval'                  => array('tl_class'=>'long clr', 'submitOnChange'=>true, 'multiple'=>true, 'feEditable'=>true, 'feGroup'=>'login', 'chosen'=>true),
    'foreignKey'            => 'tl_member.email',
    'relation'              => array('type'=>'belongsToMany', 'load'=>'lazy'),
    'load_callback'         => array(array('tl_member_group_c4g_groups', 'cacheInitMemberConfig')),
    'save_callback'         => array(
                                  array('tl_member_group_c4g_groups', 'checkSize'),
                                  array('tl_member_group_c4g_groups', 'syncMemberBinding'),
                               ),
    'sql'                   => "blob NULL",
  );
  $GLOBALS['TL_DCA']['tl_member']['fields']['membercache'] = array
  (
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('feEditable'=>false, 'doNotShow'=>true, 'feViewable'=>false, 'disabled'=>true/*, 'feGroup'=>'login'*/),
  );

  $GLOBALS['TL_DCA']['tl_member_group']['fields']['cg_member_rights'] = array
  (
    'label'                 => &$GLOBALS['TL_LANG']['tl_member_group']['cg_member_rights'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'options_callback'      => array('tl_member_group_c4g_groups','getRightList'),
    'eval'                  => array(
                                  'multiple' => true,
                                  'tl_class' => 'c4g_w50',
                                ),
    'sql'                   => "blob NULL"
  );

  $GLOBALS['TL_DCA']['tl_member_group']['fields']['cg_owner_rights'] = array
  (
    'label'                 => &$GLOBALS['TL_LANG']['tl_member_group']['cg_owner_rights'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'options_callback'      => array('tl_member_group_c4g_groups','getRightList'),
    'eval'                  => array(
                                  'multiple' => true,
                                  'tl_class' => 'c4g_w50',
                                ),
    'sql'                   => "blob NULL"
  );


/**
 * Class tl_member_group_c4g_groups
 */
class tl_member_group_c4g_groups extends Backend
{

  /**
   * Update the DCA
   * @param  DataContainer $dc
   */
  public function updateDCA (DataContainer $dc)
  {
    // owners have all rights by default
    $GLOBALS['TL_DCA']['tl_member_group']['fields']['cg_owner_rights']['default'] = array_keys( $this->getRightList() );
  }


  /**
   * Get a list of all available rights
   * @return array()
   */
  public function getRightList ()
  {
    $rights = $GLOBALS['TL_LANG']['tl_member_group']['cg_rights'];
    foreach ($rights as $right => $rightname) {
      if (trim($rightname) != '') {
          $return[$right] = $rightname;
      }
    }
    return $return;
  }


  /**
   * Returns the memberlist for this group as array,
   * with the key being the members id and the value being a string
   * in the format "FIRSTNAME LASTNAME (EMAIL)".
   * @param  DataContainer $dc
   * @return array()
   */
  public function getMemberList (DataContainer $dc)
  {
    if ($dc->id) {
      $members = con4gis\GroupsBundle\Resources\contao\models\MemberModel::getMemberListForGroup($dc->id);
      if (empty( $members )) {
        return array();
      }

      $return = array();
      foreach ($members as $member) {
        $return[$member->id] = $member->firstname . ' ' . $member->lastname . ' (' . $member->email . ')';
      }
      return $return;
    } else {
      return array();
    }
  }

  public function checkSetSize ($size, DataContainer $dc)
  {
    // throw an exception if the user tries to set a limit
    // that is under the current number of members
    if ($size > 0 && $size < count(unserialize($dc->activeRecord->cg_member))) {
      throw new Exception($GLOBALS['TL_LANG']['tl_member_group']['errors']['limit_under_current_count']);
    }

    return $size;
  }

  public function checkSize ($members, DataContainer $dc)
  {
    // throw an exception if there is a member-number-restriction
    // which would be exceeded with this action
    if ($dc->activeRecord->cg_max_member > 0 && $dc->activeRecord->cg_max_member < count(unserialize($members))) {
      throw new Exception($GLOBALS['TL_LANG']['tl_member_group']['errors']['to_many_members_in_group']);
    }

    return $members;
  }

  /**
   * Saves the initial memberlist, for comparison on save
   * @param  Array         $members [description]
   * @param  DataContainer $dc      [description]
   * @return Array                  [description]
   */
  public function cacheInitMemberConfig ($members, DataContainer $dc)
  {
    $members = $members ? unserialize($members) : array();
    $dc->activeRecord->membercache = $members;

    return $members;
  }

  /**
   * Syncs "member->groups"-binding
   * @param  Array         $members [description]
   * @param  DataContainer $dc      [description]
   * @return Array                  [description]
   */
  public function syncMemberBinding ($members, DataContainer $dc)
  {
    $members = $members ? unserialize($members) : array();

    // check if the dc is really available
    if ($dc->id) {
      // get the previous memberset
      $memberCache = $dc->activeRecord->membercache;
      // check members against cache to get newly added members
      $newMembers = array_diff($members, $memberCache);
      // and add the group to these members in the tl_member-table
      foreach ($newMembers as $member) {
        // check if member is really not part of this group. Just in case ;)
        if (!con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::isMemberOfGroup( $dc->id, $member )) {
          $objMember = con4gis\GroupsBundle\Resources\contao\models\MemberModel::findByPk( $member );
          if ($objMember) {
            $groups = unserialize( $objMember->groups );
            $groups[] = $dc->id;
            $objMember->groups = serialize( $groups );
            $objMember->save();
          }
        }
      }

      // check cache against members to get removed members
      $removedMembers = array_diff($memberCache, $members);
      // and remove the group from these members in the tl_member-table
      foreach ($removedMembers as $member) {
        // check if member is really part of this group. Just as above ;)
        if (con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::isMemberOfGroup( $dc->id, $member )) {
          $objMember = con4gis\GroupsBundle\Resources\contao\models\MemberModel::findByPk( $member );
          if ($objMember) {
            $memberGroups = unserialize( $objMember->groups );
            $memberGroups = array_diff( $memberGroups, array( $dc->id ) );
            if(empty( $memberGroups )) { $memberGroups = array(); }
            $objMember->groups = serialize( $memberGroups );
            $objMember->save();
          }
        }
      }
    }

    // return new members, so they can be saved to the database
    return $members;
  }

  /**
   * [deleteGroupFromMembers description]
   * @param  DataContainer $dc [description]
   */
  public function deleteGroupFromMembers (DataContainer $dc)
  {
    $members = unserialize( $dc->cg_member );

    if (!empty( $members )) {
      foreach ($members as $member) {
        // check if member is really part of this group. Just as above ;)
        if (con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::isMemberOfGroup( $dc->id, $member )) {
          $objMember = con4gis\GroupsBundle\Resources\contao\models\MemberModel::findByPk( $member );
          if ($objMember) {
            $memberGroups = unserialize( $objMember->groups );
            $memberGroups = array_diff( $memberGroups, array( $dc->id ) );
            if(empty( $memberGroups )) { $memberGroups = array(); }
            $objMember->groups = serialize( $memberGroups );
            $objMember->save();
          }
        }
      }
    }
  } // end of function "deleteGroupFromMembers"

} // end of Class