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

/**
 * Legend
 */
$GLOBALS['TL_LANG']['tl_module']['c4g_groups_appearance_legend']            = 'Gruppen-Erscheinungsbild';
$GLOBALS['TL_LANG']['tl_module']['c4g_groups_groupdefaults_legend']         = 'Gruppen Standardeinstellungen festlegen';
$GLOBALS['TL_LANG']['tl_module']['c4g_groups_permissions_legend']           = 'Gruppen-Berechtigungen';


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['appearance_highlight_owner']              = array('Besondere Gruppenbesitzer-Kennzeichnung', 'Stellt den Namen des Gruppenbesitzers in der Mitgliederliste fettgeschrieben dar.');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['appearance_themeroller_css']              = array('jQuery UI ThemeRoller CSS Datei', 'Optional: wählen Sie eine, mit dem jQuery UI ThemeRoller erstellte, CSS-Datei aus, um den Stil des Moduls entsprechend anzupassen.');

$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_maximum_size']              = array('Standardwert für die maximale Gruppengröße', 'Die maximale Anzahl der Mitglieder die eine neu erstellte Gruppe standardweise haben kann. (0 = infinite)');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_displayname']               = array('Standardwert für die Namensdarstellung', 'Legt fest, wie Mitgliedernamen im Frontend dargestellt werden. Die folgenen Platzhalter können verwendet werden: "§f": Vorname, "§l": Nachname, "§u": Benutzername, "§e": E-Mail-Adresse.');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_member_rights']             = array('Standard Mitglieder-Rechte', 'Die Rechte die ein Mitglied dieser Gruppe standardmäßig hat, wenn sie über das Frontend angelegt wird.');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['default_owner_rights']              = array('Standard Besitzer-Rechte', 'Die Rechte die der Besitzer dieser Gruppe standardmäßig hat, wenn sie über das Frontend angelegt wird.');

$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['permission_creategroups_authorized_groups']     = array('Autorisierte Gruppen (Anlegen neuer Gruppen)', 'Gruppen deren Mitglieder es erlaubt ist neue Gruppen zu erstellen.');
$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['permission_deletegroups_authorized_groups']     = array('Autorisierte Gruppen (Löschen anderer Gruppen)', 'Gruppen deren Mitglieder es erlaubt ist andere Gruppen zu löschen.');

$GLOBALS['TL_LANG']['tl_module']['c4g_groups']['fields']['c4g_groups_permission_applicationgroup'] = array('Globale Mitgliedergruppe', 'In dieser Gruppe landen alle Gruppenmitglieder, sofern sie mindestens einer Gruppe angehören. Nützlich für die "geschützte Bereiche"-Einstellungen im Backend.');

$GLOBALS['TL_LANG']['tl_module']['c4g_groups_uitheme_css_select']     = array(
    'jQuery UI ThemeRoller CSS Theme',
    'Wählen Sie hier eines der Standard UI-Themes aus. Sollten Sie im nächsten Schritt eine eigene Datei auswählen, wird die geladen.'
);

$GLOBALS['TL_LANG']['tl_module']['c4g_references']['settings']  = 'con4gis Einstellungen';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['base']      = 'base';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['black-tie'] = 'black-tie';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['blitzer']   = 'blitzer';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['cupertino'] = 'cupertino';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['dark-hive'] = 'dark-hive';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['dot-luv']   = 'dot-luv';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['eggplant']  = 'eggplant';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['excite-bike']   = 'excite-bike';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['flick']         = 'flick';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['hot-sneaks']    = 'hot-sneaks';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['humanity']      = 'humanity';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['le-frog']       = 'le-frog';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['mint-choc']     = 'mint-choc';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['overcast']      = 'overcast';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['pepper-grinder'] = 'pepper-grinder';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['redmond']       = 'redmond';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['smoothness']    = 'smoothness';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['south-street']  = 'south-street';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['start']         = 'start';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['sunny']         = 'sunny';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['swanky-purse']  = 'swanky-purse';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['trontastic']    = 'trontastic';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['ui-darkness']   = 'ui-darkness';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['ui-lightness']  = 'ui-lightness';
$GLOBALS['TL_LANG']['tl_module']['c4g_references']['vader']         = 'vader';
