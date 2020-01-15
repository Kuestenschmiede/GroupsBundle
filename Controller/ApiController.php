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

namespace con4gis\GroupsBundle\Controller;

use con4gis\CoreBundle\Controller\BaseController;
use con4gis\GroupsBundle\Classes\CGController;
use Contao\ModuleModel;
use Contao\System;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends BaseController
{
    public function inviteMemberAction(Request $request, $memberEmail, $groupId)
    {
        $groupsModule = ModuleModel::findBy('type', 'c4g_groups');
        System::loadLanguageFile('frontendModules');
        $response = CGController::inviteMember($groupsModule, $groupId, $memberEmail);
        return new JsonResponse(['res' => $response]);
    }
    
    public function removeMemberFromGroupAction(Request $request, $groupId, $memberId)
    {
        $groupsModule = ModuleModel::findBy('type', 'c4g_groups');
        System::loadLanguageFile('frontendModules');
        $response = CGController::removeMemberFromGroup($groupsModule, $groupId, [$memberId]);
        return new JsonResponse(['res' => $response]);
    }
}