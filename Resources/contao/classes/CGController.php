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

namespace con4gis\GroupsBundle\Resources\contao\classes;
use c4g\C4gActivationkeyModel;
use con4gis\CoreBundle\Resources\contao\classes\C4GUtils;
use con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel;
use con4gis\GroupsBundle\Resources\contao\models\MemberModel;
use Contao\System;

/**
 * Class CGController
 * @package c4g
 */
class CGController
{
  /**
   * Creates a new group.
   *
   * @param  object  $objThis    The "C4GGroups"-Module
   * @param  array   $arrConfig
   * @return array
   */
  public static function createGroup ($objThis, $arrConfig)
  {
    // check permissions
    if (!$objThis->currentMemberHasPermission( 'creategroups' ) || !FE_USER_LOGGED_IN) {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'],
      );
    }

    $ownerId = $objThis->User->id;

    // check if groupname is set...
    if (empty( $arrConfig['groupname'] ) || empty( $ownerId )) {
      return array
      (
        'usermessage' => $arrConfig['groupname'].$GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOGROUPNAME'],
      );
    }
    $name = $arrConfig['groupname'];

    // ...or already taken
    if (MemberGroupModel::findOneBy( 'name', $name )) {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_GROUPNAMETAKEN'],
      );
    }

    // get owners user-model
    $owner = MemberModel::findByPk( $ownerId );
    if (empty( $owner )) return;

    // create group
    $group = new MemberGroupModel();

    // set name
    $group->name = C4GUtils::secure_ugc( $name );

    // set timestamp
    $date = new \DateTime();
    $group->tstamp = $date->getTimestamp();

    // set maximum group-size
    $group->cg_max_member = $objThis->c4g_groups_default_maximum_size;

    // set first member (group-model)
    $group->cg_member = serialize( array( $ownerId ) );

    // set owner
    $group->cg_owner_id = $ownerId;

    // set member-rights
    $group->cg_member_rights = $objThis->c4g_groups_default_member_rights;
    // set owner-rights
    $group->cg_owner_rights = $objThis->c4g_groups_default_owner_rights;

    // save group
    $group->save();
    // $group->refresh();

    // set first member (member-model)
    //    this needs to be done after the group was saved,
    //    because the new group-id is needed here
    $ownerGroups = unserialize( $owner->groups );
    if (empty( $owner )) { $ownerGroups = array(); }
    $ownerGroups[] = $group->id;
    $owner->groups = serialize( $ownerGroups );
    $owner->save();

    return array
    (
      'dialogclose' => 'groupcreatedialog' . $ownerId,
      'performaction' => 'viewmemberlist:' . $group->id,
    );
  } // end of function "createGroup"

  /**
   * Edit or delete group.
   *
   * @param  object       $objThis    The "C4GGroups"-Module
   * @param  integer      $groupId
   * @param  array        $arrConfig
   * @return array|null
   */
  public static function configureGroup ($objThis, $groupId, $arrConfig)
  {
    // get group
    $group = MemberGroupModel::findByPk($groupId);
    if (empty( $group )) { return; }
    $action = '';

    $memberId = $objThis->User->id;

      $ownerGroupId = $groupId;
      $deleteRight = 'group_edit_delete';
      if ($group->cg_pid > 0) {
          $ownerGroupId = $group->cg_pid;
          $deleteRight = 'rank_edit_delete';
      }

    // should group be deleted?
    if (($objThis->currentMemberHasPermission('deletegroups') || MemberModel::hasRightInGroup( $memberId, $ownerGroupId, $deleteRight ))
        && $arrConfig['deletegroup'] == $GLOBALS['TL_LANG']['C4G_GROUPS']['KEYWORD_DELETE'])
    {
        $action = 'viewgrouplist';
        if ($group->cg_pid > 0) {
            $action = 'viewranklist:'.$group->cg_pid;
        }

        $group->delete();

    } else {
      // set new name
      if (MemberModel::hasRightInGroup( $memberId, $groupId, 'group_edit_name' ) && !empty( $arrConfig['groupname'] )) {
        // if not already taken
        $name = C4GUtils::secure_ugc( $arrConfig['groupname'] );
        if (MemberGroupModel::findOneBy( 'name', $name )) {
          return array
          (
            'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_GROUPNAMETAKEN'],
          );
        }
        $group->name = $name;
      }

      // set new membername-format
      if (MemberModel::hasRightInGroup( $memberId, $groupId, 'group_edit_membernameformat' ) && !empty( $arrConfig['membernameformat'] )) {
        // $group->cg_member_displayname = C4GUtils::secure_ugc( $arrConfig['membernameformat'] );
        $group->cg_member_displayname = C4GUtils::secure_ugc( $arrConfig['membernameformat'] );
      }

      // set new owner
      if (MemberModel::hasRightInGroup( $memberId, $groupId, 'group_edit_owner' ) && !empty( $arrConfig['groupowner'] )) {
        $group->cg_owner_id = $arrConfig['groupowner'];
      }

      // set new member-rights
      if (MemberModel::hasRightInGroup( $memberId, $groupId, 'group_edit_rights' )) {
        // load the languagefile, which contains the rights
        System::loadLanguageFile('tl_member_group');
        $newRights = array();
        $rightPrefix = 'right_';
        // search all $arrConfig entrys for keys that starts with "right_",
        //   are set to "true" and are valid rights
        foreach ($arrConfig as $key => $value) {
          if(C4GUtils::startsWith( $key, $rightPrefix ) && ( (is_bool($value) && $value) || $value === 'true' )) {
            $origRightname = substr($key, strlen($rightPrefix));
            if (isset($GLOBALS['TL_LANG']['tl_member_group']['cg_rights'][$origRightname])) {
              // save them in a new array
              $newRights[] = $origRightname;
            }
          }
        }
        // serialize the new array and save it
        $group->cg_member_rights = serialize($newRights);
      }

      $group->save();
      $action = 'viewmemberlist:' . $groupId;
        if ($group->cg_pid > 0) {
            $action = 'viewrankmemberlist:' . $groupId;
        }
    }

    return array
    (
      'dialogclose' => 'groupconfigdialog' . $groupId,
      'performaction' => $action,
    );
  } // end of function "configureGroup"

    /**
     * Edit or delete group.
     *
     * @param  object       $objThis    The "C4GGroups"-Module
     * @param  integer      $groupId
     * @param  array        $arrConfig
     * @return array|null
     */
    public static function configureRank ($objThis, $groupId, $arrConfig)
    {
        // get group
        $group = MemberGroupModel::findByPk($groupId);
        if (empty( $group )) { return; }
        $action = '';

        $memberId = $objThis->User->id;

        $deleteRight = 'rank_edit_delete';
        $ownerGroupId = $group->cg_pid;
        $ownerGroup = MemberGroupModel::findByPk($ownerGroupId);

        if (empty( $ownerGroup )) { return; }

        // should group be deleted?
        if (($objThis->currentMemberHasPermission('deletegroups') || MemberModel::hasRightInGroup( $memberId, $ownerGroupId, $deleteRight ))
            && $arrConfig['deletegroup'] == $GLOBALS['TL_LANG']['C4G_GROUPS']['KEYWORD_DELETE'])
        {
            $action = 'viewranklist:'.$group->cg_pid;
            $group->delete();

        } else {
            // set new name
            if (MemberModel::hasRightInGroup( $memberId, $groupId, 'rank_edit_name' ) && !empty( $arrConfig['groupname'] )) {
                // if not already taken
                $name = C4GUtils::secure_ugc( $arrConfig['groupname'] );
                if (MemberGroupModel::findOneBy( 'name', $name )) {
                    return array
                    (
                        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_GROUPNAMETAKEN'],
                    );
                }
                $group->name = $ownerGroup->name.'|'.$arrConfig['groupname'];
            }

            // set new membername-format
//            if (MemberModel::hasRightInGroup( $memberId, $groupId, 'group_edit_membernameformat' ) && !empty( $arrConfig['membernameformat'] )) {
//                // $group->cg_member_displayname = C4GUtils::secure_ugc( $arrConfig['membernameformat'] );
//                $group->cg_member_displayname = C4GUtils::secure_ugc( $arrConfig['membernameformat'] );
//            }

            // set new owner
//            if (MemberModel::hasRightInGroup( $memberId, $groupId, 'group_edit_owner' ) && !empty( $arrConfig['groupowner'] )) {
//                $group->cg_owner_id = $arrConfig['groupowner'];
//            }

            // set new member-rights
            if (MemberModel::hasRightInGroup( $memberId, $groupId, 'rank_edit_rights' )) {
                // load the languagefile, which contains the rights
                \System::loadLanguageFile('tl_member_group');
                $newRights = array();
                $rightPrefix = 'right_';
                // search all $arrConfig entrys for keys that starts with "right_",
                //   are set to "true" and are valid rights
                foreach ($arrConfig as $key => $value) {
                    if(C4GUtils::startsWith( $key, $rightPrefix ) && ( (is_bool($value) && $value) || $value === 'true' )) {
                        $origRightname = substr($key, strlen($rightPrefix));
                        if (isset($GLOBALS['TL_LANG']['tl_member_group']['cg_rights'][$origRightname])) {
                            // save them in a new array
                            $newRights[] = $origRightname;
                        }
                    }
                }
                // serialize the new array and save it
                $group->cg_member_rights = serialize($newRights);
            }

            $group->save();
            $action = 'viewrankmemberlist:' . $groupId;
        }

        return array
        (
            'dialogclose' => 'rankconfigdialog' . $groupId,
            'performaction' => $action,
        );
    } // end of function "configureRank"

  /**
   * Remove member(s) from (or leave) group
   *
   * @param  object      $objThis    The "C4GGroups"-Module
   * @param  integer     $groupId
   * @param  array       $memberIds
   * @return array|null
   */
  public static function removeMemberFromGroup ($objThis, $groupId, $memberIds=array())
  {
    // check permissions
    // if $memberIds is empty it means the current member leaves the group
    if (!empty($memberIds) && !MemberModel::hasRightInGroup( $objThis->User->id, $groupId, 'member_remove' )) {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']
      );
    }

    if (empty($memberIds)) {
      $memberIds = array( $objThis->User->id );
      $closeDialog = 'leavegroupdialog' . $groupId;
      // $performAction = 'viewgrouplist';
    } else {
      $memberIds = explode(',', $memberIds);
      $closeDialog = 'removememberdialog' . $groupId;
      // $performAction = 'viewmemberlist:' . $groupId;
    }

    // get group
    $group = MemberGroupModel::findByPk( $groupId );
    $members = MemberModel::findMultipleByIds( $memberIds );
    if (empty( $group ) || empty( $members )) { return; }

    // remove members froum group-model
    $groupmembers = unserialize( $group->cg_member );
    $group->cg_member = serialize( array_diff( $groupmembers, $memberIds ) );
    $group->save();

    // remove group from member-models
    $arrGroup = array( $groupId );
    $memberGroups = '';

      $parentId = $group->cg_pid;
      if ($parentId > 0) {
          $performAction = 'viewrankmemberlist:' . $groupId;
      } else {
          $performAction = 'viewmemberlist:' . $groupId;
      }

      foreach ($members as $member) {
      $memberGroups = unserialize( $member->groups );
      if (!empty( $memberGroups )) {
        $memberGroups = array_diff( $memberGroups, $arrGroup );
        if (empty( $memberGroups )) {
          $memberGroups = array();
        }

        //remove member from standardgroup
        $allgroups = MemberGroupModel::getGroupListForMember($member->id);
        if (empty($allgroups) && ($objThis->c4g_groups_permission_applicationgroup) && ($objThis->c4g_groups_permission_applicationgroup > 0)) {
          $applicationGroup = array( $objThis->c4g_groups_permission_applicationgroup );
          if ($applicationGroup) {
            $memberGroups = array_diff( $memberGroups, $applicationGroup );
            if (empty( $memberGroups )) {
              $memberGroups = array();
            }

            $agroup = MemberGroupModel::findByPk($objThis->c4g_groups_permission_applicationgroup);
            if ($agroup) {
              $groupmembers = unserialize( $agroup->cg_member );
              $agroup->cg_member = serialize( array_diff( $groupmembers, $member->id ) );
              $agroup->save();
            }
          }
        }

        $member->groups = serialize( $memberGroups );
        $member->save();

        //we have to change the member booking count
        //ToDo remove with BookingBundle
        if ($GLOBALS['con4gis_booking_extension']['installed']) {
          \c4g\projects\C4gBookingGroupsModel::checkMemberCount($groupId);
        }

        // redirect member back to the grouplist, if he removed himself
        if (($member->id === $objThis->User->id) && (!$parentId)) {
          $performAction = 'viewgrouplist';
        }
      }
    }

    return array
    (
      'dialogclose' => $closeDialog,
      'performaction' => $performAction,
    );
  } // end of function "removeMemberFromGroup"

  /**
   * Function for member-invitation
   *
   * @param  object   $objThis      The "C4GGroups"-Module
   * @param  integer  $groupId
   * @param  string   $mailaddress
   * @return array
   */
  public static function inviteMember ($objThis, $groupId, $mailaddress)
  {
    // check permissions
    if (!FE_USER_LOGGED_IN) {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOTLOGGEDIN']
      );
    }
    if (!MemberModel::hasRightInGroup( $objThis->User->id, $groupId, 'member_invite_' )) {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']
      );
    }

    // secure the user generated content
    $mailaddress = C4GUtils::secure_ugc( $mailaddress );

    // check if it's a valid emailaddress
    if (!C4GUtils::emailIsValid( $mailaddress )) {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_EMAILNOTVALID']
      );
    }

    // check if a user with this emailaddress has already joined the group
    $objMember = \MemberModel::findOneBy( 'email', $mailaddress );
    if ($objMember) {
      if (MemberGroupModel::isMemberOfGroup( $groupId, $objMember->id )) {
        return array
        (
          'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_USERALREADYINGROUP']
        );
      }
    }

    // generate Activationkey
    $key = C4gActivationkeyModel::generateActivationkey( 'c4g_joingroup:' . $groupId. '&' . $objThis->c4g_groups_permission_applicationgroup );
    $link = C4gActivationkeyModel::generateActivationLinkFromKey( $key );
    // send key to user
    if (static::sendInvitationMail ($objThis, $mailaddress, $groupId, $link, $objThis->User->username)) {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAIL_NOTIFICATION_INVITATION_SEND'],
        'dialogclose' => 'invitememberdialog'.$groupId
      );
    } else {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_INVITENOTSEND']
      );
    }
  } // end of function "inviteMember"

  /**
   * Send a mail to choosen (or all) members
   *
   * @param  object   $objThis  The "C4GGroups"-Module
   * @param  integer  $groupId
   * @param  array    $data
   * @return array
   */
  public static function sendMailToMember ($objThis, $groupId, $data)
  {
    // translate member-ids to their mailaddresses, if needed
    if (empty( $data['to'] )) {
      $objMembers = empty( $data['toid'] ) ? MemberModel::getMemberListForGroup( $groupId ) : MemberModel::findMultipleByIds(explode(';', $data['toid'] ));
      if ($objMembers) {
        $mailaddresses = array();
        foreach ($objMembers as $objMember) {
          // skip own address
          if ($objMember->id == $objThis->User->id) continue;
          $mailaddresses[] = $objMember->email;
        }

        // fetch groupname
        $objGroup = MemberGroupModel::findByPk( $groupId );
        if (!$objGroup) { return false;}
        $groupName = $objGroup->name;

        // reciever
        $data['to'] = trim(implode(', ', $mailaddresses));

        // subject
        //$mailData['subject'] = sprintf( $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAIL_AN_INVITATION_FROM'] , $senderName );

        // message-text
        $text = $data['text'];
        $data['text'] = sprintf( $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAIL_MESSAGE'] , $objThis->User->username, $groupName, $text );
      }
    }
    // check mail
    $mailErrors = C4GUtils::getMailErrors( $data );
    if (!empty( $mailErrors )) {
      return $mailErrors;
    }
    // send mail
    if (C4GUtils::sendMail( $data )) {
      return array
      (
        'performaction' => 'viewmemberlist:'.$groupId,
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAIL_NOTIFICATION_SEND']
      );
    } else {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_EMAILNOTSEND']
      );
    }
  } // end of function "sendMailToMember"

  /**
   * Sends an invitation (+link) to an email-address
   *
   * @param  object          $objThis     The "C4GGroups"-Module
   * @param  string          $to
   * @param  int             $groupId
   * @param  string          $link
   * @param  string          $senderName
   * @return array|boolean
   */
  public static function sendInvitationMail ($objThis, $to, $groupId, $link, $senderName)
  {
    // fetch groupname
    $objGroup = MemberGroupModel::findByPk( $groupId );
    if (!$objGroup) { return false;}
    $groupName = $objGroup->name;

    // preparemail
    $mailData = array();

    // reciever
    $mailData['to'] = trim($to);

    // subject
    $mailData['subject'] = sprintf( $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAIL_AN_INVITATION_FROM'] , $senderName );

    // message-text
    $mailData['text'] = sprintf( $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAIL_INVITATION_MESSAGE'] , $senderName, $groupName, $link );

    // send mail
    return C4GUtils::sendMail( $mailData );

  } // end of function "sendInvitationMail"

//----------------------------------------------------------------------------------------------------------------------------------------------------

  /**
   * Needed for using the "con4gis-Core - Activationpage"!
   * Handles join-group-functionality after clicking an invitationlink
   *
   * @param  string  $action
   * @param  array   $params
   * @return array            Keys: "success" and "output"
   */
  public function performActivationAction ($action, $params)
  {
    // load the languagefile
    // (because this is not available, by default when calling this function externally)
    \System::loadLanguageFile('frontendModules');
    // prepare output-array
    $return = array
    (
      'success' => false,
      'output' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_CANNOTJOINGROUP']
    );

    //check if the user is logged in
    if (FE_USER_LOGGED_IN === true) {
      $objUser = \FrontendUser::getInstance();

      // check action
      switch ($action) {
        case 'c4g_joingroup':
          if (!empty( $params[0] ) && MemberGroupModel::assignMemberToGroup( $params[0], $objUser->id, true )) {
            $objGroup = MemberGroupModel::findByPk( $params[0] );
            if ($objGroup) {
              $return['output'] = sprintf( $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_GROUPJOINED'], $objGroup->name );
              $return['success'] = true;
            }

            // assign member to the standard group
            if ( !empty($params[1]) && ($params[1] > 0) ) {
              MemberGroupModel::assignMemberToGroup( $params[1], $objUser->id, true );

              //if a member was added we have to change the member booking count
              //ToDo remove with BookingBundle
              if ($GLOBALS['con4gis_booking_extension']['installed']) {
                \c4g\projects\C4gBookingGroupsModel::checkMemberCount($params[1]);
              }
            }
          }
          break;

        default:
          break;
      }

    }
    // return output-array
    return $return;
  }

    /**
     * Creates a new rank.
     *
     * @param  object  $objThis    The "C4GGroups"-Module
     * @param  array   $arrConfig
     * @return array
     */
    public static function createRank ($objThis, $arrConfig, $groupId)
    {
        $ownerId = $objThis->User->id;

        // check permissions
        if (!MemberModel::hasRightInGroup( $ownerId, $groupId, 'rank_create' ) || !FE_USER_LOGGED_IN) {
            return array
            (
                'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'],
            );
        }



        // check if rankname is set...
        if (empty( $arrConfig['groupname'] ) || empty( $ownerId )) {
            return array
            (
                'usermessage' => $arrConfig['rankname'].$GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOGROUPNAME'],
            );
        }

        $group = $group = MemberGroupModel::findByPk( $groupId );
        $name = $group->name.'|'.$arrConfig['groupname'];

        // ...or already taken
        if (MemberGroupModel::findOneBy( 'name', $name )) {
            return array
            (
                'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_GROUPNAMETAKEN'],
            );
        }

        // get owners user-model
        $owner = MemberModel::findByPk( $ownerId );
        if (empty( $owner )) return;

        // create group
        $group = new MemberGroupModel();

        // set parent
        $group->cg_pid = $groupId;

        // set name
        $group->name = C4GUtils::secure_ugc( $name );

        // set timestamp
        $date = new \DateTime();
        $group->tstamp = $date->getTimestamp();

        // set maximum group-size
        $group->cg_max_member = $objThis->c4g_groups_default_maximum_size;

        // set first member (group-model)
        //$group->cg_member = serialize( array( $ownerId ) );

       // set member-rights
        $group->cg_member_rights = $objThis->c4g_groups_default_member_rights;
        // set owner-rights
        $group->cg_owner_rights = $objThis->c4g_groups_default_owner_rights;

        // save group
        $group->save();
        // $group->refresh();

        // set first member (member-model)
        //    this needs to be done after the group was saved,
        //    because the new group-id is needed here
//        $ownerGroups = unserialize( $owner->groups );
//        if (empty( $owner )) { $ownerGroups = array(); }
//        $ownerGroups[] = $group->id;
//        $owner->groups = serialize( $ownerGroups );
//        $owner->save();

        return array
        (
            'dialogclose' => 'rankcreatedialog' . $ownerId,
            'performaction' => 'viewrankmemberlist:' . $group->id,
        );
    } // end of function "createRank"

    /**
     * Creates a new rank.
     *
     * @param  object  $objThis    The "C4GGroups"-Module
     * @param  array   $arrConfig
     * @return array
     */
    public static function addMember ($objThis, $arrConfig, $rankId)
    {
        $ownerId = $objThis->User->id;
        $rank = MemberGroupModel::findByPk( $rankId );
        $groupId = $rank->cg_pid;

        // check permissions
        if (!MemberModel::hasRightInGroup( $ownerId, $groupId, 'rank_member' ) || !FE_USER_LOGGED_IN) {
            return array
            (
                'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'],
            );
        }



        // check if rankname is set...
        if (empty( $arrConfig['rankmember'] )) {
            return array
            (
                'usermessage' => $arrConfig['rankname'].$GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_ASSIGNRANKMEMBER'],
            );
        }

        $memberId = $arrConfig['rankmember'];

        if (!MemberModel::assignGroupToMember($memberId, $rankId)) {
            return array
            (
                'usermessage' => $arrConfig['rankname'].$GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NORANKMEMBER'],
            );
        } /*else {
          // assign member to the standard group
          if ( ($objThis->c4g_groups_permission_applicationgroup) && ($objThis->c4g_groups_permission_applicationgroup > 0))
            MemberModel::assignGroupToMember($memberId, $objThis->c4g_groups_permission_applicationgroup);
        }*/

        return array
        (
            'dialogclose' => 'rankmemberdialog' . $ownerId,
            'performaction' => 'viewrankmemberlist:' . $rankId,
        );
    } // end of function "createRank"

} // end of Class