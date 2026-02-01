<?php
/*
 * SECTION: OVERVIEW
 *
 * "A Basic Content Management System" (ABCMS)
 * AKA "Aionian Bible Content Management System" at AionianBible.org
 * PHP web developer toolkit and CMS in a single file
 * Install with Composer or copy to document root
 * Everything is an extension with the abcms() router
 * Run CLI "php index.php /abcms/help | html2text"
 *
 */


/*
 * SECTION: EXPLANATION
 *
 * 1. A whole CMS web developer toolkit in one file is possible
 * 2. Constants allow sharing immutable values and they are fast
 * 3. Try Catch allows fail safe error handling of the core abcms() function
 * 4. Core dump exception allows developer debugging with all information
 * 5. Instantiate and process inputs allows global onetime input handling
 * 6. EVERYTIHNG is an extension with $abcms->output()
 * Here is the idea, all output is extendable which helps us think more simply about content management.
 * We have inputs, processing, and outputs. The output function serves as both a command router and extension
 * manager. The generic output function does not even require a default function because it expects to be
 * extended by you to do something meaningful. The ABCMS engine looks for you to override the "/nainoiainc/abcms/begin"
 * hook first. From there you output what you want and also include your own extendable calls to output() yourself.
 * I am still learning how to think about this cool tool. One thought is that Symfony Twig and Laravel Blade template
 * engines seem like an unneccessary reduction of template power. With ABCMS PHP itself is the template engine. PHP
 * is a powerful tool to mix HTML output with procedural logic. In fact is there any more powerful combination of 
 * HTML and procedural logic than PHP? So that is the template engine for ABCMS. Sure this requires that frontend
 * developers understand PHP and HTML, but that is also both simpler and more powerful.
 * 
 * 
 * 
 */ 
 
 
/* 
 * SECTION: TODO
 *
 * Beautiful file conversion with "libreoffice" to keep PHP tight.
 *	yum install libreoffice
 *	input and output filters: https://help.libreoffice.org/25.8/en-US/text/shared/guide/convertfilters.html?&DbPAR=SHARED&System=WIN
 *	filter options: https://help.libreoffice.org/25.8/en-US/text/shared/guide/csv_params.html?&DbPAR=SHARED&System=WIN
 *	beware TRUE and FALSE: https://ask.libreoffice.org/t/entering-true-or-false-as-text-into-any-cell-is-interpreted-as-true-or-false/104905
 *	working example: libreoffice --convert-to "csv:Text - txt - csv (StarCalc):9,,UTF-8,1" --infilter="MS Excel 97" --outdir ./ input.xls
 *
 * File browser - buidl myself
 * File and text editor?
 *	Ace Editor: https://ace.c9.io/
 *	Code Mirror: https://codemirror.net/
 *
 * SMTP - PHPMailer or smaller alternative
 * https://www.mydreams.cz/en/hosting-wiki/1402-phpmailer-sending-emails-from-php-via-smtp-without-using-composer.html
 * OR just use mail() and optionally composer to PHPMailer and check for existence.
 *
 * More stuff...
 *
 */



/*
 * SECTION: CONSTANTS
 *
 */
// General
$abcms_constant			= 'constant';
const ABCMS_GOOD		= "<span style='color: green;'>\u{2611}</span> ";
const ABCMS_BAD			= "<span style='color: red;'>\u{2612}</span> ";
// Extensions
const ABCMS_EXT_SELF	= "/nainoiainc/abcms";
const ABCMS_EXT_SETS	= "/abcmsset";
// Regex
// Includefile?function #^(|/vendor/package/filepath)(|?(|classobject(::|->|()->))funcmeth)#
const ABCMS_REGEX_FUNC	= "/^((\/[^?]+)\?)?((([a-zA-Z_\x{7f}-\x{ff}][a-zA-Z0-9_\x{7f}-\x{ff}]*)(::|\->|\(\)\->))?([a-zA-Z_\x{7f}-\x{ff}][a-zA-Z0-9_\x{7f}-\x{ff}]*))?$/u";
const ABCMS_REGEX_HOOK	= "/^\/[^\/]+\/[^\/]+\/[^\/]+$/u"; // Path-like, but not a filepath
const ABCMS_REGEX_METH	= "/(CLI|GET|POST|PUT|HEAD|DELETE|PATCH|OPTIONS|CONNECT|TRACE)/u";
const ABCMS_REGEX_META	= array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE');
const ABCMS_REGEX_PATH	= "/^(\/[^\/]*)(\/.+)?$/u";
const ABCMS_REGEX_URLV	= "/\/([A-Za-z0-9\-_.~]+)=([A-Za-z0-9\-_.~]+)/u";
const ABCMS_REGEX_VARS	= "/^[A-Za-z0-9\-_.~]+$/u";
// Arrays
const ABCMS_ARRAY_TYPE	= array('mixed','string','array','integer','float','bool','boolean','email','domain','url','ip','mac');
// Files
const ABCMS_ABCMSLOG	= "../private/nainoiainc/abcms/ABCMS.abcmslog";
const ABCMS_COREDUMP	= "../private/nainoiainc/abcms/ABCMS.coredump";
const ABCMS_SESSIONS	= "../private/nainoiainc/abcms/ABCMS.sessions";
const ABCMS_SETTINGS	= "../private/nainoiainc/abcms/ABCMS.settings";
// Flags
const ABCMS_FLAG_JSON	= JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
// Roles
const ABCMS_ROLE_PUBLIC	= 0;
const ABCMS_ROLE_AUTHEN	= 1;
const ABCMS_ROLE_READER	= 2;
const ABCMS_ROLE_WRITER	= 3;
const ABCMS_ROLE_EDITOR	= 4;
const ABCMS_ROLE_MANAGE	= 5;
const ABCMS_ROLE_ADMINS	= 6;
const ABCMS_ROLE_CLI	= 7;
const ABCMS_ROLE_SET	= array(0,1,2,3,4,5,6,7);
// Other
const ABCMS_EXTORD_MIN	= -9999;
const ABCMS_EXTORD_MAX	=  9999;



/*
 * SECTION: GLOBAL NAMESPACE
 *
 * Constants above as 'ABCMS_*'
 * $abcms_constant = 'constant' = constant() to interpolate constants in strings
 * $abcms = ABCMS object reference for convenience
 * abcms() = Function to return ABCMS object
 *
 */
 


/*
 * SECTION: TRY/CATCH
 *
 * Run the CMS or coredump if failure
 *
 */
