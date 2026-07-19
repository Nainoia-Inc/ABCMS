<?php
/*	SECTION: OVERVIEW

	"A Basic Content Management System" (ABCMS)
	AKA "Aionian Bible Content Management System" @ AionianBible.org
	PHP web developer toolkit and CMS in a single file
	Everything is an extension with the abcms() router
	Install with Composer or run me in a document root
*/



/*	SECTION: EXPLAINED

	1. Constants allow fast global sharing of immutable values
	2. Try Catch for error handling of the core abcms() function
	3. Exception core dump gives developers all debug information
	4. Instantiate and process inputs onetime for global handling
	5. Everthing is a routed extension with $abcms->output()
	6. Data stored in OS cached files fast as shared memory and simpler
	7. SQLite and MYSQL to be considered later for scalability
	8. Session security through opt-in, validation, CSRF, and login

	All output is extendable which helps us think more simply about content management.
	We have inputs, processing, and outputs. The output function serves as both a command
	router and extension manager. The generic output() function does not even require a
	default function because it expects to be extended by you to do something meaningful.
	The ABCMS engine expects you to override the "/nainoiainc/abcms/begin" hook first.
	From there you output what you want and also include your own extendable calls to
	output() yourself. Since file and function locations are passed to the extension
	manager at execution time this model is even faster than Composer lazy loading which
	matches every registered object class with the file location on every call. Lazy
	loading does a lot of work! ABCMS also allows the extension of files, functions,
	methods, objects, and classes, while Composer only allows the extension of classes.

	ABCMS uses PHP as the template engine. PHP is designed to intermingle both HTML and
	procedural function with conditional logic. And PHP is well known so that one does
	not need to learn another language like Symfony Twig or Laravel Blade. Symfony and
	Laravel template engines seem an unneccessary reduction of PHP template power. So
	PHP is the template engine for ABCMS. Frontend developers must understand PHP and
	HTML, but that is simpler and more powerful.
	
	The first version of ABCMS uses files alone for data storage. While SQL and other
	databases allow flexible and fast data storage and retrieval not every website
	application needs this level of data storage complexity. In fact SQL databases often
	encourage data storage complexity with all the possible data storage rows, columns,
	types, and indices. However, if a unit of data is only every accessed as a unit, such
	as a website page, why not store the entire	blob of page data in a single file? The
	page can then be quickly read as a single file rather than many reads of tiny pieces
	of data to build the page. This is better for many applications. An SQL database API
	may be added later for applications that require more complexity.

	Session security strategy breaks convention with a slightly longer session lifetime.
	However, threat is migtigated with the addition of a custom 64 byte security cookie
	name and token value for validating the session along with reasonable inactive and
	maxlifetime session threshholds. There is no "Remember Me" option for longer active
	logins because password lockers make it easier to login anyway. Additional form 
	security is injected into every <form> with a CSRF token, honeypot, void pot,
	image captcha, javascript expected delay, and rapid submission triggers. Finally,
	the $_SESSION is not protected from rogue extensions. So extension are discouraged
	from using $_SESSION, but if needed sequester yourself to $_SESSION[extension-name'].
	Users must be allowed to opt-in or opt-out of session cookies.
*/



/*	SECTION: TODO

	File conversion with "libreoffice" to keep PHP tight.
	yum install libreoffice
	Input and output filters: https://help.libreoffice.org/25.8/en-US/text/shared/guide/convertfilters.html?&DbPAR=SHARED&System=WIN
	Filter options: https://help.libreoffice.org/25.8/en-US/text/shared/guide/csv_params.html?&DbPAR=SHARED&System=WIN
	Beware TRUE and FALSE: https://ask.libreoffice.org/t/entering-true-or-false-as-text-into-any-cell-is-interpreted-as-true-or-false/104905
	Working example: libreoffice --convert-to "csv:Text - txt - csv (StarCalc):9,,UTF-8,1" --infilter="MS Excel 97" --outdir ./ input.xls

	File browser - build myself
	File and text editor?
	Ace Editor: https://ace.c9.io/
	Code Mirror: https://codemirror.net/

	SMTP - PHPMailer or smaller alternative
	https://www.mydreams.cz/en/hosting-wiki/1402-phpmailer-sending-emails-from-php-via-smtp-without-using-composer.html
	OR just use mail() and optionally composer to PHPMailer and check for existence.
*/



/*	SECTION: CONSTANTS
*/
// General
$abcms_constant			= 'constant';
const ABCMS_GOOD		= "<span style='color: green;'>\u{2611}</span> ";
const ABCMS_BAD			= "<span style='color: red;'>\u{2612}</span> ";
// Extensions
const ABCMS_EXT_SELF	= "/nainoiainc/abcms";
const ABCMS_EXT_SETS	= "/abcmsset";
const ABCMS_EXT_ALPHA	= "/begin";
const ABCMS_EXT_BEGIN	= "/nainoiainc/abcms".ABCMS_EXT_ALPHA;
const ABCMS_EXT_PAGE	= "/nainoiainc/abcms/htmldefault_page";
// Regex
// Includefile?function #^(|/vendor/package/filepath)(|?(|classobject(::|->|()->))funcmeth)#
const ABCMS_REGEX_FUNC	= "/^((\/[^?]+)\?)?((([a-zA-Z_\x{7f}-\x{ff}][a-zA-Z0-9_\x{7f}-\x{ff}]*)(::|\->|\(\)\->))?([a-zA-Z_\x{7f}-\x{ff}][a-zA-Z0-9_\x{7f}-\x{ff}]*))?$/u";
const ABCMS_REGEX_HOOK	= "/^\/[^\/]+\/[^\/]+\/[^\/]+$/u"; // Path-like, but not a filepath
const ABCMS_REGEX_METH	= "/(CLI|GET|POST|PUT|HEAD|DELETE|PATCH|OPTIONS|CONNECT|TRACE)/u";
const ABCMS_REGEX_META	= array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE');
const ABCMS_REGEX_PATH	= "/^(\/[^\/]*)(\/.+)?$/u";
const ABCMS_REGEX_URLV	= "/\/([A-Za-z0-9\-_.~]+)=([A-Za-z0-9\-_.~]+)/u";
const ABCMS_REGEX_VARS	= "/^[A-Za-z0-9\-_.~]+$/u";
const ABCMS_REGEX_UUID	= "/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i";
const ABCMS_REGEX_FORM	= "/(<input\s+[^>]*?type\s*=\s*['\"]submit['\"]|<button)(>|\s+[^>]*?>)/ui";
// Arrays
const ABCMS_ARRAY_TYPE	= array('mixed','string','array','integer','float','bool','boolean','email','domain','uri','url','ip','mac','uuid','path');
// Files
const ABCMS_ABCMSLOG	= "../private/nainoiainc/abcms/ABCMS.errorlog";
const ABCMS_COREDUMP	= "../private/nainoiainc/abcms/ABCMS.coredump";
const ABCMS_SESSIONS	= "../private/nainoiainc/abcms/ABCMS.sessions";
const ABCMS_SETTINGS	= "../private/nainoiainc/abcms/ABCMS.settings";
const ABCMS_DATABASE	= "../private/nainoiainc/abcms/ABCMS.database";
// Flags
const ABCMS_FLAG_JSON	= JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
// Session session_start
const ABCMS_SES			= ABCMS_EXT_SELF;
const ABCMS_SES_ROTA	= 60*15;
const ABCMS_SES_IDLE	= 60*60*24*4;
const ABCMS_SES_LIFE	= 60*60*24*11;
const ABCMS_SES_FORM	= 60*60;
const ABCMS_SES_XPIR	= 1;
const ABCMS_SES_DELY	= 3;
const ABCMS_SES_OPEN	= 12;
const ABCMS_SES_SLAP	= 60;
const ABCMS_SES_HITS	= 20;
const ABCMS_SES_TIME	= 20;
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



/*	SECTION: GLOBALS

	Constants above as 'ABCMS_*'
	$abcms_constant = 'constant' = constant() to interpolate constants in strings
	$abcms = $GLOBALS['abcms'] = ABCMS object reference for convenience
	abcms() = return ABCMS object
	abcms_dump() = return unabridged debug infor
*/
 


