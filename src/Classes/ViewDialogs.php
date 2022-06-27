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

namespace con4gis\GroupsBundle\Classes;

use con4gis\CoreBundle\Classes\C4GHTMLFactory;
use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\CoreBundle\Resources\contao\models\C4gActivationkeyModel;
use con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel;
use con4gis\GroupsBundle\Resources\contao\models\MemberModel;

/**
 * Class ViewDialogs
 * @package c4g
 */
class ViewDialogs
{
    /**
     * Shows "create group"-dialog
     *
     * @param  object       $objThis  The "C4GGroups"-Module
     * @return array|null
     */
    public static function viewGroupCreateDialog($objThis, $groupId)
    {
        if (!$objThis || !$objThis->user) {
            return;
        }
        $ownerId = $objThis->user->id;
        $dialogId = 'groupcreatedialog' . $ownerId;

        if (!$objThis->currentMemberHasPermission('creategroups')) {
            return [
                'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'],
            ];
        }

        $owner = MemberModel::findByPk($ownerId);
        if (empty($owner)) {
            return;
        }

        $view = '<div class="c4gGroups_dialog_groupCreate ui-widget ui-widget-content ui-corner-bottom">';

        // groupname (label + input)
        $view .=
      '<label for="cg_setgroupname">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPNAME'] . '</label>' .
      '<input type="text" id="cg_setgroupname" class="formdata" name="groupname" value="">' .
      C4GHTMLFactory::lineBreak();

        $view .= '</div>';

        // return
        return [
      'dialogtype' => 'html',
      'dialogdata' => $view,
      'dialogoptions' => C4GUtils::addDefaultDialogOptions([
        'title' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPCONFIG'],
        'modal' => true,
      ]),
      'dialogid' => $dialogId,
      'dialogstate' => 'viewmemberlist:' . $groupId . ';viewgroupcreatedialog',
      'dialogbuttons' => [
          [
            'action' => 'creategroup'/*.';viewmemberlist:'.$groupId*/,
            'class' => 'c4gGuiDefaultAction',
            'type' => 'send',
            'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['SAVE'],
          ],
          [
            'action' => 'closedialog:' . $dialogId,
            'type' => 'get',
            'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
          ],
      ],
    ];
    }// end of "viewGroupCreateDialog"

    /**
     * Shows "configurate group"-dialog
     *
     * @param  object       $objThis  The "C4GGroups"-Module
     * @param  integer      $groupId
     * @return array|null
     */
    public static function viewGroupConfigDialog($objThis, $groupId)
    {
        $dialogId = 'groupconfigdialog' . $groupId;

        // get group- and related member-models
        $group = MemberGroupModel::findByPk($groupId);
        $members = MemberModel::getMemberListForGroup($groupId);
        if (empty($group)/* || empty( $members )*/) {
            return;
        }

        // current member-id ("shortcut")
        $memberId = $objThis->user->id;

        $view = '<div class="c4gGroups_dialog_groupConfig ui-widget ui-widget-content ui-corner-bottom">' . C4GHTMLFactory::lineBreak();
        ;

        // add options
        // but display only options, wich the member is allowed to edit
        //
        // groupname (label + input)

        $nameRight = 'group_edit_name';
        //$formatRight = 'group_edit_membernameformat';
        $parentGroupId = $groupId;
        $group_edit_rights = 'group_edit_rights';
        if ($group->cg_pid > 0) {
            $nameRight = 'rank_edit_name';
            $parentGroupId = $group->cg_pid;
            $group_edit_rights = 'rank_edit_rights';
        }

        if (MemberModel::hasRightInGroup($memberId, $groupId, $nameRight)) {
            $view .=
        '<label for="cg_setgroupname">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPNAME'] . '</label>' .
        '<input type="text" id="cg_setgroupname" class="formdata" name="groupname" value="' . $group->name . '">' .
        C4GHTMLFactory::lineBreak();
        }

        // username format (label + input + span)
        if (MemberModel::hasRightInGroup($memberId, $parentGroupId, 'group_edit_membernameformat')) {
            $view .=
        '<label for="cg_setmembernameformat">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['MEMBERNAMEFORMAT'] . '</label>' .
        '<input type="text" id="cg_setmembernameformat" class="formdata" name="membernameformat" value="' . htmlentities($group->cg_member_displayname) . '">' .
        '<div class="cg_info_block">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_MEMBERNAMEFORMAT'] . '</div>' .
        C4GHTMLFactory::lineBreak();
        }

        // groupowner (label + select)
        if (MemberModel::hasRightInGroup($memberId, $parentGroupId, 'group_edit_owner')) {
            $view .=
        '<label for="cg_setgroupowner">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPOWNER'] . '</label>' .
        '<select type="text" id="cg_setgroupowner" class="formdata" name="groupowner">';
            foreach ($members as $member) {
                if ($member->id == $group->cg_owner_id) {
                    $selected = ' selected';
                } else {
                    $selected = '';
                }
                $view .= '<option' . $selected . ' value="' . $member->id . '">' . MemberModel::getDisplaynameForGroup($groupId, $member->id) . '</option>';
            }
            $view .=
        '</select>' .
        C4GHTMLFactory::lineBreak();
        }

        // member-rights (label + checkboxes)
        if (MemberModel::hasRightInGroup($memberId, $groupId, $group_edit_rights)) {
            // load the languagefile, which contains the rights
            \System::loadLanguageFile('tl_member_group');
            $ownergroup = $group;
            if ($ownergroup->cg_pid > 0) {
                $ownergroup = MemberGroupModel::findByPk($ownergroup->cg_pid);
            }
            if ($ownergroup->cg_owner_rights) {
                $arrOwnerRights = \Contao\StringUtil::deserialize($ownergroup->cg_owner_rights);
            } else {
                $arrOwnerRights = [];
            }
            if ($group->cg_member_rights) {
                $arrMemberRights = array_flip(\Contao\StringUtil::deserialize($group->cg_member_rights));
            } else {
                $arrMemberRights = [];
            }

            $view .=
        '<label>' . $GLOBALS['TL_LANG']['C4G_GROUPS']['MEMBERRIGHTS'] . '</label>';

            // only display the rights of the owner, since a member can never have more rights than the owner
            foreach ($arrOwnerRights as $ownerRight) {
                $rightId = 'right_' . $ownerRight;
                $rightName = $GLOBALS['TL_LANG']['tl_member_group']['cg_rights'][$ownerRight];
                if ($rightName) {
                    $view .=
                C4GHTMLFactory::lineBreak() .
                // [note] cannot use "memberrights[]" as name, since the custom "send-to-server"-function
                //   of "c4gGui.js" cannot handle this properly
                '<input type="checkbox" class="formdata" id="' . $rightId . '" name="' . $rightId . '" value="' .
                $ownerRight . '"' .
                (isset($arrMemberRights[$ownerRight])? ' checked="checked"': '') . '>' .
                '<label class="cg_checkbox_label" for="' . $rightId . '">' . $rightName . '</label>';
                }
            }

            $view .=
        C4GHTMLFactory::lineBreak() .
        C4GHTMLFactory::lineBreak();
        }

        $deleteRight = 'group_edit_delete';
        $viewmemberlist = 'viewmemberlist';
        $ownergroupId = $groupId;
        $label = $GLOBALS['TL_LANG']['C4G_GROUPS']['DELETEGROUP'];

        if ($group->cg_pid > 0) {
            $ownergroupId = $group->cg_pid;
            $deleteRight = 'rank_edit_delete';
            $viewmemberlist = 'viewrankmemberlist';
            $label = $GLOBALS['TL_LANG']['C4G_GROUPS']['DELETERANK'];
        }

        // delete group (label + input + span)
        if ($objThis->currentMemberHasPermission('deletegroups') || MemberModel::hasRightInGroup($memberId, $ownergroupId, $deleteRight)) {
            $view .=
        '<label for="cg_deletegroup">' . $label . '</label>' .
        '<input type="text" id="cg_deletegroup" class="formdata" name="deletegroup" value="">' .
        '<div class="cg_info_block">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_DELETE'] . '</div>' .
        C4GHTMLFactory::lineBreak();
        }

        $view .= '</div>';

        $title = $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPCONFIG'];
        if ($group->cg_pid > 0) {
            $title = $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKCONFIG'];
        }

        // return
        return [
      'dialogtype' => 'html',
      'dialogdata' => $view,
      'dialogoptions' => C4GUtils::addDefaultDialogOptions([
        'title' => $title,
        'modal' => true,
      ]),
      'dialogid' => $dialogId,
      'dialogstate' => $viewmemberlist . $groupId . ';viewgroupconfigdialog:' . $groupId,
      'dialogbuttons' => [
        [
          'action' => 'configuregroup:' . $groupId,
          'class' => 'c4gGuiDefaultAction',
          'type' => 'send',
          'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['SAVE'],
        ],
        [
          'action' => 'closedialog:' . $dialogId,
          'type' => 'get',
          'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
        ],
      ],
    ];
    }// end of "viewGroupConfigDialog"

    /**
     * Shows "configurate group"-dialog
     *
     * @param  object       $objThis  The "C4GGroups"-Module
     * @param  integer      $groupId
     * @return array|null
     */
    public static function viewRankConfigDialog($objThis, $groupId)
    {
        $dialogId = 'rankconfigdialog' . $groupId;

        // get group- and related member-models
        $group = MemberGroupModel::findByPk($groupId);
        $members = MemberModel::getMemberListForGroup($groupId);
        if (empty($group)/* || empty( $members )*/) {
            return;
        }

        // current member-id ("shortcut")
        $memberId = $objThis->user->id;

        $view = '<div class="c4gGroups_dialog_groupConfig ui-widget ui-widget-content ui-corner-bottom">' . C4GHTMLFactory::lineBreak();
        ;

        if (MemberModel::hasRightInGroup($memberId, $groupId, 'rank_edit_name')) {
            $view .=
                '<label for="cg_setgroupname">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKNAME'] . '</label>' .
                '<input type="text" id="cg_setgroupname" class="formdata" name="groupname" value="' . substr($group->name, strrpos($group->name, '|') + 1) . '">' .
                C4GHTMLFactory::lineBreak();
        }

        // member-rights (label + checkboxes)
        if (MemberModel::hasRightInGroup($memberId, $groupId, 'rank_edit_rights')) {
            // load the languagefile, which contains the rights
            \System::loadLanguageFile('tl_member_group');
            $ownergroup = MemberGroupModel::findByPk($group->cg_pid);

            if ($ownergroup->cg_owner_rights) {
                $arrOwnerRights = \Contao\StringUtil::deserialize($ownergroup->cg_owner_rights);
            } else {
                $arrOwnerRights = [];
            }

            if ($group->cg_member_rights) {
                $arrMemberRights = array_flip(\Contao\StringUtil::deserialize($group->cg_member_rights));
            } else {
                $arrMemberRights = [];
            }

            $view .=
                '<label>' . $GLOBALS['TL_LANG']['C4G_GROUPS']['MEMBERRIGHTS'] . '</label>';

            // only display the rights of the owner, since a member can never have more rights than the owner
            foreach ($arrOwnerRights as $ownerRight) {
                $rightId = 'right_' . $ownerRight;
                $rightName = $GLOBALS['TL_LANG']['tl_member_group']['cg_rights'][$ownerRight];
                if ($rightName) {
                    $view .=
                        C4GHTMLFactory::lineBreak() .
                        // [note] cannot use "memberrights[]" as name, since the custom "send-to-server"-function
                        //   of "c4gGui.js" cannot handle this properly
                        '<input type="checkbox" class="formdata" id="' . $rightId . '" name="' . $rightId . '" value="' .
                        $ownerRight . '"' .
                        (isset($arrMemberRights[$ownerRight])? ' checked="checked"': '') . '>' .
                        '<label class="cg_checkbox_label" for="' . $rightId . '">' . $rightName . '</label>';
                }
            }

            $view .=
                C4GHTMLFactory::lineBreak() .
                C4GHTMLFactory::lineBreak();
        }

