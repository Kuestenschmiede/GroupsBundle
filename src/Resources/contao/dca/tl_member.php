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

 use Contao\Backend;
 use Contao\StringUtil;
 use Contao\DataContainer;


//___CONFIG____________________________________________________

  $GLOBALS['TL_DCA']['tl_member']['config']['ondelete_callback'][] = array('tl_member_c4g_groups', 'deleteMemberFromGroups');

//___PALETTES__________________________________________________



//___FIELDS____________________________________________________

  $GLOBALS['TL_DCA']['tl_member']['fields']['groups'] = array
  (
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['groups'],
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'select',
    'sql'                     => "blob NULL",
    'foreignKey'              => 'tl_member_group.name',
    'relation'                => array('type'=>'belongsToMany', 'load'=>'lazy'),
    'eval'                    => array('tl_class'=>'long', 'submitOnChange'=>true, 'multiple'=>true, 'feEditable'=>true, 'feGroup'=>'login', 'chosen'=>true),
    'load_callback'           => array(array('tl_member_c4g_groups','cacheInitGroupConfig')),
    'save_callback'           => array(array('tl_member_c4g_groups','syncGroupBinding')),
  );

  $GLOBALS['TL_DCA']['tl_member']['fields']['groupcache'] = array
  (
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('feEditable'=>false, 'doNotShow'=>true, 'feViewable'=>false, 'disabled'=>true/*, 'feGroup'=>'login'*/),
  );


/**
 * Class tl_member_c4g_groups
 */
class tl_member_c4g_groups extends Backend
{
  /**
   * Saves the initial group list, for comparison on save
   * @param  string|array $groups  Serialized array or array of member group list
   * @param  DataContainer|FrontendUser $dc DataContainer in backend, FrontendUser in frontend
   * @return array
   */
  public function cacheInitGroupConfig ($groups, $dc): array
  {
    $groups = StringUtil::deserialize($groups, true);
    if ($dc instanceof DataContainer) {
        $dc->activeRecord->groupcache = $groups;
    }

    return $groups;
  }

  /**
   * Syncs "group->members"-binding
   * @param  string|array $groups  Serialized array or array of member group list
   * @param  DataContainer|FrontendUser $dc DataContainer in backend, FrontendUser in frontend
   * @return array
   * @throws Exception
   */
  public function syncGroupBinding ($groups, $dc): array
  {
      $groups = StringUtil::deserialize($groups, true);

    // check if the dc is really available
    if ($dc instanceof DataContainer && $dc->id) {
      // get the previous group set
      $groupCache = $dc->activeRecord->groupcache;
      if (!is_array($groupCache)) {
          $groupCache = [];
      }
      // check groups against cache to get newly added groups
      $newGroups = array_diff($groups, $groupCache);
      // and add the member to these groups in the tl_member_groups-table
      foreach ($newGroups as $group) {
        // check if member is really not part of this group. Just in case ;)
        if (!\con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::isMemberOfGroup($group, $dc->id)) {
          $objGroup = \con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::findByPk($group);
          if ($objGroup) {
            // check if the group has a member-limitation
            if ($objGroup->cg_max_member > 0 && $objGroup->cg_max_member <= count(StringUtil::deserialize($objGroup->cg_member))) {
              throw new Exception($GLOBALS['TL_LANG']['tl_member']['errors']['to_many_members_in_group'] . ' (' . $objGroup->name . ')');
            }

            $members = StringUtil::deserialize( $objGroup->cg_member );
            $members[] = $dc->id;
            $objGroup->cg_member = serialize( $members );
            $objGroup->save();
          } else {
            throw new Exception($GLOBALS['TL_LANG']['tl_member']['errors']['group_not_found']);
          }
        }
      }

      // check cache against groups to get removed groups
      $removedGroups = array_diff($groupCache, $groups);
      // and remove the member from these groups in the tl_member_groups-table
      foreach ($removedGroups as $group) {
        // check if member is really part of this group. Just as above ;)
        if (\con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::isMemberOfGroup($group, $dc->id)) {
          $objGroup = \con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::findByPk($group);
          if ($objGroup) {
            $members = \Contao\StringUtil::deserialize( $objGroup->cg_member );
            // not the most performant way, but reliable
            // walk through the array and keep every user, that is not this user
            $cleanedMembers = array();
            foreach ($members as $member) {
              if ($member != $dc->id) {
                $cleanedMembers[] = $member;
              }
            }
            $objGroup->cg_member = serialize( $cleanedMembers );
            $objGroup->save();
          }
        }
      }
    }

    // return new groups, so they can be saved to the database
    return $groups;
  }

  /**
   * [deleteMemberFromGroups description]
   * @param  DataContainer $dc [description]
   */
  public function deleteMemberFromGroups (DataContainer $dc)
  {
    $groups = StringUtil::deserialize( $dc->groups );

    if (!empty( $groups )) {
      foreach ($groups as $group) {
        // check if member is really part of this group. Just as above ;)
        if (\con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::isMemberOfGroup($group, $dc->id)) {
          $objGroup = \con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel::findByPk($group);
          if ($objGroup) {
            $members = StringUtil::deserialize( $objGroup->cg_member );
            // not the most performant way, but reliable
            // walk through the array and keep every user, that is not this user
            $cleanedMembers = array();
            foreach ($members as $member) {
              if ($member != $dc->id) {
                $cleanedMembers[] = $member;
              }
            }
            $objGroup->cg_member = serialize( $cleanedMembers );
            $objGroup->save();
          }
        }
      }
    }
  } // end of function "deleteMemberFromGroups"

} // end of Class