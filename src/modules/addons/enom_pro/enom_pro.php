<?php
/**
 * eNom Pro WHMCS Addon
 * @version @VERSION@
 * Copyright @YEAR@ Orion IP Ventures, LLC. All Rights Reserved.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 * @codeCoverageIgnore
 */
defined( "WHMCS" ) or die( "This file cannot be accessed directly" );

defined( 'ENOM_PRO_ROOT' ) or define( 'ENOM_PRO_ROOT', ROOTDIR . '/modules/addons/enom_pro/' );
defined( 'ENOM_PRO_INCLUDES' ) or define( 'ENOM_PRO_INCLUDES', ENOM_PRO_ROOT . 'includes/' );
/**
 * @var string version number
 */
define( "ENOM_PRO_VERSION", '@VERSION@' );
define( 'ENOM_PRO', '@NAME@' );

/**
 * @var string full path to temp dir, with trailing /
 * Override here to change the temp file location
 */
defined( 'ENOM_PRO_TEMP' ) or define( 'ENOM_PRO_TEMP', ENOM_PRO_ROOT . 'temp/' );

/**
 * Load required core files
 */
require_once ENOM_PRO_INCLUDES . 'exceptions.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro_controller.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro_license.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro_widget.php';
/**
 * Helper to show a form input for quick-selecting smarty merge fields
 *
 * @param string $smartyTag the raw string inside of {$smartyTag} == smartyTag
 *
 * @return string
 */
if (! function_exists( 'enom_pro_smarty_field')) :
function enom_pro_smarty_field( $smartyTag ) {

	return '<input type="text" onclick="this.select(); return false;" value="{$' . $smartyTag . '}" />';
}
endif;

/**
 * @return array
 * @codeCoverageIgnore
 */
