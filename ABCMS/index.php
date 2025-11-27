<?php
/*
 * SECTION: OVERVIEW
 *
 * "A Basic Content Management System" (ABCMS)
 * Created for AKA "Aionian Bible Content Management System"
 * A PHP web developer toolkit and CMS in a single file
 * Install with Composer or copy this file to your document root
 * With the abcms() router engine EVERYTHING is a hook or extension
 * Run https://domain.com/abcms/help or "php index.php /abcms/help"
 *
 *
 * 
 * SECTION: NOTES
 *
 * 1. Use of https://phpdoc.org/ is too restrictive.
 * 2. Beautiful file conversion with "libreoffice" to keep PHP tight.
 *		yum install libreoffice
 *		input and output filters: https://help.libreoffice.org/25.8/en-US/text/shared/guide/convertfilters.html?&DbPAR=SHARED&System=WIN
 *		filter options: https://help.libreoffice.org/25.8/en-US/text/shared/guide/csv_params.html?&DbPAR=SHARED&System=WIN
 *		beware TRUE and FALSE: https://ask.libreoffice.org/t/entering-true-or-false-as-text-into-any-cell-is-interpreted-as-true-or-false/104905
 *		working example: libreoffice --convert-to "csv:Text - txt - csv (StarCalc):9,,UTF-8,1" --infilter="MS Excel 97" --outdir ./ input.xls
 * 3. 
 *
 */



/*
 * SECTION: CONSTANTS
 *
 */
const ABCMS_CHECK	= "\u{2611}";													// Success check mark
const ABCMS_BALLOT	= "\u{2612}";													// Failure X mark



/*
 * SECTION: RUN ABCMS
 *
 * If named 'index.php' in your document root then I do as I pleasae
 * If named anything else you include me and reference abcms() as you please
 *
 */
try {																				// Try to run ABCMS
	$abcms_object = NULL;															// Not constructed yet
	$abcms_object = abcms();														// Object pointer for catch() block
	if (empty(abcms()->ABCMS['boss'])) { return TRUE; }								// I do nada and you do as you please with me
	$args[] = "Hello World! ".ABCMS_CHECK."<br>\n";									// Say hello if nothing else
	$args[] = "I am alive! ".ABCMS_CHECK."<br>\n";									// Be happy about it
	abcms()->output(																// I am boss and I do as I please with you
		'/nainoiainc/abcms/begin',													// Name of the entry hook and everything is a hook
		'abcms()->echo',															// Default function if not extended
		...$args,																	// Default arguments to output() by reference
	);
}
catch (Exception $e) {																// Oops, ungraceful error, coredump, and WSOD
	$corefile = "../private/nainoiainc/abcms/ABCMS.coredump";						// Coredump location
	echo "Hello World! ".ABCMS_CHECK."<br>\n";										// Try to be friendly
	echo "I am wounded! ".ABCMS_BALLOT." See {$corefile}<br>\n";					// Now call for help
	$error = ($e->getMessage() ?: 'NA');											// Get the exception error
	$sys = (($sys = error_get_last()) ? print_r($sys,TRUE) : 'System Error: NA');	// Get the system error
	$globals = print_r((isset($GLOBALS) ? $GLOBALS : array()), TRUE);				// Get $GLOBALS
	$abcms = $settings = $debugs = $packages = 'NA';								// Initialize NA also
	if (is_object($abcms_object)) {													// If ABCMS contructed, grab more info
		$abcms = print_r($abcms_object->ABCMS,TRUE);								// ABCMS settings
		$settings = print_r($abcms_object->get_settings(),TRUE);					// Dynamic settings
		$debugs = print_r($abcms_object->get_debugs(),TRUE);						// Debug info
		if (abcms()->ABCMS['auto']) {												// If Composer list packages
			$packages = NULL;
			foreach (Composer\InstalledVersions::getInstalledPackagesByType('abcms-extension') as $name) {
				$packages .= "{$name} : " . Composer\InstalledVersions::getInstallPath($name) . "\n";
			}
		}
	}
	else {
		ini_set('error_log', '../private/nainoiainc/abcms/ABCMS.errorlog');			// Constructor failed, so set this
	}
	// Report to the error_log
	error_log("ABCMS Exception: Error = {$error}");
	error_log("ABCMS Exception: System = {$sys}");
	// Dump the corefile
	$coredump = <<< EOF
ABCMS COREDUMP BEGIN


ABCMS COREDUMP Error message:
$error


ABCMS COREDUMP System error:
$sys


ABCMS COREDUMP GLOBALS:
$globals


ABCMS COREDUMP ABCMS:
$abcms


ABCMS COREDUMP Settings:
$settings


ABCMS COREDUMP Debugs:
$debugs


ABCMS COREDUMP Packages:
$packages

ABCMS COREDUMP End

EOF;
	file_put_contents($corefile, $coredump);
}
finally {														// End well if possible
	;															// Remove all locks
}
return TRUE;													// Function definitions only beyond this point



