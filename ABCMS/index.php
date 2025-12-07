<?php
/*
 * SECTION: OVERVIEW
 *
 * "A Basic Content Management System" (ABCMS)
 * Created AKA "Aionian Bible Content Management System"
 * PHP web developer toolkit and CMS in a single file
 * Install with Composer or copy this file to document root
 * EVERYTHING is an extension with the abcms() router
 * Run CLI "php index.php /abcms/help"
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
 */



/*
 * SECTION: CONSTANTS
 *
 */
// Constants in strings with {$abcms_constant('CONSTANT')}
$abcms_constant			= 'constant';
// General
const ABCMS_GOOD		= "<span style='color: green;'>\u{2611}</span> ";
const ABCMS_BAD			= "<span style='color: red;'>\u{2612}</span> ";
const ABCMS_EXT			= "/nainoiainc/abcms";
const ABCMS_EXT_BEGIN	= "/nainoiainc/abcms/begin";
const ABCMS_EXT_ADMIN	= "/nainoiainc/abcms/admin";
const ABCMS_SETTINGS	= "../private/nainoiainc/abcms/ABCMS.settings";
const ABCMS_ERRORLOG	= "../private/nainoiainc/abcms/ABCMS.errorlog";
const ABCMS_COREDUMP	= "../private/nainoiainc/abcms/ABCMS.coredump";
// User role must be >= output() roles to execute
const ABCMS_ROLE_EVERY	= 0;
const ABCMS_ROLE_READ1	= 1;
const ABCMS_ROLE_READ2	= 2;
const ABCMS_ROLE_READ3	= 3;
const ABCMS_ROLE_EDIT1	= 4;
const ABCMS_ROLE_EDIT2	= 5;
const ABCMS_ROLE_EDIT3	= 6;
const ABCMS_ROLE_BOSS1	= 7;
const ABCMS_ROLE_BOSS2	= 8;
const ABCMS_ROLE_BOSS3	= 9;
const ABCMS_ROLE_CLI	= 10;



/*
 * SECTION: TRY/CATCH
 *
 * If 'index.php' in doc root I do as I please
 * ElseIf !'index.php' reference abcms() as you please
 *
 */
