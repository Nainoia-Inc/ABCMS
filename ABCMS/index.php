<?php
/*
ABCMS - A Basic Content Management System
The abcms() function is defined here and returns the abcms object.
What is with all these crazy PHP template engines? People. PHP is the template!
And pal by the way, let's not trust each other, trust Jesus and write good code.

CODE NOTES:
File conversion with "libreoffice"
yum install libreoffice
input and output filters: https://help.libreoffice.org/25.8/en-US/text/shared/guide/convertfilters.html?&DbPAR=SHARED&System=WIN
filter options: https://help.libreoffice.org/25.8/en-US/text/shared/guide/csv_params.html?&DbPAR=SHARED&System=WIN
beware TRUE and FALSE: https://ask.libreoffice.org/t/entering-true-or-false-as-text-into-any-cell-is-interpreted-as-true-or-false/104905
working example: libreoffice --convert-to "csv:Text - txt - csv (StarCalc):9,,UTF-8,1" --infilter="MS Excel 97" --outdir ./ input.xls

*/



/**********
Run ABCMS
***********/
try {
	if (empty(abcms()->_ABCMS['boss'])) { return TRUE; }		// I do nada, use abcms() as you please
	abcms()->output(abcms()->_ABCMS['urlcommand']);				// I am da boss and do what I please 
}
catch (Exception $e) {
	$message = "\nUser: ".$e->getMessage()."\n";
	$coredump =
		"\n\n\n_ABCMS:\n".print_r($this->_ABCMS,TRUE) .
		"\n\n\nGLOBALS:\n".print_r($this->GLOBALS) .
		"\n\n\n_SERVER:\n".print_r($this->_SERVER) .
		"\n\n\n_GET:\n".print_r($this->_GET) .
		"\n\n\n_POST:\n".print_r($this->_POST) .
		"\n\n\n_FILES:\n".print_r($this->_FILES) .
		"\n\n\n_COOKIE:\n".print_r($this->_COOKIE) .
		"\n\n\n_SESSION:\n".print_r($this->_SESSION) .
		"\n\n\n_REQUEST:\n".print_r($this->_REQUEST) .
		"\n\n\n_ENV:\n".print_r($this->_ENV) .
		"\n\n\nStack:\n".print_r(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT));
	$corefile = "../private/nainoiainc/abcms/.coredump";
	set_file($corefile, $$coredump);
	if (($error = error_get_last())) {
	$message .= <<< EOF
Type: {$error['type']}
Mess: {$error['message']}
File: {$error['file']}
Line: {$error['line']}
Dump: {$corefile}

EOF;
	}
	if ($this->_ABCMS['cli']) { echo $message; }				// CLI
	else {						echo nl2br($message, FALSE); }	// HTML
}
// Always
finally {
	;															// Remove all locks
}
return TRUE;



