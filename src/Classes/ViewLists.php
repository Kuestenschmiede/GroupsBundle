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
use con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel;
use con4gis\GroupsBundle\Resources\contao\models\MemberModel;
use Contao\StringUtil;

/**
 * Class ViewLists
 * @package c4g
 */
class ViewLists
{
    /**
     * Generates a list of groups for the current member
     *
     * @param  object  $objThis  The "C4GGroups"-Module
     * @return array
     */
    public static function viewGroupList($objThis, $headline = '')
    {
        // check Login-State
        if (!$objThis->user) {
            $return['usermessage'] = $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOTLOGGEDIN'];

            return $return;
        }

        $memberId = $objThis->user->id;
        $mgroups = StringUtil::deserialize($objThis->user->groups);
        foreach ($mgroups as $mgroup) {
            //fix group assignment with default registration
            if (!MemberGroupModel::isMemberOfGroup($mgroup,$memberId)) {
                MemberGroupModel::assignMemberToGroup($mgroup, $memberId, false);
            }
        }

        // fetch data from db
        $groups = MemberGroupModel::getGroupListForMember($memberId);

        // define datatable
        $data = [];
        $data['aoColumnDefs'] = [
        ['sTitle' => 'key', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => [0], 'responsivePriority' => [0]],
        ['sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPNAME'], 'aDataSort' => [1], 'aTargets' => [1], 'responsivePriority' => [1]],
        ['sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPSIZE'], 'sWidth' => '20%', 'aTargets' => [2], 'responsivePriority' => [2]],
        ['sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPRANK'], 'sWidth' => '20%', 'aDataSort' => [3], 'bSearchable' => false, 'aTargets' => [3], 'responsivePriority' => [3]],
    ];

        $data['bJQueryUI'] = true;
        $data['aaSorting'] = [[1, 'desc']];
        $data['bScrollCollapse'] = true;
        $data['bStateSave'] = true;
        $data['sPaginationType'] = 'full_numbers';
        $data['oLanguage'] = [
        'oPaginate' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PAGINATION'],
        'sEmptyTable' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_EMPTY'],
        'sInfo' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_INFO'],
        'sInfoEmpty' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_EMPTY'],
        'sInfoFiltered' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_FILTERED'],
        'sInfoThousands' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_THOUSANDS'],
        'sLengthMenu' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_LENGTHMENU'],
        'sProcessing' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PROCESSING'],
        'sSearch' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_SEARCH'],
        'sZeroRecords' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_NOTFOUND'],
    ];
        $data['responsive'] = true;

        // insert data into table
        foreach ($groups as $group) {
            // count group members
            $memberCount = \Contao\StringUtil::deserialize($group->cg_member);
            if (empty($memberCount)) {
                $memberCount = 0;
            } else {
                $memberCount = count($memberCount);
            }

            // check for maximum group-size
            $maxMember = '';
            if ($group->cg_max_member != 0) {
                $maxMember = sprintf($GLOBALS['TL_LANG']['C4G_GROUPS']['OF_MAX_MEMBER'], $group->cg_max_member);
            }

            // fetch member-ranks
            $memberRanks = MemberGroupModel::getMemberRanksOfGroupAsString($group->id, $memberId);

            $data['aaData'][] = [
          'viewmemberlist:' . $group->id,
          $group->name,
          $memberCount . $maxMember,
          $memberRanks,
      ];
        }

        // check permissions and add create-button
        $buttonBar = [];
        if ($objThis->currentMemberHasPermission('creategroups')) {
            $buttonBar[] = [
        'id' => 'viewgroupcreatedialog:' . $memberId,
        'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CREATEGROUP'],
      ];
        }

        if (!$headline) {
            $headline = C4GHTMLFactory::headline($GLOBALS['TL_LANG']['C4G_GROUPS']['HEADLINES_MYGROUPS']);
        } else {
            $headline = '';
        }

        // return it
        return [
      'contenttype' => 'datatable',
      'contentdata' => $data,
      'contentoptions' => [
        'actioncol' => 0,
        // 'tooltipcol' => 10,
        'selectOnHover' => true,
        'clickAction' => true,
      ],
      'state' => 'viewgrouplist;',
      'headline' => $headline,
      'buttons' => $buttonBar,
    ];
    }// end of "viewGroupList"

    /**
     * Generates a list of members of the given group
     *
     * @param  object  $objThis  The "C4GGroups"-Module
     * @param  int     $groupId
     * @return array
     */
    public static function viewMemberList($objThis, $groupId)
    {
        $memberId = $objThis->user->id;

        // fetch data from db
        $members = MemberModel::getMemberListForGroup($groupId);
        $group = MemberGroupModel::findByPk($groupId);

        // define datatable
        $data = [];
        $data['aoColumnDefs'] = [
        ['sTitle' => 'key', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => [0], 'responsivePriority' => [0]],
        ['sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPMEMBER'], 'aDataSort' => [1,2], 'aTargets' => [1], 'responsivePriority' => [1]],
        ['sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPRANK'], 'sWidth' => '20%', 'aDataSort' => [2,1], 'aTargets' => [2], 'responsivePriority' => [2]],
        ['sTitle' => 'tooltip', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => [3], 'responsivePriority' => [3]],
    ];
        $data['bJQueryUI'] = true;
        $data['aaSorting'] = [[2, 'desc']];
        $data['bScrollCollapse'] = true;
        $data['bStateSave'] = true;
        $data['sPaginationType'] = 'full_numbers';
        $data['oLanguage'] = [
        'oPaginate' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PAGINATION'],
        'sEmptyTable' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_EMPTY'],
        'sInfo' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_INFO'],
        'sInfoEmpty' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_EMPTY'],
        'sInfoFiltered' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_FILTERED'],
        'sInfoThousands' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_THOUSANDS'],
        'sLengthMenu' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_LENGTHMENU'],
        'sProcessing' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PROCESSING'],
        'sSearch' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_SEARCH'],
        'sZeroRecords' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_NOTFOUND'],
    ];
        $data['responsive'] = true;

        // insert data into table
        foreach ($members as $member) {
            // get membername in the right format
            $memberName = MemberModel::getDisplaynameForGroup($groupId, $member->id);
            // fetch member-ranks
            $memberRanks = MemberGroupModel::getMemberRanksOfGroupAsString($groupId, $member->id);

            // highlight group-owner
            if ($objThis->c4g_groups_appearance_highlight_owner && MemberGroupModel::isOwnerOfGroup($groupId, $member->id)) {
                $memberName = '<strong>' . $memberName . '</strong>';
                // $memberRanks = '<strong>' . $memberRanks . '</strong>';
            }

            $data['aaData'][] = [
          $member->id,
          $memberName,
          $memberRanks,
          sprintf($GLOBALS['TL_LANG']['C4G_GROUPS']['TOOLTIP_CLICKTOSELECT'], $memberName),
      ];
        }

        // built button-bar
        //
        // back-button
        $buttonBar = [
      [
        'id' => 'viewgrouplist',
        'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['BACK'],
      ],
    ];
        // group-configuration
        if ($objThis->currentMemberHasPermission('deletegroups') || MemberModel::hasRightInGroup($memberId, $groupId, 'group_edit_')) {
            $buttonBar[] = [
        'id' => 'viewgroupconfigdialog:' . $groupId,
        'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPCONFIG'],
      ];
        }
        // rank-configuration
        if (MemberModel::hasRightInGroup($memberId, $groupId, 'rank_create') || MemberGroupModel::getMemberRanksOfGroup($groupId, $memberId) > 0) {
            $buttonBar[] = [
              'id' => 'viewranklist:' . $groupId,
              'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKLIST'],
          ];
        }
        // invitation
        if (MemberModel::hasRightInGroup($memberId, $groupId, 'member_invite_')) {
            $buttonBar[] = [
        'id' => 'viewinvitememberdialog:' . $groupId,
        'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['INVITEMEMBER'],
      ];
        }
        // remove member
        if (MemberModel::hasRightInGroup($memberId, $groupId, 'member_remove')) {
            $buttonBar[] = [
        'id' => 'viewremovememberdialog:' . $groupId,
        'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['REMOVEMEMBER'],
        'tableSelection' => true,
      ];
        }
        if (MemberModel::hasRightInGroup($memberId, $groupId, 'group_leave')) {
            // leave group
            $buttonBar[] = [
            'id' => 'viewleavegroupdialog:' . $groupId,
            'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['LEAVEGROUP'],
        ];
        }
        // contact
        if (MemberModel::hasRightInGroup($memberId, $groupId, 'member_contact_')) {
            $buttonBar[] = [
        'id' => 'viewsendmaildialog:' . $groupId,
        'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['SENDMAIL'],
        'tableSelection' => true,
      ];
        }

        // return it
        return [
      'contenttype' => 'datatable',
      'contentdata' => $data,
      'contentoptions' => [
        'actioncol' => 0,
        'tooltipcol' => 3,
        'clickAction' => false,
        'multiSelect' => true,
      ],
      'state' => 'viewmemberlist:' . $groupId,
      'headline' => C4GHTMLFactory::headline($group->name, 2),
      'buttons' => $buttonBar,
    ];
    } // end of "viewMemberList"

    /**
     * Generates a list of ranks for the current member
     *
     * @param  object  $objThis  The "C4GGroups"-Module
     * @return array
     */
    public static function viewRankList($objThis, $groupId)
    {
        // check Login-State
        if (!$objThis->user) {
            $return['usermessage'] = $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOTLOGGEDIN'];

            return $return;
        }

        $memberId = $objThis->user->id;

        // fetch data from db
        $ranks = MemberGroupModel::getMemberRanksOfGroup($groupId, $memberId);
        $group = MemberGroupModel::findByPk($groupId);

        // define datatable
        $data = [];
        $data['aoColumnDefs'] = [
            ['sTitle' => 'key', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => [0], 'responsivePriority' => [0]],
            ['sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKNAME'], 'aDataSort' => [1], 'aTargets' => [1], 'responsivePriority' => [1]],
            ['sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKSIZE'], 'sWidth' => '10%', 'aTargets' => [2], 'responsivePriority' => [2]],
        ];

        $data['bJQueryUI'] = true;
        $data['aaSorting'] = [[1, 'desc']];
        $data['bScrollCollapse'] = true;
        $data['bStateSave'] = true;
        $data['sPaginationType'] = 'full_numbers';
        $data['oLanguage'] = [
            'oPaginate' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PAGINATION'],
            'sEmptyTable' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_EMPTY'],
            'sInfo' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_INFO'],
            'sInfoEmpty' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_EMPTY'],
            'sInfoFiltered' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_FILTERED'],
            'sInfoThousands' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_THOUSANDS'],
            'sLengthMenu' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_LENGTHMENU'],
            'sProcessing' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PROCESSING'],
            'sSearch' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_SEARCH'],
            'sZeroRecords' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_NOTFOUND'],
        ];
        $data['responsive'] = true;
        // insert data into table
        if ($ranks) {
            foreach ($ranks as $rank) {
                // count group members
                $memberCount = \Contao\StringUtil::deserialize($rank->cg_member);
                if (empty($memberCount)) {
                    $memberCount = 0;
                } else {
                    $memberCount = count($memberCount);
                }

                $data['aaData'][] = [
                    'viewrankmemberlist:' . $rank->id,
                    substr($rank->name, strrpos($rank->name, '|') + 1),
                    $memberCount,
                ];
            }
        }

        // check permissions and add create-button
        $buttonBar = [];

        // back-button
        $buttonBar = [
            [
                'id' => 'viewmemberlist:' . $groupId,
                'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['BACK'],
            ],
        ];

        if (MemberModel::hasRightInGroup($memberId, $groupId, 'rank_create')) {
            $buttonBar[] = [
                'id' => 'viewrankcreatedialog:' . $groupId,
                'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['CREATERANK'],
            ];
        }

        // return it
        return [
            'contenttype' => 'datatable',
            'contentdata' => $data,
            'contentoptions' => [
                'actioncol' => 0,
                // 'tooltipcol' => 10,
                'selectOnHover' => true,
                'clickAction' => true,
            ],
            'state' => 'viewranklist:' . $groupId,
            'headline' => C4GHTMLFactory::headline($group->name . ' / ' . $GLOBALS['TL_LANG']['C4G_GROUPS']['HEADLINES_MYRANKS'], 2),
            'buttons' => $buttonBar,
        ];
    }// end of "viewRankList"

    /**
     * Generates a list of members of the given rank
     *
     * @param  object  $objThis  The "C4GGroups"-Module
     * @param  int     $rankId
     * @return array
     */
    public static function viewRankMemberList($objThis, $rankId)
    {
        $memberId = $objThis->user->id;

        // fetch data from db
        $members = MemberModel::getMemberListForGroup($rankId);
        $rank = MemberGroupModel::findByPk($rankId);
        $groupId = $rank->cg_pid;
        $group = MemberGroupModel::findByPk($groupId);

        // define datatable
        $data = [];
        $data['aoColumnDefs'] = [
            ['sTitle' => 'key', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => [0], 'responsivePriority' => [0]],
            ['sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPMEMBER'], 'aTargets' => [1], 'responsivePriority' => [1]],
            ['sTitle' => 'tooltip', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => [2], 'responsivePriority' => [2]],
        ];
        $data['bJQueryUI'] = true;
        $data['aaSorting'] = [[1, 'asc']];
        $data['bScrollCollapse'] = true;
        $data['bStateSave'] = true;
        $data['sPaginationType'] = 'full_numbers';
        $data['oLanguage'] = [
            'oPaginate' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PAGINATION'],
            'sEmptyTable' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_EMPTY'],
            'sInfo' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_INFO'],
            'sInfoEmpty' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_EMPTY'],
            'sInfoFiltered' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_FILTERED'],
            'sInfoThousands' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_THOUSANDS'],
            'sLengthMenu' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_LENGTHMENU'],
            'sProcessing' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PROCESSING'],
            'sSearch' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_SEARCH'],
            'sZeroRecords' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_NOTFOUND'],
        ];
        $data['responsive'] = true;

        // insert data into table
        if ($members) {
            foreach ($members as $member) {
                // get membername in the right format
                $memberName = MemberModel::getDisplaynameForGroup($groupId, $member->id);

                $data['aaData'][] = [
                    $member->id,
                    $memberName,
                    sprintf($GLOBALS['TL_LANG']['C4G_GROUPS']['TOOLTIP_CLICKTOSELECT'], $memberName),
                ];
            }
        }

        // built button-bar
        //
        // back-button
        $buttonBar = [
            [
                'id' => 'viewranklist:' . $groupId,
                'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['BACK'],
            ],
        ];
        // group-configuration
        if ($objThis->currentMemberHasPermission('deletegroups') || MemberModel::hasRightInGroup($memberId, $groupId, 'rank_edit_')) {
            $buttonBar[] = [
                'id' => 'viewrankconfigdialog:' . $rankId,
                'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKCONFIG'],
            ];
        }

        // add member
        if (MemberModel::hasRightInGroup($memberId, $groupId, 'rank_member')) {
            $buttonBar[] = [
                'id' => 'viewaddmemberdialog:' . $rankId,
                'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ADDMEMBER'],
                'tableSelection' => false,
            ];
        }

        // remove member
        if (MemberModel::hasRightInGroup($memberId, $groupId, 'rank_member')) {
            $buttonBar[] = [
                'id' => 'viewremovememberdialog:' . $rankId,
                'text' => $GLOBALS['TL_LANG']['C4G_GROUPS']['REMOVEMEMBER'],
                'tableSelection' => true,
            ];
        }

//        // remove member
//        if (MemberModel::hasRightInGroup( $memberId, $groupId, 'rank_edit_delete' )) {
//            $buttonBar[] = array
//            (
//                'id'              => 'viewdeleterankdialog:'.$rankId,
//                'text'            => $GLOBALS['TL_LANG']['C4G_GROUPS']['DELETERANK'],
//                'tableSelection'  => true
//            );
//        }

        // return it
        return [
            'contenttype' => 'datatable',
            'contentdata' => $data,
            'contentoptions' => [
                'actioncol' => 0,
                'tooltipcol' => 1,
                'clickAction' => false,
                'multiSelect' => true,
            ],
            'state' => 'viewrankmemberlist:' . $rankId,
            'headline' => C4GHTMLFactory::headline($group->name . ' / ' . substr($rank->name, strrpos($rank->name, '|') + 1), 2),
            'buttons' => $buttonBar,
        ];
    } // end of "viewRankMemberList"
} // end of Class