try {																		// Try catch
	$abcms = NULL;													// Not constructed
	$abcms = abcms();												// Object for catch()
	if (empty(abcms()->inputs['boss'])) { return TRUE; }					// I do nada, do as you please
	$args[] = ABCMS_GOOD."Hello World.<br>\n";								// Default Greeting
	$args[] = ABCMS_BAD."System error, core extension failed.<br>\n";		// Default means core failed
	abcms()->output(														// I am boss and do as I please
		ABCMS_EXT_BEGIN,													// Entry hook and everything is a hook
		'abcms()->echo',													// Default function
		ABCMS_ROLE_EVERY,													// Minimum role
		0,																	// 0 = freely extendable
		...$args,															// Default args
	);
}
catch (Exception $e) {														// WSOD coredump
	// Screen message
	echo <<< EOF
{$abcms_constant('ABCMS_GOOD')}Hello World.<br>
{$abcms_constant('ABCMS_BAD')}WSOD '{$abcms_constant('ABCMS_COREDUMP')}'.<br>
EOF;
	// Get info
	$error		= ($e->getMessage() ?: 'NA');								// Exception error
	$sys		= (($sys = error_get_last()) ? print_r($sys,TRUE) : 'NA');	// System error
	$syserr		= (isset($sys['message']) ? $sys['message'] : 'NA');		// System error message
	$globals	= print_r((isset($GLOBALS) ? $GLOBALS : 'NA'), TRUE);		// Get $GLOBALS
	$inputs		= $settings = $errors = $debugs = $packages = 'NA';			// Extra initialized as 'NA'
	if (is_object($abcms)) {											// If contructed, get extra
		$inputs		= print_r($abcms->inputs,TRUE);					// Processed input
		$settings	= print_r($abcms->get_settings(),TRUE);			// Settings file
		$errors		= print_r($abcms->get_errors(),TRUE);			// Runtime errors
		$debugs		= print_r($abcms->get_debugs(),TRUE);			// Runtime debugs
		if (abcms()->inputs['auto']) {										// Composer packages
			$packages = NULL;
			foreach (Composer\InstalledVersions::getInstalledPackagesByType('abcms-extension') as $name) {
				$packages .= "{$name} : " . Composer\InstalledVersions::getInstallPath($name) . "\n";
			}
		}
	}
	// Report error_log()
	ini_set('error_log', ABCMS_ERRORLOG);									// In case constructor failed
	error_log("ABCMS, Exception, Error = {$error}");
	error_log("ABCMS, Exception, System = {$syserr}\n{$sys}");
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
	file_put_contents(ABCMS_COREDUMP, $coredump);							// Coredump
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
$_abcms = new class {							// Instanciate object
readonly	array $GLOBALS;						// Readonly raw inputs
readonly	array $inputs;						// Readonly processed inputs
private		array $settings	= array();			// Application settings
private		array $errors	= array();			// Runtime errors
private		array $debugs	= array();			// Runtime debugs
// Construct process inputs
function __construct() {
	// WSOD errors
	if (PHP_VERSION < '8.2.0') {									$this->throw_wsod("Fatal construct, >=PHP82 required"); }							// PHP version or WSOD
	if (!chdir(__DIR__)) {											$this->throw_wsod("Fatal construct, chdir('".__DIR__."')");	}						// chdir() or WSOD
	if (!ini_set('error_log', ABCMS_ERRORLOG)) {					$this->throw_wsod("Fatal construct, ini_set('error_log','".ABCMS_ERRORLOG."')"); }	// ini_set() or WSOD
	if (FALSE===$this->set_settings()) {							$this->throw_wsod("Fatal construct, set_settings(). Temporary!"); }					// JEFF TEMP DEV!!!!!!!!!!!!!!
	if (FALSE===$this->get_json(ABCMS_SETTINGS,$this->settings)) {	$this->throw_wsod("Fatal construct, get_json(".ABCMS_SETTINGS.")"); }				// Settings or WSOD
	// Process input
	$this->GLOBALS	= isset($GLOBALS)	? $GLOBALS	: array();																							// Protect GLOBALS
	$regex = "/\/([^=\/]+)=([^\/]+)/u";																													// Regex parse path vars
	$this->inputs	= array(																															// Process inputs
		'boss'			=> ('index.php' === basename(__FILE__) ? TRUE : FALSE),																			// Who is boss?
		'filename'		=> (__FILE__),																													// My filename
		'documentroot'	=> (__DIR__),																													// My documentroot folder
		'projectroot'	=> (dirname(__DIR__)),																											// My project folder
		'project'		=> (basename(dirname(__DIR__))),																								// My project name
		'urlfull'		=> ($urlfull = ('cli' === PHP_SAPI ? ('cli://localhost' . ($_SERVER['argc']!=2 || empty($_SERVER['argv'][1]) || '/' !== $_SERVER['argv'][1][0] ? '/abcms/help' : $_SERVER['argv'][1])) :
							((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http').'://'.
							(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown').
							(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/unknown')))),													// URL full
		'urlparsed'		=> ($urlparsed = parse_url($urlfull)),																							// URL parsed: ["scheme"], ["host"], ["path"], ["query"]
		'urlvars'		=> (!preg_match_all($regex, $urlparsed['path'], $matches, PREG_PATTERN_ORDER) ? array() :
							$this->output_varval(array_combine(array_map('urldecode', $matches[1]), array_map('urldecode', $matches[2])), 'allowv')),	// URL path variables
		'urlstripped'	=> ($urlstripped = '/'.(trim(preg_replace($regex, '/', $urlparsed['path']), '/'))), 											// URL stripped of variables, no trailing slash
		'urlpathall'	=> (urldecode($urlstripped)),																									// URL urldecoded
		'urlpathone'	=> (urldecode((!($ret = preg_match("/(\/[^\/]*)(\/.+)?/u", $urlstripped, $matches)) ? '/' : $matches[1]))),						// URL first segment
		'urlpathext'	=> (urldecode((!$ret || empty($matches[2]) ? '/' : $matches[2]))),																// URL remainder segment
		'urlquery'		=> ($this->output_varval((($tmp = array()) || (!empty($urlparsed['query']) && parse_str($urlparsed['query'],$tmp)) ?: $tmp), 'allowq')), // URL query vars needed because CLI has no $_GET
		'urlmethod'		=> ('cli' === PHP_SAPI ? 'CLI' : (empty($_SERVER['REQUEST_METHOD']) ? 'GET' : $_SERVER['REQUEST_METHOD'])),						// URL request method
		'cli'			=> ('cli' === PHP_SAPI ? TRUE : FALSE),																							// CLI command line execution
		'argv'			=> $_SERVER['argv'],																											// CLI arguments
		'argc'			=> $_SERVER['argc'],																											// CLI argument count
		'role'			=> ('cli' === PHP_SAPI ? ABCMS_ROLE_CLI : ABCMS_ROLE_BOSS3),																	// Assign user role
		'auto'			=> (file_exists(($auto = (__DIR__ . '/../vendor/autoload.php'))) ? $auto : NULL),												// Composer auto-loader
	);
	// Composer slower than my runtime loader
	if ($this->inputs['auto']) { require_once($this->inputs['auto']); }
	// User errors
	if (!preg_match("#^{$this->inputs['urlstripped']}#ui", $urlparsed['path'])) { $this->set_errors("User error, Invalid URL, variables before path"); }
	return;
}
// Dynamic properties disallowed
public function __set(string $name, mixed $value) : void { $this->throw_wsod("Fatal programmer error, dynamic properties disallowed for name = '{$name}'."); }
// Cloning disallowed
public function __clone() { $this->throw_wsod("Fatal programmer error, cloning ABCMS disallowed."); }
// Allow path variable
public function output_allowv(
	string $var,			// Allowed path variable
	string $type = 'mixed',	// Allowed type
) : void {
	if (empty($var) ||
		!empty($this->settings['allowv'][$var]) ||
		!in_array($type, array('mixed','string','array','integer','float','bool','boolean','email','domain','url','ip','mac'))) {
		$this->error_log("Non-fatal programmer error, illdefined or duplicate abcms()->output_allowv('{$var}', '{$type}').");
	}
	else {
		$this->settings['allowv'][$var] = $type;
	}
	return;
}
// Allow query variable
public function output_allowq(
	string $var,			// Allowed query variable
	string $type = 'mixed',	// Allowed type
) : void {
	if (empty($var) ||
		!empty($this->settings['allowq'][$var]) ||
		!in_array($type, array('mixed','string','array','integer','float','bool','boolean','email','domain','url','ip','mac'))) {
		$this->error_log("Non-fatal programmer error, illdefined or duplicate abcms()->output_allowq('{$var}', '{$type}').");
	}
	else {
		$this->settings['allowq'][$var] = $type;
	}
	return;
}
// Validate path/query variable
private function output_varval(
	array $vars,			// Path/query variable
	string $porq,			// 'allowv' or 'allowq'
) : array {
	$last = NULL;
	foreach($vars as $var => $val) {
		if ($var < $last) {							$this->set_errors("User error, URL variables are not alphbetical, '{$var}' < '{$last}'"); }
		if (empty($this->settings[$porq][$var])) {	$this->set_errors("User error, ignoring undefined URL path/query variable, '{$var}'");	unset($vars[$var]);	continue; }
		$last = $var;
		if ('null' == mb_strtolower($val)) {																								$vars[$var] = NULL;	continue; }
		switch($this->settings[$porq][$var]) {
			case 'mixed'	:
			case 'string'	:																																	continue 2;
			case 'array'	:	if (preg_match("/,/", $val)) { $this->set_errors("User error, ignoring invalid URL array variable, '{$var}'");	break; }
								$vars[$var] = explode(',', $val);																								continue 2;
			case 'integer'	:	if (FALSE === ($vars[$var] = filter_var($val, FILTER_VALIDATE_INT))) {											break; }		continue 2;
			case 'float'	:	if (FALSE === ($vars[$var] = filter_var($val, FILTER_VALIDATE_FLOAT))) {										break; }		continue 2;
			case 'bool'		:
			case 'boolean'	:	if (NULL  === ($vars[$var] = filter_var($val, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE))) {					break; }		continue 2;
			case 'email'	:	if (FALSE === ($vars[$var] = filter_var($val, FILTER_VALIDATE_EMAIL))) {										break; }		continue 2;
			case 'domain'	:	if (FALSE === ($vars[$var] = filter_var($val, FILTER_VALIDATE_DOMAIN))) {										break; }		continue 2;
			case 'url'		:	if (FALSE === ($vars[$var] = filter_var($val, FILTER_VALIDATE_URL))) {											break; }		continue 2;
			case 'ip'		:	if (FALSE === ($vars[$var] = filter_var($val, FILTER_VALIDATE_IP))) {											break; }		continue 2;
			case 'mac'		:	if (FALSE === ($vars[$var] = filter_var($val, FILTER_VALIDATE_MAC))) {											break; }		continue 2;
			default:			$this->throw_wsod("Fatal programmer error, impossible path/query variable type, var='{$var}', type='{$this->settings[$porq][$var]}', val='{$val}'");
		}
		$this->set_errors("User error, ignoring invalid path/query variable, var='{$var}', type='{$this->settings[$porq][$var]}', val='{$val}'");
		unset($vars[$var]);
	}
	return $vars;
}
// Which extension called the function that called me?
private function extension() : string {
	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);																			// Level 2 is caller of previous function
	if (empty($trace[1]['file'])) {																										// Problem, no trace
		$this->throw_wsod("Fatal system call, debug_backtrace('file not found')");
	}
	else if ($trace[1]['file'] === $this->inputs['filename']) {																			// Ok, I called myself
		return ABCMS_EXT;
	}
	else if (preg_match("|{$this->inputs['projectroot']}/private(/[^/]+/[^/]+)|u", $trace[1]['file'], $match) && !empty($match[1])) {	// Ok, valid extension
		return $match[1];
	}
	$this->throw_wsod("Fatal system call, debug_backtrace('file not private/extension')");													// Problem, invalid extension
}
// Hooked function output path router extension manager
public function output(
	string	$hook,		// Hook name
	string	$default,	// Default function
	int		$role,		// Minimum role permissions
	int		$flag,		// <0 = Exclusive -or- >0 = self-extend only -or- 0 = freely extend
	mixed	&...$args,	// Default arguments
) : bool {
	if (!empty($this->inputs['urlquery']['debug'])) { $this->set_debugs(array('Extendable Hook:') + func_get_args(),'Extendable Hook'); }	// Expose extension for developers
	if ($this->inputs['role'] < $role) { $this->set_errors("No permission"); return FALSE; }												// User has no permision to call
	// loop through default or extension
	$extended = FALSE;																														// Extension found?
	$caller   = ($flag > 0 ? $this->extension() : NULL);
	$supplant = ($default ? FALSE : TRUE);																									// Extend or supplant default
	if (isset($this->settings['route'][$hook]['in'])) {																						// Extend input, pre-output, or supplant default
		foreach($this->settings['route'][$hook]['in'] as $extin) {																			// Loop through input extensions by priority
			if (!$this->output_doit($hook, $extin, $caller)) { continue; }																	// Consider hook extension
			if (isset($extin['args']) && NULL !== $extin['args']) { $args = $extin['args']; }												// Extend input returned aguments or function input
			if ($extin['one']) { $supplant = TRUE; }																						// Supplant default function
			do {																															// Call hook extension for repeating rows
				if (FALSE === ob_start()) { $this->throw_wsod("Fatal system call, ob_start(), hook = {$hook}"); }							// Buffer each output row
				$more = $this->output_call($extin['fun'], ...$args);																		// Call hook extension
				if (FALSE === ($out = ob_get_clean())) { $this->throw_wsod("Fatal system call, ob_get_clean(), hook = {$hook}"); }			// Retrieve buffer
				if (isset($this->settings['route'][$hook]['out'])) {																		// Hook extension filter output
					foreach($this->settings['route'][$hook]['out'] as $extout) {															// Loop through output filter extensions by priority
						if (!$this->output_doit($hook, $extout, $caller)) { continue; }														// Consider hook extension
						$this->output_call($extout['fun'], $out, ...$args);																	// Call output filter
						if ($flag < 0) { break; }																							// Exclusive extension only	
					}
				}
				echo $out;																													// Echo filtered output
			} while ($more);																												// Output all rows
			$extended = TRUE;																												// Hook is extended
			if ($flag < 0) { break; }																										// Exclusive extension only
		}
	}
	if ($supplant) { return ($extended ? TRUE : FALSE); }																					// No hook no extension allowed
	// default function
	do {
		if (FALSE === ob_start()) { $this->throw_wsod("Fatal system call, ob_start(), hook = {$hook}"); }									// Buffer each output row
		$more = $this->output_call($default, ...$args);																						// Call default
		if (FALSE === ($out = ob_get_clean())) { $this->throw_wsod("Fatal system call, ob_get_clean(), hook = {$hook}"); }						// Retrieve buffer
		if (isset($this->settings['route'][$hook]['out'])) {																				// Hook extension filter output
			foreach($this->settings['route'][$hook]['out'] as $extout) {																	// Loop through output filter extensions by priority
				if (!$this->output_doit($hook, $extout, $caller)) { continue; }																// Consider hook extension
				$this->output_call($extout['fun'], $out, ...$args);																			// Output filter
				if ($flag < 0) { break; }																									// Exclusive extension only	
			}
		}
		echo $out;
	} while ($more);
	return TRUE;
}
// Execute hook extension?
private function output_doit(
	string	$hook,		// Hook name
	array	$ext,		// Extension definition
	?string	$caller,	// Is this caller allowed
) : bool {
	if (empty($ext['fun'])) {																					return FALSE; }	// Nothing to run
	if ($caller && !preg_match("|^{$caller}|u", $hook)) {
		$this->error_log("Non-fatal programmer error, hook='{$hook}', caller='{$caller}' not allowed.");		return FALSE; }	// Caller hook match failure
	if (!empty($ext['meth'])) {
		if (preg_match("#{$this->inputs['urlmethod']}#", $ext['meth'])) {														// run if method and
			if (empty($ext['ext']) ||																							// no extension name
				!empty($this->settings['equate']["{$this->inputs['urlpathall']}-{$hook}-{$ext['ext']}"]) ||						// "pathall-hook-ext" match
				!empty($this->settings['equate']["{$this->inputs['urlpathone']}-{$hook}-{$ext['ext']}"])) {		return TRUE; }	// "pathone-hook-ext" match
		}
	}
	else {																														// run if no method and
		if (empty($ext['ext']) ||																								// no extension name
			!empty($this->settings['equate']["{$this->inputs['urlpathall']}-{$hook}-{$ext['ext']}"]) ||							// "pathall-hook-ext" matches
			!empty($this->settings['equate']["{$this->inputs['urlpathone']}-{$hook}-{$ext['ext']}"])) {			return TRUE; }	// "pathone-hook-ext" matches	
	}
	return FALSE;
}
// Call extension function
private function output_call(
	string	$filefunc,	// File function
	string	&...$args,	// Arguments passed
) : ?bool {
	// Parse file function string #^(|/vendor/package/filepath)(|?(|classobject(::|->|()->))funcmeth)#
	if (!preg_match("#^((/[^?]+)\?)?((([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(::|\->|\(\)\->))?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))?$#", $filefunc, $match)) {
																								$this->throw_wsod("Fatal programmer error, output_call() preg_match('{$filefunc}')");
	}
	$filepath	= $match[2];																	// Dynamic extension file inclusion
	$classobject= $match[5];																	// Class or object
	$operator	= $match[6];																	// Operator to function
	$funcmeth	= $match[7];																	// Function/method
	$result = FALSE;																			// Default failure
	// Include file
	if ($filepath) {
		if ($funcmeth) {	$result = (bool)ABCMS()->include_once($filepath, ...$args); }		// Include for function, once only
		else {				$result = (bool)ABCMS()->include($filepath, ...$args); }			// No function, multiple executions
	}
	// Call function
	if ($funcmeth) {																			// Function attempt
		if ($classobject) {																		// Class or object method
			if ('abcms' === $classobject) {														// Disallow access to my privates
				$reflection = new ReflectionClass($this);
				if ($reflection->getMethod($funcmeth)->isPrivate()) {							$this->throw_wsod("Fatal programmer error, private abcms()->method access disallowed '{$filefunc}'"); }
			}
			if ("::" === $operator) {															// Class operator
				if (!class_exists($classobject) || !method_exists($classobject, $funcmeth)) {	$this->throw_wsod("Fatal programmer error, output_call() invalid class method '{$filefunc}'"); }
				$result = (bool)$classobject::$funcmeth(...$args);								// Execute
			}
			else if ("->" === $operator) {														// Instance or object operator
				if (!is_object($classobject) || !method_exists($classobject, $funcmeth)) {		$this->throw_wsod("Fatal programmer error, output_call() invalid object method '{$filefunc}'"); }
				$result = (bool)$classobject->$funcmeth(...$args);								// Execute
			}
			else if ("()->" === $operator) {													// Returned object operator
				if (!function_exists($classobject)) {											$this->throw_wsod("Fatal programmer error, output_call() invalid function for object '{$filefunc}'"); }
				if (!is_object(($newobject = $classobject()))) {								$this->throw_wsod("Fatal programmer error, output_call() invalid function object '{$filefunc}'"); }
				if (!method_exists($newobject, $funcmeth)) {									$this->throw_wsod("Fatal programmer error, output_call() invalid function object method '{$filefunc}'"); }
				$result = (bool)$newobject->$funcmeth(...$args);								// Execute
			}
			else {																				$this->throw_wsod("Fatal programmer error, output_call() invalid operator '{$filefunc}'"); }
		}
		else {
			if (!is_function($funcmeth)) {														$this->throw_wsod("Fatal programmer error, output_call() invalid function '{$filefunc}'"); }
			$result = (bool)$funcmeth(...$args);												// Execute
		}
	}
	return $result;
}
// Register hook extension
public function output_extend(
	string $meth,			// Allowed HTTP methods listed in string or '' = always, "CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE"
	string $hook,			// /vendor/package/hook (not a file path)
	string $ext,			// Name of extension or '' = always
	string $typ,			// Type 'one', 'in', 'out'
	string $fun,			// include?function
	int $ord = 0,			// Order
	mixed $args = NULL,		// Argument
) : void {
	$newtyp =
		('one'=== $typ ? 'in' :
		('in' === $typ ? 'in' : 'out'));
	$this->settings['route'][$hook][$newtyp][] = array(
		'meth'	=> $meth,
		'ext'	=> $ext,
		'fun'	=> $fun,
		'ord'	=> $ord,
		'args'	=> $args,
		'one'	=> ('one' === $typ ? TRUE : FALSE),
	);
	return;
}
// Equate path to hook extension
public function output_equate(
	string $hook,			// name of hook
	string $ext,			// name of extension
	string $path,			// unique path command must be unique
) : void {
	$this->settings['equate']["{$path}-{$hook}-{$ext}"] = TRUE;
	return;
}



/*
 * SECTION: DEBUG/ERRORS/LOGS
 *
 */
public function throw_wsod(					// Throw WSOD
	mixed ...$data,							// Exception data
) : void {
	throw new Exception("Exception thrown:\n".print_r($data,TRUE));
	return;
}
public function error_log(					// Set error_log
	string $message,						// Error message
) : void {
	error_log("ABCMS, {$message}");
	return;
}
public function set_debugs(					// Set debugs
	mixed $data,							// Debug data
) : void {
	$this->debugs[] = $data;
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
private function set_settings() : bool {
	// register valid path and query variables
	$this->output_allowq('debug','bool');
	$this->output_allowv('test','string');
	$this->output_allowq('test','string');
	// core hook extensions
	$this->output_extend('CLI-GET-POST',	ABCMS_EXT_BEGIN,	'',  		'one',	'abcms()->home',		0);
	$this->output_extend('CLI-GET-POST',	ABCMS_EXT_ADMIN,	'home',		'one',	'abcms()->homepage',	0);
	$this->output_extend('CLI-GET-POST',	ABCMS_EXT_ADMIN,	'help',		'one',	'abcms()->help',		0);
	$this->output_extend('CLI-GET-POST',	ABCMS_EXT_ADMIN,	'debug',	'one',	'abcms()->throw_wsod',	0);
	// output filtering
	$this->output_extend('CLI-GET-POST',	ABCMS_EXT_ADMIN,	'',  		'out',	'abcms()->replace',		1);
	// core hook extension paths
	$this->output_equate(ABCMS_EXT_ADMIN,	'home',		'/');
	$this->output_equate(ABCMS_EXT_ADMIN,	'home',		'/abcms/home');
	$this->output_equate(ABCMS_EXT_ADMIN,	'help',		'/abcms/help');
	$this->output_equate(ABCMS_EXT_ADMIN,	'debug',	'/abcms/debug',);
	// extension sort order
	foreach($this->settings['route'] as &$hook) {
		if (isset($hook['pre'])  && is_array($hook['pre'])) {  usort($hook['pre'],  function($a, $b) { return $a['ord'] <=> $b['ord'];} ); }
		if (isset($hook['post']) && is_array($hook['post'])) { usort($hook['post'], function($a, $b) { return $a['ord'] <=> $b['ord'];} ); }
	}
	// fast loading json
	return $this->set_json(ABCMS_SETTINGS, $this->settings);
}
public function home(string &...$unused) : ?int { // Non-function wrapper so extendable
	echo "<div style='margin: 20px;'>";
	if (!empty($this->errors)) {
		echo(ABCMS_BAD."Errors:<br>\n".implode("<br>\n",$this->errors)."<br>\n");
	}
	$args[] = ABCMS_GOOD."Hello World.<br>\n";
	$args[] = ABCMS_BAD."Page not found.<br>\n";
	abcms()->output(
		ABCMS_EXT_ADMIN,
		'abcms()->echo',
		ABCMS_ROLE_BOSS3,
		1,
		...$args,
	);
	echo "</div>";

	return FALSE;
}
public function homepage(string &...$unused) : ?int { // Non-function wrapper so extendable
	echo ABCMS_GOOD."Hello World.<br>\n";
	echo ABCMS_GOOD."I am alive.<br>\n";
	$args[] = 'unused';
	//echo ABCMS_GOOD.implode(' ',abcms()->output('/nainoiainc/abcms/variable','',...$args))."<br>\n";
	return FALSE;
}
public function help(string &...$unused) : ?int { // Non-function wrapper so extendable
	static $count = 3;
	echo ABCMS_GOOD."Hello World.<br>\n";
	return $count--;
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
	$args[0] = preg_replace("/Hello/u", "Hi", $args[0]);
	return FALSE;
}
public function echo(string &...$args) : void { // Non-function wrapper so extendable
	for($x=0,$z=count($args); $x<$z; ++$x){
		echo $args[$x];
	}
	return;
}
public function print(string $string = NULL) : bool { // Non-function wrapper so extendable
	return print($string);
}
public function set_path(string $path = NULL) : ?string { // set path
	return $path;
}
public function get_path(string $path = NULL) : ?string { // get path
	return $path;
}
public function set_file(string $filename, string $value) : bool { // set file, TODO error check that reading and writing in my own directory!!
	if (FALSE === file_put_contents($filename, $value)) {
		$this->error_log("System call, file_put_contents({$filename}), ".error_get_last());
		return FALSE;
	}
	return TRUE;
}
public function get_file(string $filename, string &$data) : bool { // get file, TODO error check that reading and writing in my own directory!!
	if (FALSE === ($data = file_get_contents($filename))) {
		$this->error_log("System call, file_get_contents({$filename}), ".error_get_last());
		return FALSE;
	}
	return TRUE;
}
public function set_json(string $filename, mixed $value) : bool { // set json
	if (FALSE === $this->set_file($filename, json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
		$this->error_log("System call, file_put_contents({$filename}), ".error_get_last());
		return FALSE;
	}
	if (json_last_error() !== JSON_ERROR_NONE) {
		$this->error_log("System fail, json_encode({$filename}), ".json_last_error_msg());
		return FALSE;
	}
	return TRUE;
}
public function get_json(string $filename, mixed &$data) : bool { // get json
	if (NULL === ($data = json_decode(file_get_contents($filename), TRUE))) {
		$this->error_log("System fail, file_get_contents(json_decode({$filename})), ".json_last_error_msg().", ".error_get_last());
		return FALSE;
	}
	return TRUE;
}


// end object
}; }

return $_abcms;
}
