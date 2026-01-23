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
 * SECTION: UNDERSTANDING
 * 1. Constants allow sharing immutable values and they are fast
 * 2. Try Catch allows fail safe error handling of the core abcms() function
 * 3. Core dump exception allows developer debugging with all information
 * 4. Instantiate and process inputs allows global onetime input handling
 * 5. EVERYTIHNG is an extension with $abcms->output()
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
 * More stuff...
 *
 */



/*
 * SECTION: CONSTANTS
 *
 */
// Constants in strings {$abcms_constant('CONSTANT')}
$abcms_constant			= 'constant';
// General
const ABCMS_GOOD		= "<span style='color: green;'>\u{2611}</span> ";
const ABCMS_BAD			= "<span style='color: red;'>\u{2612}</span> ";
// Extensions
const ABCMS_EXT_SELF	= "/nainoiainc/abcms";
const ABCMS_EXT_BEGINS	= "/begin";
const ABCMS_EXT_BEGIN	= "/nainoiainc/abcms/begin";
const ABCMS_EXT_ADMINS	= "/admin";
const ABCMS_EXT_ADMIN	= "/nainoiainc/abcms/admin";
// Regex
const ABCMS_REGEX_FUNC	= "/^((\/[^?]+)\?)?((([a-zA-Z_\x{7f}-\x{ff}][a-zA-Z0-9_\x{7f}-\x{ff}]*)(::|\->|\(\)\->))?([a-zA-Z_\x{7f}-\x{ff}][a-zA-Z0-9_\x{7f}-\x{ff}]*))?$/u";
const ABCMS_REGEX_HOOK	= "/\/[^\/]+\/[^\/]+\/[^\/]+/u";
const ABCMS_REGEX_METH	= "/(CLI|GET|POST|PUT|HEAD|DELETE|PATCH|OPTIONS|CONNECT|TRACE)/u";
const ABCMS_REGEX_META	= array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE');
const ABCMS_REGEX_PATH	= "/(\/[^\/]*)(\/.+)?/u";
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


/*
 * SECTION: TRY/CATCH
 *
 * Run the CMS or coredump if failure
 *
 */