if (! function_exists( 'enom_pro_config' )) :
function enom_pro_config() {

	$view         = '';
	$spinner_help = '<br/><span class="textred" >';
	$spinner_help .= 'Make sure your active cart & domain checker templates have ' . enom_pro_smarty_field( 'namespinner' ) . ' in them.</span>';
	if ( isset( $_GET['view'] ) ) {
		switch ( $_GET['view'] ) {
			case 'pricing_import':
				$view = ' - Import ' . ( enom_pro::is_retail_pricing() ? 'Retail' : 'Wholesale' ) . ' Pricing from eNom';
				break;
			case 'domain_import':
				$view = ' - Import Domains from eNom';
				break;
			case 'pricing_sort':
				$view = ' - Sort TLD Pricing';
				break;
			case 'whois_checker':
				$view = ' - WHOIS Checker';
				break;
			case 'help':
				$view = ' - Online Help';
				break;
		}
	}
	$save_button_desc = '<input type="submit" name="save_enom_pro" value="Save Changes" class="btn primary btn-success btn-sm">';
	$button           = '<span class="enom_pro_output"><a class="btn btn-info btn-sm" href="' . enom_pro::MODULE_LINK . '" target="_top">Go to @NAME@ &rarr;</a>';
	$button .= $save_button_desc . '</span>';
	$save_button_row      = array(
		'FriendlyName' => 'Save',
		'Type'         => 'null',
		'Description'  => $save_button_desc,
	);
	$support_dept_options = enom_pro::getSupportDepartments();
	$support_dept_string  = 'Disabled';
	foreach ( $support_dept_options as $department_id => $support_meta ) {
		$support_dept_string .= ',' . $department_id . ' | ' . $support_meta['name'];
	}
	$requirements_link = '<a target="_blank" href="http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=54">View requirements help</a>';
	$error_message     = '';
	if ( ! class_exists( 'ZipArchive' ) ) {
		$error_message .= '<div class="errorbox">eNom PRO requires ZipArchive. Get help: ' . $requirements_link . '</div>';
	}
	if ( ! function_exists( 'simplexml_load_string' ) ) {
		$error_message .= '<div class="errorbox">eNom PRO requires SimpleXML. Get help: ' . $requirements_link . '</div>';
	}
	$config = array(
		'name'        => '@NAME@' . $view,
		'version'     => '@VERSION@',
		'author'      => '<a href="https://mycircletree.com/">Circle Tree, LLC</a>',
		'description' => 'Shows eNom Balance and active Transfers on the admin homepage in widgets.
            Adds a clientarea page that displays active transfers to clients.' . $error_message,
		'fields'      => array(
			'quicklink'                => array(
				'FriendlyName' => "",
				"Type"         => "null",
				"Description"  => '<h1 style="margin:0;line-height:1.5;" >' . ENOM_PRO . ' Settings ' . $button . '</h1>'
			),
			'license'                  => array(
				'FriendlyName' => "License Key",
				"Type"         => "text",
				"Size"         => "30"
			),
			'api_request_limit'        => array(
				'FriendlyName' => "API Limit",
				"Type"         => "dropdown",
				"Options"      => "5,10,25,50,75,100,200,500,1000",
				"Default"      => "10",
				"Description"  => "Limit Number of remote API requests. IE - 5 * 100 = 500 domains"
			),
			'client_limit'             => array(
				'FriendlyName' => "Client List Limit",
				"Type"         => "dropdown",
				"Options"      => "50,250,500,1000,10000,Unlimited",
				"Default"      => "Unlimited",
				"Description"  => "Limit size of new order client list"
			),
			'balance_warning'          => array(
				'FriendlyName' => "Credit Balance Warning Threshold",
				"Type"         => "dropdown",
				"Options"      => "Off,10,25,50,100,150,200,500,1000,5000",
				"Default"      => "50",
				"Description"  => "Turns the Credit Balance Widget into a RED flashing warning indicator"
			),
			'debug'                    => array(
				'FriendlyName' => "Debug Mode",
				"Type"         => "yesno",
				"Description"  => "Enable debug messages on frontend. Used for troubleshooting the namespinner,
                             for example."
			),
			'beta'                     => array(
				'FriendlyName' => "Beta Opt-In",
				"Type"         => "yesno",
				"Description"  => "Help beta test the latest &amp; greatest " . ENOM_PRO . " features."
			),
			'disable_gzip'             => array(
				'FriendlyName' => "Disable GZip",
				"Type"         => "yesno",
				"Description"  => "Disabled GZIP Compression on AJAX Requests. Use if your server already compresses all output."
			),
			/****************************
			 * Import (domains, pricing)
			 ***************************/
			'import_section'           => array(
				'FriendlyName' => '',
				"Type"         => "null",
				'default'      => true,
				"Description"  => '<h1 style="line-height:1.1;margin:0;" >Import Options ' . $button . '</h1>'
			),
			'import_per_page'          => array(
				'FriendlyName' => "Show",
				"Type"         => "dropdown",
				"Options"      => "5,10,25,50,75,100,250",
				"Default"      => "25",
				"Description"  => "Results per-page on the Domain Import Page"
			),
			'auto_activate'            => array(
				'FriendlyName' => "Automatically Activate Orders on Import",
				"Type"         => "yesno",
				"Description"  => "Set imported orders to active and eNom registrar",
				"Default"      => "on"
			),
			'next_due_date'            => array(
				'FriendlyName' => "Next Due Date",
				"Type"         => "dropdown",
				"Options"      => "Expiration Date,-1 Day,-3 Days,-5 Days,-7 Days,-14 Days",
				"Default"      => "-3 Days",
				"Description"  => "Set active, imported domain next billing due date, relative to # of days BEFORE expiration. <br/>
                                    <b>Auto-Activation, above, must be enabled for this to function.</b>"
			),
			'pricing_years'            => array(
				'FriendlyName' => "Import TLD Pricing Max Years",
				"Type"         => "dropdown",
				"Options"      => "1,2,3,4,5,6,7,8,9,10",
				"Default"      => "10",
				"Description"  => "Limit the maximum number of years to import TLD pricing for.
                                <em>Speeds Up the Import Process if you only offer registrations up to 3 years, for example.</em>"
			),
			'pricing_per_page'         => array(
				'FriendlyName' => "Show",
				"Type"         => "dropdown",
				"Options"      => "5,10,25,50,75,100,250,500",
				"Default"      => "25",
				"Description"  => "Results per-page on the TLD Pricing Import Page"
			),
			'pricing_retail'           => array(
				'FriendlyName' => "Retail Pricing ",
				"Type"         => "yesno",
				'default'      => false,
				"Description"  => "Use your eNom Retail Pricing. Un-check to use wholesale pricing (Your Cost)"
			),
			'exchange_rate_provider'   => array(
				'FriendlyName' => "Exchange Rate Data Provider",
				"Type"         => "dropdown",
				"Options"      => "google,currency-api",
				"Default"      => "google",
				"Description"  => "Choose where to fetch currency data from"
			),
			'custom-exchange-rate'     => array(
				'FriendlyName' => "Custom Exchange Rate",
				"Type"         => "text",
				"Default"      => null,
				"Description"  => 'Override the remote API exchange rate for your own',
				'Size'         => 8
			),
			'exchange-rate-api-key'    => array(
				'FriendlyName' => "Exchange Rate API Key",
				"Type"         => "text",
				"Default"      => null,
				"Description"  => 'API key for <a href="http://currency-api.appspot.com/" target="_blank" >Currency API. Sign up for free here.</a>',
				'Size'         => 0
			),
			/****************************
			 * SSL
			 ***************************/
			'ssl_section'              => array(
				'FriendlyName' => '',
				"Type"         => "null",
				'default'      => true,
				"Description"  => '<h1 style="line-height:1.1;margin:0;" >SSL Reminder Options ' . $button . '</h1>'
			),
			'ssl_days'                 => array(
				'FriendlyName' => "Widget Expiring SSL Days",
				"Type"         => "text",
				"Size"         => 3,
				"Default"      => "30",
				"Description"  => "Number of days before SSL Certificate Expiration to show in Widget"
			),
			'ssl_email_enabled'        => array(
				'FriendlyName' => "Enable SSL Reminder Email",
				"Type"         => "yesno",
				'default'      => true,
				"Description"  => enom_pro::is_ssl_email_installed() > 0 ? '<a class="btn btn-block btn-default" href="configemailtemplates.php?action=edit&id=' . enom_pro::is_ssl_email_installed() . '">Edit SSL Email</a>' : '<a class="btn btn-block btn-default" href="' . enom_pro::MODULE_LINK . '&action=install_ssl_template">Install SSL Email</a>'
			),
			'ssl_email_days'           => array(
				'FriendlyName' => "Expiring SSL Reminder Days",
				"Type"         => "text",
				"Size"         => 3,
				"Default"      => "30",
				"Description"  => "Number of days before sending the SSL Certificate Expiration email, or opening a support ticket for client. (Or, both)."
			),
			'ssl_open_ticket'          => array(
				'FriendlyName' => "Open a ticket on SSL reminder in this department",
				"Type"         => "dropdown",
				"Options"      => $support_dept_string,
				"Size"         => 60,
				"Default"      => "Disabled",
				"Description"  => "Opens a support ticket in the selected department when an SSL certificate is due for renewal."
			),
			'ssl_ticket_priority'      => array(
				'FriendlyName' => "Ticket Priority",
				"Type"         => "dropdown",
				"Options"      => 'Low,Medium,High',
				"Default"      => "Low",
				"Description"  => ""
			),
			'ssl_ticket_subject'       => array(
				'FriendlyName' => "Ticket Subject",
				"Type"         => "text",
				"Default"      => 'Expiring SSL Certificate',
				"Description"  => '',
				'Size'         => 60
			),
			'ssl_ticket_message'       => array(
				'FriendlyName' => "Ticket Message",
				"Type"         => "textarea",
				"Default"      => 'We have opened a ticket to renew {$product} for {$domain_name}, which  is set to expire on {$expiry_date}. Our staff will help you get your certificate renewed.',
				"Description"  => 'Merge fields are: ' . enom_pro_smarty_field( 'product' ) . enom_pro_smarty_field( 'domain_name' ) . enom_pro_smarty_field( 'expiry_date' ),
				'Cols'         => 100
			),
			'ssl_ticket_email_enabled' => array(
				'FriendlyName' => "Send ticket opened email",
				"Type"         => "yesno",
				'default'      => false,
				"Description"  => "In addition to the SSL Reminder Email from " . ENOM_PRO . ", also send the client a message about this ticket being opened."
			),
			'ssl_ticket_default_name'  => array(
				'FriendlyName' => "Ticket Default Name",
				"Type"         => "text",
				"Default"      => '',
				"Description"  => 'If no client is found, open a ticket using this default name.<br/> <b>Leave blank to disable</b>',
				'Size'         => 60
			),
			'ssl_ticket_default_email' => array(
				'FriendlyName' => "Ticket Default Email",
				"Type"         => "text",
				"Default"      => '',
				"Description"  => 'If no client is found, open a ticket using this default email address.',
				'Size'         => 60
			),
			/****************************
			 * NameSpinner
			 ***************************/
			'spinner_section'          => array(
				'type'        => null,
				'Description' => '<h1 style="line-height:1.1;margin:0;" >NameSpinner Options ' . $button . '</h1>'
			),
			'spinner_results'          => array(
				'FriendlyName' => "Namespinner Results",
				"Type"         => "text",
				"Default"      => 10,
				"Description"  => "Max Number of namespinner results to show" . $spinner_help,
				'Size'         => 10
			),
			'spinner_columns'          => array(
				'FriendlyName' => "Namespinner Columns",
				"Type"         => "dropdown",
				"Options"      => "1,2,3,4",
				"Default"      => "3",
				"Description"  => "Number of columns to display results in.
                            Make sure it is divisible by the # of results above to make nice columns.",
				'Size'         => 10
			),
			'spinner_sortby'           => array(
				'FriendlyName' => "Sort Results",
				"Type"         => "dropdown",
				"Options"      => "score,domain",
				"Default"      => "score",
				"Description"  => "Sort namespinner results by score or domain name"
			),
			'spinner_sort_order'       => array(
				'FriendlyName' => "Sort Order",
				"Type"         => "dropdown",
				"Options"      => "Ascending,Descending",
				"Default"      => "Descending",
				"Description"  => "Sort order for results"
			),
			'spinner_checkout'         => array(
				'FriendlyName' => "Show Add to Cart Button?",
				"Type"         => "yesno",
				"Description"  => "Display checkout button at the bottom of namespinner results"
			),
			'cart_css_class'           => array(
				'FriendlyName' => "Cart CSS Class",
				"Type"         => "dropdown",
				"Options"      => "btn,btn-primary,button,custom",
				"Default"      => "btn-primary",
				"Description"  => "Customize the Add to Cart button by CSS class"
			),
			'custom_cart_css_class'    => array(
				'FriendlyName' => "Cart CSS Class",
				"Type"         => "text",
				"Description"  => "Add a custom cart CSS class"
			),
			'spinner_css'              => array(
				'FriendlyName' => "Style Spinner?",
				"Type"         => "yesno",
				"Description"  => "Include Namespinner CSS File"
			),
			'spinner_animation'        => array(
				'FriendlyName' => "Namespinner Result Animation Speed",
				"Type"         => "dropdown",
				"Default"      => "Medium",
				"Options"      => "Off,Slow,Medium,Fast",
				"Description"  => "Speed of the NameSpinner Results Animation",
				'Size'         => 10
			),
			'spinner_com'              => array(
				'FriendlyName' => ".com",
				"Type"         => "yesno",
				"Description"  => "Display .com namespinner results"
			),
			'spinner_net'              => array(
				'FriendlyName' => ".net",
				"Type"         => "yesno",
				"Description"  => "Display .net namespinner results"
			),
			'spinner_tv'               => array(
				'FriendlyName' => ".tv",
				"Type"         => "yesno",
				"Description"  => "Display .tv namespinner results"
			),
			'spinner_cc'               => array(
				'FriendlyName' => ".cc",
				"Type"         => "yesno",
				"Description"  => "Display .cc namespinner results"
			),
			'spinner_hyphens'          => array(
				'FriendlyName' => "Hyphens",
				"Type"         => "yesno",
				"Description"  => "Use hyphens (-) in namespinner results"
			),
			'spinner_numbers'          => array(
				'FriendlyName' => "Numbers",
				"Type"         => "yesno",
				"Description"  => "Use numbers in namespinner results"
			),
			'spinner_sensitive'        => array(
				'FriendlyName' => "Block sensitive content",
				"Type"         => "yesno",
				"Description"  => "Block sensitive content"
			),
			'spinner_basic'            => array(
				'FriendlyName' => "Basic Results",
				"Type"         => "dropdown",
				"Default"      => "Medium",
				"Description"  => "Higher values return suggestions that are built by
                            adding prefixes, suffixes, and words to the original input",
				"Options"      => "Off,Low,Medium,High"
			),
			'spinner_related'          => array(
				'FriendlyName' => "Related Results",
				"Type"         => "dropdown",
				"Default"      => "High",
				"Description"  => "Higher values return domain names by interpreting the input semantically
                            and construct suggestions with a similar meaning.<br/>
                            <b>Related=High will find terms that are synonyms of your input.</b>",
				"Options"      => "Off,Low,Medium,High"
			),
			'spinner_similiar'         => array(
				'FriendlyName' => "Similiar Results",
				"Type"         => "dropdown",
				"Default"      => "Medium",
				"Description"  => "Higher values return suggestions that are similar to the customer's input,
                            but not necessarily in meaning.<br/>
                            <b>Similar=High will generate more creative terms, with a slightly looser
                            relationship to your input, than Related=High.</b>",
				"Options"      => "Off,Low,Medium,High"
			),
			'spinner_topical'          => array(
				'FriendlyName' => "Topical Results",
				"Type"         => "dropdown",
				"Default"      => "High",
				"Description"  => "Higher values return suggestions that reflect current topics
                            and popular words.",
				"Options"      => "Off,Low,Medium,High"
			),
			'quicklink2'               => array(
				'FriendlyName' => "Quick-Links",
				"Type"         => "null",
				"Description"  => $button
			),
		)
	);

	return $config;
}
endif;
/**
 * @codeCoverageIgnore
 */
