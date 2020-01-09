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
 * LEGENDS
 */
$GLOBALS['TL_LANG']['tl_member_group']['c4g_groups_legend'] = 'Frontendkonfiguration (con4gis-Groups)';

/**
 * FIELDS
 */
$GLOBALS['TL_LANG']['tl_member_group']['cg_owner_id']           = array( 'Besitzer', 'Das Mitglied dem die Gruppe gehört. Muss ausgewählt sein damit die Gruppe im Frontend sichtbar ist.');
$GLOBALS['TL_LANG']['tl_member_group']['cg_max_member']         = array( 'Maximale Mitgliederanzahl', 'Legt fest wieviele Mitglieder die Gruppe haben kann. (0 = unbegrenzt)');
$GLOBALS['TL_LANG']['tl_member_group']['cg_member_displayname'] = array( 'Format der Mitgliedernamen', 'Legt fest, wie Mitgliedernamen im Frontend dargestellt werden. Die folgenen Platzhalter können verwendet werden: "§f": Vorname, "§l": Nachname, "§u": Benutzername, "§e": E-Mail-Adresse.');
$GLOBALS['TL_LANG']['tl_member_group']['cg_member']             = array( 'Mitglieder', 'Fügen Sie der Gruppe bzw. dem Team Mitglieder hinzu, oder entfernen Sie welche.');
$GLOBALS['TL_LANG']['tl_member_group']['cg_member_rights']      = array( 'Mitgliederrechte', 'Legt fest was die Mitglieder innerhalb dieser Gruppe bzw. dieses Teams dürfen.');
$GLOBALS['TL_LANG']['tl_member_group']['cg_owner_rights']       = array( 'Besitzerrechte', 'Legt fest was der Besitzer innerhalb dieser Gruppe bzw. dieses Teams darf. Diese Einstellung "maskiert" die im Frontend einstellbaren Rechte, da ein Mitglied nicht mehr Rechte haben kann als der Besitzer.');

/**
 * ERRORS
 */
$GLOBALS['TL_LANG']['tl_member_group']['errors']['limit_under_current_count']     = 'Dieses Limit kann die derzeitige Anzahl der Gruppenmitglieder nicht unterschreiten!';
$GLOBALS['TL_LANG']['tl_member_group']['errors']['to_many_members_in_group']      = 'Die Anzahl der Mitglieder übersteigt das festgelegte Gruppen-Limit!';

/**
 * RIGHTS
 */
$GLOBALS['TL_LANG']['tl_member_group']['cg_rights'] = array
(
  // groups
  'group_edit_delete'             => 'Gruppe löschen',
  'group_edit_name'               => 'Gruppenname bearbeiten',
  'group_edit_owner'              => 'Gruppenbesitzer ändern',
  'group_edit_membernameformat'   => 'Mitglieder-Namesformat ändern',
  'group_edit_rights'             => 'Mitglieder-Rechte ändern',
  'group_leave'                   => 'Gruppe verlassen',
  // ranks
  'rank_create'                   => 'Teams anlegen',
  'rank_edit_delete'              => 'Teams löschen',
  'rank_edit_name'                => 'Teamnamen ändern',
  'rank_edit_rights'              => 'Teamkonfiguration ändern',
  'rank_member'                   => 'Teammitglieder verwalten',

  // member
  'member_invite_email'           => 'Mitglieder einladen (via E-Mail)',
  'member_invite_link'            => 'Mitglieder einladen (via Link)',
  'member_remove'                 => 'Mitglieder entfernen',
  'member_contact_email'          => 'Mitglieder kontaktieren (via E-Mail)',
);