try {																		// Try catch
	abcms();																// Construct and assign to $abcms
	if (empty($abcms->inputs['boss'])) { return TRUE; }						// I do nada, do as you please
	$page = <<<EOF
<h4>Status</h4>
{$abcms_constant('ABCMS_GOOD')}Hello World. Graceful termination.<br>
{$abcms_constant('ABCMS_BAD')}Fatal error. Core extension failed.<br>
<br>
Please contact the webmaster for help.
EOF;
	$args = array(FALSE,NULL,NULL,NULL,$page,NULL);
	$abcms->output(															// I am boss and do as I please
		'/begin',															// Entry hook and everything is a hook
		'CLI-GET-POST',														// Method allowed for default
		'abcms->htmldoc',													// Default function
		ABCMS_ROLE_PUBLIC,													// Minimum role required
		1,																	// 1 = allow Exclusive
		FALSE,																// Default required
		...$args,															// Default args
	);
	if (!empty($abcms->inputs['urlquery']['debug'])) {
		$abcms->throw_wsod("Requested WSOD exception for debugging.");		// Force coredump
	}
}
catch (Exception $e) {														// WSOD coredump
	// Screen
	echo ob_get_clean();
	$head = "<div style='display: inline-block;'><h2 style='font-size: 1.5em'>Console</h2></div><div style='float: right; display: inline-block;'><h2 style='font-size: 1.5em'><a href='/' title='Close Console' style='text-decoration: none; color: white;'>X</a></h2></div>";
	$page = <<< EOF
<h4>Status</h4>
{$abcms_constant('ABCMS_GOOD')}Hello World. Graceful termination.<br>
{$abcms_constant('ABCMS_BAD')}Fatal error. Exception caught.<br>
{$abcms_constant('ABCMS_GOOD')}Coredump available for review.<br>
<br>
Please contact the webmaster for help.<br>
EOF;
	$foot = "Thank you!";
	$alive = (isset($abcms) && is_object($abcms) ? TRUE : FALSE);
	ob_end_clean();
	if (headers_sent()) {	echo $head.$page; }
	else {					ob_end_clean(); abcms_htmldoc(TRUE, NULL, NULL, $head, $page, $foot, -1, FALSE); }
	// Get info
	$error		= ($e->getMessage() ?: 'NA');								// Exception error
	$sys		= (($sys = error_get_last()) ? print_r($sys,TRUE) : 'NA');	// System error
	$syserr		= (isset($sys['message']) ? $sys['message'] : 'NA');		// System error message
	$globals	= print_r((isset($GLOBALS) ? $GLOBALS : 'NA'), TRUE);		// Get $GLOBALS
	$inputs		= $settings = $errors = $debugs = $packages = 'NA';			// Extra initialized as 'NA'
	if ($alive) {															// If contructed, get extra
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
	// Report error_log()
	ini_set('error_log', ABCMS_ABCMSLOG);									// If constructor failed
	error_log("ABCMS: WSOD exception, error = '{$error}'");
	error_log("ABCMS: WSOD exception, system = '{$syserr}'\n{$sys}");
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
	file_put_contents(ABCMS_COREDUMP, $coredump);							// Save coredump
}
finally {																	// Clean up
	;																		// Remove all locks
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
if (isset($GLOBALS['abcms'])) {											$this->throw_wsod("Fatal construct, \$abcms already set."); }							// $abcms already set
$GLOBALS['abcms'] = $_abcms = new class {		// Instanciate assign global object $abcms
readonly	array $GLOBALS;						// Readonly raw inputs
readonly	array $inputs;						// Readonly processed inputs
private		array $settings	= array();			// Application settings
private		array $errors	= array();			// Runtime errors
private		array $debugs	= array();			// Runtime debugs
// Construct process inputs
function __construct() {
	// WSOD errors
	if (PHP_VERSION < '8.2.0') {									$this->throw_wsod("Fatal construct, >=PHP82 required."); }									// PHP version
	if (!chdir(__DIR__)) {											$this->throw_wsod("Fatal construct, failed chdir('".__DIR__."').");	}						// chdir()
	if (!ini_set('error_log', ABCMS_ABCMSLOG)) {					$this->throw_wsod("Fatal construct, failed ini_set('error_log','".ABCMS_ABCMSLOG."').");}	// ini_set()
	if (FALSE===$this->set_settings()){								$this->throw_wsod("Fatal construct, failed set_settings() load."); }						// JEFF TEMP DEV!!!!!!!!!!!!!!
	if (FALSE===$this->get_json(ABCMS_SETTINGS, $this->settings)) {	$this->throw_wsod("Fatal construct, settings file not found."); }							// Load settings
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
							$this->output_varval(array_combine(array_map('urldecode', $matches[1]), array_map('urldecode', $matches[2])), $role, 'allowv')),	// URL path variables
		'urlstripped'	=> ($urlstripped = '/'.(trim(preg_replace(ABCMS_REGEX_URLV, '/', $pars['path']), '/'))), 												// URL stripped of variables, no trailing slash
		'urlpathall'	=> (urldecode($urlstripped)),																											// URL urldecoded
		'urlpathone'	=> (urldecode((!($ret = preg_match(ABCMS_REGEX_PATH, $urlstripped, $matches)) ? '/' : $matches[1]))),									// URL first segment
		'urlpathext'	=> (urldecode((!$ret || empty($matches[2]) ? '/' : $matches[2]))),																		// URL remainder segments
		'urlquery'		=> ($this->output_varval((($tmp = array()) || (!empty($pars['query']) && parse_str($pars['query'],$tmp)) ?: $tmp), $role, 'allowq')),	// URL parse_str() because CLI has no $_GET
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
// Dynamic properties disallowed
public function __set(string $name, mixed $value) : void { $this->throw_wsod("Fatal programmer error, dynamic properties disallowed for name = '{$name}'."); }
// Cloning disallowed
public function __clone() { $this->throw_wsod("Fatal programmer error, cloning ABCMS disallowed."); }
// Allow path variable
public function output_allowv(
	string $var,			// Allowed path variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role permissions
) : void {
	$this->output_allow($var, $type, $role, 'allowv');
	return;
}
// Allow query variable
public function output_allowq(
	string	$var,			// Allowed query variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role permissions
) : void {
	$this->output_allow($var, $type, $role, 'allowq');	
	return;
}
// Allow variable
private function output_allow(
	string	$var,			// Allowed variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role permissions
	string	$porq,			// Path or query variable
) : void {
	if (!preg_match(ABCMS_REGEX_VARS, $var) ||
		!empty($this->settings[$porq][$var]) ||
		!in_array($type, ABCMS_ARRAY_TYPE) ||
		!in_array($role, ABCMS_ROLE_SET)) {
		$this->error_log("Programmer error, illdefined or duplicate abcms()->output_{$porq}('".implode("', '",func_get_args())."')");
		return;
	}
	$this->settings[$porq][$var] = array('type'=>$type, 'role'=>$role);
	return;
}
// Validate path/query variable
private function output_varval(
	array	$vars,			// Path/query variable
	int		$role,			// User role
	string	$porq,			// 'allowv' or 'allowq'
) : array {
	$last = NULL;
	foreach($vars as $var => $val) {
		if ($var < $last) {									$this->set_errors("User error, URL variables are not alphbetical, '{$var}' < '{$last}'"); }
		$last = $var;
		if (empty($this->settings[$porq][$var]['type'])) {	$this->set_errors("User error, ignoring undefined URL variable, '{$var}'");	unset($vars[$var]);	continue; }
		if ($role < $this->settings[$porq][$var]['role']) {	$this->set_errors("Insufficient permission to use variable, '{$var}'");		unset($vars[$var]);	continue; }
		if ('null' == mb_strtolower($val)) {																							$vars[$var] = NULL;	continue; }
		switch($this->settings[$porq][$var]['type']) {
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
			default:			$this->throw_wsod("Fatal programmer error, impossible URL variable type, var='{$var}', type='{$this->settings[$porq][$var]['type']}', val='{$val}'");
		}
		$this->set_errors("User error, ignoring invalid URL variable, var='{$var}', type='{$this->settings[$porq][$var]['type']}', val='{$val}'");
		unset($vars[$var]);
	}
	return $vars;
}
// Which extension called the function that called me?
private function extension() : string {
	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);																				// Level 2 is caller of previous function
	if (empty($trace[1]['file'])) {																											// Problem, no trace
		$this->throw_wsod("Fatal system call, debug_backtrace('file not found')");
	}
	else if ($trace[1]['file'] === (__FILE__)) {																							// Ok, I called myself
		return ABCMS_EXT_SELF;
	}
	else if (preg_match("|^".dirname(__DIR__)."/private(/[^/]+/[^/]+)|u", $trace[1]['file'], $match) && !empty($match[1])) {				// Ok, valid extension, chdir() matters!
		return $match[1];
	}
	$this->throw_wsod("Fatal system call, debug_backtrace('extension location not ../private/vendor/extension')");							// Problem, invalid extension
}
// Hooked function output path router extension manager
public function output(
	string	$hook,		// /vendor/extension/$hook segment name
	string	$met,		// Allowed HTTP methods, '' = ALL = "CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE"
	string	$default,	// Default function
	int		$rol,		// Minimum role permissions
	int		$flag,		// <0 = author exclusive -or- 0 = anyone -or- 1 = extender exclusive allowed
	bool	$must,		// Must do default function TRUE = required -OR- FALSE = optional
	mixed	&...$args,	// Default arguments
) : bool {
	$this->set_debugs(array('ABCMS()->output():') + func_get_args());																		// Developer info
	// initialize
	$extended = FALSE;																														// Extension found?
	$whoami = $this->extension();																											// Which extension am I?
	$myself = (ABCMS_EXT_SELF === $whoami ? TRUE : FALSE);																					// I am myself?
	$hook = $whoami . $hook;																												// Full hook name
	$caller   = ($flag < 0 ? $whoami : NULL);																								// Extension must be self
	$bypass = FALSE;																														// Bypass default
	// setup default
	$ext = array(																															// Default
		'I' => (empty($default) ? array() : array(																							// Input unless no default
			array(																															// Array
				'met'	=> $met,																											// HTTP methods
				'fun'	=> $default,																										// Function
				'rol'	=> $rol,																											// Role
				'ord'	=> 0,																												// Order
				'ctl'	=> NULL,																											// Control
				'who'	=> $whoami,																											// Default is myself
				'arg'	=> NULL,																											// Args
			)
		)),
		'O' => array(),																														// No default output filter
	);
	// sort extensions
	if (isset($this->settings['route'][$hook])) {																							// Compile hook extensions
		$hooky = $this->settings['route'][$hook];																							// Shortened variable reference
		$ext = array_merge_recursive(																										// Merge the extensions
			$ext,																															// Default included
			(!empty($hooky['eq'][$this->inputs['urlpathall']]) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->inputs['urlpathall']]]) ?
			 $hooky['ex'][$hooky['eq'][$this->inputs['urlpathall']]] :																		// Full path match?
			('/' !== $this->inputs['urlpathone'] &&
			 !empty($hooky['eq'][$this->inputs['urlpathone'].'/']) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->inputs['urlpathone'].'/']]) ?
			 $hooky['ex'][$hooky['eq'][$this->inputs['urlpathone'].'/']] :																	// 1st segment match?
			 array())),																														// Or nothing?
			(!empty($hooky['eq']['']) && !empty($hooky['ex'][$hooky['eq']['']])	? $hooky['ex'][$hooky['eq']['']] : array()),				// Empty path = always considered
			(!empty($hooky['ex'][''])											? $hooky['ex'][''] : array()));								// Empty ext = awlays considered
		// sort the extension array
		if (isset($ext['I'])) {	usort($ext['I'], function($a, $b) { return (($ret=(isset($a['ctl']['U'])===isset($b['ctl']['U']) ? 0 : (isset($a['ctl']['U']) ? -1 : 1))) ? $ret : $a['ord'] <=> $b['ord']); } );}
		if (isset($ext['O'])) {	usort($ext['O'], function($a, $b) { return (($ret=(isset($a['ctl']['U'])===isset($b['ctl']['U']) ? 0 : (isset($a['ctl']['U']) ? -1 : 1))) ? $ret : $a['ord'] <=> $b['ord']); } );}
	}
	$this->set_debugs(print_r($ext,TRUE));																									// Developer info
	// do extensions
	$exin = $exout = NULL;
	$musty = TRUE;
	foreach($ext['I'] as $extin) {																											// Loop through input extensions by priority
		if (!$this->output_doit($extin, $caller, $flag, ($must || $musty), $exin)) { continue; }											// Consider hook extension
		if (!$must && $extin['ord'] < 0 && !isset($extin['ctl']['D'])) { $musty = FALSE; }													// Do default unless one extended says no before the default
		if (isset($extin['arg']) && NULL !== $extin['arg']) { $args = $extin['arg']; }														// Extend input returned aguments or function input
		do {																																// Call hook extension for repeating rows
			if (FALSE === ob_start()) { $this->throw_wsod("Fatal system call, ob_start(), hook = {$hook}"); }								// Buffer each output row
			$more = $this->output_call($myself, $extin['fun'], ...$args);																	// Call hook extension
			if (FALSE === ($out = ob_get_clean())) { $this->throw_wsod("Fatal system call, ob_get_clean(), hook = {$hook}"); }				// Retrieve buffer
			foreach($ext['O'] as $extout) {																									// Loop through output filter extensions by priority
				if (!$this->output_doit($extout, $caller, $flag, TRUE, $exout)) { continue; }												// Consider hook extension
				$this->output_call($myself, $extout['fun'], $out, ...$args);																// Call output filter
			}
			echo $out;																														// Echo filtered output
		} while ($more);																													// Output all rows
		$extended = TRUE;																													// Hook is extended
		if (isset($extin['ctl']['U'])) { break; }																							// Uno extension allowed
	}
	return $extended;
}
// Execute hook extension?
private function output_doit(
	array	$ext,		// Extension definition
						// 'I' = Input/default -OR- 'O' = Output/filter
						// 'E' = Exclusive by my extension or omit me, otherwise default anyone
						// 'U' = Uno/single extension, otherwise default multiple extensions cooperate 
						// 'D' = Default included, otherwise default excluded if extended and $ord < 0
	?string	$caller,	// Is this caller allowed
	int		$flag,		// <0 = author exclusive -or- 0 = anyone -or- 1 = extender exclusive allowed
	bool	$must,		// Must do default function TRUE==required
	?string	&$excl,		// Name of chosen exclusive extension
) : bool {
	// Exits before exclusive non-exclusive test
	if (!$must && !$ext['ord']) {																			return FALSE; }		// Default extension excluded
	if (!empty($ext['met']) && FALSE === stripos($ext['met'], $this->inputs['urlmethod'])) { 				return FALSE; }		// HTTP method
	if ($caller && $caller !== $ext['who']) {
		$this->error_log("Programmer error, caller not self disallowed '{$caller}' !== '{$ext['who']}'.");	return FALSE;		// Caller match
	}
	// Exclusive or non-exclusive
	if (!$flag && isset($ext['ctl']['E'])) {																return FALSE; }		// Exclusive not allowed, but requested, so forget it
	if ($flag > 0) {																											// Exclusive allowed
		if (NULL === $excl) { $excl = (isset($ext['ctl']['E']) ? $ext['who'] : FALSE); }										// First viable extension determines if exclusive or non-exclusive
		if (!$excl && isset($ext['ctl']['E'])) {															return FALSE; }		// Non-exclusive, but exclusive requested
		if ($excl && $ext['who'] != $excl) {																return FALSE; }		// Exclusive granted, but wrong extension
	}
	if (empty($ext['fun'])) {																				return FALSE; }		// Nothing to do
	if ($this->inputs['role'] < $ext['rol']) {
		$this->set_errors("Insufficient permission for requested resource");								return FALSE;		// Permision
	}
	return TRUE;
}
// Call extension function
private function output_call(
	bool	$myself,	// Am I myself?
	string	$filefunc,	// File function
	mixed	&...$args,	// Arguments passed
) : ?bool {
	// Parse file function string #^(|/vendor/package/filepath)(|?(|classobject(::|->|()->))funcmeth)#
	if (!preg_match(ABCMS_REGEX_FUNC, $filefunc, $match)) {										$this->throw_wsod("Fatal programmer error, output_call() preg_match('{$filefunc}')"); }
	$filepath	= $match[2];																	// Dynamic extension file inclusion
	$classobject= $match[5];																	// Class or object
	$operator	= $match[6];																	// Operator to function
	$funcmeth	= $match[7];																	// Function/method
	$result = FALSE;																			// Default failure
	// Include file
	if ($filepath) {
		if ($funcmeth) {	$result = (bool)$this->include_once($filepath, ...$args); }			// Include for function, once only
		else {				$result = (bool)$this->include($filepath, ...$args); }				// No function, multiple executions
	}
	// Call function
	if ($funcmeth) {																			// Function attempt
		if ($classobject) {																		// Class or object method
			if ("::" === $operator) {															// Class operator
				if (!class_exists($classobject) || !method_exists($classobject, $funcmeth)) {	$this->throw_wsod("Fatal programmer error, output_call() invalid class method '{$filefunc}'"); }
				$result = (bool)$classobject::$funcmeth(...$args);								// Execute
			}
			else {
				if ("->" === $operator) {														// Instance or object operator
					if (!isset($GLOBALS[$classobject]) || !is_object($GLOBALS[$classobject])) {	$this->throw_wsod("Fatal programmer error, output_call() invalid object '{$filefunc}'"); }
					$newobject = $GLOBALS[$classobject];
				}
				else if ("()->" === $operator) {												// Returned object operator
					if (!function_exists($classobject)) {										$this->throw_wsod("Fatal programmer error, output_call() invalid function for object '{$filefunc}'"); }
					if (!is_object(($newobject = $classobject()))) {							$this->throw_wsod("Fatal programmer error, output_call() invalid function object '{$filefunc}'"); }
				}
				else {																			$this->throw_wsod("Fatal programmer error, output_call() invalid operator '{$filefunc}'"); }
				if (!method_exists($newobject, $funcmeth)) {									$this->throw_wsod("Fatal programmer error, output_call() invalid object method '{$filefunc}'"); }
				global $abcms;																	// Global pointer to myself allowing 'abcms' object reference
				if (!$myself && $newobject === $abcms) {										// Special check to disallow abcms() privates because we are inside abcms(), unless I am myself
					$reflection = new ReflectionClass($this);
					if ($reflection->getMethod($funcmeth)->isPrivate()) {						$this->throw_wsod("Fatal programmer error, private abcms()->method access disallowed '{$filefunc}'"); }
				}
				$result = (bool)$newobject->$funcmeth(...$args);								// Execute
			}
		}
		else {
			if (!function_exists($funcmeth)) {													$this->throw_wsod("Fatal programmer error, output_call() invalid function '{$filefunc}'"); }
			$result = (bool)$funcmeth(...$args);												// Execute
		}
	}
	return $result;
}
// Register hook extension
public function output_extend(
	string	$hok,						// /vendor/package/hook (not file path)
	string	$ext,						// Extension name or '' for ALL
	string	$met,						// Allowed HTTP methods, '' = ALL = "CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE"
	string	$str,						// Control string
										// 'I' = Input/default -OR- 'O' = Output/filter
										// 'E' = Exclusive by my extension or omit me, otherwise default anyone
										// 'U' = Uno/single extension, otherwise default multiple extensions cooperate 
										// 'D' = Default included, otherwise default excluded if extended and $ord < 0
	string	$fun,						// Include?function
	int		$rol = ABCMS_ROLE_PUBLIC,	// Minimum role permission
	int		$ord = 0,					// Order, -9999 >= $ord <= 9999
	mixed	$arg = NULL,				// Argument
) : bool {
	$ctl = array_flip(($key=str_split(strtoupper($str))));
	$key = array_diff_key($key, array('I','O','E','U','D'));
	if (!preg_match(ABCMS_REGEX_HOOK, $hok) ||
		!preg_match(ABCMS_REGEX_METH, $met) ||
		(isset($ctl['I']) && isset($ctl['O'])) ||
		!empty($key) ||
		!preg_match(ABCMS_REGEX_FUNC, $fun)) {
		$this->error_log("Programmer error, invalid extension output_extend('{$hok}','{$ext}','{$met}','{$str}','{$fun}','{$rol}','{$ord}').");
		return FALSE;
	}
	$this->settings['route'][$hok]['ex'][$ext][(isset($ctl['O']) ? 'O' : 'I')][] = array(
		'met'	=> $met,
		'fun'	=> $fun,
		'rol'	=> $rol,
		'ord'	=> min(9999, max(-9999, $ord)),
		'ctl'	=> $ctl,
		'who'	=> $this->extension(),
		'arg'	=> $arg,
	);
	return TRUE;
}
// Equate path to named hook extension
public function output_equate(
	string $hook,			// Hook name
	string $ext,			// Extension name or '' for all
	string $path,			// Unique URL path, use trailing '/' to match 1st path segment, otherwise no trailing slash
) : bool {
	if (!preg_match(ABCMS_REGEX_HOOK, $hook) ||
		(substr_count($path, '/')>2 && '/' == $path[-1]) ||
		('' !== $path && ('/' !== $path[0] || FALSE === filter_var('http://localhost'.$path, FILTER_VALIDATE_URL))) ||
		isset($this->settings['route'][$hook]['eq'][$path])) {
		$this->error_log("Programmer error, hook equate error or path already defined output_equate('{$hook}-{$ext}-{$path}').");
		return FALSE;
	}
	$this->settings['route'][$hook]['eq'][$path] = $ext;
	return TRUE;
}