if (! function_exists( 'enom_pro_activate')) :
function enom_pro_activate() {


	if ( ! version_compare(substr($GLOBALS['CONFIG']['Version'],0, 3), '7.1', 'ge')) {
		//We introduced a breaking change for 3.x due to WHMCS 7.1's new widget API
		return array('status' => 'error', 'description' => 'WHMCS 7.1 is required for @NAME@ version 4.0 and above');
	}
	if ( ! class_exists( 'ZipArchive' ) ) {
		return array(
			'status'      => 'error',
			'description' => 'ZipArchive is required for eNom PRO to function.'
		);
	}
	if ( ! function_exists( 'simplexml_load_string' ) ) {
		return array(
			'status'      => 'error',
			'description' => 'SimpleXML is required for eNom PRO to function.'
		);
	}

	$query = "CREATE TABLE IF NOT EXISTS `mod_enom_pro` (
            `id` INT(1) NOT NULL,
            `local` TEXT NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	mysql_query( $query );

	//Reset license
	$query = "INSERT INTO `mod_enom_pro` VALUES(0, '');";
	mysql_query( $query );

	//Delete the defaults so MySQL doesn't error out on duplicate insert
	$query = "	DELETE FROM `tbladdonmodules` WHERE `module` = 'enom_pro';";
	mysql_query( $query );

	//Check for backed-up settings
	$query  = "SELECT * FROM `mod_enom_pro` WHERE `id` = 1";
	$result = mysql_fetch_assoc( mysql_query( $query ) );
	if ( $result ) {
		$backup_array = unserialize( $result['local'] );
		foreach ( $backup_array as $setting_key => $setting_value ) {
			$setting_key   = mysql_real_escape_string( $setting_key );
			$setting_value = mysql_real_escape_string( $setting_value );
			$query         = "INSERT INTO `tbladdonmodules` VALUES('enom_pro', '{$setting_key}', '{$setting_value}')";
			mysql_query( $query );
		}

		return array(
			'status'      => 'info',
			'description' => ENOM_PRO . ' Re-Activated. Settings have been restored.'
		);

	} else {
		mysql_query( "BEGIN" );
		//Insert these defaults due to a bug in the WHMCS addon api with checkboxes
		$query = "
	            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_net', 'on');";
		mysql_query( $query );
		$query = "
	            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_com', 'on');";
		mysql_query( $query );
		$query = "
	            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_css', 'on');";
		mysql_query( $query );
		$query = "
	            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_checkout', 'on');";
		mysql_query( $query );
		mysql_query( "COMMIT" );

		return array(
			'status'      => 'success',
			'description' => ENOM_PRO . ' Activated'
		);
	}
	if ( mysql_error() ) {
		return array(
			'status'      => 'error',
			'description' => ENOM_PRO . ' MySQL Error: ' . mysql_error()
		);
	}
}
endif;
if (! function_exists( 'enom_pro_deactivate')) :
function enom_pro_deactivate() {

	$query           = "SELECT * FROM `tbladdonmodules` WHERE `module` = 'enom_pro'";
	$result          = mysql_query( $query );
	$settings_backup = array();
	while ( $row = mysql_fetch_assoc( $result ) ) {
		$settings_backup[ $row['setting'] ] = $row['value'];
	}
	mysql_query( "DELETE FROM `mod_enom_pro` WHERE `id` = 1" );
	$settings_backup = mysql_real_escape_string( serialize( $settings_backup ) );
	$query           = "INSERT INTO `mod_enom_pro` VALUES (1, '{$settings_backup}')";
	mysql_query( $query );

	return array(
		'status'      => 'success',
		'description' => ENOM_PRO . ' Deactivated. Settings have been backed up.'
	);
}
endif;
/**
 * @param array $vars module vars
 *
 * @return string
 * @codeCoverageIgnore
 */