//        $ownergroupId = $group->cg_pid;
        $deleteRight = 'rank_edit_delete';
        $viewmemberlist = 'viewrankmemberlist';

        // delete group (label + input + span)
        if ($objThis->currentMemberHasPermission('deletegroups') || MemberModel::hasRightInGroup($memberId, $groupId, $deleteRight)) {
            $view .=
                '<label for="cg_deletegroup">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['DELETEGROUP'] . '</label>' .
                '<input type="text" id="cg_deletegroup" class="formdata" name="deletegroup" value="">' .
                '<div class="cg_info_block">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_DELETE'] . '</div>' .
                C4GHTMLFactory::lineBreak();
        }

        $view .= '</div>';

        $title = $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKCONFIG'];

        // return
        return [
            'dialogtype' => 'html',
            'dialogdata' => $view,
            'dialogoptions' => C4GUtils::addDefaultDialogOptions([
                'title' => $title,
                'modal' => true,
            ]),
            'dialogid' => $dialogId,
            'dialogstate' => $viewmemberlist . $groupId . ';viewrankconfigdialog:' . $groupId,
            'dialogbuttons' => [
                [
                    'action' => 'configurerank:' . $groupId,
                    'class' => 'c4gGuiDefaultAction',
                    'type' => 'send',
                    'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['SAVE'],
                ],
                [
                    'action' => 'closedialog:' . $dialogId,
                    'type' => 'get',
                    'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
                ],
            ],
        ];
    }// end of "viewRankConfigDialog"

    /**
     * Show "remove Member" Dialog.
     *
     * @param  object  $objThis       The "C4GGroups"-Module
     * @param  integer $groupId       [description]
     * @param  array   $arrMemberIds  [description]
     * @return array                  [description]
     */
    public static function viewRemoveMemberDialog($objThis, $groupId, $arrMemberIds)
    {
        $dialogId = 'removememberdialog' . $groupId;
        $arrRemoveMemberIds = [];
        $arrDialogButtons = [];
        $removeSelf = false;
        $additionalInfo = '';
        $removeMemberList = '';

        // try to fetch group and members
        $objGroup = MemberGroupModel::findByPk($groupId);
        $objMembers = MemberModel::findMultipleByIds($arrMemberIds);
        if (empty($objGroup) || empty($objMembers)) {
            $objMembers = [];
        }

//      $parent = $objGroup->cg_pid;
//      if ($parent) {
//
//      }

        // build memberlist
        //   and check if one of the member is the owner of this group
        //   or the action-executing member
        foreach ($objMembers as $objMember) {
            if (MemberGroupModel::isMemberOfGroup($groupId, $objMember->id)) {
                if (MemberGroupModel::isOwnerOfGroup($groupId, $objMember->id)) {
                    // an owner was tried to remove
                    $additionalInfo = $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_REMOVEOWNER'];
                } else {
                    if ($objMember->id === $objThis->user->id) {
                        // the member is going to remove himself
                        $additionalInfo = $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_REMOVESELF'];
                    }
                    // add member to "remove list"
                    $arrRemoveMemberIds[] = $objMember->id;
                    $removeMemberList .= '<li>' . MemberModel::getDisplaynameForGroup($groupId, $objMember->id) . '</li>';
                }
            }
        }

        // build output
        $view = '<div class="c4gGroups_dialog_removeMember ui-widget ui-widget-content ui-corner-bottom">';
        // display additional info, if existing
        if (!empty($additionalInfo)) {
            $view .= '<div class="cg_info_block">' . $additionalInfo . '</div>';
        }
        // check if there are valid member-ids to remove
        if (!empty($arrRemoveMemberIds)) {
            // remove-message
            $view .= C4GHTMLFactory::paragraph(sprintf($GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_REMOVE_MEMBER'], '<strong>' . $objGroup->name . '</strong>'));
            // memberlist
            $view .= '<ul>' . $removeMemberList . '</ul>';

            // add "confirm"-button to the dialog-buttons
            $arrDialogButtons[] = [
        'action' => 'removemember:' . $groupId . ':' . implode(',', $arrRemoveMemberIds),
        'class' => 'c4gGuiDefaultAction',
        'type' => 'send',
        'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['REMOVEMEMBER'],
      ];
        } else {
            $view .= '<div class="cg_info_block">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOVALIDMEMBERTOREMOVE'] . '</div>';
        }

        $view .= '</div>';

        // add "close"-button to the dialog-buttons
        //   its label changes from "cancel" to "back" if there is no one to remove
        $arrDialogButtons[] = [
      'action' => 'closedialog:' . $dialogId,
      'type' => 'get',
      'text' => empty($arrRemoveMemberIds) ? $GLOBALS['TL_LANG']['C4G_GROUPS']['BACK'] : $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
    ];

        // return
        return [
      'dialogtype' => 'html',
      'dialogdata' => $view,
      'dialogoptions' => C4GUtils::addDefaultDialogOptions([
        'title' => $GLOBALS['TL_LANG']['C4G_GROUPS']['REMOVEMEMBER'],
        'modal' => true,
      ]),
      'dialogid' => $dialogId,
      'dialogstate' => 'viewmemberlist:' . $groupId . ';viewremovememberdialog:' . $groupId,
      'dialogbuttons' => $arrDialogButtons,
    ];
    }// end of "viewRemoveMemberDialog"

    /**
     * Show "leave group" dialog.
     *
     * @param  object        $objThis  The "C4GGroups"-Module
     * @param  integer       $groupId  [description]
     * @return array|null              [description]
     */
    public static function viewLeaveGroupDialog($objThis, $groupId)
    {
        $dialogId = 'leavegroupdialog' . $groupId;

        $returnCannotLeave = [
      'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_CANNOTLEAVEGROUP'],
    ];
        $confirmAction = 'removemember:' . $groupId;
        $memberId = $objThis->user->id;

        $group = MemberGroupModel::findByPk($groupId);
        $member = MemberModel::findByPk($objThis->user->id);
        if (empty($group) || empty($member)) {
            return;
        }

        // check if user is in this group
        if (!MemberGroupModel::isMemberOfGroup($groupId, $memberId)) {
            return $returnCannotLeave;
        }

        // built output
        $view = '<div class="c4gGroups_dialog_leaveGroup ui-widget ui-widget-content ui-corner-bottom">';

        // remove-message
        $view .= C4GHTMLFactory::paragraph(sprintf($GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_LEAVE_GROUP'], '<strong>' . $group->name . '</strong>'));

        // check if user is owner of this group
        if (MemberGroupModel::isOwnerOfGroup($groupId, $memberId)) {
            if (MemberModel::hasRightInGroup($memberId, $groupId, 'group_edit_owner')) {
                $view .=
          '<div class="cg_info_block">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_LEAVE_GROUP_OWNER'] . '<br><br>' .
          '<label for="cg_setgroupowner">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['NEWGROUPOWNER'] . '</label>' .
          '<select type="text" id="cg_setgroupowner" class="formdata" name="groupowner">';
                $members = MemberModel::getMemberListForGroup($groupId);
                if (!$members) {
                    return $returnCannotLeave;
                }
                $break = true;
                foreach ($members as $member) {
                    if ($member->id != $group->cg_owner_id) {
                        // skip owner, since he is the one who wants to leave
                        $view .= '<option value="' . $member->id . '">' . MemberModel::getDisplaynameForGroup($groupId, $member->id) . '</option>';
                        $break = false;
                    }
                }
                // return error message if there are no other members
                if ($break) {
                    return $returnCannotLeave;
                }
                $view .=
          '</select></div>' .
          C4GHTMLFactory::lineBreak();

                $confirmAction = 'configuregroup:' . $groupId . ';' . $confirmAction;
            } else {
                return $returnCannotLeave;
            }
        }

        // close div
        $view .= '</div>';

        // return
        return [
      'dialogtype' => 'html',
      'dialogdata' => $view,
      'dialogoptions' => C4GUtils::addDefaultDialogOptions([
        'title' => $GLOBALS['TL_LANG']['C4G_GROUPS']['LEAVEGROUP'],
        'modal' => true,
      ]),
      'dialogid' => $dialogId,
      'dialogstate' => 'viewmemberlist:' . $groupId . ';leavegroupdialog:' . $groupId,
      'dialogbuttons' => [
        [
          'action' => $confirmAction,
          'class' => 'c4gGuiDefaultAction',
          'type' => 'send',
          'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['LEAVEGROUP'],
        ],
        [
          'action' => 'closedialog:' . $dialogId,
          'type' => 'get',
          'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
        ],
      ],
    ];
    }// end of "viewLeaveGroupDialog"

    /**
     * Show "invite Member" Dialog.
     *   Used for multiple invitation-methods.
     *   NOTE: Test for 'member_invite_'-right before executing!
     *
     * @param  object   $objThis  The "C4GGroups"-Module
     * @param  integer  $groupId  [description]
     * @param  string   $mode     [description]
     * @return array              [description]
     */
    public static function viewInviteMemberDialog($objThis, $groupId, $mode = 'select')
    {
        $dialogId = 'invitememberdialog' . $groupId;

        $arrDialogButtons = [];

        // fetch rights
        $rightEmail = MemberModel::hasRightInGroup($objThis->user->id, $groupId, 'member_invite_email');
        $rightLink = MemberModel::hasRightInGroup($objThis->user->id, $groupId, 'member_invite_link');
        // skip 'select-menu' if only the email-right is granted
        if ($rightEmail && !$rightLink) {
            $mode = 'email';
        }

        // build output
        $view = '<div class="c4gGroups_dialog_inviteMember ui-widget ui-widget-content ui-corner-bottom">';

        // check mode
        switch ($mode) {
      case 'email':
        // invite members via email
        if ($rightEmail) {
            $view .=
            '<div class="cg_block">' .
              '<label for="cg_mailaddress">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAIL'] . '</label>' .
              '<input type="text" id="cg_mailaddress" class="formdata" name="mailaddress" value="">' .
              C4GHTMLFactory::span('&nbsp; ' . $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_INVITEMAIL_ADDRESS']) .
            '</div>';

            $arrDialogButtons[] = [
            'action' => 'invitemember:' . $groupId,
            'class' => 'c4gGuiDefaultAction',
            'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['INVITEMEMBER'],
            'type' => 'send',
          ];
        } else {
            $view .=
            '<div class="cg_info_block">' .
              $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'] .
            '</div>';
        }

        $arrDialogButtons[] = [
            'action' => 'closedialog:' . $dialogId,
            'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
            'type' => 'get',
          ];

        break;
      case 'link':
        // create invitation-link
        if ($rightLink) {
            // generate Activationkey
            $key = C4gActivationkeyModel::generateActivationkey('c4g_joingroup:' . $groupId . '&' . $objThis->c4g_groups_permission_applicationgroup);
            $link = C4gActivationkeyModel::generateActivationLinkFromKey($key, 'c4g_joingroup');

            $view .=
            '<div class="cg_info_block">' .
              $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_INVITATIONLINK'] .
            '</div>' .
            '<div class="cg_block">' .
              $link .
            '</div>';
        } else {
            $view .=
            '<div class="cg_info_block">' .
              $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'] .
            '</div>';
        }

        $arrDialogButtons[] = [
          'action' => 'closedialog:' . $dialogId,
          'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['BACK'],
          'type' => 'get',
        ];

        break;

      case 'select':
      default:
        $view .=
          '<div class="cg_info_block">' .
            $GLOBALS['TL_LANG']['C4G_GROUPS']['INFO_CHOOSEINVITEMETHOD'] .
          '</div>' .
          '<div class="cg_block">';

        if ($rightEmail) {
            $view .=
            C4GHTMLFactory::c4gGuiButton($GLOBALS['TL_LANG']['C4G_GROUPS']['INVITE_MAIL'], 'viewinvitememberdialog:' . $groupId . ':email') .
            '&nbsp;';
        }
        if ($rightLink) {
            $view .=
            C4GHTMLFactory::c4gGuiButton($GLOBALS['TL_LANG']['C4G_GROUPS']['INVITE_LINK'], 'viewinvitememberdialog:' . $groupId . ':link');
        }

        $view .= '</div>';

        $arrDialogButtons[] = [
          'action' => 'closedialog:' . $dialogId,
          'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
          'type' => 'get',
        ];

        break;
    }

        $view .= '</div>';

        // return
        return [
      'dialogtype' => 'html',
      'dialogdata' => $view,
      'dialogoptions' => C4GUtils::addDefaultDialogOptions([
        'title' => $GLOBALS['TL_LANG']['C4G_GROUPS']['INVITEMEMBER'],
        'modal' => true,
      ]),
      'dialogid' => $dialogId,
      'dialogstate' => 'viewmemberlist:' . $groupId . ';viewinvitememberdialog:' . $groupId,
      'dialogbuttons' => $arrDialogButtons,
    ];
    } // end of "viewInviteMemberDialog"

    /**
     * Show "send mail" dialog.
     * If $memberIds is empty, the mail will be send to all members.
     *
     * @param  object   $objThis    The "C4GGroups"-Module
     * @param  integer  $groupId    [description]
     * @param  array    $memberIds  [description]
     * @return array                [description]
     */
    public static function viewSendEmailDialog($objThis, $groupId, $memberIds = [])
    {
        $dialogId = 'sendemaildialog' . $groupId;

        // fetch display for chosen members
        $memberNames = [];
        foreach ($memberIds as $memberId) {
            $member = MemberModel::findByPk($memberId);
            if (empty($member)) {
                continue;
            }

            $memberNames[] = MemberModel::getDisplaynameForGroup($groupId, $memberId);
        }
        // send mail to all members if ($memberId)s is empty
        if (empty($memberNames)) {
            $memberNames[] = $GLOBALS['TL_LANG']['C4G_GROUPS']['EVERYONEINGROUP'];
        }

        $view = '<div class="c4gGroups_dialog_sendEmail ui-widget ui-widget-content ui-corner-bottom">' . C4GHTMLFactory::lineBreak();

        $view .=
      '<input type="hidden" id="cg_memberids" class="formdata" name="toid" value="' . implode(';', $memberIds) . '" disabled>' .
      '<label for="cg_reciever">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['RECIEVER'] . '</label>' .
      //@TODO [note] make this editable? choosen-field?
      '<input type="text" id="cg_reciever" class="formnodata" name="reciever" value="' . implode(', ', $memberNames) . '" disabled>' .
      C4GHTMLFactory::lineBreak() .
      '<label for="cg_mailsubject">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAILSUBJECT'] . '</label>' .
      '<input type="text" id="cg_mailsubject" class="formdata" name="subject" value="">' .
      C4GHTMLFactory::lineBreak() .
      '<label for="cg_mailmessage">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['EMAILMESSAGE'] . '</label>' .
      '<textarea id="cg_mailmessage" class="formdata" name="text" rows="6"></textarea>' .
      C4GHTMLFactory::lineBreak() .
      '</div>';

        // return
        return [
      'dialogtype' => 'html',
      'dialogdata' => $view,
      'dialogoptions' => C4GUtils::addDefaultDialogOptions([
        'title' => $GLOBALS['TL_LANG']['C4G_GROUPS']['SENDMAIL'],
        'modal' => true,
      ]),
      'dialogid' => $dialogId,
      'dialogstate' => 'viewmemberlist:' . $groupId . ';viewsendemaildialog:' . $groupId,
      'dialogbuttons' => [
        [
          'action' => 'sendmembermail:' . $groupId,
          // 'class' => 'c4gGuiDefaultAction',
          'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['SENDMAIL'],
          'type' => 'send',
        ],
        [
          'action' => 'closedialog:' . $dialogId,
          'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
          'type' => 'get',
        ],
      ],
    ];
    } // end of "viewSendEmailDialog"

    /**
     * Shows "create rank"-dialog
     *
     * @param  object       $objThis  The "C4GGroups"-Module
     * @return array|null
     */
    public static function viewRankCreateDialog($objThis, $groupId)
    {
        if (!$objThis->user) {
            return;
        }
        $ownerId = $objThis->user->id;
        $dialogId = 'rankcreatedialog' . $ownerId;

        if (!MemberModel::hasRightInGroup($ownerId, $groupId, 'rank_create')) {
            return [
                'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'],
            ];
        }

        $owner = MemberModel::findByPk($ownerId);
        if (empty($owner)) {
            return;
        }

        $view = '<div class="c4gGroups_dialog_rankCreate ui-widget ui-widget-content ui-corner-bottom">';

        // groupname (label + input)
        $view .=
            C4GHTMLFactory::lineBreak() .
            '<label for="cg_setgroupname">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKNAME'] . '</label>' .
            '<input type="text" id="cg_setgroupname" class="formdata" name="groupname" value="">' .
            C4GHTMLFactory::lineBreak();

        $view .= '</div>';

        $title = $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKCONFIG'];

        // return
        return [
            'dialogtype' => 'html',
            'dialogdata' => $view,
            'dialogoptions' => C4GUtils::addDefaultDialogOptions([
                'title' => $title,
                'modal' => true,
            ]),
            'dialogid' => $dialogId,
            'dialogstate' => 'viewrankmemberlist:' . $groupId . ';viewrankcreatedialog',
            'dialogbuttons' => [
                [
                    'action' => 'createrank:' . $groupId,
                    'class' => 'c4gGuiDefaultAction',
                    'type' => 'send',
                    'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['SAVE'],
                ],
                [
                    'action' => 'closedialog:' . $dialogId,
                    'type' => 'get',
                    'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
                ],
            ],
        ];
    }// end of "viewRankCreateDialog"

    /**
     * Shows "create rank"-dialog
     *
     * @param  object       $objThis  The "C4GGroups"-Module
     * @return array|null
     */
    public static function viewAddMemberDialog($objThis, $rankId)
    {
        if (!$objThis->user) {
            return;
        }
        $ownerId = $objThis->user->id;
        $dialogId = 'rankmemberdialog' . $ownerId;
        $rank = MemberGroupModel::findByPk($rankId);
        $groupId = $rank->cg_pid;

        if (!MemberModel::hasRightInGroup($ownerId, $groupId, 'rank_member')) {
            return [
                'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'],
            ];
        }

        $owner = MemberModel::findByPk($ownerId);
        if (empty($owner)) {
            return;
        }

        $view = '<div class="c4gGroups_dialog_rankMember ui-widget ui-widget-content ui-corner-bottom">';

        $memberlist = MemberModel::getMemberListForGroup($groupId);
        $options = [];
        foreach ($memberlist as $member) {
            if (
                (MemberGroupModel::isMemberOfGroup($groupId, $member->id)) &&
                (!MemberGroupModel::isMemberOfGroup($rankId, $member->id))) {
                $option_id = $member->id;
                $option_name = MemberModel::getDisplaynameForGroup($groupId, $option_id);
                $options = $options . '<option value=' . $option_id . '>' . $option_name . '</option>';
            }
        }

        if (empty($options)) {
            return [
                'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOMOREMEMBERSAVAILABLE'],
            ];
        }

        // groupname (label + input)
        $view .=
            '<label for="cg_rankmember">' . $GLOBALS['TL_LANG']['C4G_GROUPS']['MEMBERNAME'] . '</label>' .
            '<select id="' . cg_rankmember . '" class="formdata" name="rankmember" size="1" >' . $options . '</select>' .
            C4GHTMLFactory::lineBreak();

        $view .= '</div>';

        // return
        return [
            'dialogtype' => 'html',
            'dialogdata' => $view,
            'dialogoptions' => C4GUtils::addDefaultDialogOptions([
                'title' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPCONFIG'],
                'modal' => true,
            ]),
            'dialogid' => $dialogId,
            'dialogstate' => 'viewrankmemberlist:' . $groupId . ';viewaddmemberdialog',
            'dialogbuttons' => [
                [
                    'action' => 'addmember:' . $groupId . ':' . $rankId,
                    'class' => 'c4gGuiDefaultAction',
                    'type' => 'send',
                    'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['SAVE'],
                ],
                [
                    'action' => 'closedialog:' . $dialogId,
                    'type' => 'get',
                    'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CANCEL'],
                ],
            ],
        ];
    }// end of "viewRankCreateDialog"
} // end of Class
