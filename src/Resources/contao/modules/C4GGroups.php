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

namespace con4gis\GroupsBundle\Resources\contao\modules;

use con4gis\CoreBundle\Classes\C4GAutomator;
use con4gis\ProjectsBundle\Classes\jQuery\C4GJQueryGUI;
use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\GroupsBundle\Classes\CGController;
use con4gis\GroupsBundle\Classes\ViewDialogs;
use con4gis\GroupsBundle\Classes\ViewLists;
use con4gis\GroupsBundle\Resources\contao\models\MemberGroupModel;
use con4gis\GroupsBundle\Resources\contao\models\MemberModel;
use Contao\ArrayUtil;
use Contao\Database;
use Contao\FrontendUser;
use Contao\Module;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

use Contao\BackendTemplate;
use Contao\FilesModel;
use Contao\StringUtil;

/**
 * Class C4GGroups
 * @package c4g
 */
class C4GGroups extends Module
{
    protected $strTemplate = 'mod_c4g_groups';
    protected $putVars = null;
    protected $frontendUrl = null;
    public $user = null; //ToDo getter/setter

    public function __construct($objModule, $strColumn='main')
    {
        parent::__construct($objModule, $strColumn);

        $this->user = FrontendUser::getInstance();
    }
    
    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate ()
    {
        if (C4GUtils::isBackend()) {
            $objTemplate = new BackendTemplate('be_wildcard');
            
            $objTemplate->wildcard = '### '.$GLOBALS['TL_LANG']['FMD']['c4g_groups'][0].' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->title;
            $objTemplate->href = System::getContainer()->get('router')->generate('contao_backend').'/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            
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
        
        // initialize used Javascript Libraries and CSS files
        C4GJQueryGUI::initializeLibraries(
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
            $objFile = FilesModel::findByUuid($this->c4g_groups_appearance_themeroller_css);
            $GLOBALS['TL_CSS']['c4g_jquery_ui'] = $objFile->path;
        } else if(!empty($this->c4g_groups_uitheme_css_select) && ($this->c4g_groups_uitheme_css_select != 'settings')) {
            $theme = $this->c4g_groups_uitheme_css_select;
            $GLOBALS['TL_CSS']['c4g_jquery_ui'] = 'bundles/con4giscore/vendor/jQuery/ui-themes/themes/' . $theme . '/jquery-ui.min.css';
        } else {
            $settings = Database::getInstance()->execute("SELECT * FROM tl_c4g_settings LIMIT 1")->fetchAllAssoc();
            
            if ($settings) {
                $settings = $settings[0];
            }
            if ($settings && $settings['c4g_appearance_themeroller_css']) {
                $objFile = FilesModel::findByUuid($this->settings['c4g_appearance_themeroller_css']);
                $GLOBALS['TL_CSS']['c4g_jquery_ui'] = $objFile->path;
            } else if ($settings && $settings['c4g_uitheme_css_select']) {
                $theme = $settings['c4g_uitheme_css_select'];
                $GLOBALS['TL_CSS']['c4g_jquery_ui'] = 'bundles/con4giscore/vendor/jQuery/ui-themes/themes/' . $theme . '/jquery-ui.min.css';
            } else {
                $GLOBALS['TL_CSS']['c4g_jquery_ui'] = 'bundles/con4giscore/vendor/jQuery/ui-themes/themes/base/jquery-ui.min.css';
            }
        }
        
        // load needed css
        $GLOBALS ['TL_CSS'][] = 'bundles/con4gisgroups/dist/css/c4g_groups.css';
        
        // set needed params for "c4gGui.js"
        $data['id'] = $this->id;
        $data['ajaxUrl'] = "con4gis/groupsService/".$objPage->language;
        $data['ajaxData'] = $this->id;
        
       /*  if ($_GET['state']) {
            $request = $_GET['state'];
        } else {
            $request = 'initnav';
        } */

        $getState = System::getContainer()->get('request_stack')->getCurrentRequest()->query->has('state');
        if ($getState) {
            $request = $getState;
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
            // $request = $_GET['req'];
            $request = System::getContainer()->get('request_stack')->getCurrentRequest()->query->has('req');
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
            $session = isset($this->Session) ? $this->Session->getData() : null;
            $url = parse_url($_SERVER['HTTP_REFERER']);
            $this->frontendUrl = $url['host'].$url['path'].'/'.$session['referer']['current'];

            if (($_SERVER['REQUEST_METHOD']) == 'PUT') {
                parse_str(file_get_contents("php://input"),$this->putVars);
            }
            
            // if there was an initial get parameter "state" then use it for jumping directly
            // to the refering function
            $getInitReq = System::getContainer()->get('request_stack')->getCurrentRequest()->query->has('initreq');
            $getHistoryReq = System::getContainer()->get('request_stack')->getCurrentRequest()->query->has('historyreq');
            if (($request=='initnav') && /* $_GET['initreq'] */ $getInitReq) {
                // $_GET['historyreq'] = $_GET['initreq'];
                $getHistoryReq = $getInitReq;
            }
            
            // history navigation
            $getHistoryReq = System::getContainer()->get('request_stack')->getCurrentRequest()->query->has('historyreq');
            if (/* $_GET['historyreq'] */ $getHistoryReq) {
                $actions = explode(';', $getHistoryReq/* $_GET['historyreq'] */);
                $result = array();
                foreach ($actions AS $action) {
                    $r = $this->performHistoryAction($action);
                    ArrayUtil::arrayInsert($result, 0, $r);
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
        } catch (\Exception $e) {
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
        //C4GAutomator::purgeApiCache();
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
                $return = MemberModel::hasRightInGroup( $this->user->id, $values[1], 'rank_create' )
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
                $return = MemberModel::hasRightInGroup( $this->user->id, $values[1], 'rank_create' )
                    ? CGController::createRank( $this, $this->putVars , $values[1])
                    : array('usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']);
                break;
            case 'configurerank':
                $return = $this->executeIfAuthorized( CGController::configureRank( $this, $values[1], $this->putVars ), $ownerGroupId, $ownerRight );
                break;
            case 'addmember':
                $return = MemberModel::hasRightInGroup( $this->user->id, $values[1], 'rank_member' )
                    ? CGController::addMember( $this, $this->putVars , $values[2])
                    : array('usermessage' => $GLOBALS['TL_LANG']['C4G_GROUPS']['ERROR_PERMISSIONDENIED']);
                break;
            
            case 'invitemember':
                $return = $this->executeIfAuthorized( CGController::inviteMember( $this, $values[1], $this->putVars['mailaddress'], $this->frontendUrl ), $values[1], 'member_invite_' );
                break;
            case 'removemember':
                $memberIds = [];
                if ($values[2] && !is_array($values[2])) {
                    $memberIds[] = $values[2];
                } else {
                    $memberIds = $values[2];
                }
                $return = empty($values[2])
                    ? CGController::removeMemberFromGroup( $this, $values[1] )
                    : $this->executeIfAuthorized( CGController::removeMemberFromGroup( $this, $values[1], $memberIds ), $values[1], 'member_remove' );
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
        if (MemberModel::hasRightInGroup( $this->user->id, $groupId, $right )) {
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
                $authorizedGroups = StringUtil::deserialize($this->c4g_groups_permission_creategroups_authorized_groups);
                break;
            case 'deletegroups':
                $authorizedGroups = StringUtil::deserialize($this->c4g_groups_permission_deletegroups_authorized_groups);
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
        if ($this->user) {
            foreach ($authorizedGroups as $group) {
                if (MemberGroupModel::isMemberOfGroup( $group, $this->user->id )) return true;
            }
        }
        
        return false;
    }
} // end of Class