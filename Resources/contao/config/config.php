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

/**
 * FRONT END MODULES
 */
array_insert( $GLOBALS['FE_MOD']['con4gis'], 7, array
  (
    'c4g_groups' => 'con4gis\GroupsBundle\Resources\contao\modules\C4GGroups'
  )
);


/**
 * API MODULES
 */
//$GLOBALS['TL_API']['c4gGroupsService'] = 'con4gis\GroupsBundle\Resources\contao\modules\api\C4gGroupsAjaxApi';


/**
 * ACTIVATIONPAGE-FUNCTION
 */
$GLOBALS['C4G_ACTIVATIONACTION']['c4g_joingroup'] = 'con4gis\GroupsBundle\Classes\CGController';

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['con4gis Groups'] = array
(
    'invite_member'   => array
    (
        'recipients'           => array('member_email','new_member_email'),
        'email_subject'        => array('subject'),
        'email_text'           => array('text_content'),
        'email_html'           => array('member_name',),
        'email_sender_name'    => array('member_email', 'member_name'),
        'email_sender_address' => array('member_email'),
        'email_recipient_cc'   => array('member_email'),
        'email_recipient_bcc'  => array('member_email'),
        'email_replyTo'        => array('member_email'),
        'file_content'           => array('member_name','text_content', 'subject'),
    ),
    'notify_member'   => array
    (
        'recipients'           => array('mail_receiver',),
        'email_subject'        => array(),
        'email_text'           => array('text_content'),
        'email_html'           => array('text_content',),
        'email_sender_name'    => [],
        'email_sender_address' => [],
        'email_recipient_cc'   => [],
        'email_recipient_bcc'  => [],
        'email_replyTo'        => [],
        'file_content'           => array('mail_receiver','text_content',),
    )
);