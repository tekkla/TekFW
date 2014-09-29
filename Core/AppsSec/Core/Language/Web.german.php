<?php
global $forum_copyright;

// Version: 2.0; Web
$txt['app_name'] = 'TekFW Framework';

$forum_copyright .= '<b<smalerweitert durch ' . $txt['app_name'] . ' © 2014, Michael "Tekkla" Zorn</smal';

$txt['app_framework_config'] = $txt['app_name'];

/*****************************************************************************/
/* BASICS
/*****************************************************************************/

// States
$txt['app_on'] = 'An';
$txt['app_off'] = 'Aus';

// Settings
$txt['app_config'] = 'Einstellungen';
$txt['app_info'] = 'Informationen';
$txt['app_init'] = 'Initialisieren...';

/* ERRORS */
$txt['app_error'] = 'Fehler';
$txt['app_error_general'] = 'Ein allgemeiner Fehler ist aufgetreten.';
$txt['app_error_404'] = 'Die angeforderte Seite existiert nicht.';
$txt['app_error_403'] = 'Keine Zugriffsberechtigung vorhanden.';
$txt['app_error_500'] = 'Ein interner Fehler ist aufgetreten.';

// Basics
$txt['app_next'] = '&gt;&gt;';
$txt['app_prev'] = '&lt;&lt;';

$txt['app_save'] = 'Speichern';
$txt['app_cancel'] = 'Abbruch';

// NED texts
$txt['app_delete'] = 'L&ouml;schen';
$txt['app_delete_confirm'] = 'Daten wirklich l&ouml;schen?';
$txt['app_new'] = 'Neu';
$txt['app_edit'] = 'Bearbeiten';

// allow or deny
$txt['app_access_allow'] = 'Nur gewählten Gruppen anzeigen';
$txt['app_access_deny'] = 'Vor gewählten Gruppen verstecken';

/*****************************************************************************/
/* CONFIG
/*****************************************************************************/
$txt['app_cfg_headline'] = 'TekFW Framework Einstellungen';

// Inhalte
$txt['app_cfg_group_global'] = 'Inhaltsverarbeitung';
$txt['app_cfg_default_app'] = 'Standard App';
$txt['app_cfg_default_app_desc'] = 'Name der App, die als Standard beim Seitenaufruf geladen werden soll.';
$txt['app_cfg_default_ctrl'] = 'Standard Controller';
$txt['app_cfg_default_ctrl_desc'] = 'Name des in der Standard App aufzurufenden Controllers.';
$txt['app_cfg_content_handler'] = 'Content Handler App';
$txt['app_cfg_content_handler_desc'] = 'Name einer App, an die der auszugebende Content für weitere Aufgaben übergeben wird. Dieser Punkt ist besonders für die Integration von Portal Apps gedacht.';
$txt['app_cfg_menu_handler'] = 'Menu Handler App';
$txt['app_cfg_menu_handler_desc'] = 'Name einer App, an die die Menubuttons zur weiteren Bearbeitung übergebne werden sollen.';

// Minifier
$txt['app_cfg_group_minify'] = 'Minify';
$txt['app_cfg_css_minify'] = 'CSS Minifier';
$txt['app_cfg_css_minify_desc'] = 'Diese Option aktiviert die automatische Minimierung aller genutzten CSS Files. (siehe <a href="https://code.google.com/p/minify/https://code.google.com/p/minify/</)';
$txt['app_cfg_js_minify'] = 'JS Minifier';
$txt['app_cfg_js_minify_desc'] = 'Diese Option aktiviert die automatische Minimierung aller genutzten Javascripte und Files. (siehe <a href="https://code.google.com/p/minify/https://code.google.com/p/minify/</)';