try {
	abcms(); // Construct and assign to global $abcms
	if (empty($abcms->inputs['boss'])) { return TRUE; } // I do nada, you do as you please
	// I am boss and do as I please
	$args = array(FALSE,NULL,NULL,NULL,NULL,NULL);
	$args[4] = <<<EOF
<h4>Status</h4>
{$GLOBALS['abcms_constant']('ABCMS_GOOD')}Hello World. Graceful termination.<br>
{$GLOBALS['abcms_constant']('ABCMS_BAD')}Fatal error. Core extension failed.<br>
<br>
Please contact the webmaster for help.
EOF;
	$abcms->output(
		'/begin', // Entry hook and everything is a hook
		'CLI-GET-POST', // Default methods
		'abcms->htmldoc', // Default function
		ABCMS_ROLE_PUBLIC, // Minimum role required
		1, // 1 = Exclusive extensions allowed
		FALSE, // Default required
		...$args, // Default args
	);
	// Force coredump
	if (!empty($abcms->inputs['urlquery']['debug'])) { $abcms->error_wsod("Debug requested WSOD exception."); }
}
catch (Exception $e) { // WSOD coredump
	// Screen
	echo ob_get_clean();
	echo <<< EOF
<div style='position: absolute; bottom: 0px; left: 0px; width: 100%; background-color: #FFFFFF; color: red; text-align: center; padding: 3px 0;'>
Fatal exception caught. Coredump available. Contact the webmaster for help.
</div>
EOF;
	// Get info
	$error		= ($e->getMessage() ?: 'NA');								// Exception error
	$sys		= (($sys = error_get_last()) ? print_r($sys,TRUE) : 'NA');	// System error
	$syserr		= (isset($sys['message']) ? $sys['message'] : 'NA');		// System error message
	$globals	= print_r((isset($GLOBALS) ? $GLOBALS : 'NA'), TRUE);		// Get $GLOBALS
	$inputs		= $settings = $errors = $debugs = $packages = 'NA';			// Extra initialized as 'NA'
	if (isset($abcms) && is_object($abcms)) {								// If contructed, get extra
		$inputs		= print_r($abcms->inputs,TRUE);							// Processed input
		$settings	= print_r($abcms->get_settings(),TRUE);					// Settings file
		$errors		= print_r($abcms->get_errors(),TRUE);					// Runtime errors
		$debugs		= print_r($abcms->get_debugs(),TRUE);					// Runtime debugs
		if (abcms()->inputs['auto']) {										// Composer packages
			$packages = NULL;
			foreach (Composer\InstalledVersions::getInstalledPackagesByType('abcms-extension') as $name) {
				$packages .= "{$name} : " . Composer\InstalledVersions::getInstallPath($name) . "\n";
			}
		}
	}
	// Error log
	ini_set('error_log', ABCMS_ABCMSLOG); // If constructor failed
	error_log("ABCMS->WSOD() {$error}.");
	error_log("ABCMS->WSOD() {$syserr}.\n{$sys}.");
	// Coredump
	$coredump = <<< EOF
ABCMS COREDUMP BEGIN\n
ABCMS COREDUMP ERROR MESSAGE:\n
$error\n
ABCMS COREDUMP SYSTEM ERROR:\n
$syserr
$sys\n
ABCMS COREDUMP GLOBALS:\n
$globals\n
ABCMS COREDUMP INPUTS:\n
$inputs\n
ABCMS COREDUMP SETTINGS:\n
$settings\n
ABCMS COREDUMP ERRORS:\n
$errors\n
ABCMS COREDUMP DEBUGS:\n
$debugs\n
ABCMS COREDUMP PACKAGES:\n
$packages\n
ABCMS COREDUMP END\n
EOF;
	file_put_contents(ABCMS_COREDUMP, $coredump); // Save coredump
}
finally { // Clean up
	; // Remove all locks
}
return TRUE;
// End, function definitions follow



/*
 * SECTION: CORE
 *
 */