/*
 * SECTION: DEBUG/ERRORS/LOGS
 *
 */
public function throw_wsod(					// Throw WSOD
	mixed ...$data,							// Exception data
) : void {
	throw new Exception(print_r($data,TRUE));
	return;
}
public function error_log(					// Set error_log
	string $message,						// Error message
) : void {
	error_log("ABCMS: {$message}");
	return;
}
public function set_debugs(					// Set debugs
	mixed $data,							// Debug data
) : void {
	if (!empty($this->inputs['urlquery']['debug'])) { $this->debugs[] = $data; }
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
public function htmldoc(		// Default theme function and method
	mixed &...$args,			// Aguments
) : ?bool {
	return abcms_htmldoc(...$args);
}
public function htmldefault(	// Default theme function and method
	mixed &...$args,			// Aguments
) : ?bool {
	global $abcms_constant;
	$args[4] = NULL;
	$args[6] = 1;
	return $this->htmldoc(...$args);
}
private function htmladmin(
	mixed &...$args,			// Aguments
) : ?bool {
	global $abcms_constant;
	$args[0] = TRUE;
	$args[3] = "<div style='display: inline-block;'><h2 style='font-size: 1.5em'>Console</h2></div><div style='float: right; display: inline-block;'><h2 style='font-size: 1.5em'><a href='/' title='Close Console' style='text-decoration: none; color: white;'>X</a></h2></div>";
	$args[4] = NULL;
	$args[6] = -1;
	return $this->htmldoc(...$args);
}
 
 
 
private function set_settings() : bool { // This function to be moved to /admin/setup and loop through all extensions
	// register path variables
	$this->output_allowv('debug',	'bool',		ABCMS_ROLE_PUBLIC);
	// register query / _GET variables
	$this->output_allowq('debug',	'bool',		ABCMS_ROLE_ADMINS);
	// register _POST variables

	// default extensions
	// String with 'I'=Input/default, 'O'=Output, 'E'=Exclusive/Author, 'U'=One/Uno, 'D'=DefaultAlso
	$this->output_extend('/nainoiainc/abcms/begin',				'',			'CLI-GET-POST',	'IEU',	'abcms->htmldefault',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_extend('/nainoiainc/abcms/begin',				'admin',	'CLI-GET-POST',	'IEU',	'abcms->htmladmin',		ABCMS_ROLE_ADMINS,	-11);
	$this->output_equate('/nainoiainc/abcms/begin',				'admin',	'/admin/');
	$this->output_extend('/nainoiainc/abcms/begin',				'code',		'CLI-GET-POST',	'IEU',	'abcms->codeadmin',		ABCMS_ROLE_ADMINS,	-9999+10);
	$this->output_equate('/nainoiainc/abcms/begin',				'code',		'/admin/code');
	// user extensions
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'home',		'CLI-GET-POST',	'IE',	'abcms->pagehome',		ABCMS_ROLE_PUBLIC,	-10);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'home',		'/');
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'home',		'/home');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'contact',	'CLI-GET-POST',	'IE',	'abcms->pagecontact',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'contact',	'/contact');
	// admin extensions
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'status',	'CLI-GET-POST',	'IE',	'abcms->pageadmin',		ABCMS_ROLE_ADMINS,	-9999+10);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'status',	'/admin');
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'status',	'/admin/status');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'phpinfo',	'CLI-GET-POST',	'IE',	'abcms->pagephpinfo',	ABCMS_ROLE_ADMINS,	-9999+10);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'phpinfo',	'/admin/phpinfo');
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'help',		'CLI-GET-POST',	'IE',	'abcms->adminhelp',		ABCMS_ROLE_ADMINS,	-9999+10);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'help',		'/admin/help');
	// test extensions
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'test',		'CLI-GET-POST',	'IED',	'abcms->pagetest',		ABCMS_ROLE_ADMINS,	-9999);
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'',			'CLI-GET-POST',	'IED',	'abcms->pagetest',		ABCMS_ROLE_ADMINS,	-9999);
	$this->output_extend('/nainoiainc/abcms/htmldefault_page',	'',			'CLI-GET-POST',	'IED',	'abcms->pagetest',		ABCMS_ROLE_ADMINS,	9999);
	$this->output_equate('/nainoiainc/abcms/htmldefault_page',	'test',	'');

	// fast loading json
	return $this->set_json(ABCMS_SETTINGS, $this->settings);
}
private function pagetest(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	echo "<br>TESTY TEST TEST<br>";
	return NULL;
}
private function pagehome(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	global $abcms_constant;
	?><h4>Homepage</h4><?
	echo $abcms_constant('ABCMS_GOOD'); ?>Hello World. I am alive.<br><?
	echo $abcms_constant('ABCMS_GOOD'); ?>Thank you!<br><?
	echo $this->see_errors();
	echo "<br><a href='/admin'>/admin</a>";
	return NULL;
}
private function pagecontact(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	global $abcms_constant;
	?><h4>Contact</h4>
This is where to contact us.<?
	echo $this->see_errors();
	echo "<br><a href='/home'>/home</a>";
	return NULL;
}
private function codeadmin(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	highlight_file($this->inputs['filename']);
	return NULL;
}
private function adminhelp(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
echo <<<EOF
<h4>Help</h4>
<a href='/'>/</a> : Homepage<br>
<a href='/home'>/home</a> : Homepage<br>
<a href='/admin'>/admin</a> : Admin status page<br>
<a href='/admin/status'>/admin/status</a> : Admin status page<br>
<a href='/admin/code'>/admin/code</a> : Display ABCMS sourc code<br>
<a href='/admin/phpinfo'>/admin/phpinfo</a> : Display PHP info<br>
EOF;	
	return NULL;
}
private function pageadmin(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	static $count = 3;
	if ($count===3) { echo "<h4>Status</h4>"; }
	echo ABCMS_GOOD."Helping you! {$count}<br>\n";
	--$count;
	if ($count>0) { return TRUE; }
	echo $this->see_errors();
echo <<<EOF
<br>
<a href='/'>/</a><br>
<a href='/home'>/home</a><br>
<a href='/admin'>/admin</a><br>
<a href='/admin/status'>/admin/status</a><br>
<a href='/admin/help'>/admin/help</a><br>
<a href='/admin/code'>/admin/code</a><br>
<a href='/admin/phpinfo'>/admin/phpinfo</a><br>
<a href='/bogus'>/bogus</a><br>
EOF;	
	return NULL;
}
private function pagephpinfo(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	echo "<h4>PHP Info</h4>";
	phpinfo();
	return NULL;
}
private function browser(string $path = NULL) : void {
	if (NULL===$path) { $path = $this->inputs['projectroot']; }
	$display = <<< EOF
Filename: {$path}

EOF;
	$files = array_diff(scandir($path), array('..'));
	foreach($files as $file) {
		$display .= $file."<br>\n";
	}
	echo $display;
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
public function replace(mixed &...$args) : bool { // Test replace function
	$args[0] = preg_replace("/Hello/u", "Howdy", $args[0]);
	return FALSE;
}
public function echo(?string &...$args) : void { // Non-function wrapper so extendable
	for($x=0,$z=count($args); $x<$z; ++$x){
		echo $args[$x];
	}
	return;
}
public function echo_error(?string &...$args) : void { // Non-function wrapper so extendable
	for($x=0,$z=count($args); $x<$z; ++$x){
		echo $args[$x];
	}
	if (!empty($this->errors)) {
		echo(ABCMS_BAD."Errors:<br>\n".implode("<br>\n",$this->errors)."<br>\n");
	}
	return;
}
public function print(?string $string = NULL) : bool { // Non-function wrapper so extendable
	return print($string);
}
public function set_path(?string $path = NULL) : ?string { // set path
	return $path;
}
public function get_path(?string $path = NULL) : ?string { // get path
	return $path;
}
public function set_file(string $filename, string $value) : bool { // set file, TODO error check that reading and writing in my own directory!!
	if (FALSE === file_put_contents($filename, $value)) {
		$this->error_log("System error, file_put_contents({$filename}), ".error_get_last());
		return FALSE;
	}
	return TRUE;
}
public function get_file(string $filename, string &$data) : bool { // get file, TODO error check that reading and writing in my own directory!!
	if (!file_exists($filename) || FALSE === ($data = file_get_contents($filename))) {
		$this->error_log("System error, file_get_contents({$filename}), ".error_get_last());
		return FALSE;
	}
	return TRUE;
}
public function set_json(string $filename, mixed $value) : bool { // set json
	if (FALSE === $this->set_file($filename, json_encode($value, ABCMS_FLAG_JSON))) {
		$this->error_log("System error, file_put_contents({$filename}), ".error_get_last());
		return FALSE;
	}
	if (json_last_error() !== JSON_ERROR_NONE) {
		$this->error_log("System error, json_encode({$filename}), ".json_last_error_msg());
		return FALSE;
	}
	return TRUE;
}
public function get_json(string $filename, mixed &$data) : bool { // get json
	if (!file_exists($filename) || NULL === ($data = json_decode(file_get_contents($filename), TRUE))) {
		$this->error_log("System error, file_get_contents(json_decode({$filename})), ".json_last_error_msg().", ".error_get_last());
		return FALSE;
	}
	return TRUE;
}
public function include(string $filename, string &...$args) : mixed { // include always
	if (!file_exists($filename)) {
		$this->error_log("Programmer error, include({$filename}) does not exist");
	}
	return include($filename);
}
public function include_once(string $filename, string &...$args) : mixed { // PHP should provide a no fault include_once function
	static $included = array();
	if (!($filename = realpath($filename)) || !file_exists($filename)) {
		$this->error_log("Programmer error, include_once({$filename}) does not exist");
	}
	else if (!isset($included[$filename])) {
		$included[$filename] = TRUE;
		include($filename);
	}
	return FALSE;
}

// end object
}; }

