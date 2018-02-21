<?php

/**
 * con4gis - the gis-kit
 *
 * @version   php 5
 * @package   con4gis
 * @author    con4gis contributors (see "authors.txt")
 * @license   GNU/LGPL http://opensource.org/licenses/lgpl-3.0.html
 * @copyright Küstenschmiede GmbH Software & Design 2011 - 2018
 * @link      https://www.kuestenschmiede.de
 */

namespace con4gis\GroupsBundle\Resources\contao\classes;

use con4gis\CoreBundle\Resources\contao\classes\C4GHTMLFactory;
use con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel;
use con4gis\GroupsBundle\Resources\contao\models\MemberModel;

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
  public static function viewGroupList ($objThis, $headline = '')
  {
    // check Login-State
    if (!FE_USER_LOGGED_IN) {
      $return['usermessage'] = $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOTLOGGEDIN'];
      return $return;
    }

    $memberId = $objThis->User->id;

    // fetch data from db
    $groups = MemberGroupModel::getGroupListForMember( $memberId );

    // define datatable
    $data = array();
    $data['aoColumnDefs'] = array(
        array('sTitle' => 'key', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => array(0), 'responsivePriority' => array(0)),
        array('sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPNAME'], 'aDataSort' => array(1), 'aTargets' => array(1), 'responsivePriority' => array(1)),
        array('sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPSIZE'], 'sWidth' => '20%', 'aTargets'=>array(2), 'responsivePriority' => array(2)),
        array('sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPRANK'], 'sWidth' => '20%', 'aDataSort' => array(3), 'bSearchable' => false, 'aTargets' => array(3), 'responsivePriority' => array(3)),
    );

    $data['bJQueryUI'] = true;
    $data['aaSorting'] = array(array(1, 'desc'));
    $data['bScrollCollapse'] = true;
    $data['bStateSave'] = true;
    $data['sPaginationType'] = 'full_numbers';
    $data['oLanguage'] = array(
        'oPaginate' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PAGINATION'],
        'sEmptyTable' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_EMPTY'],
        'sInfo' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_INFO'],
        'sInfoEmpty' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_EMPTY'],
        'sInfoFiltered' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_FILTERED'],
        'sInfoThousands' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_THOUSANDS'],
        'sLengthMenu' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_LENGTHMENU'],
        'sProcessing' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PROCESSING'],
        'sSearch' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_SEARCH'],
        'sZeroRecords' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_GROUPS_NOTFOUND']
    );
    $data['responsive'] = true;

    // insert data into table
    foreach($groups as $group)
    {
      // count group members
      $memberCount = unserialize( $group->cg_member );
      if (empty( $memberCount )) {
        $memberCount = 0;
      } else {
        $memberCount = count( $memberCount );
      }

      // check for maximum group-size
      $maxMember = '';
      if ($group->cg_max_member != 0) {
        $maxMember = sprintf($GLOBALS['TL_LANG']['C4G_GROUPS']['OF_MAX_MEMBER'], $group->cg_max_member );
      }

      // fetch member-ranks
      $memberRanks = MemberGroupModel::getMemberRanksOfGroupAsString( $group->id, $memberId );

      $data['aaData'][] = array(
          'viewmemberlist:' . $group->id,
          $group->name,
          $memberCount . $maxMember,
          $memberRanks
      );
    }

    // check permissions and add create-button
    $buttonBar = array();
    if ($objThis->currentMemberHasPermission( 'creategroups' )) {
      $buttonBar[] = array
      (
        'id'=>'viewgroupcreatedialog:'.$memberId,
        'text'=>$GLOBALS['TL_LANG']['C4G_GROUPS']['CREATEGROUP']
      );
    }

    if (!$headline) {
        $headline = C4GHTMLFactory::headline( $GLOBALS['TL_LANG']['C4G_GROUPS']['HEADLINES_MYGROUPS'] );
    } else {
        $headline = '';
    }

    // return it
    return array
    (
      'contenttype' => 'datatable',
      'contentdata' => $data,
      'contentoptions' => array
      (
        'actioncol' => 0,
        // 'tooltipcol' => 10,
        'selectOnHover' => true,
        'clickAction' => true
      ),
      'state' => 'viewgrouplist;',
      'headline' => $headline,
      'buttons' => $buttonBar
    );
  }// end of "viewGroupList"

  /**
   * Generates a list of members of the given group
   *
   * @param  object  $objThis  The "C4GGroups"-Module
   * @param  int     $groupId
   * @return array
   */
  public static function viewMemberList ($objThis, $groupId)
  {
    $memberId = $objThis->User->id;

    // fetch data from db
    $members = MemberModel::getMemberListForGroup( $groupId );
    $group = MemberGroupModel::findByPk( $groupId );

    // define datatable
    $data = array();
    $data['aoColumnDefs'] = array(
        array('sTitle' => 'key', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => array(0), 'responsivePriority' => array(0)),
        array('sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPMEMBER'], 'aDataSort' => array(1,2), 'aTargets' => array(1), 'responsivePriority' => array(1)),
        array('sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPRANK'], 'sWidth' => '20%', 'aDataSort' => array(2,1), 'aTargets' => array(2), 'responsivePriority' => array(2)),
        array('sTitle' => 'tooltip', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => array(3), 'responsivePriority' => array(3)),
    );
    $data['bJQueryUI'] = true;
    $data['aaSorting'] = array(array(2, 'desc'));
    $data['bScrollCollapse'] = true;
    $data['bStateSave'] = true;
    $data['sPaginationType'] = 'full_numbers';
    $data['oLanguage'] = array(
        'oPaginate' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PAGINATION'],
        'sEmptyTable' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_EMPTY'],
        'sInfo' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_INFO'],
        'sInfoEmpty' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_EMPTY'],
        'sInfoFiltered' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_FILTERED'],
        'sInfoThousands' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_THOUSANDS'],
        'sLengthMenu' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_LENGTHMENU'],
        'sProcessing' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PROCESSING'],
        'sSearch' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_SEARCH'],
        'sZeroRecords' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_NOTFOUND']
    );
    $data['responsive'] = true;

    // insert data into table
    foreach ($members as $member)
    {
      // get membername in the right format
      $memberName = MemberModel::getDisplaynameForGroup($groupId, $member->id);
      // fetch member-ranks
      $memberRanks = MemberGroupModel::getMemberRanksOfGroupAsString( $groupId, $member->id );

      // highlight group-owner
      if ($objThis->c4g_groups_appearance_highlight_owner && MemberGroupModel::isOwnerOfGroup( $groupId, $member->id )) {
        $memberName = '<strong>' . $memberName . '</strong>';
        // $memberRanks = '<strong>' . $memberRanks . '</strong>';
      }

      $data['aaData'][] = array(
          $member->id,
          $memberName,
          $memberRanks,
          sprintf( $GLOBALS['TL_LANG']['C4G_GROUPS']['TOOLTIP_CLICKTOSELECT'], $memberName)
      );
    }

    // built button-bar
    //
    // back-button
    $buttonBar = array
    (
      array
      (
        'id'    => 'viewgrouplist',
        'text'  => $GLOBALS['TL_LANG']['C4G_GROUPS']['BACK']
      ),
    );
    // group-configuration
    if ($objThis->currentMemberHasPermission('deletegroups') || MemberModel::hasRightInGroup( $memberId, $groupId, 'group_edit_' )) {
      $buttonBar[] = array
      (
        'id'    => 'viewgroupconfigdialog:'.$groupId,
        'text'  => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPCONFIG']
      );
    }
      // rank-configuration
      if (MemberModel::hasRightInGroup( $memberId, $groupId, 'rank_create' ) || MemberGroupModel::getMemberRanksOfGroup($groupId, $memberId) > 0) {
          $buttonBar[] = array
          (
              'id'    => 'viewranklist:'.$groupId,
              'text'  => $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKLIST']
          );
      }
    // invitation
    if (MemberModel::hasRightInGroup( $memberId, $groupId, 'member_invite_' )) {
      $buttonBar[] = array
      (
        'id'    => 'viewinvitememberdialog:'.$groupId,
        'text'  => $GLOBALS['TL_LANG']['C4G_GROUPS']['INVITEMEMBER']
      );
    }
    // remove member
    if (MemberModel::hasRightInGroup( $memberId, $groupId, 'member_remove' )) {
      $buttonBar[] = array
      (
        'id'              => 'viewremovememberdialog:'.$groupId,
        'text'            => $GLOBALS['TL_LANG']['C4G_GROUPS']['REMOVEMEMBER'],
        'tableSelection'  => true
      );
    }
    if (MemberModel::hasRightInGroup($memberId, $groupId, 'group_leave')) {
        // leave group
        $buttonBar[] = array
        (
            'id'              => 'viewleavegroupdialog:'.$groupId,
            'text'            => $GLOBALS['TL_LANG']['C4G_GROUPS']['LEAVEGROUP'],
        );
    }
    // contact
    if (MemberModel::hasRightInGroup( $memberId, $groupId, 'member_contact_' )) {
      $buttonBar[] = array
      (
        'id'              => 'viewsendmaildialog:'.$groupId,
        'text'            => $GLOBALS['TL_LANG']['C4G_GROUPS']['SENDMAIL'],
        'tableSelection'  => true
      );
    }

    // return it
    return array
    (
      'contenttype' => 'datatable',
      'contentdata' => $data,
      'contentoptions' => array
      (
        'actioncol' => 0,
        'tooltipcol' => 3,
        'clickAction' => false,
        'multiSelect' => true
      ),
      'state' => 'viewmemberlist:' . $groupId,
      'headline' => C4GHTMLFactory::headline( $group->name, 2 ),
      'buttons' => $buttonBar
    );
  } // end of "viewMemberList"

    /**
     * Generates a list of ranks for the current member
     *
     * @param  object  $objThis  The "C4GGroups"-Module
     * @return array
     */
    public static function viewRankList ($objThis, $groupId)
    {
        // check Login-State
        if (!FE_USER_LOGGED_IN) {
            $return['usermessage'] = $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_NOTLOGGEDIN'];
            return $return;
        }

        $memberId = $objThis->User->id;

        // fetch data from db
        $ranks = MemberGroupModel::getMemberRanksOfGroup( $groupId, $memberId );
        $group = MemberGroupModel::findByPk( $groupId );

        // define datatable
        $data = array();
        $data['aoColumnDefs'] = array(
            array('sTitle' => 'key', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => array(0), 'responsivePriority' => array(0)),
            array('sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKNAME'], 'aDataSort' => array(1), 'aTargets' => array(1), 'responsivePriority' => array(1)),
            array('sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKSIZE'], 'sWidth' => '10%', 'aTargets'=>array(2), 'responsivePriority' => array(2)),
        );

        $data['bJQueryUI'] = true;
        $data['aaSorting'] = array(array(1, 'desc'));
        $data['bScrollCollapse'] = true;
        $data['bStateSave'] = true;
        $data['sPaginationType'] = 'full_numbers';
        $data['oLanguage'] = array(
            'oPaginate' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PAGINATION'],
            'sEmptyTable' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_EMPTY'],
            'sInfo' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_INFO'],
            'sInfoEmpty' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_EMPTY'],
            'sInfoFiltered' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_FILTERED'],
            'sInfoThousands' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_THOUSANDS'],
            'sLengthMenu' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_LENGTHMENU'],
            'sProcessing' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PROCESSING'],
            'sSearch' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_SEARCH'],
            'sZeroRecords' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_RANKS_NOTFOUND']
        );
        $data['responsive'] = true;
        // insert data into table
        if ($ranks) {
            foreach($ranks as $rank)
            {
                // count group members
                $memberCount = unserialize( $rank->cg_member );
                if (empty( $memberCount )) {
                    $memberCount = 0;
                } else {
                    $memberCount = count( $memberCount );
                }

                $data['aaData'][] = array(
                    'viewrankmemberlist:' . $rank->id,
                    substr( $rank->name, strrpos($rank->name, '|')+1 ),
                    $memberCount
                );
            }

        }

        // check permissions and add create-button
        $buttonBar = array();

        // back-button
        $buttonBar = array
        (
            array
            (
                'id'    => 'viewmemberlist:'.$groupId,
                'text'  => $GLOBALS['TL_LANG']['C4G_GROUPS']['BACK']
            ),
        );

        if (MemberModel::hasRightInGroup( $memberId, $groupId, 'rank_create' )) {
            $buttonBar[] = array
            (
                'id'=>'viewrankcreatedialog:'.$groupId,
                'text'=>$GLOBALS['TL_LANG']['C4G_GROUPS']['CREATERANK']
            );
        }

        // return it
        return array
        (
            'contenttype' => 'datatable',
            'contentdata' => $data,
            'contentoptions' => array
            (
                'actioncol' => 0,
                // 'tooltipcol' => 10,
                'selectOnHover' => true,
                'clickAction' => true
            ),
            'state' => 'viewranklist:'.$groupId,
            'headline' => C4GHTMLFactory::headline( $group->name . ' / ' . $GLOBALS['TL_LANG']['C4G_GROUPS']['HEADLINES_MYRANKS'] , 2),
            'buttons' => $buttonBar
        );
    }// end of "viewRankList"

    /**
     * Generates a list of members of the given rank
     *
     * @param  object  $objThis  The "C4GGroups"-Module
     * @param  int     $rankId
     * @return array
     */
    public static function viewRankMemberList ($objThis, $rankId)
    {
        $memberId = $objThis->User->id;

        // fetch data from db
        $members = MemberModel::getMemberListForGroup( $rankId );
        $rank = MemberGroupModel::findByPk( $rankId );
        $groupId = $rank->cg_pid;
        $group = MemberGroupModel::findByPk( $groupId );

        // define datatable
        $data = array();
        $data['aoColumnDefs'] = array(
            array('sTitle' => 'key', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => array(0), 'responsivePriority' => array(0)),
            array('sTitle' => $GLOBALS['TL_LANG']['C4G_GROUPS']['GROUPMEMBER'], 'aTargets' => array(1), 'responsivePriority' => array(1)),
            array('sTitle' => 'tooltip', 'bVisible' => false, 'bSearchable' => false, 'aTargets' => array(2), 'responsivePriority' => array(2)),
        );
        $data['bJQueryUI'] = true;
        $data['aaSorting'] = array(array(1, 'asc'));
        $data['bScrollCollapse'] = true;
        $data['bStateSave'] = true;
        $data['sPaginationType'] = 'full_numbers';
        $data['oLanguage'] = array(
            'oPaginate' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PAGINATION'],
            'sEmptyTable' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_EMPTY'],
            'sInfo' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_INFO'],
            'sInfoEmpty' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_EMPTY'],
            'sInfoFiltered' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_FILTERED'],
            'sInfoThousands' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_INFO_THOUSANDS'],
            'sLengthMenu' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_LENGTHMENU'],
            'sProcessing' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_PROCESSING'],
            'sSearch' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_SEARCH'],
            'sZeroRecords' => $GLOBALS['TL_LANG']['C4G_GROUPS']['DATATABLE_CAPTION_MEMBERS_NOTFOUND']
        );
        $data['responsive'] = true;

        // insert data into table
        if ($members) {
            foreach ($members as $member)
            {
                // get membername in the right format
                $memberName = MemberModel::getDisplaynameForGroup($groupId, $member->id);

                $data['aaData'][] = array(
                    $member->id,
                    $memberName,
                    sprintf( $GLOBALS['TL_LANG']['C4G_GROUPS']['TOOLTIP_CLICKTOSELECT'], $memberName)
                );
            }
        }

        // built button-bar
        //
        // back-button
        $buttonBar = array
        (
            array
            (
                'id'    => 'viewranklist:'.$groupId,
                'text'  => $GLOBALS['TL_LANG']['C4G_GROUPS']['BACK']
            ),
        );
        // group-configuration
        if ($objThis->currentMemberHasPermission('deletegroups') || MemberModel::hasRightInGroup( $memberId, $groupId, 'rank_edit_' )) {
            $buttonBar[] = array
            (
                'id'    => 'viewrankconfigdialog:'.$rankId,
                'text'  => $GLOBALS['TL_LANG']['C4G_GROUPS']['RANKCONFIG']
            );
        }

        // add member
        if (MemberModel::hasRightInGroup( $memberId, $groupId, 'rank_member' )) {
            $buttonBar[] = array
            (
                'id'              => 'viewaddmemberdialog:'.$rankId,
                'text'            => $GLOBALS['TL_LANG']['C4G_GROUPS']['ADDMEMBER'],
                'tableSelection'  => false
            );
        }

       // remove member
        if (MemberModel::hasRightInGroup( $memberId, $groupId, 'rank_member' )) {
            $buttonBar[] = array
            (
                'id'              => 'viewremovememberdialog:'.$rankId,
                'text'            => $GLOBALS['TL_LANG']['C4G_GROUPS']['REMOVEMEMBER'],
                'tableSelection'  => true
            );
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
        return array
        (
            'contenttype' => 'datatable',
            'contentdata' => $data,
            'contentoptions' => array
            (
                'actioncol' => 0,
                'tooltipcol' => 1,
                'clickAction' => false,
                'multiSelect' => true
            ),
            'state' => 'viewrankmemberlist:' . $rankId,
            'headline' => C4GHTMLFactory::headline( $group->name . ' / ' .substr( $rank->name, strrpos($rank->name, '|')+1 ), 2 ),
            'buttons' => $buttonBar
        );
    } // end of "viewRankMemberList"


} // end of Class