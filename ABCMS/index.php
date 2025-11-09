<?php
/*
ABCMS - A Basic Content Management System
The abcms() function defined here and returns the abcms object
What is with all these crazy PHP template engines? PHP is the template
And pal by the way, let's not trust each other, trust Jesus

STRUCTURE
/project-root/composer.json
/project-root/composer.lock
/project-root/ABCMS/.htaccess
/project-root/ABCMS/index.php
/project-root/ABCMS/public/[vendor]/[project]/[public-user-data]
/project-root/src
/project-root/private/[vendor]/[project]/[private-user-data]
/project-root/vendor/*


INSTALLATION
Google this "php composer list all packages of type"

*/



// Run ABCMS
try {
	if (empty(abcms()->_ABCMS['boss'])) { return TRUE; }	// If filename != 'index.php' I do nada, use abcms() as you please
	abcms()->output();										// If filename == 'index.php' I am da boss and do what I please 
}
catch (Exception $e) {
	echo $e->getMessage();									// Report exceptions
}
finally {
	;														// Remove all locks
}
return TRUE;



// Define ABCMS
function abcms() : object {
// Initialize
static $_abcms = NULL;
if (NULL===$_abcms) {
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
	// Validation
	if (PHP_VERSION < '8.2.0') {	throw new Exception("abcms()->__construct() PHP82 or greater is required. Got ".PHP_VERSION); }
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
	$regex = "/\/([[:alnum:]\-._~%]+)=([[:alnum:]\-._~%]+)[\/]*/u";
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
		'urlstrip'		=> '/'.(urldecode(trim(preg_replace($regex,'/',$urlparsed['path']),'/'))),			// URL stripped of variables and urldecoded with no trailing slash unless '/' or urlencoded slash
		'urlquery'		=> (($urlquery = NULL) || parse_str($urlparsed['query'],$urlquery) ?: $urlquery),	// URL query variables for CLI and HTTP, $_GET only HTTP
		'cli'			=> ('cli' === PHP_SAPI ? TRUE : FALSE),												// CLI command line execution
		'argv'			=> $_SERVER['argv'],																// CLI arguments
		'argc'			=> $_SERVER['argc'],																// CLI argument count
		'auto'			=> (file_exists(($auto = (__DIR__ . '/../vendor/autoload.php'))) ? $auto : NULL),	// Auto-loader present
	);
	// Override arrays
	$this->_property	= array(array());
	$this->_method		= array(array());
	$this->_function	= array(array());
	// autoload
	if ($this->_ABCMS['auto']) { require_once($this->_ABCMS['auto']); }	// autoload
}
// Dynamic properties not allowed
public function __set(string $name, $value) {
	throw new Exception("abcms()->__set() dynamic property creation not allowed.");
}
// Auto associate with calling module
public function module() : string {
	$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
	if (empty($backtrace[0]['filename']) ||
		NULL === ($module = preg_replace("#^{$this->_ABCMS['documentroot']}#", '', $backtrace[0]['filename'], 1, $count)) ||
		$count != 1) {
		throw new Exception("abcms()->module() backtrace is missing.");
	}
	return dirname($module);
}
// Set psuedo property
public function set_property(string $name, $value=NULL) : mixed {
	if(empty($name)) { throw new Exception("abcms()->set_property('$name') property name invalid."); }
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
	if(!function_exists($name)) { throw new Exception("abcms()->set_method('$name') method name invalid."); }
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

public function get_override_include(		string $original, mixed $priority_allowed=NULL) : ?bool { return NULL; } // rank (by module or request)
public function get_override_include_once(	string $original, mixed $priority_allowed=NULL) : ?bool { return NULL; } // exclusive(first or highest)
public function get_override_require(		string $original, mixed $priority_allowed=NULL) : ?bool { return NULL; } // rank (by module or request)
public function get_override_require_once(	string $original, mixed $priority_allowed=NULL) : ?bool { return NULL; } // exclusive(first or highest)
public function get_override_return(		string $return,   mixed $priority_allowed=NULL) : ?bool { return NULL; } // exclusive(first or highest) or rank (by module or request)

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
public function output(string $include = '') : void {
	// Start buffers
	if (!$this->_ABCMS['cli'] && FALSE===ob_start()) {
		throw new Exception("abcms()->output() ob_start() buffering failed.");
	}
	// Output loop
	while(( $result = $this->get_override_include($include) )) ;
	// Default if nothing
	if (empty($include) || NULL===$result) {
		if ($this->_ABCMS['cli']) {
			echo "\nNothing for me to do.\n\n";
			print_r($this->_ABCMS);
			print_r($_GET);
		}
		else if ('/settings'===$this->_ABCMS['purl']['path']) {
			$this->settings();
		}
		else {
			$this->welcome();
		}
	}
	// Sanitize and echo buffers
	if (!$this->_ABCMS['cli']) {
		if (FALSE === ($output = ob_get_clean())) {
			throw new Exception("abcms()->output() ob_get_clearn() buffering failed.");
		}
		echo $this->sanitize($output);
	}
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

// Default settings
private function settings(string $path = NULL) : void {
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

// end object
}; }

return $_abcms;
}


// convenience GLOBALS
function abcms_noop(...$args) : mixed { return NULL; }
