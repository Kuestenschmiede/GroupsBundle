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

namespace c4g;

/**
 * Class C4GGroups
 * @package c4g
 */
class C4GGroups extends \Module
{
  protected $strTemplate = 'mod_c4g_groups';
  protected $putVars = null;
  protected $frontendUrl = null;


  /**
   * Display a wildcard in the back end
   *
   * @return string
   */
  public function generate ()
  {
    if (TL_MODE == 'BE') {
      $objTemplate = new \BackendTemplate('be_wildcard');

      $objTemplate->wildcard = '### '.$GLOBALS['TL_LANG']['FMD']['c4g_groups'][0].' ###';
      $objTemplate->title = $this->headline;
      $objTemplate->id = $this->id;
      $objTemplate->link = $this->title;
      $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

      return $objTemplate->parse();
    }

    return parent::generate();
  }

  /**
   * Generate the module
   */
  protected function compile ()
  {
    global $objPage;
    $this->import('FrontendUser', 'User');

    // initialize used Javascript Libraries and CSS files
    \C4GJQueryGUI::initializeLibraries(
      true,                     // add c4gJQuery GUI Core LIB
      true,                     // add JQuery
      true,                     // add JQuery UI
      false,                    // add Tree Control
      true,                     // add Table Control
      true,                     // add history.js
      true                      // add simple tooltip
    );

    // load custom themeroller-css if set
    if ($this->c4g_groups_appearance_themeroller_css) {
      $objFile = \FilesModel::findByUuid($this->c4g_groups_appearance_themeroller_css);
      $GLOBALS['TL_CSS']['c4g_jquery_ui'] = $objFile->path;
    } else if(!empty($this->c4g_groups_uitheme_css_select)) {
        $theme = $this->c4g_groups_uitheme_css_select;
        $GLOBALS['TL_CSS']['c4g_jquery_ui'] = 'system/modules/con4gis_core/assets/vendor/jQuery/ui-themes/themes/' . $theme . '/jquery-ui.css';
    } else {
        $GLOBALS['TL_CSS']['c4g_jquery_ui'] = 'system/modules/con4gis_core/assets/vendor/jQuery/ui-themes/themes/base/jquery-ui.css';
    }

    // load needed css
    $GLOBALS ['TL_CSS'] [] = 'system/modules/con4gis_groups/assets/c4g_groups.css';

    // set needed params for "jquery.c4gGui.js"
    $data['id'] = $this->id;
    if (\class_exists('\con4gis\ApiBundle\Controller\ApiController') &&  (version_compare( VERSION, '4', '>=' ))) {
        $data['ajaxUrl'] = "con4gis/api/c4g_groups_ajax";
    } else {
        $data['ajaxUrl'] = "system/modules/con4gis_core/api/index.php/c4g_groups_ajax";
    }
    $data['ajaxData'] = $this->id;

    if ($_GET['state']) {
      $request = $_GET['state'];
    } else {
      $request = 'initnav';
    }
    $data['initData'] = $this->generateAjax($request);

    $data['div'] = 'c4g_groups';


    $this->Template->cgData = $data;
  }

  /**
   * This function is called by every ajax-request
   *
   * @param  string  $request
   * @return json
   */
  public function generateAjax ($request=null)
  {
    if ($request==null) {

      // Ajax Request: read get parameter "req"
      $request = $_GET['req'];
      if ($request!='undefined') {
        // replace "state" parameter in Session-Referer to force correct
        // handling after login with "redirect back" set
        $session = $this->Session->getData();
        $session['referer']['last'] = $session['referer']['current'];
        $session['referer']['current'] = C4GUtils::addParametersToURL(
          $session['referer']['last'],
          array('state'=>$request ));
        $this->Session->setData($session);
      }
    }

    // load appropriate language-file
    $this->loadLanguageFile('frontendModules', $this->c4g_groups_language);

    try {
      $this->import('FrontendUser', 'User');
      $session = $this->Session->getData();
      if (version_compare(VERSION,'3.1','<')) {
        $this->frontendUrl = $this->Environment->url.$session['referer']['current'];
      }
      else {
        $this->frontendUrl = $this->Environment->url.TL_PATH.'/'.$session['referer']['current'];
      }

      if (($_SERVER['REQUEST_METHOD']) == 'PUT') {
        parse_str(file_get_contents("php://input"),$this->putVars);
      }

      // if there was an initial get parameter "state" then use it for jumping directly
      // to the refering function
      if (($request=='initnav') && $_GET['initreq']) {
        $_GET['historyreq'] = $_GET['initreq'];
      }

      // history navigation
      if ($_GET['historyreq']) {
        $actions = explode(';',$_GET['historyreq']);
        $result = array();
        foreach ($actions AS $action) {
          $r = $this->performHistoryAction($action);
          array_insert($result, 0, $r);
        }

      } else {
        switch ($request) {
          case 'initnav' :
            $this->action = 'viewgrouplist';
            $result = $this->performAction('viewgrouplist');
            break;
          default:
            $actions = explode(';',$request);
            $result = array();
            foreach ($actions AS $action) {
              $r = $this->performAction($action);
              if (is_array($r)) {
                $result = array_merge($result, $r);
              }
            }
        }
      }
    } catch (Exception $e) {
      $result = $this->showException($e);
    }

    // return it as JSON-String
    return json_encode( $result );
  }