return $_abcms;
}



/*
 * SECTION: THEME
 *
 */
function abcms_htmldoc(	// Default theme function and method
	bool $admin		= FALSE,// default or admin
	?string $css	= NULL,	// Override css default
	?string $js		= NULL,	// Override js default
	?string $head	= NULL,	// Override header default
	?string $page	= NULL,	// Override page content default
	?string $foot	= NULL,	// Override footer default
	int $flag		= 1,	// Output control flag
	bool $allow		= TRUE,	// Allow extensions below
) : ?bool {					// Return boolean
global $abcms, $abcms_constant;
$allow = ($allow && isset($abcms) && is_object($abcms) ? TRUE : FALSE);
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
if ($allow) {	$css = array($css); $abcms->output('/htmldefault_css', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$css); }
else {			echo $css; }
?>
</style>
<script type='text/javascript'>
<?
if ($allow) {	$js = array($js); $abcms->output('/htmldefault_js', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$js); }
else {			echo $js; }
?>
</script>
</head>
<body>
<div id='main'>
<div id='head'>
<?
if (!$head) {	$head = "<h2 style='font-size: 1.5em'>{$title}</h2>"; }
if ($allow) {	$head = array($head); $abcms->output('/htmldefault_head', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$head); }
else {			echo $head; }
?>
</div>
<div id='page'>
<?
if (!$page) {
	$page = <<<EOF
<h4>Status</h4>
{$abcms_constant('ABCMS_GOOD')}Hello World. I am alive.<br>
{$abcms_constant('ABCMS_BAD')}However, page requested not found.<br>
<br>
Please contact the webmaster for help.
EOF;
}
if ($allow) {	$page .= $abcms->see_errors();
				$page = array($page); $abcms->output('/htmldefault_page', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$page); }
else {			echo $page; }
?>
</div>
<div id='foot'>
<?
if (!$foot) {	$foot = "<a href='/contact' style='text-decoration: none; color: white;'>Thank you!</a>"; }
if ($allow) {	$foot = array($foot); $abcms->output('/htmldefault_foot', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...$foot); }
else {			echo $foot; }
?>
</div>
</div>
</body>
<?
return NULL; // done
}