// ABCMS object available through abcms() function
function abcms() : object {						// Returns $_abcms object
static $_abcms = NULL; if (NULL === $_abcms) {	// Create once
if (isset($GLOBALS['abcms'])) {										$this->error_wsod("Fatal, \$abcms already set."); }											// $abcms already set
$GLOBALS['abcms'] = $_abcms = new class {		// Instanciate assign global object $abcms
readonly	array $GLOBALS;						// Readonly raw inputs
readonly	array $inputs;						// Readonly processed inputs
private		array $settings	= array();			// Application settings
private		array $errors	= array();			// Runtime errors
private		array $debugs	= array();			// Runtime debugs
// Construct process inputs
function __construct() {
	// WSOD errors
	if (PHP_VERSION < '8.2.0') {									$this->error_wsod("Fatal, >=PHP82 required."); }											// PHP version
	if (!chdir(__DIR__)) {											$this->error_wsod("Fatal, failed chdir('".__DIR__."').");	}								// chdir()
	if (!ini_set('error_log', ABCMS_ABCMSLOG)) {					$this->error_wsod("Fatal, failed ini_set('error_log','".ABCMS_ABCMSLOG."').");}				// ini_set()
	if (FALSE===$this->set_settings(TRUE)){							$this->error_wsod("Fatal, failed set_settings() load."); }									// Bootstrap
	// Sanitize inputs
	$this->GLOBALS	= isset($GLOBALS) ? $GLOBALS	: array();																									// Protect GLOBALS
	$this->inputs	= array(																																	// Sanitize inputs
		'boss'			=> ('index.php' === basename(__FILE__) ? TRUE : FALSE),																					// Who's boss?
		'filename'		=> (__FILE__),																															// My filename
		'documentroot'	=> (__DIR__),																															// My documentroot folder
		'projectroot'	=> (dirname(__DIR__)),																													// My project folder
		'project'		=> (basename(dirname(__DIR__))),																										// My project name
		'role'			=> ($role = ('cli' === PHP_SAPI ? ABCMS_ROLE_CLI : ABCMS_ROLE_ADMINS)),																	// My user role
		'urlfull'		=> ($full = ('cli' === PHP_SAPI ? ('https://localhost' . 
							($_SERVER['argc']>1 && '/' === $_SERVER['argv'][1][0] && FALSE !== filter_var('http://localhost' . $_SERVER['argv'][1], FILTER_VALIDATE_URL) ? $_SERVER['argv'][1] : '/abcms/help')) :
							((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') .
							(isset($_SERVER['HTTP_HOST']) && FALSE !== filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_DOMAIN) ? $_SERVER['HTTP_HOST'] : 'unknown') .
							(isset($_SERVER['REQUEST_URI']) && FALSE !== filter_var('http://localhost' . $_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL) ? $_SERVER['REQUEST_URI'] : '/unknown')))),	// URL full
		'urlparsed'		=> ($pars = parse_url($full)),																											// URL parsed
		'urldomain'		=> ($pars['host']),																														// URL domain
		'urlvars'		=> (!preg_match_all(ABCMS_REGEX_URLV, $pars['path'], $matches, PREG_PATTERN_ORDER) ? array() :
							$this->input_valid(array_combine(array_map('urldecode', $matches[1]), array_map('urldecode', $matches[2])), $role, 'v')),			// URL path variables
		'urlstripped'	=> ($urlstripped = '/'.(trim(preg_replace(ABCMS_REGEX_URLV, '/', $pars['path']), '/'))), 												// URL stripped of variables, no trailing slash
		'urlpathall'	=> (urldecode($urlstripped)),																											// URL urldecoded
		'urlpathone'	=> (urldecode((!($ret = preg_match(ABCMS_REGEX_PATH, $urlstripped, $matches)) ? '/' : $matches[1]))),									// URL first segment
		'urlpathext'	=> (urldecode((!$ret || empty($matches[2]) ? '/' : $matches[2]))),																		// URL remainder segments
		'urlquery'		=> ($this->input_valid((($tmp = array()) || (!empty($pars['query']) && parse_str($pars['query'],$tmp)) ?: $tmp), $role, 'q')),			// URL parse_str() because CLI has no $_GET
		'urlmethod'		=> ('cli' === PHP_SAPI ? 'CLI' : ((empty($_SERVER['REQUEST_METHOD']) || !in_array($_SERVER['REQUEST_METHOD'], ABCMS_REGEX_META)) ? 'GET' : $_SERVER['REQUEST_METHOD'])), // URL request method
		'cli'			=> ('cli' === PHP_SAPI ? TRUE : FALSE),																									// CLI command line execution
		'argc'			=> $_SERVER['argc'],																													// CLI argument count
		'argv'			=> $_SERVER['argv'],																													// CLI arguments
		'auto'			=> (file_exists(($auto = (__DIR__ . '/../vendor/autoload.php'))) ? $auto : NULL),														// Composer auto-loader
	);
	// Composer slower than my runtime loader
	if ($this->inputs['auto']) { require_once($this->inputs['auto']); }
	// More errors
	if (0 !== stripos($pars['path'], $this->inputs['urlstripped'])) {	$this->set_errors("User error, invalid URL, variables before path"); }					// SEO requires consistent path
	return;
}
// Dynamic properties and cloning disallowed
public function __set(string $name, mixed $value) : void { $this->error_wsod("Fatal, dynamic properties disallowed."); }
public function __clone() { $this->error_wsod("Fatal, cloning ABCMS disallowed."); }
// Define path variable
public function input_varpath(
	string $var,			// Allowed path variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role permissions
	?array	$reg = NULL,	// Regex patterns to match
) : void {
	$this->input_variable($var, $type, $role, 'v', $reg);
	return;
}
// Define _GET variable
public function input_varget(
	string	$var,			// Allowed query variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role permissions
	?array	$reg = NULL,	// Regex patterns to match
) : void {
	$this->input_variable($var, $type, $role, 'q', $reg);	
	return;
}
// Define _POST variable
public function input_varput(
	string	$var,			// Allowed query variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role permissions
	?array	$reg = NULL,	// Regex patterns to match
) : void {
	$this->input_variable($var, $type, $role, 'p', $reg);	
	return;
}
// Register variable
private function input_variable(
	string	$var,			// Name
	string	$type,			// Type
	int		$role,			// Minimum role permissions
	string	$cat,			// Category
	?array	$reg = NULL,	// Regex patterns to match
) : void {
	if (!preg_match(ABCMS_REGEX_VARS, $var) ||
		!empty($this->settings[$cat][$var]) ||
		!in_array($type, ABCMS_ARRAY_TYPE) ||
		!in_array($role, ABCMS_ROLE_SET)) {
		$this->error_log("Programmer, invalid or duplicate variable.");
		return;
	}
	$this->settings[$cat][$var] = array('type'=>$type, 'role'=>$role, 'reg'=>$reg);
	return;
}
// Validate path/query variable
private function input_valid(
	array	$vars,	// Path/query variable
	int		$role,	// User role
	string	$cat,	// Category
) : array {
	$last = NULL;
	foreach($vars as $var => $val) {
		if ($var < $last) {									$this->set_errors("User error, URL variables are not alphbetical, '{$var}' < '{$last}'"); }
		$last = $var;
		if (empty($this->settings[$cat][$var]['type'])) {	$this->set_errors("User error, ignoring undefined URL variable, '{$var}'");	unset($vars[$var]);	continue; }
		if ($role < $this->settings[$cat][$var]['role']) {	$this->set_errors("Insufficient permission to use variable, '{$var}'");		unset($vars[$var]);	continue; }
		if ('null' == mb_strtolower($val)) {																							$vars[$var] = NULL;	continue; }
		switch($this->settings[$cat][$var]['type']) {
			case 'array'	:	$vars[$var] = explode(',', $val);																							continue 2;
			case 'bool'		:
			case 'boolean'	:	if (NULL  === filter_var($val, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)) {							break; }			continue 2;
			case 'domain'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_DOMAIN)) {												break; }			continue 2;
			case 'email'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_EMAIL)) {												break; }			continue 2;
			case 'float'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_FLOAT)) {												break; }			continue 2;
			case 'integer'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_INT)) {													break; }			continue 2;
			case 'ip'		:	if (FALSE === filter_var($val, FILTER_VALIDATE_IP)) {													break; }			continue 2;
			case 'mac'		:	if (FALSE === filter_var($val, FILTER_VALIDATE_MAC)) {													break; }			continue 2;
			case 'mixed'	:
			case 'string'	:																																continue 2;
			case 'path'		:	if ('/' !== $val[0] || FALSE === filter_var('http://localhost'.$val, FILTER_VALIDATE_URL)) {			break; }			continue 2;
			case 'url'		:	if (FALSE === filter_var($val, FILTER_VALIDATE_URL)) {													break; }			continue 2;
			default:			$this->error_wsod("Fatal, impossible URL path or query variable type.");
		}
		$this->set_errors("Ignoring invalid URL variable = '{$var}'");
		unset($vars[$var]);
	}
	return $vars;
}
// Which extension called the function that called me?
private function extension() : string {
	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2); // Omit object and args, 2 levels
	if (empty($trace[1]['file'])) { $this->error_wsod("Fatal, backtrace not found."); } // Trace not found
	else if ($trace[1]['file'] === (__FILE__)) { return ABCMS_EXT_SELF; } // I called myself
	else if (preg_match("|^".dirname(__DIR__)."/private(/[^/]+/[^/]+)|u", $trace[1]['file'], $match) && !empty($match[1])) { return $match[1]; } // Valid extension, beware chdir() breaks this!
	$this->error_wsod("Fatal, extension not at ../private/vendor/extension."); // Invalid extension
}
// Hooked function output path router extension manager
public function output(
	string	$hook,		// /vendor/extension/$hook name, only create hooks for your own extension
	string	$meth,		// HTTP methods, '' = ALL = "CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE"
	string	$default,	// Default function, '' = no default
	int		$role,		// Minimum role permissions
	int		$flag,		// <0 = extender exclusive, 0 = anyone, 1 = extender exclusive allowed
	bool	$must,		// Must do default, TRUE = required -OR- FALSE = optional
	mixed	&...$args,	// Default arguments
) : array {
	// Initialize
	$whoami = $this->extension(); // Which extension am I?
	$hook = $whoami . $hook; // Full hook name
	$ext = array( // Default
		'I' => (empty($default) ? array() : array( array( // Empty default allowed
				'met'	=> $meth, // HTTP methods
				'fun'	=> $default, // Function
				'rol'	=> $role, // Role
				'ord'	=> 0, // Order
				'ctl'	=> NULL, // Control
				'who'	=> $whoami, // Default for each caller
				'arg'	=> NULL, // None
		))),
		'O' => array(), // No default output filter
	);
	// Prioritize
	if (isset($this->settings['route'][$hook])) { // Build hook extensions
		$hooky = $this->settings['route'][$hook]; // Shortened reference
		$ext = array_merge_recursive( // Merge extensions with matches
			$ext, // Default
			(!empty($hooky['eq'][$this->inputs['urlpathall']]) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->inputs['urlpathall']]]) ?
			 $hooky['ex'][$hooky['eq'][$this->inputs['urlpathall']]] : // Full path
			('/' !== $this->inputs['urlpathone'] &&
			 !empty($hooky['eq'][$this->inputs['urlpathone'].'/']) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->inputs['urlpathone'].'/']]) ?
			 $hooky['ex'][$hooky['eq'][$this->inputs['urlpathone'].'/']] : // OR path segment.'/'
			 array())), // OR nothing
			(!empty($hooky['eq']['']) && !empty($hooky['ex'][$hooky['eq']['']])	? $hooky['ex'][$hooky['eq']['']] : array()), // AND empty path
			(!empty($hooky['ex'][''])											? $hooky['ex'][''] : array())); // AND empty name
		if (isset($ext['I'])) {	usort($ext['I'], function($a, $b) { return (($ret=(isset($a['ctl']['U'])===isset($b['ctl']['U']) ? 0 : (isset($a['ctl']['U']) ? -1 : 1))) ? $ret : $a['ord'] <=> $b['ord']); } ); }
		if (isset($ext['O'])) {	usort($ext['O'], function($a, $b) { return (($ret=(isset($a['ctl']['U'])===isset($b['ctl']['U']) ? 0 : (isset($a['ctl']['U']) ? -1 : 1))) ? $ret : $a['ord'] <=> $b['ord']); } ); }
	}
	$this->error_log("DEBUG\n".print_r($ext,TRUE), TRUE); // Debug info
	// Execute
	$extensions = 0; // Extension count
	$exin = $exout = NULL; // Exclusive winner or non-exclusive
	$dopt = TRUE; // default optional
	foreach($ext['I'] as $extin) { // Input extensions by priority
		if (!$this->output_doit($extin, $whoami, $flag, ($must || $dopt), $exin)) { continue; } // Skip for reasons
		if (!$must && $extin['ord'] < 0 && !isset($extin['ctl']['D'])) { $dopt = FALSE; } // Omit default if hook and one extension says not required
		if (isset($extin['arg'])) { $this->array_walk_merge($args, $extin['arg']); } // Extend arguments
		if (empty($extin['fun'])) { continue; } // Extension only grabs exclusivity or set args
		do { // Repeat hook extension until FALSE -OR- NULL
			if (FALSE === ob_start()) { $this->error_wsod("Fatal, ob_start()."); } // Buffer output
			$more = $this->output_call($whoami, $extin['fun'], ...$args); // Execute hook extension
			if (FALSE === ($out = ob_get_clean())) { $this->error_wsod("Fatal, ob_get_clean()."); } // Retrieve buffer
			// Output filter extensions by priority
			foreach($ext['O'] as $extout) {
				if (!$this->output_doit($extout, $whoami, $flag, TRUE, $exout)) { continue; } // Skip for reasons
				$this->output_call($whoami, $extout['fun'], $out, ...$args); // Execute output filter
			}
			// ABCMS security output filter and injection, <FORM> security, and XSS checks, etc.
			echo $out; // Echo filtered output
		} while ($more); // Repeat hook extension until FALSE
		++$extensions; // Extension count
		if (isset($extin['ctl']['U'])) { break; } // Uno extension allowed
	}
	//return $extensions;
	return $args;
}
// Output settings
public function settings_assign(
	array	&$set,		// Default settings
) : array {
	return(($this->settings[ABCMS_EXT_SETS][$this->extension()] = $this->output(ABCMS_EXT_SETS, '', '', ABCMS_ROLE_ADMINS, 0, FALSE, ...$set)));
}
// Output settings extend
public function settings_extend(
	string	$hok,		// /vendor/extension
	array	$set,		// Default settings
	int		$ord = 0,	// Default order
) : bool {
	return $this->output_extend($hok.ABCMS_EXT_SETS, '', '', '', '', ABCMS_ROLE_ADMINS, $ord, ...$set);
}
// Output settings
private function settings_clean(
) : void {
	foreach($this->settings['route'] as $hook => $route) {
		if (preg_match("#".ABCMS_EXT_SETS."$#", $hook)) { unset($this->settings['route'][$hook]); }
	}
	return;
}
// Output settings
public function settings_get(
	string	$hok = NULL,// /vendor/extension
) : ?array {
	$hok = ($hok ?: $this->extension());
	return (isset($this->settings[ABCMS_EXT_SETS][$hok]) ? $this->settings[ABCMS_EXT_SETS][$hok] : NULL);
}
// Execute hook extension?
private function output_doit(
	array	$ext,	// Extension definition
	string	$whoami,// Is this extender allowed
	int		$flag,	// <0 = extender exclusive, 0 = anyone, 1 = extender exclusive allowed
	bool	$must,	// Must do default
	?string	&$excl,	// Exclusive extension winner
) : bool {
	// Exit before exclusive selection
	if (!$must && !$ext['ord']) {																	return FALSE; }	// No default extension
	if (!empty($ext['met']) && FALSE === stripos($ext['met'], $this->inputs['urlmethod'])) { 		return FALSE; }	// HTTP method
	if ($flag < 0 && $whoami !== $ext['who']) { $this->error_log("Programmer, extender not self.");	return FALSE; }	// Extender match
	if (!$flag && isset($ext['ctl']['E'])) {														return FALSE; }	// Non-exclusive, cancel request
	// Exclusive winner or non-exclusive
	if ($flag > 0) {
		if (NULL === $excl) { $excl = (isset($ext['ctl']['E']) ? $ext['who'] : FALSE); }							// Determine exclusive winner or non-exclusive
		if (!$excl && isset($ext['ctl']['E'])) {													return FALSE; }	// Non-exclusive, cancel request
		if ($excl && $ext['who'] !== $excl) {														return FALSE; }	// Exclusive, but not winner
	}
	if ($this->inputs['role'] < $ext['rol']) { $this->set_errors("No permission to resource.");		return FALSE; }	// No permision
	// Do it
	return TRUE;
}
// Call extension function
private function output_call(
	string	$whoami,	// Am I ABCMS?
	string	$filefunc,	// Includefile?function
	mixed	&...$args,	// Arguments passed
) : ?bool {
	// Parse includefile?function
	if (!preg_match(ABCMS_REGEX_FUNC, $filefunc, $match)) { $this->error_wsod("Fatal, invalid function name."); }
	$filepath	= $match[2]; // Dynamic extension file inclusion
	$classobject= $match[5]; // Class or object
	$operator	= $match[6]; // Operator to function
	$funcmeth	= $match[7]; // Function/method
	// Include file
	$result = FALSE; // Default failure
	if ($filepath) {
		if ($funcmeth) {	$result = (bool)$this->include_once($filepath, ...$args); } // Include for function definition
		else {				$result = (bool)$this->include($filepath, ...$args); } // No function so multiple executions allowed
	}
	// Call function
	if ($funcmeth) { // Function attempt
		if ($classobject) { // Class or object method
			if ("::" === $operator) { // Class operator
				if (!class_exists($classobject) || !method_exists($classobject, $funcmeth)) { $this->error_wsod("Fatal, invalid class method."); }
				$result = (bool)$classobject::$funcmeth(...$args); // Execute
			}
			else { // Non-class methods
				if ("->" === $operator) { // Instance or object operator
					if (!isset($GLOBALS[$classobject]) || !is_object($GLOBALS[$classobject])) { $this->error_wsod("Fatal, invalid object."); }
					$newobject = $GLOBALS[$classobject];
				}
				else if ("()->" === $operator) { // Function returned object operator
					if (!function_exists($classobject)) { $this->error_wsod("Fatal, invalid function for object."); }
					if (!is_object(($newobject = $classobject()))) { $this->error_wsod("Fatal, invalid function object."); }
				}
				else { $this->error_wsod("Fatal, invalid operator."); }
				// Execute function/method
				if (!method_exists($newobject, $funcmeth)) { $this->error_wsod("Fatal, invalid object method."); }
				global $abcms; // Global pointer to 'abcms' object 
				if (ABCMS_EXT_SELF != $whoami && $newobject === $abcms) { // Disallow abcms() privates unless extension is ABCMS
					$reflection = new ReflectionClass($this);
					if ($reflection->getMethod($funcmeth)->isPrivate()) { $this->error_wsod("Fatal, private ABCMS->method access disallowed."); }
				}
				$result = (bool)$newobject->$funcmeth(...$args); // Execute
			}
		}
		else {
			if (!function_exists($funcmeth)) { $this->error_wsod("Fatal, invalid function."); }
			$result = (bool)$funcmeth(...$args); // Execute
		}
	}
	return $result;
}
// Register hook extension
public function output_extend(
	string	$hok,						// /vendor/package/hook
	string	$ext,						// Extension name or '' for all
	string	$met,						// HTTP methods, '' = all = "CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE"
	string	$str,						// Control string
										// 'I' = Input -OR- 'O' = Output filter, default Input
										// 'E' = Exclusive to my extension or omit me, default anyone
										// 'U' = Uno/single extension, default multiple extensions cooperate 
										// 'D' = Default included, default excluded if extended by $ord < 0
	string	$fun,						// Includefile?function
	int		$rol = ABCMS_ROLE_PUBLIC,	// Minimum role permission
	int		$ord = 0,					// Order considered, ABCMS_EXTORD_MIN >= $ord <= ABCMS_EXTORD_MAX
	mixed	...$arg,					// Argument alternatives
) : bool {
	// Control string to array indices
	$ctl = array_flip(($key=str_split(strtoupper($str))));
	$key = array_diff_key($key, array('I','O','E','U','D'));
	// Error checks
	if (!preg_match(ABCMS_REGEX_HOOK, $hok) || // Hook valid
		(!empty($met) && !preg_match(ABCMS_REGEX_METH, $met)) || // Method valid
		(isset($ctl['I']) && isset($ctl['O'])) || // Input Output exclusive
		!empty($key) || // Control flags valid
		(!empty($fun) && !preg_match(ABCMS_REGEX_FUNC, $fun))) { // Function valid
		$this->error_log("Programmer, invalid extension.");
		return FALSE;
	}
	// Extension assigned
	unset($ctl['I']);
	$this->settings['route'][$hok]['ex'][$ext][(isset($ctl['O']) ? 'O' : 'I')][] = array(
		'met'	=> $met,
		'fun'	=> $fun,
		'rol'	=> $rol,
		'ord'	=> min(ABCMS_EXTORD_MAX, max(ABCMS_EXTORD_MIN, $ord)),
		'ctl'	=> $ctl,
		'who'	=> $this->extension(),
		'arg'	=> $arg,
	);
	return TRUE;
}
// Equate path to hook extension name
public function output_equate(
	string	$hook,	// Hook name
	string	$ext,	// Extension name or '' for all
	string	$path,	// Unique URL path, trailing '/' for 1st segment only, otherwise no trailing slash
) : bool {
	// Error checks
	if (!preg_match(ABCMS_REGEX_HOOK, $hook) || // Valid hook
		(substr_count($path, '/')>2 && '/' == $path[-1]) || // Trailing slash matches 1st path segment only
		('' !== $path && ('/' !== $path[0] || FALSE === filter_var('http://localhost'.$path, FILTER_VALIDATE_URL))) || // Valid path
		isset($this->settings['route'][$hook]['eq'][$path])) { // Not duplicate
		$this->error_log("Programmer, invalid or duplicate hook extension path.");
		return FALSE;
	}
	// Equate path assigned
	$this->settings['route'][$hook]['eq'][$path] = $ext;
	return TRUE;
}