/**********
Define ABCMS function and class / object
**********/
function abcms() : object {
// Initialize
static $_abcms = NULL;
if (NULL===$_abcms) {



// New class
$_abcms = new class {
// Properties
public readonly array $GLOBALS;
public readonly array $_SERVER;
public readonly array $_GET;
public readonly array $_POST;
public readonly array $_FILES;
public readonly array $_COOKIE;
public readonly array $_SESSION;
public readonly array $_REQUEST;
public readonly array $_ENV;
public readonly array $_ABCMS;
private array $_property;
private array $_method;
private array $_function;
// Construct
public function __construct() {
	// Validate system
	if (PHP_VERSION < '8.2.0') {
		throw new Exception("Invalid configuration, PHP82 or greater is required.");
	}
	// Protect GLOBALS
	$this->GLOBALS	= isset($GLOBALS)	? $GLOBALS	: array();
	$this->_SERVER	= isset($_SERVER)	? $_SERVER	: array();
	$this->_GET		= isset($_GET)		? $_GET		: array();
	$this->_POST	= isset($_POST)		? $_POST	: array();
	$this->_FILES	= isset($_FILES)	? $_FILES	: array();
	$this->_COOKIE	= isset($_COOKIE)	? $_COOKIE	: array();
	$this->_SESSION	= isset($_SESSION)	? $_SESSION	: array();
	$this->_REQUEST	= isset($_REQUEST)	? $_REQUEST	: array();
	$this->_ENV		= isset($_ENV)		? $_ENV		: array();
	// Assign properties
	$regex = "/\/([[:alnum:]\-._~%]+)=([[:alnum:]\-._~%]+)/u";
	$this->_ABCMS	= array(
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
		'settings'		=> $this->get_settings(),															// Load my settings json
	);
	// Validate input
	if (!preg_match("#^{$this->_ABCMS['urlcommand']}#u",urldecode($urlparsed['path']))) {
		throw new Exception("Invalid URL, path variables found before path command in {$this->_ABCMS['urlfull']}");
	}
	if (!TRUE) { // TODO add path variable and query variable check here
		throw new Exception("Invalid URL, unknown path variables and query variables found in {$this->_ABCMS['urlfull']}");
	}
	if (($sorted = $this->_ABCMS['urlvars']) && ksort($sorted) && $sorted !== $this->_ABCMS['urlvars']) {
		throw new Exception("Invalid URL, path variables not alphbetical in {$this->_ABCMS['urlfull']}");
	}
	if (($sorted = $this->_ABCMS['urlquery']) && ksort($sorted) && $sorted !== $this->_ABCMS['urlquery']) {
		throw new Exception("Invalid URL, query variables not alphbetical in {$this->_ABCMS['urlfull']}");
	}
	// Override arrays
	$this->_property	= array(array());
	$this->_method		= array(array());
	$this->_function	= array(array());
	// autoload
	if ($this->_ABCMS['auto']) { require_once($this->_ABCMS['auto']); }	// autoload
}
// Dynamic properties not allowed
public function __set(string $name, $value) {
	throw new Exception("Invalid programming, dynamic property creation not allowed for name = {$name}");
}
// Auto associate with calling module
public function module() : string {
	$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
	if (empty($backtrace[0]['filename']) ||
		NULL === ($module = preg_replace("#^{$this->_ABCMS['documentroot']}#", '', $backtrace[0]['filename'], 1, $count)) ||
		$count != 1) {
		throw new Exception("Invalid configuration, backtrace is missing.");
	}
	return dirname($module);
}
// Set psuedo property
public function set_property(string $name, $value=NULL) : mixed {
	if(empty($name)) { throw new Exception("Invalid programming, empty name invalid."); }
	$this->_property[$this->module()][$name] = $value;
	return $value;
}
// Get pseudo property
public function get_property(string $module, string $name) : mixed  {
	if (!isset($this->_property[$module][$name])) { return NULL; } // graceful fail when check if set
	return($this->_property[$module][$name]);
}
// Set pseudo method
public function set_method(string $name) : callable {
	if(!function_exists($name)) { throw new Exception("Invalid code, method name invalid for name = {$name}"); }
	$this->_method[$this->module()][$name] = $name;
	return $name;
}
// Get pseudo method
public function get_method(string $module, string $name) : ?callable  {
	if (!isset($this->_method[$module][$name])) { return 'abcms_noop'; }
	return($this->_method[$module][$name]);
}
// Set function override
public function set_override_function(string $include, int $priority_requested, string $function_replace, string $function_new, string $parameter1) : bool {
	$this->_function[$this->module()][$function_replace][$parameter1][] = array(
		'include'	=> $include,
		'priority'	=> $priority_requested,
		'function'	=> $function_new,
	);
	return TRUE;
}
// Get function override
public function get_override_function(int $priority_allowed, string $function, string $parameter1, ...$parameters) : mixed {
	$found = FALSE;
	foreach($this->_function[$this->module()][$function_replace][$parameter1] as $possible) {
		if (!$found || $possible['priority'] < $found['priority']) { $found = $possible; }
	}
	if ($found) {
		include($found['include']);
		if(function_exists($found['function'])) { return($found['function']($parameter1, ...$parameters)); }
	}
	return($function($parameter1, ...$parameters));
}

public function set_override_include(		string $module, string $original, string $override, int $mixed=NULL) : ?bool { return TRUE; }
public function set_override_include_once(	string $module, string $original, string $override, int $mixed=NULL) : ?bool { return TRUE; }
public function set_override_require(		string $module, string $original, string $override, int $mixed=NULL) : ?bool { return TRUE; }
public function set_override_require_once(	string $module, string $original, string $override, int $mixed=NULL) : ?bool { return TRUE; }
public function set_override_return(		string $module, string $function, string $include,  int $mixed=NULL) : ?bool { return TRUE; }

public function get_override_include(		string $original, mixed $priority_allowed=NULL) : ?bool { return FALSE; } // rank (by module or request)
public function get_override_include_once(	string $original, mixed $priority_allowed=NULL) : ?bool { return FALSE; } // exclusive(first or highest)
public function get_override_require(		string $original, mixed $priority_allowed=NULL) : ?bool { return FALSE; } // rank (by module or request)
public function get_override_require_once(	string $original, mixed $priority_allowed=NULL) : ?bool { return FALSE; } // exclusive(first or highest)
public function get_override_return(		string $return,   mixed $priority_allowed=NULL) : ?bool { return FALSE; } // exclusive(first or highest) or rank (by module or request)

// Non-function to function wrappers
public function hello(string ...$strings) : void {
	for($x=0,$z=count($strings); $x<$z; ++$x){
		echo $strings[$x];
	}
	return;
}
public function print(string $string = NULL) : bool {
	return print($string);
}

// Path functions
public function set_path(string $path = NULL) : ?string {
	return $path;
}
public function get_path(string $path = NULL) : ?string {
	return $path;
}

// Read or build overrides array
private function overrides() : void {
}

// Output loop
public function output(string $command) : void {
	while($this->output_recursion($command)) ;
}
private function output_recursion(string $command) : bool {
	// Start buffers
	if (FALSE===ob_start()) {
		throw new Exception("System call failure, ob_start() with command = {$command}");
	}
	// Router lookup
	static $recursion = 0; ++$recursion;
	if (!empty($this->_ABCMS['settings']['router'][$this->_ABCMS['urlmethod']][$this->_ABCMS['urlcommand']])) {
			$route = array('level' => 1, 'include' => 'include_once', 'filename' => 'ABCMS', 'class' => 'ABCMS', 'function' => 'unknown');
	}
	else if (('GET'===$this->_ABCMS['urlmethod'] || 'POST'===$this->_ABCMS['urlmethod']) && !empty($this->_ABCMS['settings']['router']['GETPOST'][$this->_ABCMS['urlcommand']])) {
			$route = array('level' => 1, 'include' => 'include_once', 'filename' => 'ABCMS', 'class' => 'ABCMS', 'function' => 'unknown');
	}
	else if (!empty($this->_ABCMS['settings']['router'][NULL][$this->_ABCMS['urlcommand']])) {
			$route = array('level' => 1, 'include' => 'include_once', 'filename' => 'ABCMS', 'class' => 'ABCMS', 'function' => 'unknown');
	}
	else {
			$route = array('level' => 1, 'include' => 'include_once', 'filename' => 'ABCMS', 'class' => 'ABCMS', 'function' => 'unknown');
	}
	//get_override_include(		string $original, mixed $priority_allowed=NULL)
	//get_override_include_once(	string $original, mixed $priority_allowed=NULL)
	//get_override_require(		string $original, mixed $priority_allowed=NULL)
	//get_override_require_once(	string $original, mixed $priority_allowed=NULL)
	//get_override_return(		string $return,   mixed $priority_allowed=NULL)
	$result = FALSE;

	// Default if nothing
	if (empty($command) || !$result) {
		if ($this->_ABCMS['cli']) {
			echo "\nNothing for me to do.\n\n";
			print_r($this->_ABCMS);
			print_r($_GET);
		}
		else if ('/settings'===$this->_ABCMS['urlcommand']) {
			$this->browser();
		}
		else {
			$this->welcome();
		}
	}
	// Sanitize and echo buffers
	if (FALSE === ($output = ob_get_clean())) {
		throw new Exception("System call failure, ob_get_clean() with command = {$command}");
	}
	echo $this->sanitize($output);
	// return
	return $result;
}

// Sanitize string
private function sanitize(string $output) : string {
	return $output;
}

// Default welcome
private function welcome() : void {
	echo <<< EOF
<br>
<br>
&nbsp;&nbsp;Hello World!<br>
EOF;

// test auto-loader
if ($this->_ABCMS['auto']) {
	echo <<< EOF
<br>
<br>
&nbsp;&nbsp;ABCMS package!<br>
EOF;
	foreach (($packages = Composer\InstalledVersions::getInstalledPackagesByType('abcms-package')) as $name) { // get 'abcms-package'
		echo "<br>{$name} : " . Composer\InstalledVersions::getInstallPath($name);
	}
}

// dump all
	echo <<< EOF
<br>
<br>
&nbsp;&nbsp;ABCMS object!<br>
EOF;
echo '<pre>';
print_r($this->_ABCMS);
print_r($_GET);
echo '</pre>';
}

// Settings output
private function set_settings() : void {
	// settings
	$settings = array(
		'preference'=> true,
		'router'	=> array(),
		);
	// router
	$settings['router']['GET'		][NULL] = NULL;
	$settings['router']['POST'		][NULL] = NULL;
	$settings['router']['PUT'		][NULL] = NULL;
	$settings['router']['HEAD'		][NULL] = NULL;
	$settings['router']['DELETE'	][NULL] = NULL;
	$settings['router']['PATCH'		][NULL] = NULL;
	$settings['router']['OPTIONS'	][NULL] = NULL;
	$settings['router']['CONNECT'	][NULL] = NULL;
	$settings['router']['TRACE'		][NULL] = NULL;
	$settings['router']['GETPOST'	]['/']			= array('level' => 1, 'include' => 'include_once', 'filename' => 'ABCMS', 'class' => 'ABCMS', 'function' => 'welcome');
	$settings['router']['GETPOST'	]['/Settings']	= array('level' => 1, 'include' => 'include_once', 'filename' => 'ABCMS', 'class' => 'ABCMS', 'function' => 'settings');
	$settings['router']['GETPOST'	]['/Unknown']	= array('level' => 1, 'include' => 'include_once', 'filename' => 'ABCMS', 'class' => 'ABCMS', 'function' => 'unknown');
	// lowercase the command key
	foreach($settings['router'] as &$methods) {
		$newArray = [];
		foreach ($methods as $key => $value) {
			$newKey = mb_strtolower($key, 'UTF-8');
			$newArray[$newKey] = $value;
		}
		$methods = $newArray;
    }
	file_put_contents("ABCMS.json",json_encode($settings,JSON_PRETTY_PRINT));
}
private function browser(string $path = NULL) : void {
	if (NULL===$path) { $path = $this->_ABCMS['project']; }
	$display = "./".preg_replace("/^{$this->_ABCMS['project']}/", "", $path);
	echo <<< EOF
<br>
<br>
&nbsp;&nbsp;Filename: {$display}<br>
<br>
EOF;
	if ("./"!==$display) { echo "&nbsp;&nbsp;..<br>"; }
	$files = array_diff(scandir($path), array('.', '..'));
	foreach($files as $file) {
		echo "&nbsp;&nbsp;".$file."<br>";
	}
}



// set and get file types
// TODO error check that reading and writing in my own directory!!
public function set_file($filename string, $value) : void {
	if (FALSE === file_put_contents($filename, $value)) {
		throw new Exception("System call failure, file_put_contents({$filename}).");
	}
}
public function get_file($filename string) : mixed {
	if (FALSE === ($data = file_get_contents($filename))) {
		throw new Exception("System call failure, file_get_contents({$filename}).");
	}
	return($data);
}

public function set_json($filename string, $value mixed) : void {
	if (FALSE === ($value = json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))) {
		throw new Exception("System call failure, json_encode({$filename}).");
	}
	if (FALSE === file_put_contents($filename, $value)) {
		throw new Exception("System call failure, file_put_contents({$filename}).");
	}
}
public function get_json($filename string) : mixed {
	if (FALSE === ($json = file_get_contents($filename))) {
		throw new Exception("System call failure, file_get_contents({$filename}).");
	}
	if (NULL === ($json = json_decode($json, TRUE))) {
		throw new Exception("System call failure, json_decode({$filename}).");
	}
	return($json);
}



// end object
}; }

return $_abcms;
}


// convenience GLOBALS
function abcms_noop(...$args) : mixed { return NULL; }