/*	SECTION: ENTRY

	Run the CMS or coredump if failure
*/
// try ABCMS
try {
	// construct $abcms
	abcms();
	// output controller
	$abcms->output(
		ABCMS_EXT_ALPHA,	// entry extension
		'CLI-GET-POST',		// methods extended
		'abcms->htmldoc',	// default function
		ABCMS_ROLE_PUBLIC,	// minimum role
		1,					// 1 = exclusive allowed
		FALSE,				// default required
		// abcms->htmldoc() arguments
		...$args = array(
			FALSE,			// homepage format
			NULL,			// override css
			NULL,			// override js
			NULL,			// override header
			<<<EOF
<h4>Status</h4>
{$GLOBALS['abcms_constant']('ABCMS_GOOD')}Hello World. Graceful termination.<br>
{$GLOBALS['abcms_constant']('ABCMS_BAD')}Fatal error. Core extension failed.<br>
<br>
Please contact the webmaster for help.
EOF
			,				// override page
			NULL,			// override footer
			1,				// 1 = exclusive allowed
		),
	);
	// coredump requested
	if (!empty($abcms->input['urlquery']['debug'])) {
		abcms_dump(NULL, 'file');
	}
}
// catch exceptions
catch (\Throwable $e) {
	abcms_dump($e, 'html');
}
// clean up
finally {
	; // remove locks
}
// done, function definitions follow
return TRUE;
// coredump output
function abcms_dump(
	\Throwable $e = NULL, // error thrown
	string $display = 'html', // output format
) : void {
	// initialize
	global $abcms;
	$live		= (isset($abcms->input['auto']) ? TRUE : FALSE);
	$exception	= (isset($e) ? (htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') ?: 'Unknown exception.') : 'Intentional debugging.');
	$system		= (error_get_last() ?? array('message' => 'NA'));
	$composer	= array();
	if ($live && abcms()->input['auto']) {
		foreach (Composer\InstalledVersions::getInstalledPackagesByType('abcms-extension') as $name) {
			$composer[$name] = Composer\InstalledVersions::getInstallPath($name);
		}
	}
	// HTML
	if ('html' == $display) {
		echo ob_get_clean() ?: "<br> My apologies. The CMS object is unavailable. <a href='/'>Try again</a>.";
		echo <<< EOF
<dialog open style='position: absolute; inset: 0; margin: auto; width: 350px; height: 150px; background-color: red; color: white; text-align: center; padding: 7px;'>
I am terribly sorry.<br>
{$exception}<br>
Please inform webmaster of this coredump.<br>
<br>
<form method="dialog"><button>Resume</button></form>
</dialog>
EOF;
	}
	// log
	ini_set('error_log', ABCMS_ABCMSLOG);
	error_log("ABCMS->COREDUMP()\n" . print_r(array('COREDUMP_EXCEPTION' => $exception, 'COREDUMP_SYSTEM' => $system), TRUE));
	// corefile
	file_put_contents(
		ABCMS_COREDUMP,
		print_r(array(
			'COREDUMP_EXCEPTION'=> $exception,
			'COREDUMP_SYSTEM'	=> $system,
			'COREDUMP_ABCMS'	=> ($live ? $abcms : 'NA'),
			'COREDUMP_GLOBALS'	=> $GLOBALS,
			'COREDUMP_COMPOSER' => $composer,
		), TRUE),
	);
	// page
	if ('page' == $display) {
		echo '<pre>';
		readfile(ABCMS_COREDUMP);
		echo '</pre>';
	}
}



/*	SECTION: CORE
*/
// abcms() function returns $abcms object
function abcms() : object {
// create once
static $_abcms = NULL;
if (NULL === $_abcms) {
// $abcms already set
if (isset($GLOBALS['abcms'])) {						throw new Exception("Core already defined."); }
// $abcms object assigned
$GLOBALS['abcms'] = $_abcms = new class {
readonly	array	$GLOBALS;				// readonly $GLOBALS copy
readonly	array	$boots;					// bootstrap input before session
readonly	array	$input;					// sanitized input after session
readonly	array	$settings;				// read application settings
private		array	$compiles	= array();	// compile application settings
private		array	$database	= array();	// database
private		array	$errors		= array();	// runtime errors
private		array	$debugs		= array();	// runtime debugs
private		bool	$formvalid	= FALSE;	// form tested valid
private		bool	$formhuman	= FALSE;	// form tested human
// Construct object
function __construct() {
	// WSOD errors
	if (PHP_VERSION < '8.2.0') {					$this->error_wsod("Script version must be >= 8.2.0."); }
	if (!chdir(__DIR__)) {							$this->error_wsod("Working directory not found."); }
	if (!ini_set('error_log', ABCMS_ABCMSLOG)) {	$this->error_wsod("Error location not found."); }
	if (FALSE === $this->set_settings(TRUE)){		$this->error_wsod("Application settings not found."); }
	if (isset($_SESSION)) {							$this->error_wsod("Session is already started."); }
	// bootstrap for session_start(), then session user validates inputs
	$this->boots = array(
		// current time()
		'time' => time(),
		// user identity
		'uagent' => (($_SERVER['REMOTE_ADDR']??'')?:'unknown').(($_SERVER['HTTP_USER_AGENT']??'')?:'unknown'),
		// URL full
		'urlfull' => ($urlfull =
			// CLI domain
			('cli' === PHP_SAPI ? ('https://localhost' . 
			// CLI URI validation or default
			($_SERVER['argc']>1 && '/' === $_SERVER['argv'][1][0] && FALSE !== filter_var('http://localhost' . $_SERVER['argv'][1], FILTER_VALIDATE_URL) ? $_SERVER['argv'][1] : '/abcms/help')) :
			// HTTP secure
			((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') .
			// HTTP domain validation including multibyte to punycode
			(isset($_SERVER['HTTP_HOST']) && FALSE!==filter_var(idn_to_ascii($_SERVER['HTTP_HOST'], IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46),FILTER_VALIDATE_DOMAIN) ? $_SERVER['HTTP_HOST'] : 'unknown') .
			// HTTP URI validation, ascii only
			(isset($_SERVER['REQUEST_URI']) && mb_check_encoding($_SERVER['REQUEST_URI'],'ASCII') && FALSE!==filter_var('http://localhost'.$_SERVER['REQUEST_URI'],FILTER_VALIDATE_URL) ? $_SERVER['REQUEST_URI'] : '/unknown')))),
		// URL parse
		'urlparsed' => ($urlparsed = parse_url($urlfull)),
		// URL domain
		'urldomain' => (mb_strtolower($urlparsed['host'], 'UTF-8')),
		// URL request method
		'urlmethod' => ('cli' === PHP_SAPI ? 'CLI' : ((empty($_SERVER['REQUEST_METHOD']) || !in_array($_SERVER['REQUEST_METHOD'], ABCMS_REGEX_META)) ? 'GET' : $_SERVER['REQUEST_METHOD'])),
	);
	// lazy session start
	$session = $this->session_start(0);
	// copy $GLOBAL with session, if possible
	$this->GLOBALS	= $GLOBALS;
	// sanitize inputs with session user
	$this->input = array(
		// session result
		'session' => $session,
		// my user
		'user' => $_SESSION[ABCMS_SES]['user']??NULL,
		// my role
		'role' => ($role = ('cli' === PHP_SAPI ? ABCMS_ROLE_CLI : $_SESSION[ABCMS_SES]['user']['role']??ABCMS_ROLE_PUBLIC)),
		// URL path variables 'v'
		'urlvars' => (!preg_match_all(ABCMS_REGEX_URLV, $urlparsed['path'], $matches, PREG_PATTERN_ORDER) ?
			// None
			array() :
			// Validate variables
			$this->input_valid('v', array_combine(array_map('urldecode', $matches[1]), array_map('urldecode', $matches[2])), $role)),
		// URL stripped of variables, no trailing slash
		'urlstripped' => ($urlstripped = '/'.(trim(preg_replace(ABCMS_REGEX_URLV, '/', $urlparsed['path']), '/'))),
		// URL urldecoded
		'urlpathall' => (urldecode($urlstripped)),
		// URL first segment for primary router
		'urlpathone' => (urldecode((!($ret = preg_match(ABCMS_REGEX_PATH, $urlstripped, $matches)) ? '/' : $matches[1]))),
		// URL second plus segments for secondary router
		'urlpathext' => (urldecode((!$ret || empty($matches[2]) ? '/' : $matches[2]))),
		// URL query variables 'q' from parse_str() because CLI has no $_GET
		'urlquery' => ($this->input_valid('q', ((!empty($urlparsed['query']) && mb_parse_str($urlparsed['query'], $result)) ? $result : array()), $role)),
		// POST variables 'p'
		'postvars' => array(), // ($this->input_valid('p', $_POST, $role)),
		// CLI command line execution
		'cli' => ('cli' === PHP_SAPI ? TRUE : FALSE),
		// CLI argument count
		'argc' => $_SERVER['argc'],
		// CLI arguments
		'argv' => $_SERVER['argv'],
		// Auto-Loader
		'auto' => $this->settings['core']['auto'],
	);
	// require composer
	if ($this->input['auto']) { require_once($this->input['auto']); }
	// variables within path
	if (0 !== stripos($urlparsed['path'], $this->input['urlstripped'])) { $this->set_errors("URL questioned, variables within path"); }
	// Done
	return;
}
// Disallowed methods
public function __set(string $name, mixed $value) : void { $this->error_wsod("Dynamic properties disallowed."); }
public function __clone() { $this->error_wsod("Cloning object disallowed."); }
// Define path variable
public function input_varpath(
	string	$var,			// Allowed path variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role
	?array	$reg = NULL,	// Regex validation
) : void {
	$this->input_variable('v', $var, $type, $role, $reg);
	return;
}
// Define _GET variable
public function input_varget(
	string	$var,			// Allowed query variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role
	?array	$reg = NULL,	// Regex validation
) : void {
	$this->input_variable('q', $var, $type, $role, $reg);	
	return;
}
// Define _POST variable
public function input_varput(
	string	$var,			// Allowed post variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role
	?array	$reg = NULL,	// Regex validation
) : void {
	$this->input_variable('p', $var, $type, $role, $reg);	
	return;
}
// Register variable
private function input_variable(
	string	$cat,			// Category
	string	$var,			// Variable name
	string	$type,			// Allowed type
	int		$role,			// Minimum role
	?array	$reg = NULL,	// Regex validation
) : void {
	if (!preg_match(ABCMS_REGEX_VARS, $var) ||
		!empty($this->compiles[$cat][$var]) ||
		!in_array($type, ABCMS_ARRAY_TYPE) ||
		!in_array($role, ABCMS_ROLE_SET)) {
		$this->error_log("Invalid or duplicate variable.");
		return;
	}
	$this->compiles[$cat][$var] = array('type'=>$type, 'role'=>$role, 'reg'=>$reg);
	return;
}
// Validate path/get/post variable
private function input_valid(
	string	$cat,	// Category
	array	$vars,	// Path/get/post variable
	int		$role,	// User role
) : array {
	// Loop variables
	$last = NULL;
	foreach($vars as $var => $val) {
		// Expected alphabetical
		if ($var < $last) {									$this->set_errors("URL variables not alphabetical as expected"); }
		$last = $var;
		// Ignore undefined
		if (empty($this->settings[$cat][$var]['type'])) {	$this->set_errors("Ignoring undefined URL variable, '{$var}'");						unset($vars[$var]);	continue; }
		// Insufficient permission
		if ($role < $this->settings[$cat][$var]['role']) {	$this->set_errors("Insufficient permission for URL variable, '{$var}'");			unset($vars[$var]);	continue; }
		// NULL special case
		if ('null' == mb_strtolower($val)) {																									$vars[$var] = NULL;	continue; }
		// Switch possibilities
		switch($this->settings[$cat][$var]['type']) {
			case 'array'	:	$vars[$var] = explode(',', $val);																									continue 2;
			case 'bool'		:
			case 'boolean'	:	if (NULL  === filter_var($val, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)) {									break; }			continue 2;
			case 'domain'	:	if (FALSE === filter_var(idn_to_ascii($val, IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_DOMAIN)) {	break; }			continue 2;
			case 'email'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_EMAIL)) {														break; }			continue 2;
			case 'float'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_FLOAT)) {														break; }			continue 2;
			case 'integer'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_INT)) {															break; }			continue 2;
			case 'ip'		:	if (FALSE === filter_var($val, FILTER_VALIDATE_IP)) {															break; }			continue 2;
			case 'mac'		:	if (FALSE === filter_var($val, FILTER_VALIDATE_MAC)) {															break; }			continue 2;
			case 'mixed'	:
			case 'string'	:																																		continue 2;
			case 'path'		:	if ('/' !== $val[0] || FALSE === filter_var('http://localhost'.$val, FILTER_VALIDATE_URL)) {					break; }			continue 2;
			case 'uri'		:	if (!mb_check_encoding($val, 'ASCII') && FALSE === filter_var('http://localhost'.$val, FILTER_VALIDATE_URL)) {	break; }			continue 2;
			case 'url'		:	if (!mb_check_encoding($val, 'ASCII') && FALSE === filter_var($val, FILTER_VALIDATE_URL)) {						break; }			continue 2;
			case 'uuid'		:	if (!preg_match(ABCMS_REGEX_UUID, $val)) {																		break; }			continue 2;			
			// Variable found, but undefined type registered by input_variable()
			default:			$this->error_wsod("Undefined URL variable type, '{$this->settings[$cat][$var]['type']}'");
		}
		// Variable name and type found, but value is invalid
		$this->set_errors("Ignoring invalid URL variable, '{$this->settings[$cat][$var]['type']}' = '{$var}'");
		unset($vars[$var]);
	}
	return $vars;
}
// Which extension called function that called me?
private function extension() : string {
	// Omit object and args, 2 levels
	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
	// No trace
	if (empty($trace[1]['file'])) { $this->error_wsod("Backtrace result unavailable."); }
	// Called myself
	else if ($trace[1]['file'] === (__FILE__)) { return ABCMS_EXT_SELF; }
	// Valid extension
	else if (preg_match("|^".dirname(__DIR__)."/private(/[^/]+/[^/]+)|u", $trace[1]['file'], $match) && !empty($match[1])) { return $match[1]; }
	// Invalid extension
	$this->error_wsod("Extension not found.");
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
			(!empty($hooky['eq'][$this->input['urlpathall']]) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->input['urlpathall']]]) ?
			 $hooky['ex'][$hooky['eq'][$this->input['urlpathall']]] : // Full path
			('/' !== $this->input['urlpathone'] &&
			 !empty($hooky['eq'][$this->input['urlpathone'].'/']) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->input['urlpathone'].'/']]) ?
			 $hooky['ex'][$hooky['eq'][$this->input['urlpathone'].'/']] : // OR path segment.'/'
			 array())), // OR nothing
			(!empty($hooky['eq']['']) && !empty($hooky['ex'][$hooky['eq']['']])	? $hooky['ex'][$hooky['eq']['']] : array()), // AND empty path
			(!empty($hooky['ex'][''])											? $hooky['ex'][''] : array())); // AND empty name
		if (isset($ext['I'])) {	usort($ext['I'], function($a, $b) { return (($ret=(isset($a['ctl']['U'])===isset($b['ctl']['U']) ? 0 : (isset($a['ctl']['U']) ? -1 : 1))) ? $ret : $a['ord'] <=> $b['ord']); } ); }
		if (isset($ext['O'])) {	usort($ext['O'], function($a, $b) { return (($ret=(isset($a['ctl']['U'])===isset($b['ctl']['U']) ? 0 : (isset($a['ctl']['U']) ? -1 : 1))) ? $ret : $a['ord'] <=> $b['ord']); } ); }
	}
	$this->error_log("DEBUG\n".print_r($ext,TRUE), TRUE); // Debug info
	// Execute
	$exin = $exout = NULL; // Exclusive winner or non-exclusive
	$dopt = TRUE; // default optional
	foreach($ext['I'] as $extin) { // Input extensions by priority
		if (!$this->output_doit($extin, $whoami, $flag, ($must || $dopt), $exin)) { continue; } // Skip for reasons
		if (!$must && $extin['ord'] < 0 && !isset($extin['ctl']['D'])) { $dopt = FALSE; } // Omit default if hook and one extension says not required
		if (isset($extin['arg'])) { $this->array_walk_merge($args, $extin['arg']); } // Extend arguments
		if (empty($extin['fun'])) { continue; } // Extension only grabs exclusivity or set args
		do { // Repeat hook extension until FALSE -OR- NULL
			if (FALSE === ob_start()) { $this->error_wsod("Buffer start failure."); } // Buffer output
			$more = $this->output_call($whoami, $extin['fun'], ...$args); // Execute hook extension
			if (FALSE === ($out = ob_get_clean())) { $this->error_wsod("Buffer clean failure."); } // Retrieve buffer
			// Output filter extensions by priority
			foreach($ext['O'] as $extout) {
				if (!$this->output_doit($extout, $whoami, $flag, TRUE, $exout)) { continue; } // Skip for reasons
				$this->output_call($whoami, $extout['fun'], $out, ...$args); // Execute output filter
			}
			// ABCMS security output filter and injection, <FORM> security, and XSS checks, etc.
			if (ABCMS_EXT_BEGIN == $hook) {
				$this->html_security($out);
				$this->html_debug($out); // TEMP CODE
			}
			echo $out; // Echo filtered output
		} while ($more); // Repeat hook extension until FALSE
		if (isset($extin['ctl']['U'])) { break; } // Uno extension allowed
	}
	//return $arguments;
	return $args;
}
// Output settings
public function settings_assign(
	array	&$set,		// Default settings
) : array {
	return(($this->compiles[ABCMS_EXT_SETS][$this->extension()] = $this->output(ABCMS_EXT_SETS, '', '', ABCMS_ROLE_ADMINS, 0, FALSE, ...$set)));
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
	foreach($this->compiles['route'] as $hook => $route) {
		if (preg_match("#".ABCMS_EXT_SETS."$#", $hook)) { unset($this->compiles['route'][$hook]); }
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
	if (!empty($ext['met']) && FALSE === stripos($ext['met'], $this->boots['urlmethod'])) { 		return FALSE; }	// HTTP method
	if ($flag < 0 && $whoami !== $ext['who']) { $this->error_log("Extender not self.");				return FALSE; }	// Extender match
	if (!$flag && isset($ext['ctl']['E'])) {														return FALSE; }	// Non-exclusive, cancel request
	// Exclusive winner or non-exclusive
	if ($flag > 0) {
		if (NULL === $excl) { $excl = (isset($ext['ctl']['E']) ? $ext['who'] : FALSE); }							// Determine exclusive winner or non-exclusive
		if (!$excl && isset($ext['ctl']['E'])) {													return FALSE; }	// Non-exclusive, cancel request
		if ($excl && $ext['who'] !== $excl) {														return FALSE; }	// Exclusive, but not winner
	}
	if ($this->input['role'] < $ext['rol']) { $this->set_errors("No permission to resource, {$ext['fun']}.");		return FALSE; }	// No permision
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
	if (!preg_match(ABCMS_REGEX_FUNC, $filefunc, $match)) { $this->error_wsod("Calling invalid function name."); }
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
				if (!class_exists($classobject) || !method_exists($classobject, $funcmeth)) { $this->error_wsod("Calling invalid class method."); }
				$result = (bool)$classobject::$funcmeth(...$args); // Execute
			}
			else { // Non-class methods
				if ("->" === $operator) { // Instance or object operator
					if (!isset($GLOBALS[$classobject]) || !is_object($GLOBALS[$classobject])) { $this->error_wsod("Calling invalid object."); }
					$newobject = $GLOBALS[$classobject];
				}
				else if ("()->" === $operator) { // Function returned object operator
					if (!function_exists($classobject)) { $this->error_wsod("Calling invalid function to object."); }
					if (!is_object(($newobject = $classobject()))) { $this->error_wsod("Calling invalid function object."); }
				}
				else { $this->error_wsod("Calling invalid operator."); }
				// Execute function/method
				if (!method_exists($newobject, $funcmeth)) { $this->error_wsod("Calling invalid object method."); }
				global $abcms; // Global pointer to 'abcms' object 
				if (ABCMS_EXT_SELF != $whoami && $newobject === $abcms) { // Disallow abcms() privates unless extension is ABCMS
					$reflection = new ReflectionClass($this);
					if ($reflection->getMethod($funcmeth)->isPrivate()) { $this->error_wsod("Calling private method disallowed."); }
				}
				$result = (bool)$newobject->$funcmeth(...$args); // Execute
			}
		}
		else {
			if (!function_exists($funcmeth)) { $this->error_wsod("Calling invalid function."); }
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
		$this->error_log("Invalid extension.");
		return FALSE;
	}
	// Extension assigned
	unset($ctl['I']);
	$this->compiles['route'][$hok]['ex'][$ext][(isset($ctl['O']) ? 'O' : 'I')][] = array(
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
		isset($this->compiles['route'][$hook]['eq'][$path])) { // Not duplicate
		$this->error_log("Invalid or duplicate hook extension path.");
		return FALSE;
	}
	// Equate path assigned
	$this->compiles['route'][$hook]['eq'][$path] = $ext;
	return TRUE;
}