/*
 * SECTION: DEBUG/ERRORS/LOGS
 *
 */
private function error_trace() : array { // Correct backtrace
	$back = debug_backtrace(0,3); // Omit object, include args, 3 levels back
	if (!isset($back[2]['function'])) {	$back[2]['function'] = 'unavailable'; }
	if (!isset($back[2]['args'])) {		$back[2]['args'] = array(); }
	array_walk_recursive($back[2]['args'], function (&$value) {
		if (is_string($value) && mb_strlen($value) > 256) { // Truncate long strings
			$value = mb_substr($value, 0, 256) . '...';
		}
	});
	return $back;
}
public function error_wsod( // Throw WSOD
	string $mess,			// Message
) : void {
	$back = $this->error_trace();
	throw new Exception("ABCMS->WSOD->{$back[1]['function']}() {$mess}\n".print_r($back[2]['args'],TRUE));
	return;
}
public function error_log(	// Log error
	string	$mess,			// Message
	bool	$debug = FALSE,	// Only if debug
) : void {
	if ($debug && empty($this->inputs['urlquery']['debug'])) { return; } // Skip debug message if not debug mode
	$back = $this->error_trace();
	error_log(($form = "ABCMS->{$back[1]['function']}() {$mess}\n".print_r($back[2]['args'],TRUE)));
	if (!empty($this->inputs['urlquery']['debug'])) { $this->debugs[] = $form; }
	return;
}
public function set_errors(					// Set errors
	string ...$errors,						// Error strings
) : void {
	array_push($this->errors, ...$errors);
	return;
}
public function get_debugs() : array {		// Get private debugs for public
	return $this->debugs;
}
public function get_errors() : array {		// Get private errors for public
	return $this->errors;
}
public function see_errors() : ?string {	// Format private errors for public
	if (!empty($this->errors)) { return '<br><br>Errors:<br>'.implode('<br>',$this->errors); }
	return NULL;
}	
	
