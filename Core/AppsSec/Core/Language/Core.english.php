<?php

// Version: 2.0, Web
return [

    'name' => 'TekFW Framework',

	/*
	 * **************************************************************************
	 * BASICS
	 * ****************************************************************************
	 */

	// States
	'on' => 'On',
    'off' => 'Off',

    // Settings
    'config' => 'Settings',
    'info' => 'Informations',
    'init' => 'Init...',

	/* ERRORS */
	'error' => 'Error',
    'error_general' => 'A general error occured.',
    'error_404' => 'The requested document does not exist.',
    'error_403' => 'You are not allowed to access the requested document',
    'error_500' => 'An internal error occured.',

    // Basics
    'noscript' => '<span style="color: #FF0000, font-size: 16px, border: 1px solid #FF0000, padding: 3px, width: 100%, text-align: center,DIESE SEITE BENÖTIGT JAVASCRIPT.<br BITTE AKTIVIERE ES IN DEINEN BRWOSEREINSTELLUNGEN.</spa',
    'next' => '&gt,&gt,',
    'prev' => '&lt,&lt,',

    'save' => 'Save',
    'cancel' => 'Cancel',

    'login' => 'Login',
    'logout' => 'Logout',

    'username' => 'Username',
    'password' => 'Password',
    'remember_me' => 'Stay logged in',

    'admin' => 'Administration',

    // NED texts
    'delete' => 'Delete',
    'delete_confirm' => 'Should data really be deleted?',
    'new' => 'New',
    'edit' => 'Edit',

    // allow or deny
    'access_allow' => 'Allowed to selected groups',
    'access_deny' => 'Denied to selected groups',

    /**
     * **************************************************************************
     * CONFIG
     * **************************************************************************
     */
    'cfg_headline' => 'TekFW Framework Settings',

	/* Contenthandler
	'cfg_default_action' => 'Default SMF action',
	'cfg_default_action_desc' => 'Name of default action to use.',
	'cfg_group_global' => 'Content',
	'cfg_default_app' => 'Default app',
	'cfg_default_desc' => 'Name of app which is used for pagecontrol',
	'cfg_default_ctrl' => 'Default Controller',
	'cfg_default_ctrl_desc' => 'Name of controller to call in default app.',
	'cfg_content_handler' => 'Contenthandler app',
	'cfg_content_handler_desc' => 'Name of app which handles the content output.',
	'cfg_menu_handler' => 'Menuhandler app',
	'cfg_menu_handler_desc' => 'Name of app which handles menucreation.',*/

	// Minifier
	'cfg_group_minify' => 'Minify',
    'cfg_css_minify' => 'CSS Minifier',
    'cfg_css_minify_desc' => 'This option activates the automatic minify process for all used CSS files. (see <a href="https://code.google.com/p/minify/">https://code.google.com/p/minify/</a>)',
    'cfg_js_minify' => 'JS Minifier',
    'cfg_js_minify_desc' => 'This option activates the automatic minify process for all javascripts and files. (see <a href="https://code.google.com/p/minify/">https://code.google.com/p/minify/</a>)',

    // Security
    'cfg_group_security' => 'Security',
    'cfg_min_login_lenght' => 'Minimum login lenght',
    'cfg_min_login_lenght_desc' => 'The minimum number of chars needed to create a login name.',
    'cfg_max_login_lenght' => 'Maximum login lenght',
    'cfg_max_login_lenght_desc' => 'The maximum number of chars allowed to create a login name.',
    'cfg_min_password_lenght' => 'Minimum password lenght',
    'cfg_min_password_lenght_desc' => 'The minimum number of chars needed to create a password.',
    'cfg_max_password_lenght' => 'Maximum password lenght',
    'cfg_max_password_lenght_desc' => 'The maximum number of chars allowed to create a password.',

    // Javascript
    'cfg_group_js' => 'Javascript',

    'cfg_js_default_position' => 'Script position',
    'cfg_js_default_position_desc' => 'Setting to control the placement of javascript by default.',
    'cfg_jquery_version' => 'jQuery version',
    'cfg_jquery_version_desc' => '<strong>Note:</strong> Make sure to have the needed files in the core js folder when using jQuery from local and not from CDN.',
    'cfg_jquery_use_local' => 'Local jQuery files',
    'cfg_jquery_use_local_desc' => 'Use local jQuery files instead of CDN?',

    'cfg_js_html5shim' => 'html5shim',
    'cfg_js_html5shim_desc' => 'This option activates use of html5shim. (siehe <a href="https://code.google.com/p/html5shim/">https://code.google.com/p/html5shim/</a>)',
    'cfg_js_selectivizr' => 'Selectivizr',
    'cfg_js_selectivizr_desc' => 'This option activates thue use of Selectivizr. (siehe <a href="http://selectivizr.com/">http://selectivizr.com/</a>)',
    'cfg_js_modernizr' => 'Modernizer',
    'cfg_js_modernizr_desc' => 'This option activates the use of Modernizr. (siehe <a href="http://modernizr.com/">http://modernizr.com/</a>)',
    'cfg_js_fadeout_time' => 'Fadeouttime',
    'cfg_js_fadeout_time_desc' => 'Time (in milliseconds) to use as global fadeout timer.',

    // Style
    'cfg_group_style' => 'Visuals',
    'cfg_group_style_desc' => 'Gestaltung',
    'cfg_bootstrap_version' => 'Bootstrap Version',
    'cfg_bootstrap_version_desc' => 'Version number of Bootstrap css framework to use. Do not forget to place the corresponding file into frameworks css folder. The filename has to use "bootstrap-version.css" or "bootstrap-versions.min.css" pattern.',
    'cfg_bootstrap_use_local' => 'Local Bootstrap files',
    'cfg_bootstrap_use_local_desc' => 'Local Bootstrap files instead of official CDN?',
    'cfg_fontawesome_version' => 'Fontawesome Version',
    'cfg_fontawesome_version_desc' => 'Version number of Fontawesome css framework to use. Do not forget to place the corresponding file into frameworks css folder. The filename has to use "fontawesome-versionnumber.css" or "fontawesome-version.min.css" pattern.',
    'cfg_fontawesome_use_local' => 'Use local Fontawesome files instead of official CDN?',
    'cfg_theme' => 'Them to use',

    // Logging
    'cfg_group_logging' => 'Logging',
    'cfg_log' => 'Loggin is',
    'cfg_show_log_output' => 'Show log output',
    'cfg_log_db' => 'DB logging',
    'cfg_log_app' => 'Page logging',
    'cfg_fire' => '',
    'cfg_log_handler' => 'Log handler',
    '' => '',
    '' => '',

    /**
     * **************************************************************************
     * VALIDATORS
     * ****************************************************************************
     */
    'validator_required' => 'This field has to be set.',
    'validator_empty' => 'This field is not alloed to be empty.',

    // Strings
    'validator_textrange' => 'Strings number of chars has to be between %d and %d. The checked string contains %d chars.',
    'validator_textminlength' => 'The number of chars has to be %d at minimum.',
    'validator_textmaxlength' => 'The number of chars has to be %d at maximum.',

    // Dates
    'validator_date_iso' => 'Date in ISO Format (YYYY-MM-DD) expected.',
    'validator_date' => 'Please provide proper date.',

    // Time
    'validator_time24' => 'Time in 24h format (HH:II:ss) expected',

    // Number
    'validator_compare' => 'Comparecheck failed. Checked: $1 $3 $2',
    'validator_numbermin' => 'The value is not allowed to be smaller then %d.',
    'validator_numbermax' => 'The value exeeds the set maximum of $1',
    'validator_numberrange' => 'The value has to be between %d and %d.',

    /**
     * **************************************************************************
     * Models
     * ****************************************************************************
     */
    'model_error_field_not_exist' => 'Column [%s does not exist in model [%s].',

    /**
     * **************************************************************************
     * TIMESTRINGS
     * ****************************************************************************
     */
    'time_year' => 'year',
    'time_years' => 'years',
    'time_month' => 'month',
    'time_months' => 'months',
    'time_week' => 'week',
    'time_weeks' => 'weeks',
    'time_day' => 'day',
    'time_days' => 'days',
    'time_hour' => 'hour',
    'time_hours' => 'hours',
    'time_minute' => 'minute',
    'time_minutes' => 'minutes',
    'time_second' => 'second',
    'time_seconds' => 'seconds'
];