/*	SECTION: DEBUG/ERRORS/LOGS
*/
// Backtrace simplified
private function error_trace() : array {
	// Omit object, include args, 3 levels back
	$back = debug_backtrace(0, 3);
	$function = (empty($back[1]['function']) ? 'unknown' : $back[1]['function']);
	$args = (empty($back[2]['args']) ? array('unknown') : $back[2]['args']);
	// Truncate long strings
	array_walk_recursive($args, function (&$value) {
		if (is_string($value) && mb_strlen($value) > 256) {
			$value = mb_substr($value, 0, 256) . '...';
		}
	});
	return [$function, $args];
}
// Throw exception
public function error_wsod(
	string $mess,
) : void {
	[$function, $args] = $this->error_trace();
	error_log("{$function}->error_wsod() {$mess}\n".print_r($args,TRUE));
	throw new Exception($mess);
	return;
}
// Log error
public function error_log(
	string	$mess,
	bool	$debug = FALSE,
) : void {
	if ($debug && empty($this->input['urlquery']['debug'])) { return; }
	[$function, $args] = $this->error_trace();
	error_log(($mess = "{$function}->error_log() {$mess}\n".print_r($args,TRUE)));
	if (!empty($this->input['urlquery']['debug'])) { $this->debugs[] = $mess; }
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
public function error_get_last() : ?string {// Get last error message
	$error = error_get_last();
	return ($error ? "{$error['message']} [type={$error['type']}] in {$error['file']} on line {$error['line']}" : NULL);
}

public function see_errors() : ?string {	// Format private errors for public
	if (!empty($this->errors)) { return '<br><br>Errors:<br>'.implode('<br>',$this->errors); }
	return NULL;
}	
	
public function get_settings() : array {	// Get private settings for public
	return $this->settings;
}




/*	SECTION: AUTHENTICATION / SESSION
*/
public function session_start(
	int $cmd,	// 1=start, -1=destroy, 0=conditional
) : bool {		// TRUE=started, FALSE=destroyed
	// initialize options
	$session_active = (session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE);
	$valid = NULL;
	$slap = 0;
	static $now = NULL;
	static $posthandled = FALSE;
	static $options = NULL;
	if (NULL === $options) {
		$now = $this->boots['time'];
		$options = [
			'save_path'			=> $this->settings['core']['session_folder'],	// or .htaccess: php_value session.save_path '/path'
			'name'				=> $this->settings['core']['session_cookie'],	// custom name
			'save_handler'		=> 'files',										// session files
			'gc_probability'	=> '1',											// garbage collection
			'gc_divisor'		=> '100',										// garbage collection
			'gc_maxlifetime'	=> ABCMS_SES_LIFE,								// garbage collection
			'cookie_lifetime'	=> ABCMS_SES_LIFE,								// cookie lifetime
			'cookie_path'		=> '/',											// whole domain
			'cookie_domain'		=> $this->boots['urldomain'],					// current sub.domain only
			'cookie_secure'		=> '1',											// HTTPS only
			'cookie_httponly'	=> '1',											// No JS
			'cookie_samesite'	=> 'Strict',									// No cross-site
			'use_strict_mode'	=> '1',											// Reject unknown SIDs
			'use_cookies'		=> '1',											// No SID in URL
			'use_only_cookies'	=> '1',											// No SID in URL
			'use_trans_sid'		=> '0',											// Disable URL rewriting
			];
	}
	// destroy session
	if ($cmd < 0) { $error = 'You are logged out.'; goto DOIT; }
	// already started
	if ($session_active) { return TRUE; }
	// already headers
	if (headers_sent()) { $this->error_wsod("Session start failed, headers already sent.");	}
	// conditional start, post validation first time only
	$post = ('POST' === $this->boots['urlmethod'] && !$posthandled ? TRUE : FALSE);
	if (0 === $cmd &&													// conditional
		!isset($_COOKIE[$this->settings['core']['session_logins']]) &&	// no login cookie
		!$post) {														// no $_POST
		return FALSE;
	}
	// start session
	if (!session_start($options)) { $this->error_wsod("Session start failed.");	}
	// initialize flags
	$session_active = TRUE;
	$_COOKIE[$options['name']] = session_id(); 
	$uagent = $error = $formhuman = NULL;
	$csrf = ($post && !empty($_POST['csrf']) ? $_POST['csrf'] : '');
	if (!empty($_SESSION[ABCMS_SES]['valid'])) { $valid = &$_SESSION[ABCMS_SES]['valid']; }
	// validate session
	if (!$valid) {
		// POST requires CSRF session
		if ($post) {																											$error = 'Session ended, POST requires session.';	$slap = 400; }
	}
	else {
		// rapid hit counter
		$got20 = FALSE; $valid['counts'][] = $now; if (count($valid['counts']) > ABCMS_SES_HITS) { array_shift($valid['counts']); $got20 = TRUE; }
		// uagent inconsistent
		if ($valid['uagent'] !== ($uagent = $this->get_hash($this->boots['uagent']))) {											$error = 'Session ended, IP/Agent unknown.';		$slap = 400; }
		// secrets differ
		else if (!hash_equals($valid['secret'], ($_COOKIE[$valid['cookie']]??'x'))) {											$error = 'Session ended, secrets differ.';			$slap = 400; }
		// rapid hits
		else if ($got20 && $valid['counts'][ABCMS_SES_HITS-1] - $valid['counts'][0] < ABCMS_SES_TIME) {							$error = 'Session ended, rapid hits.';				$slap = 429; }
		// POST CSRF1 missing
		else if ($post && !$csrf) {																								$error = 'Session ended, CSRF1 missing.';			$slap = 400; }
		// POST CSRF1 not equal, end session, but don't slap, could be 2nd submit of multi-form page
		else if ($post && !hash_equals(($_SESSION[ABCMS_SES]['form'][$csrf]['csrf_valu']??''), $csrf)) {						$error = 'Session ended, CSRF1 wrong.'; }
		// POST CSRF2 not equal
		else if ($csrf && !hash_equals(($_SESSION[ABCMS_SES]['form'][$csrf]['csrf_valu']??''),
			(($_POST[$_SESSION[ABCMS_SES]['form'][$csrf]['csrf_name']]??'x')?:'x'))) {											$error = 'Session ended, CSRF2 wrong.';				$slap = 400; }
		// POST VOID populated
		else if ($csrf && !empty($_POST[$_SESSION[ABCMS_SES]['form'][$csrf]['void_name']])) {									$error = "Session ended, CAPTCHA1 wrong.";			$slap = 400; }
		// POST FULL differs
		else if ($csrf && !hash_equals(($_SESSION[ABCMS_SES]['form'][$csrf]['full_valu']??''),
			(($_POST[$_SESSION[ABCMS_SES]['form'][$csrf]['full_name']]??'x')?:'x'))) {											$error = 'Session ended, CAPTCHA2 wrong.';			$slap = 400; }
		// POST too rapid
		else if ($csrf && ($now - ($_SESSION[ABCMS_SES]['form'][$csrf]['mark_time']??$now)) < ABCMS_SES_DELY) {					$error = "Session ended, rapid submission.";		$slap = 400; }
		// login failed, end session, but don't slap
		else if (isset($_COOKIE[$this->settings['core']['session_logins']]) &&
			(($_COOKIE[$this->settings['core']['session_logins']]?:'x') !== ($_SESSION[ABCMS_SES]['session_logins']??'') ||
			empty($_SESSION[ABCMS_SES]['user']) ||
			// reload user for every page load to confirm permissions
			!($_SESSION[ABCMS_SES]['user'] = $this->get_database('user', $_SESSION[ABCMS_SES]['user']['userid'])))) {			$error = 'Session ended, login failed.'; }
		// login expired
		else if (!isset($_COOKIE[$this->settings['core']['session_logins']]) && !empty($_SESSION[ABCMS_SES]['user'])) {			$error = 'Session ended, login expired.'; }
		// max idle time exceeded
		else if ($now > ($valid['active'] + ABCMS_SES_IDLE)) {																	$error = 'Session ended, inactivity threshold.'; }
		// max time exceeded
		else if ($now > ($valid['create'] + ABCMS_SES_LIFE)) {																	$error = 'Session ended, maxtime threshold.'; }
		// POST image mismatch
		else if ($csrf && empty($_SESSION[ABCMS_SES]['user']) &&
			(($_SESSION[ABCMS_SES]['form'][$csrf]['test_valu']??'') !==
			(($_POST[$_SESSION[ABCMS_SES]['form'][$csrf]['test_name']]??'x')?:'x'))) {											$this->set_errors('Session ended, CAPTCHA3 wrong.'); }
		else {																													$formhuman = TRUE; }
	}
	$posthandled = TRUE;
	// destroy session by request or for corruption
	if ($error) {
DOIT:	// set errors
		$this->set_errors($error);
		// start session to destroy
		if (!$session_active) { $session_active = session_start($options); }
		// remove cookies
		$this->set_cookie($options['name'], '', ABCMS_SES_XPIR); // session cookie
		if (isset($_SESSION[ABCMS_SES]['valid']['cookie'])) { $this->set_cookie($_SESSION[ABCMS_SES]['valid']['cookie'], '', ABCMS_SES_XPIR); } // secret cookie
		$this->set_cookie($this->settings['core']['session_logins'], '', ABCMS_SES_XPIR); // login cookie
		// PHP says mark sessions for garbage collection to prevent races, but don't want garbage around
		$_SESSION = [];
		if ($session_active && !session_destroy()) { $this->error_log("Session destroy failed.");	}
		// slap the evil
		if ($slap) { http_response_code($slap); header('Retry-After: '.ABCMS_SES_SLAP); exit; }
		// session destroyed
		return FALSE;
	}
	// update valid session
	if ($valid) {
		// form POST cleanup
		if ($post) {
			// purge timedout forms
			$timedout = 0;
			foreach($_SESSION[ABCMS_SES]['form'] as $key => $form) {
				if (($now - ($form['mark_time']??0)) > ABCMS_SES_FORM) {
					unset($_SESSION[ABCMS_SES]['form'][$key]);
					++$timedout;
				}
			}
			if ($timedout) { $this->set_errors("Open forms timed out: {$timedout}"); }
			// reward valid POST
			if (isset($_SESSION[ABCMS_SES]['form'][$csrf])) {
				unset($_SESSION[ABCMS_SES]['form'][$csrf]);
				$this->formvalid = TRUE;
				$this->formhuman = ($formhuman ? TRUE :FALSE);
			}
			// purge excessive forms
			if (($excess = count($_SESSION[ABCMS_SES]['form'])) > ABCMS_SES_OPEN) {
				unset($_SESSION[ABCMS_SES]['form']);
				$this->set_errors("Too many open forms closed: {$excess}");
			}
		}
		// rotate if post?? or exceed rotate time or my $user updated
		if ($now > ($valid['rotate'] + ABCMS_SES_ROTA)) {
			// session cookie
			if (!session_regenerate_id(true)) { $this->error_wsod("Session regeneration failed."); }
			$_COOKIE[$options['name']] = session_id();
			// secret cookie
			$valid['cookie'] = $this->get_uniq();
			$valid['secret'] = $this->get_uniq();
			$this->set_cookie($valid['cookie'], $valid['secret'], $valid['create'] + ABCMS_SES_LIFE);
			// login cookie
			if (!empty($_SESSION[ABCMS_SES]['session_logins'])) {
				$_SESSION[ABCMS_SES]['session_logins'] = $this->get_uniq();
				$this->set_cookie($this->settings['core']['session_logins'], $_SESSION[ABCMS_SES]['session_logins'], $valid['create'] + ABCMS_SES_LIFE);
			}
			// rotated time
			$valid['rotate'] = $now;
		}
		// active time
		$valid['active'] = $now;
	}
	// validate new session
	else {
		$_SESSION[ABCMS_SES] = [
			'valid' => [
				'create'	=> $now,
				'active'	=> $now,
				'rotate'	=> $now,
				'uagent'	=> ($uagent ?: $this->get_hash($this->boots['uagent'])),
				'cookie'	=> $this->get_uniq(),
				'secret'	=> $this->get_uniq(),
				'counts'	=> array(),
			],
			'form'			=> [],
			'session_logins'=> NULL,
			'user'			=> [],
		];
		$this->set_cookie($_SESSION[ABCMS_SES]['valid']['cookie'], $_SESSION[ABCMS_SES]['valid']['secret'], $now + ABCMS_SES_LIFE);
	}
	return TRUE;
}
// Set cookie
public function set_cookie(
	string	$cookie,	// cookie name
	string	$value,		// cookie value
	int		$expires,	// expiration date
): void {
	// headers sent
	if (headers_sent()) { $this->error_wsod("Set cookie headers already sent"); }
	// Set cookie
	if (!empty($cookie) && setcookie(
		$cookie,
		$value,
		[
			'expires'	=> $expires,					// Expiration time
			'path'		=> '/',							// For entire website
			'domain'	=> $this->boots['urldomain'],	// For domain only
			'secure'	=> TRUE,						// Only over HTTPS
			'httponly'	=> TRUE,						// No javascript prevents XSS
			'samesite'	=> 'Strict',					// Avoid CSRF attacks
		])) {
		if ($expires && $expires < $this->boots['time']) {	unset($_COOKIE[$cookie]); }
		else {												$_COOKIE[$cookie] = $value; }
		return;
	}
	unset($_COOKIE[$cookie]); // failed so unset
	$this->error_wsod("Set cookie failed");
	return;
}



/*	SECTION: FORMS
*/
// form security
private function html_security(string &$html) : void {
	// not a form
	if (!preg_match(ABCMS_REGEX_FORM, $html)) { return; }
	// start session
	if (!$this->session_start(1)) { return; }
	// tokens
	$csrf = $this->get_uniq();
	$_SESSION[ABCMS_SES]['form'][$csrf]['mark_time'] = $this->boots['time'];
	$_SESSION[ABCMS_SES]['form'][$csrf]['csrf_name'] = $this->get_uniq();
	$_SESSION[ABCMS_SES]['form'][$csrf]['csrf_valu'] = $csrf;
	$_SESSION[ABCMS_SES]['form'][$csrf]['void_name'] = $this->get_uniq();
	$_SESSION[ABCMS_SES]['form'][$csrf]['full_name'] = $this->get_uniq();
	$_SESSION[ABCMS_SES]['form'][$csrf]['full_valu'] = $this->get_uniq();
	$_SESSION[ABCMS_SES]['form'][$csrf]['test_name'] = $this->get_uniq();
	$_SESSION[ABCMS_SES]['form'][$csrf]['test_valu'] = 'abc';
	static $delay = ABCMS_SES_DELY * 1000;
	// html
	$captcha = (!empty($_SESSION[ABCMS_SES]['user']) ? NULL : "Enter CAPTCHA <input name='{$_SESSION[ABCMS_SES]['form'][$csrf]['test_name']}' value='{$_SESSION[ABCMS_SES]['form'][$csrf]['test_valu']}'>\n");
	$injection = <<<EOF
<div>
<input type='hidden' name='csrf'													value='{$_SESSION[ABCMS_SES]['form'][$csrf]['csrf_valu']}'>
<input type='hidden' name='{$_SESSION[ABCMS_SES]['form'][$csrf]['csrf_name']}'	value='{$_SESSION[ABCMS_SES]['form'][$csrf]['csrf_valu']}'>
<input type='hidden' name='{$_SESSION[ABCMS_SES]['form'][$csrf]['void_name']}'	value='{$_SESSION[ABCMS_SES]['form'][$csrf]['full_valu']}'>
<input type='hidden' name='{$_SESSION[ABCMS_SES]['form'][$csrf]['full_name']}'	value=''>
{$captcha}
\$1 onclick="
this.disabled=true;
event.preventDefault();
setTimeout(() => {
	this.form.{$_SESSION[ABCMS_SES]['form'][$csrf]['void_name']}.value = '';
	this.form.{$_SESSION[ABCMS_SES]['form'][$csrf]['full_name']}.value = '{$_SESSION[ABCMS_SES]['form'][$csrf]['full_valu']}';
	HTMLFormElement.prototype.submit.call(this.form);
 }, {$delay});
"
\$2
</div>
EOF;
	// injection, dom is better, but regex is faster
	if (!($html = preg_replace(ABCMS_REGEX_FORM, $injection, $html))) { $this->error_wsod("Form security injection failed."); }
	return;
}
// inject debug information
private function html_debug(string &$html = NULL) : void {
	if (NULL === $html) { echo "ABCMS_HTML_DEBUG"; return; }
	$injection = 
		$this->see_errors().
		'<br>boots:<br><pre>'.print_r($this->boots,TRUE).'</pre>'.
		'<br>input:<br><pre>'.print_r($this->input,TRUE).'</pre>'.
		'<br>settings[core]:<br><pre>'.print_r($this->settings['core'],TRUE).'</pre>'.
		"<br>_COOKIE:<br>".(isset($_COOKIE) ? '<pre>'.print_r($_COOKIE,TRUE).'</pre>' : 'NA').
		"<br>_SESSION:<br>".(isset($_SESSION) ? '<pre>'.print_r($_SESSION,TRUE).'</pre>' : 'NA');
	if (!($html = preg_replace("/ABCMS_HTML_DEBUG/u", $injection, $html))) { $this->error_wsod("Form debug injection failed."); }
	return;
}



/*	SECTION: SETUP
*/



/*	SECTION: CRON
*/



/*	SECTION: ADMIN
*/
// This function to be moved to /admin/setup and loop through all extensions 
private function set_settings(
	bool	$boot = FALSE,	// Bootstrap load existing
) : bool {
	// Overwrite?
	if ($boot && file_exists(ABCMS_SETTINGS)) {
		if (NULL === ($this->settings = json_decode(file_get_contents(ABCMS_SETTINGS), TRUE))) {
			$this->error_wsod("System, ".json_last_error_msg().", ".$this->error_get_last());
		}
		return TRUE;
	}
	// Start with zero
	$this->compiles = array();
	// Core application settings
	touch(__FILE__);
	$this->compiles['core']['filename']			= (__FILE__); // My filename
	$this->compiles['core']['getmyinode']		= getmyinode(); // My inode
	$this->compiles['core']['getlastmod']		= getlastmod(); // My modified date
	$this->compiles['core']['documentroot']		= (__DIR__); // My documentroot
	$this->compiles['core']['projectroot']		= (dirname(__DIR__)); // My project folder
	$this->compiles['core']['project']			= (basename(dirname(__DIR__))); // My project name
	$this->compiles['core']['auto']				= (($auto = realpath(__DIR__ . '/../vendor/autoload.php')) ? $auto : FALSE); // Composer auto-loader
	$this->compiles['core']['session_folder']	= (realpath(ABCMS_SESSIONS) ?: ABCMS_SESSIONS); // My session folder
	$this->compiles['core']['session_cookie']	= $this->get_hash('session_cookie'); // My session cookie name
	$this->compiles['core']['session_logins']	= $this->get_hash('session_logins'); // My login cookie name
	// Register variables
	$this->input_varpath('debug',	'bool',		ABCMS_ROLE_PUBLIC);
	$this->input_varget('debug',	'bool',		ABCMS_ROLE_ADMINS);
	// Register _POST variables
	// Extension controls
	// 'I' = Input -OR- 'O' = Output filter, default Input
	// 'E' = Exclusive to my extension or omit me, default anyone
	// 'U' = Uno/single extension, default multiple extensions cooperate 
	// 'D' = Default included, default excluded if extended by $ord < 0
	// Bootstrap extensions
	$this->output_extend(ABCMS_EXT_BEGIN,	'',			'CLI-GET-POST',	'IEU',	'abcms->htmldefault',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_extend(ABCMS_EXT_BEGIN,	'admin',	'CLI-GET-POST',	'IEU',	'abcms->htmladmin',		ABCMS_ROLE_ADMINS,	-20);
	$this->output_equate(ABCMS_EXT_BEGIN,	'admin',	'/admin/');
	$this->output_extend(ABCMS_EXT_BEGIN,	'code',		'CLI-GET-POST',	'IEU',	'abcms->admincode',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_BEGIN,	'code',		'/admin/code');
	$this->output_extend(ABCMS_EXT_BEGIN,	'corelive',	'CLI-GET-POST',	'IEU',	'abcms->admincorelive',	ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_BEGIN,	'corelive',	'/admin/corelive');
	$this->output_extend(ABCMS_EXT_BEGIN,	'corelivesession',	'CLI-GET-POST',	'IEU',	'abcms->admincorelivesession',	ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_BEGIN,	'corelivesession',	'/admin/corelivesession');
	// Frontend page extensions
	$this->output_extend(ABCMS_EXT_PAGE,	'home',		'CLI-GET-POST',	'IE',	'abcms->pagehome',		ABCMS_ROLE_PUBLIC,	-10);
	$this->output_extend(ABCMS_EXT_PAGE,	'home',		'CLI-GET-POST',	'OE',	'abcms->pagekickin',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_equate(ABCMS_EXT_PAGE,	'home',		'/');
	$this->output_equate(ABCMS_EXT_PAGE,	'home',		'/abcms');
	$this->output_extend(ABCMS_EXT_PAGE,	'contact',	'CLI-GET-POST',	'IE',	'abcms->pagecontact',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_equate(ABCMS_EXT_PAGE,	'contact',	'/abcms/contact');
	$this->output_extend(ABCMS_EXT_PAGE,	'account',	'CLI-GET-POST',	'IE',	'abcms->pageaccount',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_equate(ABCMS_EXT_PAGE,	'account',	'/abcms/account');
	$this->output_extend(ABCMS_EXT_PAGE,	'logout',	'CLI-GET-POST',	'IE',	'abcms->pagelogout',	ABCMS_ROLE_PUBLIC,	-10);
	$this->output_equate(ABCMS_EXT_PAGE,	'logout',	'/abcms/logout');
	// Admin page extensions
	$this->output_extend(ABCMS_EXT_PAGE,	'status',	'CLI-GET-POST',	'IE',	'abcms->adminstatus',	ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_PAGE,	'status',	'/admin');
	$this->output_equate(ABCMS_EXT_PAGE,	'status',	'/admin/status');
	$this->output_extend(ABCMS_EXT_PAGE,	'phpinfo',	'CLI-GET-POST',	'IE',	'abcms->adminphpinfo',	ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_PAGE,	'phpinfo',	'/admin/phpinfo');
	$this->output_extend(ABCMS_EXT_PAGE,	'help',		'CLI-GET-POST',	'IE',	'abcms->adminhelp',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_PAGE,	'help',		'/admin/help');
	$this->output_extend(ABCMS_EXT_PAGE,	'init',		'CLI-GET-POST',	'IE',	'abcms->admininit',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_PAGE,	'init',		'/admin/init');
	$this->output_extend(ABCMS_EXT_PAGE,	'cron',		'CLI-GET-POST',	'IE',	'abcms->admincron',		ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_PAGE,	'cron',		'/admin/cron');
	$this->output_extend(ABCMS_EXT_PAGE,	'browse',	'CLI-GET-POST',	'IE',	'abcms->adminbrowse',	ABCMS_ROLE_ADMINS,	ABCMS_EXTORD_MIN);
	$this->output_equate(ABCMS_EXT_PAGE,	'browse',	'/admin/browse');
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

	// clean up
	$this->settings_clean();
	if (FALSE===$this->set_json(ABCMS_SETTINGS, $this->compiles)) {	$this->error_wsod("Settings write failure."); }
	if ($boot) { $this->settings = $this->compiles; }
	unset($this->compiles);
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
<a href='/abcms/account'>Account Profile</a><br>
<a href='/abcms/logout'>Logout</a><br>
<a href='/admin'>Admin Console</a><br>
<br>
<?php echo $GLOBALS['abcms_constant']('ABCMS_GOOD'); ?> Hello World. I am alive.<br>
<?php echo $GLOBALS['abcms_constant']('ABCMS_GOOD'); ?> Thank you!<br>
<br>
Variable1: <?php echo $variable['variable'] . ' ' . $returned['variable'];?><br>
Variable2: <?php print_r($variable2);?><br>
Settings: <?php print_r($returned3);?><br>
UUIDV4: <?php echo $this->get_uuid();?><br>
<br>
<?php
	return NULL;
}
private function pageerror(mixed &...$html) : void {
$this->session_start(-1); // destroy session and logout
if (($string = implode('', $html))) { echo $string; return; }
echo <<<EOF
<h4>Status</h4>
You have been logged out.<br>
<br>
Please contact the webmaster for help.
EOF;
return;
}
private function pagelogout(mixed &...$unused) : void {
$this->session_start(-1); // destroy session and logout
echo <<<EOF
<h4>Status</h4>
You have been logged out.<br>
<br>
Please contact the webmaster for help.
EOF;
return;
}
private function pagecontact(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
?><h4>Contact</h4>
This is where to contact us.
<?php
	echo "<br><a href='/'>Home</a><br>";
	return NULL;
}



private function pageaccount(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
echo "<h4>Account</h4>";
if (!$this->session_start(1)) {
	echo "Session failure!";
	return NULL;
}
else if ($this->formhuman) {
	echo "Submittal success!";
	$this->set_database('user', $_POST);
	$_SESSION[ABCMS_SES]['user'] = $this->get_database('user', $_POST);
	$_SESSION[ABCMS_SES]['session_logins'] = $this->get_uniq();
	$this->set_cookie($this->settings['core']['session_logins'], $_SESSION[ABCMS_SES]['session_logins'], $_SESSION[ABCMS_SES]['valid']['create'] + ABCMS_SES_LIFE);
}
else if ($this->formvalid) {
	echo "CAPTCHA snafu, try again!";
}
else {
	echo "Enter information below!";
}
?>
<form action='' method='post' accept-charset='UTF-8' class='form-grid'>
<label></label><div>Login:</div>
<label for='Account_Email'		>Email:</label>			<input type='email'		id='Account_Email'		name='Account_Email'	value='<?php echo ($_POST['Account_Email']		?? ''); ?>'>
<label for='Account_Security'	>Email2:</label>		<input type='email'		id='Account_Email2'		name='Account_Email2'	value='<?php echo ($_POST['Account_Email2']		?? ''); ?>'>
<label for='Account_Password'	>Password:</label>		<input type='password'	id='Account_Password'	name='Account_Password'	value=''>
<label></label><div>Identity:</div>
<label for='Account_First'		>Firstname:</label>		<input type='text'		id='Account_First'		name='Account_First'	value='<?php echo ($_POST['Account_First']		?? ''); ?>'>
<label for='Account_Last'		>Lastname:</label>		<input type='text'		id='Account_Last'		name='Account_Last'		value='<?php echo ($_POST['Account_Last']		?? ''); ?>'>
<label for='Account_Company'	>Company:</label>		<input type='text'		id='Account_Company'	name='Account_Company'	value='<?php echo ($_POST['Account_Company']	?? ''); ?>'>
<label for='Account_Cell'		>Cell Phone:</label>	<input type='tel'		id='Account_Cell'		name='Account_Cell'		value='<?php echo ($_POST['Account_Cell']		?? ''); ?>'>
<label for='Account_Phone'		>Phone:</label>			<input type='tel'		id='Account_Phone'		name='Account_Phone'	value='<?php echo ($_POST['Account_Phone']		?? ''); ?>'>
<label></label><div>Billing:</div>
<label for='Bill_Address'		>Address:</label>		<input type='text'		id='Bill_Address'		name='Bill_Address'		value='<?php echo ($_POST['Bill_Address']		?? ''); ?>'>
<label for='Bill_Address2'		>Address2:</label>		<input type='text'		id='Bill_Address2'		name='Bill_Address2'	value='<?php echo ($_POST['Bill_Address2']		?? ''); ?>'>
<label for='Bill_City'			>City:</label>			<input type='text'		id='Bill_City'			name='Bill_City'		value='<?php echo ($_POST['Bill_City']			?? ''); ?>'>
<label for='Bill_State'			>State:</label>			<input type='text'		id='Bill_State'			name='Bill_State'		value='<?php echo ($_POST['Bill_State']			?? ''); ?>'>
<label for='Bill_Zipcode'		>Zipcode:</label>		<input type='text'		id='Bill_Zipcode'		name='Bill_Zipcode'		value='<?php echo ($_POST['Bill_Zipcode']		?? ''); ?>'>
<label for='Bill_Country'		>Country:</label>		<input type='text'		id='Bill_Country'		name='Bill_Country'		value='<?php echo ($_POST['Bill_Country']		?? ''); ?>'>
<label></label><div>Shipping:</div>
<label for='Ship_Address'		>Address:</label>		<input type='text'		id='Ship_Address'		name='Ship_Address'		value='<?php echo ($_POST['Ship_Address']		?? ''); ?>'>
<label for='Ship_Address2'		>Address2:</label>		<input type='text'		id='Ship_Address2'		name='Ship_Address2'	value='<?php echo ($_POST['Ship_Address2']		?? ''); ?>'>
<label for='Ship_City'			>City:</label>			<input type='text'		id='Ship_City'			name='Ship_City'		value='<?php echo ($_POST['Ship_City']			?? ''); ?>'>
<label for='Ship_State'			>Ship_State:</label>	<input type='text'		id='Ship_State'			name='Ship_State'		value='<?php echo ($_POST['Ship_State']			?? ''); ?>'>
<label for='Ship_Zipcode'		>Ship_Zipcode:</label>	<input type='text'		id='Ship_Zipcode'		name='Ship_Zipcode'		value='<?php echo ($_POST['Ship_Zipcode']		?? ''); ?>'>
<label for='Ship_Country'		>Ship_Country:</label>	<input type='text'		id='Ship_Country'		name='Ship_Country'		value='<?php echo ($_POST['Ship_Country']		?? ''); ?>'>
<label></label>											<input type='submit'	id='submit'				name='submit'			value='submit'>
</form>

<?php
return NULL;
}
private function admincode(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	highlight_file($this->settings['core']['filename']);
	return NULL;
}
private function admincorelive(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	abcms_dump(NULL, 'page');
	return NULL;
}
private function admincorelivesession(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	$this->session_start(1);
	abcms_dump(NULL, 'page');
	return NULL;
}
private function adminstatus(mixed &...$unused) : ?bool { // Non-function wrapper so extendable
	static $count = 3;
	if ($count===3) { echo "<h4>Status</h4>"; }
	echo ABCMS_GOOD."Helping you! {$count}<br>\n";
	--$count;
	if ($count>0) { return TRUE; }
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
<a href='/admin/corelive'>/admin/corelive</a><br>
<a href='/admin/corelivesession'>/admin/corelivesession</a><br>
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
	$result = $this->set_settings(); // recreate settings
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
	$path = $this->settings['core']['projectroot'];
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




/*	SECTION: WEBSERVANT
*/



/*	SECTION: WEBPAGES
*/



/*	SECTION: UTILITIES
*/
// Test replace function
public function replace(mixed &...$args) : bool {
	$args[0] = preg_replace("/Hello/u", "Howdy", $args[0]);
	return FALSE;
}
// Wrapper around construct so extendable
public function echo(?string &...$args) : void {
	echo implode('',$args);
	return;
}
// Wrapper around construct so extendable
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
		$this->error_log("System, ".$this->error_get_last());
		return FALSE;
	}
	return TRUE;
}
// Get file, TODO error check that reading and writing in my own extension directory!!
public function get_file(string $filename, string &$data) : bool {
	if (!file_exists($filename) || FALSE === ($data = file_get_contents($filename))) {
		$this->error_log("System, ".$this->error_get_last());
		return FALSE;
	}
	return TRUE;
}
// Set json
public function set_json(string $filename, mixed $value) : bool {
	if (FALSE === $this->set_file($filename, json_encode($value, ABCMS_FLAG_JSON))) {
		$this->error_log("System file_put_contents(), ".$this->error_get_last());
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
		$this->error_log("System, ".json_last_error_msg().", ".$this->error_get_last());
		return FALSE;
	}
	return TRUE;
}
// Set Database
public function set_database(string $filename, mixed $data) : mixed {
	return TRUE;
}
// Get Database
public function get_database(string $filename, mixed $data) : mixed {
	return array(
		'userid'=> 1,
		'first'	=> 'Jeff',
		'last'	=> 'Martin',
		'role'	=>	ABCMS_ROLE_ADMINS,
	);
}
// Include always
public function include(string $filename, ...$args) : mixed {
	if (!file_exists($filename)) {
		$this->error_log("Include does not exist.");
	}
	return include($filename); // Variable scope natually limited here
}
// Include once, PHP should provide a native no fault include_once() function
public function include_once(string $filename, ...$args) : mixed {
	static $included = array();
	if (!($filename = realpath($filename)) || !file_exists($filename)) {
		$this->error_log("Include once does not exist.");
	}
	else if (!isset($included[$filename])) {
		$included[$filename] = TRUE;
		// Anonymous function scopes $args within the include
		$anonymous =  function($filename, ...$args) { return include($filename); };
		return $anonymous($filename, ...$args);
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
// RFC 4122 compliant Version 4 UUIDs, globally unique
public function get_uuid() : string {
	// Generate 16 bytes (128 bits) of random data
	$data = random_bytes(16);
	if (strlen($data) !== 16) { $this->error_wsod("Sixteen bytes unavailable for uuidv4."); }
    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
// Unique token, 64 bytes
public function get_uniq(): string {
	return chr(random_int(97,122)).chr(random_int(97,122)).bin2hex(random_bytes(31));
}
// Unique DB ID, 32 bytes
public function get_dbid(): string {
	return chr(random_int(97,122)).chr(random_int(97,122)).bin2hex(random_bytes(15));
}
// Unique hash for getmyinode() + getlastmod() + $string, not for permanent storage, 64 bytes
public function get_hash(?string $input): string {
	if (isset($this->settings)) {
		return hash('sha256', $this->settings['core']['getmyinode'].$this->settings['core']['getlastmod'].$input);
	}
	return hash('sha256', getmyinode().getlastmod().$input);
}



/*	SECTION: THEME
*/
public function htmldoc(	// Default theme function and method
	bool	$admin	= FALSE,// default or admin
	?string	$css	= NULL,	// Override css default
	?string	$js		= NULL,	// Override js default
	?string	$head	= NULL,	// Override header default
	?string	$page	= NULL,	// Override page content default
	?string	$foot	= NULL,	// Override footer default
	int		$flag	= 1,	// Output control flag
) : ?bool {					// Return boolean
$title = (isset($_SERVER['HTTP_HOST']) && FALSE !== filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_DOMAIN) ? $_SERVER['HTTP_HOST'] : 'Unknown');
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<title><?php echo $title; ?></title>
<meta name='description' content='<?php echo $title; ?>'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<meta name='apple-mobile-web-app-capable' content='yes'>
<style>
a:hover {
	color: #0096FF !important;
}
form.form-grid {
	display: grid;
	grid-template-columns: max-content 1fr;
	gap: 15px;
	align-items: center;
	max-width: 600px;
}
label {
	text-align: right;
}
input:required {
	border: 1px solid red;
}
#head a, #foot a {
	text-decoration: none;
	color: white;
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
<?php
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
<?php
}
$this->output('/htmldefault_css', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...array($css));
?>
</style>
<script type='text/javascript'>
<?php
$this->output('/htmldefault_js', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...array($js));
?>
</script>
</head>
<body>
<div id='main'>
<div id='head'>
<?php
if (!$head) { $head = "<h2 style='font-size: 1.5em'>$title</h2>"; }
$this->output('/htmldefault_head', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...array($head));
?>
</div>
<div id='page'>
<?php
if (!$page) { $page = <<<EOF
<h4>Status</h4>
{$GLOBALS['abcms_constant']('ABCMS_GOOD')}Hello World. I am alive!<br>
{$GLOBALS['abcms_constant']('ABCMS_BAD')}However, page requested not found.<br>
<br>
You have been logged out.<br>
<br>
Please contact the webmaster for help.
EOF;
}
$this->output('/htmldefault_page', 'CLI-GET-POST', 'abcms->pageerror', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...array($page));
$this->html_debug();
?>
</div>
<div id='foot'>
<?php
if (!$foot) {	$foot = "<a href='/abcms/contact'>Contact</a>"; }
$this->output('/htmldefault_foot', 'CLI-GET-POST', 'abcms->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...array($foot));
?>
</div>
</div>
</body>
<?php
return NULL; // done
}


// end object
}; }

return $_abcms;
}