public function get_settings() : array {	// Get private settings for public
	return $this->settings;
}



/*
 * SECTION: AUTHENTICATION
 *
 */



/*
 * SECTION: FORMS
 *
 */



/*
 * SECTION: SETUP
 *
 */



/*
 * SECTION: CRON
 *
 */



/*
 * SECTION: ADMIN
 *
 */
// This function to be moved to /admin/setup and loop through all extensions 
private function set_settings(
	bool	$boot = FALSE,	// Bootstrap load existing
) : bool {
	// Overwrite?
	if ($boot && file_exists(ABCMS_SETTINGS)) {
		if (FALSE===$this->get_json(ABCMS_SETTINGS, $this->settings)) {	$this->error_wsod("Fatal, settings file not found."); }
		return TRUE;
	}
	// Start with zero
	$this->settings = array();
	// Register path variables
	$this->input_varpath('debug',	'bool',		ABCMS_ROLE_PUBLIC);
	// Register query / _GET variables
	$this->input_varget('debug',	'bool',		ABCMS_ROLE_ADMINS);
	// Register _POST variables
	// Extension controls
	// 'I' = Input -OR- 'O' = Output filter, default Input
	// 'E' = Exclusive to my extension or omit me, default anyone
	// 'U' = Uno/single extension, default multiple extensions cooperate 
	// 'D' = Default included, default excluded if extended by $ord < 0
	// Bootstrap extensions
	$this->output_extend('/nainoiainc/abcms/begin',				'',			'CLI-GET-POST',	'IEU',	'abcms->htmldefault',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_extend('/nainoiainc/abcms/begin',				'admin',	'CLI-GET-POST',	'IEU',	'abcms->htmladmin',		ABCMS_ROLE_ADMINS,	-20);
	$this->output_equate('/nainoiainc/abcms/begin',				'admin',	'/admin/');
	$this->output_extend('/nainoiainc/abcms/begin',				'code',		'CLI-GET-POST',	'IEU',	'abcms->admincode',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate('/nainoiainc/abcms/begin',				'code',		'/admin/code');
	// Frontend page extensions
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'home',		'CLI-GET-POST',	'IE',	'abcms->pagehome',		ABCMS_ROLE_PUBLIC,	-10);
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'home',		'CLI-GET-POST',	'OE',	'abcms->pagekickin',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'home',		'/');
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'home',		'/abcms');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'contact',	'CLI-GET-POST',	'IE',	'abcms->pagecontact',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'contact',	'/abcms/contact');
	// Admin page extensions
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'status',	'CLI-GET-POST',	'IE',	'abcms->adminstatus',	ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'status',	'/admin');
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'status',	'/admin/status');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'phpinfo',	'CLI-GET-POST',	'IE',	'abcms->adminphpinfo',	ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'phpinfo',	'/admin/phpinfo');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'help',		'CLI-GET-POST',	'IE',	'abcms->adminhelp',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'help',		'/admin/help');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'init',		'CLI-GET-POST',	'IE',	'abcms->admininit',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'init',		'/admin/init');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'cron',		'CLI-GET-POST',	'IE',	'abcms->admincron',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'cron',		'/admin/cron');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'browse',	'CLI-GET-POST',	'IE',	'abcms->adminbrowse',	ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'browse',	'/admin/browse');
	// Variable Extensions
	$variable['variable'] = "Yoo hooey!<br>";
	$this->output_extend('/nainoiainc/abcms/variable',			'',			'CLI-GET-POST',	'IE',	'abcms->pagevariable',	ABCMS_ROLE_PUBLIC,	-10, ...$variable);	
	$variable2['variable'] = array(
		'a' => 1,
		'b' => 2,
		'c' => 3,
		'd' => 4,
		'e' => 5,
		'f' => array(1,2,3,4,5),
	);
	$this->output_extend('/nainoiainc/abcms/variable2',			'',			'CLI-GET-POST',	'IE',	'abcms->pagevariable2',	ABCMS_ROLE_PUBLIC,	-10, ...$variable2);
	// Test extensions
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'',			'CLI-GET-POST',	'IED',	'abcms->pagetest',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MAX);

	// Extension settings completed
	// Settings strategy
	// INIT.php run by composer or at will if ABCMS or plugin changes to rebuild the settings extension array
	$this->settings_extend('/nainoiainc/abcms', array('test'=>'Changed','test2'=>'asdasdsa','test3'=>array('a'=>3,'b'=>2,'c'=>1),'test4'=>'new boy!'));
	
	// LOOP THROUGH ALL INIT NOW
	// How extend settings and then record the settings in the input hash?
	// Perhaps let extensions add values to the settings array so extension adds its own settings via extension? Yes!
	// Then after execture all INIT then call settings_assign() for all extensions and see what results!

	// Only write if bootstrap
	if ($boot) {
		if (FALSE===$this->set_json(ABCMS_SETTINGS, $this->settings)) {	$this->error_wsod("Fatal, settings file not written."); }
	}
	return TRUE;
}
// Test extension
private function pagetest(mixed &...$unused) : ?bool {
	echo "<br><br><br>TESTY TEST TEST";
	return NULL;
}
private function pagevariable(string &$variable) : ?bool {
	$variable .= "Yoo hoo baby!<br>";
	return NULL;
}
private function pagevariable2(array &$variable) : ?bool {
	$variable = array(
		'a' => 2,
		'b' => 3,
		'c' => 4,
		'd' => 5,
		'e' => 6,
		'f' => array(2,3,4,5,6),
	);
	return NULL;
}
private function pagekickin(&...$vars) : ?bool {
	$extra  = isset($vars[0]) ? "0 " : "x";
	$extra .= isset($vars[1]) ? "1 " : "x";
	$extra .= isset($vars[2]) ? "2 " : "x";
	$extra .= isset($vars[3]) ? "3 " : "x";
	$extra .= isset($vars[4]) ? "4 " : "x";
	$vars[0] = preg_replace("#I am alive.#", "I am alive and kickin {$extra}!", $vars[0]);
	return NULL;
}
// General user webpage template
public function htmldefault(
	mixed &...$unused,
) : ?bool {
	$admin		= FALSE;
	$css		= NULL;
	$js			= NULL;
	$head		= "<div style='display: inline-block;'><a href='/' title='A Basic Content Management System'><h2 style='font-size: 1.5em'>ABCMS</h2></a></div><div style='float: right; display: inline-block;'><h2 style='font-size: 1.5em'><a href='/admin' title='Admin Console'>\u{2699}</a></h2></div>";
	$page		= NULL;
	$foot		= NULL;
	$flag		= 1;
	return $this->htmldoc($admin, $css, $js, $head, $page, $foot, $flag);
}
// Admin webpage template
private function htmladmin(
	mixed &...$unused,
) : ?bool {
	$admin		= TRUE;
	$css		= NULL;
	$js			= NULL;
	$head		= "<div style='display: inline-block;'><a href='/admin' title='Admin Console'><h2 style='font-size: 1.5em'>Console</h2></a></div><div style='float: right; display: inline-block;'><h2 style='font-size: 1.5em'><a href='/' title='Close Console'>X</a></h2></div>";
	$page		= NULL;
	$foot		= NULL;
	$flag		= -1;
	return $this->htmldoc($admin, $css, $js, $head, $page, $foot, $flag);
}
// User homepage
private function pagehome(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	$errors = $this->see_errors();
	$variable['variable'] = "Yoo hoo!<br>";
	$returned = $this->output('/variable', 'CLI-GET-POST', '', ABCMS_ROLE_PUBLIC, -1, FALSE, ...$variable);
	$variable2['variable'] = array();
	$returned2 = $this->output('/variable2', 'CLI-GET-POST', '', ABCMS_ROLE_PUBLIC, -1, FALSE, ...$variable2);
	$returned3 = $this->settings_get();
?>
<h4>Homepage</h4>
"A Basic Content Management System" (ABCMS)<br>
AKA "<a href='https://www.AionianBible.org' target='_blank'>Aionian Bible</a> Content Management System"<br>
PHP web developer toolkit and CMS in a single file<br>
Install with Composer or copy to document root<br>
Everything is an extension with the abcms() router<br>
Run CLI "php index.php /abcms/help | html2text"<br>
<br>
<a href='/admin'>Admin Console</a><br>
<br>
<? echo $GLOBALS['abcms_constant']('ABCMS_GOOD'); ?> Hello World. I am alive.<br>
<? echo $GLOBALS['abcms_constant']('ABCMS_GOOD'); ?> Thank you!<br>
<br>
Variable1: <?echo $variable['variable'] . ' ' . $returned['variable'];?><br>
Variable2: <?print_r($variable2);?><br>
Settings: <?print_r($returned3);?><br>
<br>
<?echo $errors;?>
<?	
	return NULL;
}
// User contact
private function pagecontact(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	?><h4>Contact</h4>
This is where to contact us.<?
	echo $this->see_errors();
	echo "<br><a href='/'>Home</a><br>";
	return NULL;
}
private function admincode(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	highlight_file($this->inputs['filename']);
	return NULL;
}
private function adminstatus(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	static $count = 3;
	if ($count===3) { echo "<h4>Status</h4>"; }
	echo ABCMS_GOOD."Helping you! {$count}<br>\n";
	--$count;
	if ($count>0) { return TRUE; }
	echo $this->see_errors();
echo <<<EOF
<br>
<a href='/'>Home</a><br>
<a href='/abcms'>ABCMS</a><br>
<a href='/abcms/contact'>Contact</a><br>
<br>
<a href='/admin'>/admin</a><br>
<a href='/admin/status'>/admin/status</a><br>
<a href='/admin/help'>/admin/help</a><br>
<a href='/admin/code'>/admin/code</a><br>
<a href='/admin/phpinfo'>/admin/phpinfo</a><br>
<a href='/admin/init'>/admin/init</a><br>
<a href='/admin/cron'>/admin/cron</a><br>
<a href='/admin/browse'>/admin/browse</a><br>
<br>
<a href='/bogus'>/bogus</a><br>
<a href='/abcms/bogus'>/abcms/bogus</a><br>
<a href='/admin/bogus'>/admin/bogus</a><br>
<br>
{$GLOBALS['abcms_constant']('ABCMS_GOOD')} 
EOF;	
	return NULL;
}
private function adminhelp(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
echo <<<EOF
<h4>Help</h4>
Get your help here.
EOF;	
	return NULL;
}
private function admininit(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	$this->set_settings(); // redo, but is this needed?
	$returned3 = array('test'=>'Test','test2'=>'','test3'=>array('a'=>1,'b'=>2,'c'=>3));
	$this->settings_assign($returned3);
	$this->settings_clean();
	$result = ($this->set_json(ABCMS_SETTINGS, $this->settings) ? ABCMS_GOOD : ABCMS_BAD);

echo <<<EOF
<h4>Init</h4>
Result: {$result}
EOF;	
	return NULL;
}
private function admincron(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	echo "<h4>Cron</h4>Hello!";
	return NULL;
}
private function adminphpinfo(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	echo "<h4>PHP Info</h4>";
	phpinfo();
	return NULL;
}
private function adminbrowse(mixed &...$unused) : ?bool {
	echo "<h4>Browser</h4>";
	$path = $this->inputs['projectroot'];
	$display = <<< EOF
Filename: {$path}<br>
<br>
EOF;
	$files = array_diff(scandir($path), array('..'));
	foreach($files as $file) {
		$display .= $file."<br>\n";
	}
	echo $display;
	return NULL;
}




