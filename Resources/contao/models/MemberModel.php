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


namespace con4gis\GroupsBundle\Resources\contao\models;

use con4gis\CoreBundle\Resources\contao\classes\C4GUtils;
use con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel;


/**
 * Class MemberModel
 * @package c4g
 */
class MemberModel extends \Contao\MemberModel
{
  /**
   *
   * @param  integer     $groupId
   * @return array|null            The array of members
   */
  public static function getMemberListForGroup ($groupId)
  {
    // return nothing, if param is unset
    if (empty( $groupId )) return;

    // get the member-object with the given id
    $objGroup = MemberGroupModel::findByPk( $groupId );

    // return nothing, if there is no member with this id
    if (empty( $objGroup )) return;

    // fetch membergroups
    $members = unserialize($objGroup->cg_member);
    if (!is_array( $members ) || empty( $members )) {
      return array();
    }

    return parent::findMultipleByIds(
      $members
    );
  }

  /**
   * Assigns a given group to a given member.
   *
   * @param  integer  $memberId  [description]
   * @param  integer  $groupId   [description]
   * @param  boolean  $sync      [description]
   * @return boolean
   */
  public static function assignGroupToMember ($memberId, $groupId, $sync=true)
  {
    $objMember = MemberModel::findByPk( $memberId );
    if (!$objMember) return false;

    $groups = unserialize( $objMember->groups );
    if (is_array( $groups ) && in_array( $groupId, $groups )) {
      return false;
    } else {
      if ($sync) {
        if (!MemberGroupModel::assignMemberToGroup( $groupId, $memberId, false )) return false;
      }
      $groups[] = $groupId;
      $objMember->groups = serialize( $groups );
      $objMember->save();
      return true;
    }
  } // end of assignGroupToMember

  /**
   * Check if member has a specific right.
   * Or at least one right of a given right-set
   * (eg if member has right "group_edit_name", the function will return true,
   *  when asked if member has the right "group_edit")
   *
   * @param  integer  $memberId
   * @param  integer  $groupId
   * @param  string   $right
   * @return boolean
   */
  public static function hasRightInGroup ($memberId, $groupId, $right)
  {
    // check if parameters are set
    if (!$memberId || !$groupId || !$right) return false;

    // get all member-rights of group
    $arrRights = MemberGroupModel::getMemberRightsInGroup( $groupId, $memberId );

    if (!$arrRights) return false;

    foreach ($arrRights as $memberRight) {
        if(C4GUtils::startsWith( $memberRight, $right )) {
            return true;
        }
    }
    return false;
  }

  public static function hasRightInAnyGroup ($memberId, $right)
  {
      // check if parameters are set
      if (!$memberId || !$right) return false;

      $groups = MemberGroupModel::getGroupListForMember ($memberId);
      foreach ($groups as $group) {
          $groupId = $group->id;
          $result = self::hasRightInGroup($memberId,$groupId,$right);
          if ($result) {
            return true;
          }
      }

      return false;
  }

//--- non static ---

  /**
   * Get the displayname-format for this member
   * for the given group
   *
   * @param  integer      $groupId  [description]
   * @return string|null
   */
  public static function getDisplaynameForGroup ($groupId, $memberId)
  {
    // check parameter
    if (!is_numeric( $groupId )) return;

    // get group
    $group = MemberGroupModel::findByPk($groupId);

    // get groupsetting for member display-names
    $nameFormat = $group->cg_member_displayname;
    $member = MemberModel::findByPk($memberId);
    // replace placeholders with original data
    $pattern = array('/§f/', '/§l/', '/§u/', '/§e/');
    $replace = array($member->firstname, $member->lastname, $member->username, $member->email);

    return preg_replace($pattern, $replace, $nameFormat);
  }
}