// Javascript
$txt['app_cfg_group_js'] = 'Javascript';
$txt['app_cfg_js_html5shim'] = 'html5shim';
$txt['app_cfg_js_html5shim_desc'] = 'Option um html5shim auf der Seite einzusetzen. (siehe <a href="https://code.google.com/p/html5shim/https://code.google.com/p/html5shim/</)';
$txt['app_cfg_js_selectivizr'] = 'Selectivizr';
$txt['app_cfg_js_selectivizr_desc'] = 'Option um Selectivizr auf der Seite zu nutzen. (siehe <a href="http://selectivizr.com/http://selectivizr.com/</)';
$txt['app_cfg_js_modernizr'] = 'Modernizer Support';
$txt['app_cfg_js_modernizr_desc'] = 'Option um den Modernizer auf der Seite zu verwenden. (siehe <a href="http://modernizr.com/http://modernizr.com/</)';
$txt['app_cfg_js_fadeout_time'] = 'Fadeoutzeit';
$txt['app_cfg_js_fadeout_time_desc'] = 'Zeit in Millisekunden, die im gesamten Framework für Fadeouttimer genutzt werden soll.';

// Gestaltung
$txt['app_cfg_group_style'] = 'Gestaltung';
$txt['app_cfg_group_style_desc'] = 'Gestaltung';
$txt['app_cfg_bootstrap_version'] = 'Bootstrap Version';
$txt['app_cfg_bootstrap_version_desc'] = 'Versionsnummer der zu verwendenen Bootstrapversion. Bitte beachten, dass diese Version auch im Framework CSS Verzeichnis mit dem Schema "bootstrap-versionsnummer.css" oder "bootstrap-versionsnummer.min.css" hinterlegt sein muss!';
$txt['app_cfg_fontawesome_version'] = 'Fontaweseom Version';
$txt['app_cfg_fontawesome_version_desc'] = 'Versionsnummer der zu verwendenden Fontawesome Bibliothek. Auch diese Version muss wie bei Bootstrab im CSS Verzeichnis des Framworks mit dem selben Namensschema hinterlegt sein.';

// URL Behandlung
$txt['app_cfg_group_url'] = 'Url Behandlung';
$txt['app_cfg_url_seo'] = 'SEO Konverter';
$txt['app_cfg_url_seo_desc'] = 'Damit wird vor der Ausgabe der Seite der Content auf URL untersucht und alle nicht durch das Framework generierten URL umgewandelt. Beispielsweise würde aus <stronhttp://www.deinforum.tld/index.php?board=1</stron dann <stronhttp://www.deinforum.tld/board/1</stron';

/*****************************************************************************/
/* VALIDATORS
/*****************************************************************************/
$txt['app_validator_required'] = 'Dieses Feld muss gesetzt sein.';
$txt['app_validator_empty'] = 'Dieses Feld darf nicht leer sein';
$txt['app_validator_textrange'] = 'Der Text darf zwischen %d und %d Zeichen lang sein. Dein Text ist %d Zeichen lang.';
$txt['app_validator_textminlength'] = 'Der Text muss mindestens %d Zeichen lang sein';
$txt['app_validator_textmaxlength'] = 'Der Text darf maxinaml %d Zeichen lang sein';
$txt['app_validator_numbermin'] = 'Der Wert darf nicht kleiner als %d sein';

// Dates
$txt['app_validator_date_iso'] = 'Datum im ISO Format (YYYY-MM-DD) erwartet';
$txt['app_validator_date'] = 'Es wird ein gültiges Datum erwartet';

// Time
$txt['app_validator_time24'] = 'Uhrzeit im 24h Format (HH:II) erwartet';

// Number
$txt['validator_compare'] = 'Die Vergleichsprüfung schlug fehl. Geprüft wurde: $1 $3 $2';

/*****************************************************************************/
/* Models
/*****************************************************************************/
$txt['app_model_error_field_not_exist'] = 'Die Spalte [%s] existiert nicht im Model [%s].';

/*****************************************************************************/
/* TIMESTRINGS
/*****************************************************************************/
$txt['app_time_year'] = 'Jahr';
$txt['app_time_years'] = 'Jahre';
$txt['app_time_month'] = 'Monat';
$txt['app_time_months'] = 'Monate';
$txt['app_time_week'] = 'Woche';
$txt['app_time_weeks'] = 'Wochen';
$txt['app_time_day'] = 'Tag';
$txt['app_time_days'] = 'Tage';
$txt['app_time_hour'] = 'Stunde';
$txt['app_time_hours'] = 'Stunden';
$txt['app_time_minute'] = 'Minute';
$txt['app_time_minutes'] = 'Minuten';
$txt['app_time_second'] = 'Sekunde';
$txt['app_time_seconds'] = 'Sekunden';