/*
 * SECTION: WEBSERVANT
 *
 */



/*
 * SECTION: WEBPAGES
 *
 */



/*
 * SECTION: UTILITIES
 *
 */
// Test replace function
public function replace(mixed &...$args) : bool {
	$args[0] = preg_replace("/Hello/u", "Howdy", $args[0]);
	return FALSE;
}
// Wrapper around contruct so extendable
public function echo(?string &...$args) : void {
	for($x=0,$z=count($args); $x<$z; ++$x){
		echo $args[$x];
	}
	return;
}
// Wrapper around contruct so extendable
public function print(?string $string = NULL) : bool {
	return print($string);
}
// Set path
public function set_path(?string $path = NULL) : ?string {
	return $path;
}
// Get path
public function get_path(?string $path = NULL) : ?string {
	return $path;
}
// Set file, TODO error check that reading and writing in my own extension directory!!
public function set_file(string $filename, string $value) : bool {
	if (FALSE === file_put_contents($filename, $value)) {
		$this->error_log("System, ".error_get_last());
		return FALSE;
	}
	return TRUE;
}
// Get file, TODO error check that reading and writing in my own extension directory!!
public function get_file(string $filename, string &$data) : bool {
	if (!file_exists($filename) || FALSE === ($data = file_get_contents($filename))) {
		$this->error_log("System, ".error_get_last());
		return FALSE;
	}
	return TRUE;
}
// Set json
public function set_json(string $filename, mixed $value) : bool {
	if (FALSE === $this->set_file($filename, json_encode($value, ABCMS_FLAG_JSON))) {
		$this->error_log("System file_put_contents(), ".error_get_last());
		return FALSE;
	}
	if (json_last_error() !== JSON_ERROR_NONE) {
		$this->error_log("System json_encode(), ".json_last_error_msg());
		return FALSE;
	}
	return TRUE;
}
// Get json
public function get_json(string $filename, mixed &$data) : bool {
	if (!file_exists($filename) || NULL === ($data = json_decode(file_get_contents($filename), TRUE))) {
		$this->error_log("System, ".json_last_error_msg().", ".error_get_last());
		return FALSE;
	}
	return TRUE;
}
// Include always
public function include(string $filename, ...$args) : mixed {
	if (!file_exists($filename)) {
		$this->error_log("Programmer, include does not exist.");
	}
	return include($filename); // Variable scope natually limited here
}
// Include once, PHP should provide a native no fault include_once() function
public function include_once(string $filename, ...$args) : mixed {
	static $included = array();
	if (!($filename = realpath($filename)) || !file_exists($filename)) {
		$this->error_log("Programmer, include does not exist.");
	}
	else if (!isset($included[$filename])) {
		$included[$filename] = TRUE;
		return function($filename, ...$args) { return include($filename); }; // Anonymous function protects variable scope
	}
	return FALSE;
}
// Need because array_walk_recursive() cannot copy from multi-dimensional source, array_map() cannot edit destination
public function array_walk_merge(array &$destiny, array $source) : void {
	foreach($destiny as $key => $value) { // Overwrite
		if (!isset($source[$key])) { continue; } // No source
		else if (is_array($destiny[$key]) && is_array($source[$key])) { $this->array_walk_merge($destiny[$key], $source[$key]); } // Recurse branch
		else { $destiny[$key] = $source[$key]; } // Overwrite leaf
	}
	foreach($source as $key => $value) { // Extend
		if (!isset($destiny[$key])) { $destiny[$key] = $source[$key]; continue; } // Extend branch/leaf
		else if (is_array($destiny[$key]) && is_array($source[$key])) { $this->array_walk_merge($destiny[$key], $source[$key]); } // Recurse branch
	}
	return;
}



