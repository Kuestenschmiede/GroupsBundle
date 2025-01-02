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

namespace con4gis\GroupsBundle\Controller;


use con4gis\CoreBundle\Controller\BaseController;
use con4gis\GroupsBundle\Classes\CGController;
use con4gis\GroupsBundle\Resources\contao\modules\C4GGroups;
use Contao\CoreBundle\Controller\FrontendController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\System;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupsController extends AbstractController
{
    private $framework = null;

    /**
     * @param ContaoFramework $framework
     */
    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
        $this->framework->initialize(true);
    }

    /**
     * @param Request $request
     * @param $id
     * @param $req
     * @return JsonResponse
     * @Route("/con4gis/groupsService/{language}/{id}/{req}", methods={"GET|PUT"})
     */
    public function getGroupsServiceAction(Request $request, $language, $id, $req)
    {
        $response = new JsonResponse();
         $feUser = FrontendUser::getInstance()->id;
        // $feUser->authenticate();

        // $feUser = System::getContainer()->get('contao.frontend_user');

        if (!isset( $id ) || !is_numeric( $id )) {
            $response->setStatusCode(400);
        }
        if (!strlen($id) || $id < 1)
        {
            $response->setData('Missing frontend module ID');
            $response->setStatusCode(412);
        }
        $objModule = ModuleModel::findByPk($id);

        if (!$objModule)
        {
            $response->setData('Frontend module not found');
            $response->setStatusCode(404);
        }

        // Show to guests only
        $hasBackendUser = System::getContainer()->get('contao.security.token_checker')->hasBackendUser();
        // if ($objModule->guests && $feUser && !BE_USER_LOGGED_IN && !$objModule->protected)
        if ($objModule->guests && $feUser && !$hasBackendUser && !$objModule->protected)
        {
            $response->setData('Forbidden');
            $response->setStatusCode(403);
        }

        // Protected element
        if (!$hasBackendUser && $objModule->protected)
        {
            if (!$feUser)
            {
                $response->setData('Forbidden');
                $response->setStatusCode(403);
            }

            $groups = deserialize($objModule->groups);

            if (!is_array($groups) || count($groups) < 1 || count(array_intersect($groups, $feUser->groups)) < 1)
            {
                $response->setData('Forbidden');
                $response->setStatusCode(403);
            }
        }

        // Return if the class does not exist
        if (!class_exists(C4GGroups::class))
        {
//            $this->log('Module class "'.$GLOBALS['FE_MOD'][$objModule->type].'" (module "'.$objModule->type.'") does not exist', 'Ajax getFrontendModule()', TL_ERROR);
            $response->setData('Frontend module class does not exist');
            $response->setStatusCode(404);
        }

        $objModule->typePrefix = 'mod_';
        $objModule = new C4GGroups($objModule);
        $objModule->c4g_groups_language = $language;
        $return = $objModule->generateAjax($req);
        $response->setData($return);
        return $response;
    }

    /**
     * @param Request $request
     * @param $id
     * @param $memberEmail
     * @param $groupId
     * @return JsonResponse
     * @Route("/con4gis/inviteMember/{memberEmail}/{groupId}", methods={"GET"})
     */
    public function getInviteMemberAction(Request $request, $id, $memberEmail, $groupId)
    {
        $groupsModule = ModuleModel::findBy('type', 'c4g_groups');
        System::loadLanguageFile('frontendModules');
        $response = CGController::inviteMember($groupsModule, $groupId, $memberEmail);
        return new JsonResponse(['res' => $response]);
    }

    /**
     * @param Request $request
     * @param $id
     * @param $groupId
     * @param $memberId
     * @return JsonResponse
     * @Route("/con4gis/removeMember/{groupId}/{memberId}", methods={"DELETE"})
     */
    public function removeMemberFromGroupAction(Request $request, $id, $groupId, $memberId)
    {
        $groupsModule = ModuleModel::findBy('type', 'c4g_groups');
        System::loadLanguageFile('frontendModules');
        $response = CGController::removeMemberFromGroup($groupsModule, $groupId, [$memberId]);
        return new JsonResponse(['res' => $response]);
    }
}