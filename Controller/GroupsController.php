<?php
/**
 * Created by PhpStorm.
 * User: cro
 * Date: 15.09.17
 * Time: 10:33
 */

namespace con4gis\GroupsBundle\Controller;


use con4gis\GroupsBundle\Resources\contao\modules\C4GGroups;
use Contao\CoreBundle\Controller\FrontendController;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GroupsController extends FrontendController
{
    public function runAction(Request $request, $id, $req)
    {
        $response = new JsonResponse();
        $feUser = FrontendUser::getInstance();
        $feUser->authenticate();
        if (!isset( $id ) || !is_numeric( $id )) {
            header('HTTP/1.1 400 Bad Request');
            die;
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
        if ($objModule->guests && FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN && !$objModule->protected)
        {
            $response->setData('Forbidden');
            $response->setStatusCode(403);
        }

        // Protected element
        if (!BE_USER_LOGGED_IN && $objModule->protected)
        {
            if (!FE_USER_LOGGED_IN)
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
        $return = $objModule->generateAjax($req);
        $response->setData($return);
        return $response;
    }
}