/*
 * SECTION: THEME
 *
 */
public function htmldoc(	// Default theme function and method
	bool $admin		= FALSE,// default or admin
	?string $css	= NULL,	// Override css default
	?string $js		= NULL,	// Override js default
	?string $head	= NULL,	// Override header default
	?string $page	= NULL,	// Override page content default
	?string $foot	= NULL,	// Override footer default
	int $flag		= 1,	// Output control flag
) : ?bool {					// Return boolean
$title = (isset($_SERVER['HTTP_HOST']) && FALSE !== filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_DOMAIN) ? $_SERVER['HTTP_HOST'] : 'Unknown');
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<title><? echo $title; ?></title>
<meta name='description' content='<? echo $title; ?>'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<meta name='apple-mobile-web-app-capable' content='yes'>
<style>
#head a, #foot a {
	text-decoration: none;
	color: white;
}
a:hover {
	color: #0096FF !important;
}
body {
	margin: 0;
	padding: 0;
	font-family: Arial,sans-serif;
}
#main {
	display: flex;
	flex-direction: column;
	min-height: 100vh;
	margin: 0;
	background-color: #F0F0F0;
}
#head {
	background-color: #404040;
	color: #FFFFFF;
	text-align: center;
	padding: 5px 2%;
	width: 96%;
	min-width: 360px;
	max-width: 1024px;
	border-radius: 7px;
	margin: 5px auto;
}
#page {
	flex: 1;
	height: 100%;
	width: 96%;
	min-width: 360px;
	max-width: 1024px;
	margin: 0 auto;
	padding: 12px 2%;
	background-color: #FFFFFF;
	border-radius: 7px;
}
#foot {
	background-color: #404040;
	color: #FFFFFF;
	text-align: center;
	padding: 3px 2%;
	font-weight: 700;
	width: 96%;
	min-width: 360px;
	max-width: 1024px;
	border-radius: 7px;
	margin: 5px auto;
}
@media screen and (max-width: 1065px) {
#head, #page, #foot {
	border-radius: 0;
	margin: 0;
}
}
<?
if ($admin) {
?>
#main {
	border-width: 0 25px 0 25px;
	border-style: solid;
	border-color: #404040;
}
#head {
	max-width: 100%;
	border-radius: 0;
	margin: 0 auto 5px auto;
}
#page {
	border-radius: 0;
}
#foot {
	max-width: 100%;
	border-radius: 0;
	margin: 5px auto 0 auto;
}
@media screen and (max-width: 1115px) {
#head {
	border-radius: 0;
	margin: 0;
}
#foot {
	border-radius: 0;
	margin: 0;
}
}
<?
}
$css = array($css);
$this->output('/htmldefault_css', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$css);
?>
</style>
<script type='text/javascript'>
<?
$js = array($js);
$this->output('/htmldefault_js', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$js);
?>
</script>
</head>
<body>
<div id='main'>
<div id='head'>
<?
if (!$head) { $head = "<h2 style='font-size: 1.5em'>$title</h2>"; }
$head = array($head);
$this->output('/htmldefault_head', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$head);
?>
</div>
<div id='page'>
<?
if (!$page) {
	$page = <<<EOF
<h4>Status</h4>
{$GLOBALS['abcms_constant']('ABCMS_GOOD')}Hello World. I am alive.<br>
{$GLOBALS['abcms_constant']('ABCMS_BAD')}However, page requested not found.<br>
<br>
Please contact the webmaster for help.
EOF;
}
$page .= $this->see_errors();
$page = array($page);
$this->output('/htmldefault_page', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$page);
?>
</div>
<div id='foot'>
<?
if (!$foot) {	$foot = "<a href='/abcms/contact'>Contact</a>"; }
$foot = array($foot);
$this->output('/htmldefault_foot', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$foot);
?>
</div>
</div>
</body>
<?
return NULL; // done
}


// end object
}; }

return $_abcms;
}