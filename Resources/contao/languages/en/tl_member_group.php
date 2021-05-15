<?php

/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */


/**
 * LEGENDS
 */
$GLOBALS['TL_LANG']['tl_member_group']['c4g_groups_legend'] = 'con4gis_Groups configuration';

/**
 * FIELDS
 */
$GLOBALS['TL_LANG']['tl_member_group']['cg_owner_id']           = array( 'Owner', 'The one who owns the group. Needs to be set to be visible and editable from the frontend unless it is a rank.');
$GLOBALS['TL_LANG']['tl_member_group']['cg_max_member']         = array( 'Maximum group size', 'Defines how many member this group can have. (0 = infinite)');
$GLOBALS['TL_LANG']['tl_member_group']['cg_member_displayname'] = array( 'Membername format', 'Defines how members names should be displayed in the FE. You can use the following placeholders: "§f": firstname, "§l": lastname, "§u": username, "§e": emailaddress.');
$GLOBALS['TL_LANG']['tl_member_group']['cg_member']             = array( 'Group member', 'Remove or add members to this group.');
$GLOBALS['TL_LANG']['tl_member_group']['cg_member_rights']      = array( 'Member rights', 'Defines what members of this group are allowed to do in this group (and its parent group, if this is a rank).');
$GLOBALS['TL_LANG']['tl_member_group']['cg_owner_rights']       = array( 'Owner rights', 'Defines what the owner of this group is allowed to do in this group.');

/**
 * ERRORS
 */
$GLOBALS['TL_LANG']['tl_member_group']['errors']['limit_under_current_count']     = 'This limit cannot be smaller than the current number of group-member!';
$GLOBALS['TL_LANG']['tl_member_group']['errors']['to_many_members_in_group']      = 'Number auf members exceeds the given limit!';

/**
 * RIGHTS
 */
$GLOBALS['TL_LANG']['tl_member_group']['cg_rights'] = array
(
  // groups
  'group_edit_delete'             => 'Delete group',
  'group_edit_name'               => 'Edit group name',
  'group_edit_owner'              => 'Edit group owner',
  'group_edit_membernameformat'   => 'Edit group membername-format',
  'group_edit_rights'             => 'Edit group rights',
  'group_leave'                   => 'Gruppe verlassen',

  // ranks
  'rank_create'                   => 'Create ranks',
  'rank_edit_delete'              => 'Delete ranks',
  'rank_edit_name'                => 'Edit rank name',
  'rank_edit_rights'              => 'Edit rank rights',
  'rank_member'                   => 'Assign/dissociate members to/from ranks',

  // member
  'member_invite_email'           => 'Invite members via email',
  'member_invite_link'            => 'Invite members via link',
  'member_remove'                 => 'Remove members from group',
  'member_contact_email'          => 'Contact members via email',
);