if (! function_exists( 'enom_pro_sidebar')) :
function enom_pro_sidebar( $vars ) {

	ob_start();
	require_once ENOM_PRO_INCLUDES . 'sidebar.php';
	$sidebar = ob_get_contents();
	ob_end_clean();

	return $sidebar;
}
endif;
/**
 * @param array $vars
 *
 * @codeCoverageIgnore
 */
if (! function_exists( 'enom_pro_output')) :
function enom_pro_output( $vars ) {

	//No need to output anything on the admin actions
	if ( isset( $_REQUEST['action'] ) ) {
		return;
	} ?>
	<div class="enom_pro_output">
	  <?php
	  try {

		  new enom_pro_license();
		  $enom = new enom_pro();
		  ?>
				<script src="../modules/addons/enom_pro/js/bootstrap.min.js"></script>
				<div id="enom_pro_dialog" title="Loading..." style="display:none;">
					<iframe src="about:blank" id="enom_pro_dialog_iframe"
									sandbox="allow-same-origin allow-scripts allow-forms allow-top-navigation allow-pointer-lock"></iframe>
				</div>

	  <?php require_once ENOM_PRO_INCLUDES . 'admin_messages.php'; ?>
	  <?php
	  if ( isset( $_GET['view'] ) && method_exists( $enom,
			  'render_' . $_GET['view'] )
	  ) {

		  //Run this test to throw an exception sooner
		  //  (before rendering page content)
		  if ( ( isset( $_GET ) && isset( $_GET['view'] ) ) && 'help' !== $_GET['view'] ) {
			  //Don't run API check on the help page.
			  $enom->check_login();
		  }

		  $view   = (string) $_GET['view'];
		  $method = "render_$view";
		  $enom->$method();

		  return;
	  } else {
		  //Run this to check login credentials and IP restrictions
		  $enom->getAvailableBalance();
		  $enom->render_home();
	  }
	  ?>
	  <?php } catch ( EnomException $e ) { ?>
				<div class="alert alert-warning">
					<h2>eNom API Error:</h2>
			<?php echo enom_pro::render_admin_errors( $e->get_errors() ); ?>
				</div>
	  <?php } catch ( Exception $e ) { ?>
				<div class="alert alert-danger">
					<h2>Error</h2>
			<?php echo $e->getMessage(); ?>
				</div>
		  <?php
	  } //End Final Exception Catch
	  ?>
	</div>
	<?php
}
endif;
