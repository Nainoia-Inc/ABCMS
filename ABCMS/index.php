<?php
/*
ABCMS - A Basic Content Management System

The abcms() function returns the abcms object with its properties and methods.
When named 'index.php' I am the boss and will include index.* found at ../folder(s) according to my rules.
When named !'index.php' I do not do anything and you are the boss and can use abcms() according to your rules.
And let's not trust each other or share data constructs, but I will process all your output.
*/



try { // Try catch
	// Initialize
	if (empty(abcms()->_ABCMS['boss'])) { return TRUE; }
	// Output
	while (abcms()->output());
}
catch (Exception $e) { // Try exception
	echo $e->getMessage();
}
finally { // Try always
}
return TRUE;


// ABCMS
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
	// Check requirements
	if (PHP_VERSION<'8.2.0') { throw new Exception("abcms()->__construct() PHP82 or greater is required. Got ".PHP_VERSION); }
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
	$this->_ABCMS	= array(
		'boss'	=> ('index.php' === basename(__FILE__) ? TRUE : FALSE),	// if TRUE I include you, otherwise you include me
		'clif'	=> (PHP_SAPI === 'cli'),								// command line execution
		'file'	=> (__FILE__),											// filename
		'dirn'	=> (__DIR__),											// foldername
		'base'	=> (basename(__DIR__)),									// basename
		'furl'	=> $path=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), // full URL
		'purl'	=> parse_url($path)										// parsed: ["scheme"], ["host"], ["path"], ["query"]
	);
	// Override arrays
	$this->_property	= array(array());
	$this->_method		= array(array());
	$this->_function	= array(array());
}
// Dynamic properties not allowed
public function __set(string $name, $value) {
	throw new Exception("abcms()->__set() dynamic property creation not allowed.");
}
// Auto associate with calling module
public function module() : string {
	$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
	if (empty($backtrace[0]['file']) ||
		NULL === ($module = preg_replace("#^{$this->_ABCMS['dirn']}#", '', $backtrace[0]['file'], 1, $count)) ||
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
	if (!$this->_ABCMS['clif'] && FALSE===ob_start()) {
		throw new Exception("abcms()->output() ob_start() buffering failed.");
	}
	// Output loop
	while(( $result = $this->get_override_include($include) )) ;
	// Default if nothing
	if (empty($include) || NULL===$result) {
		if ($this->_ABCMS['clif']) {
			echo "Nothing for me to do.";
		}
		else if ('settings'===$this->_ABCMS['purl']['path']) {
			$this->welcome();
		}
		else {
			$this->welcome();
		}
	}
	// Sanitize and echo buffers
	if (!$this->_ABCMS['clif']) {
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
&nbsp;&nbsp;Hello World!
EOF;
}

// Default settings
private function settings() : void {
echo <<< EOF
<br>
<br>
&nbsp;&nbsp;Filename<br>
<br>
EOF;
$files = array_diff(scandir($this->_ABCMS['purl']['path']), array('.', '..')); // DO ALL
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