/*
 * SECTION: DEFINE ABCMS CLASS AND CORE
 *
 */
function abcms() : object {
// Initialize
static $_abcms = NULL;
if (NULL===$_abcms) {



// New class
$_abcms = new class {
// Properties
readonly array $GLOBALS;
readonly array $ABCMS;
private array $ABCMS_settings = array();
private array $ABCMS_debugs = array();
// Construct
function __construct() {
	// Validate system
	if (PHP_VERSION < '8.2.0') {														$this->throw_debugs("Invalid configuration, PHP82 or greater is required."); }
	if (!chdir(__DIR__)) {																$this->throw_debugs("System call failure, chdir(".__DIR__.")");	}
	if (!ini_set('error_log', ($tmp='../private/nainoiainc/abcms/ABCMS.errorlog'))) {	$this->throw_debugs("System call failure, ini_set(error_log, {$tmp})"); }

	// TODO TEMP
	$this->set_settings();
	// Protect GLOBALS
	$this->GLOBALS	= isset($GLOBALS)	? $GLOBALS	: array();
	// Assign properties
	$regex = "/\/([[:alnum:]\-._~%]+)=([[:alnum:]\-._~%]+)/u";
	$this->ABCMS	= array(
		'boss'			=> ('index.php' === basename(__FILE__) ? TRUE : FALSE),								// If TRUE I include you, otherwise you include me
		'filename'		=> (__FILE__),																		// Me filename
		'documentroot'	=> (__DIR__),																		// Documentroot folder
		'projectroot'	=> (dirname(__DIR__)),																// Project folder
		'project'		=> (basename(dirname(__DIR__))),													// Project name
		'urlfull'		=> ($urlfull = ('cli' === PHP_SAPI ? ('cli://localhost' . ($_SERVER['argc']!=2 || empty($_SERVER['argv'][1]) || '/' !== $_SERVER['argv'][1][0] ? '/help' : $_SERVER['argv'][1])) :
							((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http').'://'.
							(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown').
							(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/unknown')))),		// URL full
		'urlparsed'		=> ($urlparsed = parse_url($urlfull)),												// URL parsed: ["scheme"], ["host"], ["path"], ["query"]
		'urlvars'		=> (FALSE === preg_match_all($regex,$urlparsed['path'],$matches,PREG_PATTERN_ORDER) ? NULL : array_combine(array_map('urldecode', $matches[1]), array_map('urldecode', $matches[2]))), // URL path vars
		'urlcommand'	=> '/'.(mb_strtolower(urldecode(trim(preg_replace($regex,'/',$urlparsed['path']),'/')))), // URL command stripped of variables and urldecoded with no trailing slash unless '/' or urlencoded slash
		'urlquery'		=> (($urlquery = NULL) || !empty($urlparsed['query']) && parse_str($urlparsed['query'],$urlquery) ?: $urlquery),	// URL query variables for CLI and HTTP, $_GET has only HTTP
		'urlmethod'		=> (empty($_SERVER['REQUEST_METHOD']) ? 'GET' : $_SERVER['REQUEST_METHOD']),		// URL request method
		'cli'			=> ('cli' === PHP_SAPI ? TRUE : FALSE),												// CLI command line execution
		'argv'			=> $_SERVER['argv'],																// CLI arguments
		'argc'			=> $_SERVER['argc'],																// CLI argument count
		'auto'			=> (file_exists(($auto = (__DIR__ . '/../vendor/autoload.php'))) ? $auto : NULL),	// Auto-loader present
		'settings'		=> $this->get_json("../private/nainoiainc/abcms/ABCMS.json"),						// Load all settings
	);
	// Validate input
	if (!preg_match("#^{$this->ABCMS['urlcommand']}#u",urldecode($urlparsed['path']))) {
		$this->throw_debugs("Invalid URL, path variables found before path command in {$this->ABCMS['urlfull']}");
	}
	if (!TRUE) { // TODO add path variable and query variable check here
		$this->throw_debugs("Invalid URL, unknown path variables and query variables found in {$this->ABCMS['urlfull']}");
	}
	if (($sorted = $this->ABCMS['urlvars']) && ksort($sorted) && $sorted !== $this->ABCMS['urlvars']) {
		$this->throw_debugs("Invalid URL, path variables not alphbetical in {$this->ABCMS['urlfull']}");
	}
	if (($sorted = $this->ABCMS['urlquery']) && ksort($sorted) && $sorted !== $this->ABCMS['urlquery']) {
		$this->throw_debugs("Invalid URL, query variables not alphbetical in {$this->ABCMS['urlfull']}");
	}
	// autoload
	if ($this->ABCMS['auto']) { require_once($this->ABCMS['auto']); }	// autoload
}



// Output with extensions
public function output(
	string $hook,		// name of this hook
	string $default,	// default function
	mixed &...$args,	// default arguments
) {
	// let programmers know this hook is exposed
	$this->set_debugs(func_get_args());
	// Start buffers
	if (FALSE===ob_start()) {
		$this->throw_debugs("System call failure, ob_start() with command = {$command}");
	}
	// loop through registered pre-extensions
	$dodefault = TRUE;
	if (isset($this->ABCMS['settings']['route'][$hook]['pre'])) {
	foreach($this->ABCMS['settings']['route'][$hook]['pre'] as $ext) { // multiple extensions extend same hook for 'pre' and 'only'
		if (empty($ext['meth']) && empty($ext['ext'])) { ; }																	// run extension if no method or name
		else if (empty($ext['ext']) && preg_match("#{$this->ABCMS['urlmethod']}#", $ext['meth'])) { ; }							// run extension if no name and method matches
		else if (empty($ext['meth']) && $this->ABCMS['settings']['equate']["{$this->ABCMS['urlcommand']}{$hook}{$ext['ext']}"]) { ; }	// run extension if no method and "path,hook,ext" matches
		else if ($ext['meth'] && preg_match("#{$this->ABCMS['urlmethod']}#", $ext['meth']) &&
				 $ext['ext'] && !empty($this->ABCMS['settings']['equate']["{$this->ABCMS['urlcommand']}{$hook}{$ext['ext']}"])) { ; }	// run extension if method and path,hook,ext match
		else { continue; }
		if ($ext['only']) { $dodefault = FALSE; }
		$this->set_debugs($ext);
		while($this->output_call($ext['fun'], ...$args)) ;																		// call pre-extension until done
	}}
	// loop through default function
	if ($dodefault) {
		$this->set_debugs($default);
		while($this->output_call($default, ...$args)) ;																			// call default until done
	}
	// loop through registered post-extensions
	if (isset($this->ABCMS['settings']['route'][$hook]['post'])) {
	foreach($this->ABCMS['settings']['route'][$hook]['post'] as $ext) { // multiple extensions extend same hook for 'post'
		if (empty($ext['meth']) && empty($ext['ext'])) { ; }																	// run extension if no method or name
		else if (empty($ext['ext']) && preg_match("#{$this->ABCMS['urlmethod']}#", $ext['meth'])) { ; }							// run extension if no name and method matches
		else if (empty($ext['meth']) && $this->ABCMS['settings']['equate']["{$this->ABCMS['urlcommand']}{$hook}{$ext['ext']}"]) { ; }	// run extension if no method and "path,hook,ext" matches
		else if ($ext['meth'] && preg_match("#{$this->ABCMS['urlmethod']}#", $ext['meth']) &&
				 $ext['ext'] && !empty($this->ABCMS['settings']['equate']["{$this->ABCMS['urlcommand']}{$hook}{$ext['ext']}"])) { ; }	// run extension if method and path,hook,ext match
		else { continue; }
		while($this->output_call($ext['fun'], ...$args)) ;																			// call post-extension until done
	}}

	// Sanitize and echo buffers
	if (FALSE === ($output = ob_get_clean())) {
		$this->throw_debugs("System call failure, ob_get_clean() with command = {$command}");
	}
	echo $this->sanitize($output);
	return;
}



// Call the functions
private function output_call($filefunction, &...$args) : mixed {
	$this->set_debugs(func_get_args());
	// default hello
	$return = FALSE;
	// parse call #^(|/vendor/package/filepath)(|?(|classobject(::|->|()->))methodfunction)#
	if (!preg_match("#^((/[^?]+)\?)?((([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(::|\->|\(\)\->))?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))?$#", $filefunction, $match)) {
		$this->throw_debugs("output_call() preg_match({$filefunction}) invalid");
	}
	$this->set_debugs($match);
	$filepath		= $match[2];
	$classobject	= $match[5];
	$operator		= $match[6];
	$methodfunction	= $match[7];
	// include filepath
	if ($filepath) {
		if ($methodfunction) {
			$return = ABCMS()->include_once($filepath, ...$args);
		}
		else {
			$return = ABCMS()->include($filepath, ...$args);
		}
	}
	// call function, maybe $check = new Reflection(Class|Method|Function)($methodfunction); to confirm zero arguments
	if ($methodfunction) {
		if ($classobject) {
			if ("::" === $operator) {
				if (!class_exists($classobject) || !method_exists($classobject, $methodfunction)) {
					$this->throw_debugs("output_call() class method invalid {$filefunction}");
				}
				$return = $classobject::$methodfunction(...$args);
			}
			else if ("->" === $operator) {
				if (!is_object($classobject) || !method_exists($classobject, $methodfunction)) {
					$this->throw_debugs("output_call() object method invalid {$filefunction}");
				}
				$return = $classobject->$methodfunction(...$args);
			}
			else if ("()->" === $operator) {
				if (!function_exists($classobject)) {
					$this->throw_debugs("output_call() function invalid {$filefunction}");					
				}
				if (!is_object(($newobject = $classobject()))) {
					$this->throw_debugs("output_call() function object invalid {$filefunction}");					
				}
				if (!method_exists($newobject, $methodfunction)) {
					$this->throw_debugs("output_call() function object method invalid {$filefunction}");					
				}
				$return = $newobject->$methodfunction(...$args);
			}
			else {
				$this->throw_debugs("output_call() invalid operator {$filefunction}");
			}
		}
		else {
			if (!is_function($methodfunction)) {
				$this->throw_debugs("output_call() function invalid {$filefunction}");
			}
			$return = $methodfunction(...$args);
		}
	}
	return $return;
}



/*
 * SECTION: DEFINE DEBUGS
 *
 */
public function throw_debugs(				// Throw exception now
	string &...$info,						// Exception info
) : void {
	//$this->set_debugs(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT));
	$this->set_debugs($info);
	throw new Exception("Exception thrown\n".implode("\n", $info));
	return;
}
public function set_debugs(					// Set a debug info
	mixed $info,							// Debug info
) : void {
	$this->ABCMS_debugs[] = $info;
	return;
}
public function get_debugs() : array {		// Get private debugs array for public
	return $this->ABCMS_debugs;
}
public function get_settings() : array {	// Get private debugs array for public
	return $this->ABCMS_settings;
}


/*
 * SECTION: DEFINE EQUATE AND EXTEND
 *
 */
// Core function to map/equate path commands to hooks
public function output_equate(
	string $path,					// unique path command must be unique
	string $hook,					// name of hook
	string $extension,				// name of extension
) : void {

	$this->ABCMS_settings['equate'][mb_strtolower("{$path}{$hook}{$extension}", 'UTF-8')] = TRUE;
	return;
}



// Core function for packages to pre-register their hook extensions and store in application settings for fast autoloading
public function output_extend(
	string $meth,					// HTTP method or '' = always
	string $ext,					// name of extension or '' = always
	string $hook,					// /vendor/package/hook (not a file path)
	string $pos,					// position to default: only, pre, post, both
	string $fun,					// include?function
	int $ord,						// order
) : void {
	$newpos =
		('only' === $pos ? 'pre' :
		('pre'  === $pos ? 'pre' : 'post'));
	$this->ABCMS_settings['route'][$hook][$newpos][] = array(
		'meth'	=> $meth,
		'ext'	=> $ext,
		'fun'	=> $fun,
		'ord'	=> $ord,
		'only'	=> ('only' === $pos ? TRUE : FALSE),
	);
	if ('both' === $pos) {
	$this->ABCMS_settings['route'][$hook]['pre'][] = array(
		'meth'	=> $meth,
		'ext'	=> $ext,
		'fun'	=> $fun,
		'ord'	=> $ord,
		'only'	=> FALSE,
	);
	}
	return;
}




/*
 * SECTION: DEFINE HELP
 *
 */
public function help(string &...$args) : ?int { // Non-function wrapper so extendable
	static $count = 3;
	echo "Help World! ".ABCMS_CHECK."<br>\n";
	return $count--;
}


/*
 * SECTION: DEFINE SETUP
 *
 */



/*
 * SECTION: DEFINE CRON
 *
 */



/*
 * SECTION: DEFINE LOGS
 *
 */



/*
 * SECTION: DEFINE AUTHENTICATION AND SESSIONS
 *
 */



/*
 * SECTION: DEFINE FORMS
 *
 */



/*
 * SECTION: DEFINE ADMIN
 *
 */
private function home(string &...$args) : ?int { // Non-function wrapper so extendable
	echo <<< EOF
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<title>A Basic Content Management System</title>
<meta name='description' content="A Basic Content Management System">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name='mobile-web-app-capable' content='yes'>
<meta name="generator" content="ABCMS™">
<meta http-equiv='x-ua-compatible' content='ie=edge'>
<script></script>
</head>
<body>
EOF;
	$out[] = "I am alive! ".ABCMS_CHECK."<br>\n";
	$out[] = "But we do not belong here! ".ABCMS_BALLOT."<br>\n";
	abcms()->output(
		'/nainoiainc/abcms/abcms',
		'abcms()->echo',
		...$out,
	);
	echo <<< EOF
</body>
</html>
EOF;
	return FALSE;
}
public function homepage(string &...$args) : ?int { // Non-function wrapper so extendable
	echo "I am alive! ".ABCMS_CHECK."<br>\n";
	echo "Hello World! ".ABCMS_CHECK."<br>\n";
	return FALSE;
} 



/*
	string $meth,					// HTTP method, NULL = all
	string $ext,					// name of extension
	string $hook,					// /vendor/package/hook (not a file path)
	string $pos,					// position to default: only, pre, post
	string $fun,					// include?function
	int $ord,						// order

	$path,							// unique path command must be unique
	$hook,							// name of hook
	$extension,						// name of extension
*/
private function set_settings() : void {
	// add extensions and everything is an extension
	$this->output_extend('GET',		'', '', '', '', 0);
	$this->output_extend('POST',	'', '', '', '', 0);
	$this->output_extend('PUT',		'', '', '', '', 0);
	$this->output_extend('HEAD',	'', '', '', '', 0);
	$this->output_extend('DELETE',	'', '', '', '', 0);
	$this->output_extend('PATCH',	'', '', '', '', 0);
	$this->output_extend('OPTIONS',	'', '', '', '', 0);
	$this->output_extend('CONNECT',	'', '', '', '', 0);
	$this->output_extend('TRACE',	'', '', '', '', 0);
	
	$this->output_extend('GETPOST', '',  	'/nainoiainc/abcms/begin', 'only', 'abcms()->home', 0);
	$this->output_extend('GETPOST', 'home',	'/nainoiainc/abcms/abcms', 'only', 'abcms()->homepage', 0);
	$this->output_extend('GETPOST', 'help',	'/nainoiainc/abcms/abcms', 'only', 'abcms()->help', 0);
	$this->output_extend('GETPOST', 'debug','/nainoiainc/abcms/abcms', 'only', 'abcms()->throw_debugs', 0);
	
	$this->output_equate('/',				'/nainoiainc/abcms/abcms',	'home');
	$this->output_equate('/abcms/home',		'/nainoiainc/abcms/abcms',	'home');
	$this->output_equate('/abcms/help',		'/nainoiainc/abcms/abcms',	'help');
	$this->output_equate('/abcms/debug',	'/nainoiainc/abcms/abcms',	'debug');

	// order extensions as requested
	foreach($this->ABCMS_settings['route'] as &$hook) {
		if (isset($hook['pre']) && is_array($hook['pre'])) {  usort($hook['pre'],  function($a, $b) { return $a['ord'] <=> $b['ord'];} ); }
		if (isset($hook['post']) && is_array($hook['post'])) { usort($hook['post'], function($a, $b) { return $a['ord'] <=> $b['ord'];} ); }
	}
	// save to file for fast loading
	$this->set_json("../private/nainoiainc/abcms/ABCMS.json", $this->ABCMS_settings);
}



// ADMIN BROWSER
private function browser(string $path = NULL) : void {
	if (NULL===$path) { $path = $this->ABCMS['projectroot']; }
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
 * SECTION: DEFINE WEBSERVANT
 *
 */



/*
 * SECTION: DEFINE PAGES, BOOKS, AND LISTS
 *
 */



/*
 * SECTION: DEFINE UTILITIES
 *
 */
public function __set(string $name, $value) { // Dynamic properties not allowed
	$this->throw_debugs("Invalid programming, dynamic property creation not allowed for name = {$name}");
}
private function sanitize(string $output) : string { // Sanitize string
	return $output;
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
public function set_file(string $filename, mixed $value) : void { // set file, TODO error check that reading and writing in my own directory!!
	if (FALSE === file_put_contents($filename, $value)) {
		$this->throw_debugs("System call failure, file_put_contents({$filename}).");
	}
}
public function get_file(string $filename) : mixed { // get file, TODO error check that reading and writing in my own directory!!
	if (FALSE === ($data = file_get_contents($filename))) {
		$this->throw_debugs("System call failure, file_get_contents({$filename}).");
	}
	return($data);
}
public function set_json(string $filename, mixed $value) : void { // set json
	if (FALSE === ($value = json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))) {
		$this->throw_debugs("System call failure, json_encode({$filename}).");
	}
	if (FALSE === file_put_contents($filename, $value)) {
		$this->throw_debugs("System call failure, file_put_contents({$filename}).");
	}
}
public function get_json(string $filename) : mixed { // get json
	if (FALSE === ($json = file_get_contents($filename))) {
		$this->throw_debugs("System call failure, file_get_contents({$filename}).");
	}
	if (NULL === ($json = json_decode($json, TRUE))) {
		$this->throw_debugs("System call failure, json_decode({$filename}).");
	}
	return($json);
}


// end object
}; }

return $_abcms;
}
