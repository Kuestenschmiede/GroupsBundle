<?php

/*
  * This file is part of con4gis,
  * the gis-kit for Contao CMS.
  *
  * @package   	con4gis
  * @version    7
  * @author  	con4gis contributors (see "authors.txt")
  * @license 	LGPL-3.0-or-later
  * @copyright 	Küstenschmiede GmbH Software & Design
  * @link       https://www.con4gis.org
  */


namespace con4gis\GroupsBundle\Resources\contao\models;


/**
 * Class MemberGroupModel
 * @package c4g
 */
class MemberGroupModel extends \Contao\MemberGroupModel
{
  /**
   * Returns an array of groups for the member with the given id
   * NOTE: Only groups with an owner ('cg_owner_id') will be returned
   *
   * @param  integer     $memberId
   * @return array|null             The array of groups
   */
  public static function getGroupListForMember ($memberId)
  {
    // return nothing, if param is unset
    if (empty( $memberId )){ return array(); }

    // get the member-object with the given id
    $objMember = MemberModel::findByPk( $memberId );
    // return nothing, if there is no member with this id
    if (empty( $objMember )){ return array(); }

    // fetch membergroups
    $groups = unserialize($objMember->groups);
    if (!$groups) { return array(); }

    $colGroups = static::findMultipleByIds($groups);
    if (!$colGroups) { return array(); }

    // collect all groups, that have an owner, in an array
    $return = array();
    foreach ($colGroups as $group) {
      if (!empty( $group->cg_owner_id )) {
        $return[] = $group;
      }
    }

    // check if this array is empty. If not, return it
    if (empty( $return )) {
      return array();
    } else {
      return $return;
    }
  }

  /**
   * Returns a string-list of ranks in the group for the member with the given id
   * NOTE: Ranks are groups with a set 'pid'
   *
   * @param  integer      $groupId
   * @param  integer      $memberId
   * @param  string       $delimiter  (optional)
   * @return string|null              The list of ranks
   */
  public static function getMemberRanksOfGroupAsString ($groupId, $memberId, $delimiter = ',')
  {
    // check if user is member of group
    if (!static::isMemberOfGroup( $groupId, $memberId )) { return; }

    // check if the user is the owner of this group
    if (static::isOwnerOfGroup( $groupId, $memberId )) {
      $memberRanks = $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPOWNER'];
      $isOwner = true;
    } else {
      $memberRanks = $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPMEMBER'];
      $isOwner = false;
    }

    // fetch the group-ranks
    $arrMemberRanks = static::getMemberRanksOfGroup( $groupId, $memberId );
    if (!empty( $arrMemberRanks )) {
      // and add them to the string-list
      foreach ($arrMemberRanks as $rank) {
        $memberRanks .= $delimiter . ' ' . substr( $rank->name, strrpos($rank->name, '|')+1 );
      }
      // delete member-rank from list (does not need to be displayed if member has higher rank)
//      if (!$isOwner) {
//        $memberRanks = substr( $memberRanks, strrpos($memberRanks, ',')+2 );
//      }
    }

    // return the list of ranks
    return $memberRanks;
  }

  /**
   * Returns an array of ranks in the group for the member with the given id
   * NOTE: Ranks are groups with a set 'pid'
   *
   * @param  integer      $groupId
   * @param  integer      $memberId
   * @return array|null              The array of ranks
   */
  public static function getMemberRanksOfGroup ($groupId, $memberId)
  {
    // return nothing, if both params are unset
    if (empty( $groupId ) || empty( $memberId )) { return; }

    // get array of ranks for this group
    $ranks = static::getRanksOfGroup( $groupId );

    // return nothing, if there are no ranks for this group
    if (empty( $ranks )) { return; }

    // check if member is part of one or more of the ranks and list them in an array
    $return = array();
    foreach ($ranks as $rank) {
      $rankMember = unserialize( $rank->cg_member );
      if ($rankMember && (in_array( $memberId, $rankMember )) || (static::isOwnerOfGroup( $rank->cg_pid, $memberId ))) {
        $return[] = $rank;
      }
    }

    // check if this array is empty. If not, return it
    if (empty( $return )) {
      return;
    } else {
      return $return;
    }
  }