  /**
   * Execute functions for "actionstrings"
   *
   * @param  string       $action
   * @return array|null
   */
  public function performAction ( $action )
  {
      //delete cache -- Übergangslösung bis alles läuft.
      \c4g\Core\C4GAutomator::purgeApiCache();

    $values = explode(':',$action,5);
    $this->action = $values[0];

      $ownerGroupId = $values[1];
      $ownerRight = 'group_edit_';
      $group = MemberGroupModel::findByPk($ownerGroupId);
      if ($group) {
          if ($group->cg_pid > 0) {
              $ownerGroupId = $group->cg_pid;
              $ownerRight = 'rank_edit_';
          }
      }

    switch($values[0]) {
      case 'closedialog':
        $return = $this->closeDialog( $values[1] );
        break;
    // Views
      case 'viewgrouplist':
        $return = ViewLists::viewGroupList( $this, $this->headline );
        break;
      case 'viewmemberlist':
        $return = ViewLists::viewMemberList( $this, $values[1] );
        break;
      case 'viewranklist':
        $return = ViewLists::viewRankList( $this, $values[1] );
        break;
      case 'viewrankmemberlist':
        $return = ViewLists::viewRankMemberList( $this, $values[1] );
        break;
      case 'viewgroupcreatedialog':
        $return = $this->currentMemberHasPermission('creategroups')
                    ? ViewDialogs::viewGroupCreateDialog( $this, $values[1] )
                    : array('usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']);
        break;
        case 'viewrankcreatedialog':
            $return = MemberModel::hasRightInGroup( $this->User->id, $values[1], 'rank_create' )
                ? ViewDialogs::viewRankCreateDialog( $this, $values[1] )
                : array('usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']);
            break;
      case 'viewgroupconfigdialog':
        $return = $this->currentMemberHasPermission('deletegroups')
                    ? ViewDialogs::viewGroupConfigDialog( $this, $values[1] )
                    : $this->executeIfAuthorized( ViewDialogs::viewGroupConfigDialog( $this, $values[1] ), $ownerGroupId, $ownerRight );
        break;
        case 'viewrankconfigdialog':
            $return = $this->executeIfAuthorized( ViewDialogs::viewRankConfigDialog( $this, $values[1] ), $ownerGroupId, $ownerRight );
            break;
      case 'viewinvitememberdialog':
        $return = $this->executeIfAuthorized( ViewDialogs::viewInviteMemberDialog( $this, $values[1], $values[2] ?: 'select' ), $values[1], 'member_invite_' );
        break;
      case 'viewremovememberdialog':
        $return = $this->executeIfAuthorized( ViewDialogs::viewRemoveMemberDialog( $this, $values[1], $this->putVars ), $values[1], 'member_remove' );
        break;
      case 'viewleavegroupdialog':
        $return = ViewDialogs::viewLeaveGroupDialog( $this, $values[1] );
        break;
      case 'viewsendmaildialog':
        $return = $this->executeIfAuthorized( ViewDialogs::viewSendEmailDialog( $this, $values[1], $this->putVars ), $values[1], 'member_contact_' );
        break;
      case 'viewaddmemberdialog':
        $return = /*$this->executeIfAuthorized( */ViewDialogs::viewAddMemberDialog( $this, $values[1]);//, $values[1], 'rank_member' );
        break;
    // Controller
      case 'creategroup':
        $return = $this->currentMemberHasPermission('creategroups')
                    ? CGController::createGroup( $this, $this->putVars )
                    : array('usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']);
        break;
      case 'configuregroup':
        $return = $this->currentMemberHasPermission('deletegroups')
                    ? CGController::configureGroup( $this, $values[1], $this->putVars )
                    : $this->executeIfAuthorized( CGController::configureGroup( $this, $values[1], $this->putVars ), $ownerGroupId, $ownerRight );
        break;
      case 'createrank':
        $return = MemberModel::hasRightInGroup( $this->User->id, $values[1], 'rank_create' )
                ? CGController::createRank( $this, $this->putVars , $values[1])
                : array('usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']);
            break;
        case 'configurerank':
            $return = $this->executeIfAuthorized( CGController::configureRank( $this, $values[1], $this->putVars ), $ownerGroupId, $ownerRight );
            break;
      case 'addmember':
          $return = MemberModel::hasRightInGroup( $this->User->id, $values[1], 'rank_member' )
              ? CGController::addMember( $this, $this->putVars , $values[2])
              : array('usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']);
          break;

      case 'invitemember':
        $return = $this->executeIfAuthorized( CGController::inviteMember( $this, $values[1], $this->putVars['mailaddress'], $this->frontendUrl ), $values[1], 'member_invite_' );
        break;
      case 'removemember':
        $return = empty($values[2])
                  ? CGController::removeMemberFromGroup( $this, $values[1] )
                  : $this->executeIfAuthorized( CGController::removeMemberFromGroup( $this, $values[1], $values[2] ), $values[1], 'member_remove' );
        break;
      case 'sendmembermail':
        $return = $this->executeIfAuthorized( CGController::sendMailToMember( $this, $values[1], $this->putVars ), $values[1], 'member_contact_' );
        break;
      default:
        break;
    }

    if (isset($return)) {
      return $return;
    }
    else {
      return;
    }
  }

  /**
   * Perform a history action
   *
   * @param  string  $historyAction
   * @return array
   */
  public function performHistoryAction ($historyAction)
  {
    $values = explode(':', $historyAction);
    $this->action = $values[0];

    $result = $this->performAction( $historyAction );

    // close all dialogs that have been open to avoid conflicts
    $result['dialogcloseall'] = true;

    return $result;
  }

  /**
   * Close a dialog
   *
   * @param  int    $dialogId
   * @return array
   */
  public function closeDialog ($dialogId)
  {
    $return = array(
      'dialogclose' => $dialogId
    );
    return $return;
  }

  public function executeIfAuthorized ( $function, $groupId, $right )
  {
    if (MemberModel::hasRightInGroup( $this->User->id, $groupId, $right )) {
      return $function;
    } else {
      return array
      (
        'usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED'],
      );
    }
  }

  /**
   * Checks if the current member has one of the following permissions:
   *   - creategroups (To create new groups)
   *   - deletegroups (To delete other groups)
   *
   * @param  string   $permission
   * @return boolean
   */
  public function currentMemberHasPermission ( $permission )
  {
    $authorizedGroups = array();

    switch ($permission) {
      case 'creategroups':
        $authorizedGroups = unserialize($this->c4g_groups_permission_creategroups_authorized_groups);
        break;
      case 'deletegroups':
        $authorizedGroups = unserialize($this->c4g_groups_permission_deletegroups_authorized_groups);
        break;

      default:
        return false;
        break;
    }

    return $authorizedGroups ? $this->currentMemberIsInAuthorizedGroup( $authorizedGroups ) : false;
  }

  /**
   * Checks if the current member is in ate least one of the given groups
   *
   * @param  array    $authorizedGroups
   * @return boolean
   */
  public function currentMemberIsInAuthorizedGroup ( $authorizedGroups )
  {
    if (FE_USER_LOGGED_IN) {
      foreach ($authorizedGroups as $group) {
        if (MemberGroupModel::isMemberOfGroup( $group, $this->User->id )) return true;
      }
    }

    return false;
  }
} // end of Class