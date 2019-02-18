<?php
/**
 * Created by PhpStorm.
 * User: cro
 * Date: 14.02.19
 * Time: 17:22
 */

namespace con4gis\GroupsBundle\Controller;

use con4gis\CoreBundle\Controller\BaseController;
use con4gis\GroupsBundle\Resources\contao\classes\CGController;
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