  /**
   * Get the rights of a given member in a given group
   *
   * @param  integer        $groupId   [description]
   * @param  integer        $memberId  [description]
   * @return array|boolean
   */
  public static function getMemberRightsInGroup ($groupId, $memberId)
  {
    if (!$groupId || !$memberId) return false;

    // try to fetch group
    $objGroup = static::findByPk( $groupId );
    if (!$objGroup) return false;

      $ownerGroupId = $groupId;
      $objOwnerGroup = $objGroup;
      $parentId = $objGroup->cg_pid;
      if ( ($parentId) && ($parentId > 0) ) {
          $ownerGroupId = $parentId;
          $objOwnerGroup = static::findByPk( $ownerGroupId );
      }

    // get appropriate rights
    $rights = static::isOwnerOfGroup( $ownerGroupId, $memberId ) ? $objOwnerGroup->cg_owner_rights : $objOwnerGroup->cg_member_rights;
    if (!$rights) {
      $rights = array();
    } else {
      $rights = unserialize($rights);
    }

    // fetch ranks
    $objMemberRanks = static::getMemberRanksOfGroup( $ownerGroupId, $memberId );
    if ($objMemberRanks) {
      foreach ($objMemberRanks as $objMemberRank) {
        $rankRights = $objMemberRank->cg_member_rights;
        if ($rankRights) {
          $rankRights = unserialize($rankRights);
          // add additional rights from rank to the right-set
          $rights = array_merge( $rights, array_diff( $rankRights, $rights ) );
        }
      }
    }

    // return the calculated right-set
    return $rights;
  }

  /**
   * Returns an array of ranks in the group
   * NOTE: Ranks are groups with a set 'pid'
   *
   * @param  integer     $groupId
   * @return array|null            The array of ranks
   */
  public static function getRanksOfGroup ($groupId)
  {
    // return nothing, if param is unset
    if (empty( $groupId )) { return; }

    return static::find( array(
        'column' => 'cg_pid',
        'value' => $groupId
      ) );
  }

  /**
   * Checks if the member with the given Id is the owner of the selected group
   *
   * @param  integer  $groupId
   * @param  integer  $memberId
   * @return boolean
   */
  public static function isOwnerOfGroup ($groupId, $memberId)
  {
    // return nothing, if both params are unset
    if (empty( $groupId ) || empty( $memberId )) { return false; }

    // get the group
    $group = static::findByPk( $groupId );

    // return nothing, if the group does not exist
    if (empty( $group )) { return false; }

      $isOwner = ($group->cg_owner_id == $memberId);
//      if (!$isOwner) {
//          if ($group->cg_pid) {
//              $parentgroup = static::findByPk( $group->cg_pid );
//              if ($parentgroup) {
//                  $isOwner = ($parentgroup->cg_owner_id == $memberId);
//              }
//          }
//      }

    return $isOwner;
  }

  /**
   * Checks if the user with the given Id is a member of the selected group
   * NOTE: The owner of a groups is a member, too
   *
   * @param  integer  $groupId
   * @param  integer  $memberId
   * @return boolean
   */
  public static function isMemberOfGroup ($groupId, $memberId)
  {
    // return nothing, if both params are unset
    if (empty( $groupId ) || empty( $memberId )) { return false; }

    // get the group
    $group = static::findByPk( $groupId );

    // return nothing, if the group does not exist
    if (empty( $group )) { return false; }

    // check if user is in member-list
    $return = array();
    $member = unserialize( $group->cg_member );
    if (is_array( $member ) && in_array( $memberId, $member )) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Assign a member to a group
   * and vice versa, if $sync is true
   *
   * @param  integer  $groupId   the id of the group
   * @param  integer  $userId    the id of the member
   * @param  boolean  $sync      true, if the member-db-entry should be updated as well
   * @return boolean             false if an operation failed, otherwise true
   */
  public static function assignMemberToGroup ($groupId, $userId, $sync=true)
  {
    // check if values are set
    if (!$groupId || !$userId) {
      return false;
    }
    // get group
    $objGroup = static::findByPk( $groupId );
    if (!$objGroup) { return false; }

    $member = unserialize( $objGroup->cg_member );
    if (is_array( $member ) && in_array( $userId, $member )) {
      return false;
    } else {
      if ($sync) {
        if (!MemberModel::assignGroupToMember( $userId, $groupId, false )) { return false; }
      }
      $member[] = $userId;
      $objGroup->cg_member = serialize($member);
      $objGroup->save();
      // synchronize member-db-entry if enabled
      return true;
    }

  } // end of assignMemberToGroup

} // end of Class