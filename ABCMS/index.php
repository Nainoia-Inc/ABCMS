<?php
/*************************************************************************************************
SECTION INTRODUCTION: A Basic Content Management System and PHP toolkit.
*/

/*************************************************************************************************
SECTION CONSTANTS: Immutable constants.
*/
// extensions
const ABCMS_EXT_SELF	= "/nainoiainc/abcms";					// even abcms is an extension
const ABCMS_EXT_INIT	= "/init";								// initial extension hook
const ABCMS_EXT_INITX	= "/nainoiainc/abcms".ABCMS_EXT_INIT;	// initial extension fullname
const ABCMS_EXT_MAIN	= "/theme_main";						// default html <main> extension hook
const ABCMS_EXT_MAINX	= "/nainoiainc/abcms".ABCMS_EXT_MAIN;	// default html <main> extension fullname
// roles
const ABCMS_ROLE_PUBLIC	= 0;
const ABCMS_ROLE_AUTHEN	= 1;
const ABCMS_ROLE_READER	= 2;
const ABCMS_ROLE_WRITER	= 3;
const ABCMS_ROLE_EDITOR	= 4;
const ABCMS_ROLE_MANAGE	= 5;
const ABCMS_ROLE_ADMINS	= 6;
const ABCMS_ROLE_CLI	= 7;
const ABCMS_ROLE_SET	= array(0,1,2,3,4,5,6,7);
// regex
// includefile?function #^(|/vendor/package/filepath)(|?(|classobject(::|->|()->))funcmeth)#
const ABCMS_REGEX_FUNC	= "/^((\/[^?]+)\?)?((([a-z_\x{7f}-\x{ff}][a-z0-9_\x{7f}-\x{ff}]*)(::|\->|\(\)\->))?([a-z_\x{7f}-\x{ff}][a-z0-9_\x{7f}-\x{ff}]*))?$/ui";
const ABCMS_REGEX_HOOK	= "/^\/[^\/]+\/[^\/]+\/[^\/]+$/u";				// hook name, path-like, but not a filepath
const ABCMS_REGEX_URLV	= "/\/([a-z0-9\-_.~]+)=([a-z0-9\-_.~=]+)/ui";	// URL variable
const ABCMS_REGEX_FORM	= "/(<form(?=[\s>])[^>]*>)(.+?)(<\/form>)/uis";			// form security injection
// session - move these to overridable $settings
const ABCMS_SES			= ABCMS_EXT_SELF;	// unique session key for ABCMS
const ABCMS_SES_ROTA	= 60*15;			// rotate session after 15 minutes
const ABCMS_SES_IDLE	= 60*60*24*1;		// destroy session after 1 day idle
const ABCMS_SES_LIFE	= 60*60*24*3;		// destroy session after 3 days total
const ABCMS_SES_BADA	= 60*60*24*1;		// bad actor lockout for 1 day
const ABCMS_SES_FORM	= 60*60;			// remove form security tokens after 1 hour
const ABCMS_SES_WAIT	= 4;				// javascript form submission delay to stymie robots for 4 seconds
const ABCMS_SES_OPEN	= 21;				// max number form security token sets open total
const ABCMS_SES_HITS	= 20;				// number of session hit times to track
const ABCMS_SES_TIME	= 20;				// max session hit time before suspect, 20 in 20 seconds
const ABCMS_SES_LOGI	= 7;				// max login attempts
// cookies
const ABCMS_COOK_LIFE	= 60*60*24*365;		// choice for 1 year
const ABCMS_COOK_NONE	= 0;				// none
const ABCMS_COOK_FORM	= 1;				// security
const ABCMS_COOK_NAVS	= 2;				// navigation
const ABCMS_COOK_TRAK	= 3;				// tracking




/*************************************************************************************************
SECTION TRY/CATCH: Anonymous function for zero global footprint.
*/
(function() {				// anonymous wrapper
$code = 0;					// assume success
try {						// try output
	abcms()->output(		// extension router
		ABCMS_EXT_INIT,		// initial extension
		'CLI-GET-POST',		// methods extended
		'abcms()->theme',	// default function
		ABCMS_ROLE_PUBLIC,	// minimum role
		1,					// exclusive allowed
		FALSE,				// default required
		...$args = array(NULL,NULL,NULL,NULL,NULL,1), // css, js, header, main, footer, exclusive allowed
	);
}
catch (\Throwable $e) { // catch exceptions
	$exception = (htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') ?: 'Unknown exception.'); // thrown error
	$system = (error_get_last() ?? array('message' => 'No system error reported.')); // system error
	$composer = array(); // composer extensions
	if (class_exists(\Composer\InstalledVersions::class)) {
		foreach (Composer\InstalledVersions::getInstalledPackagesByType('abcms-extension') as $name) {
			$composer[$name] = Composer\InstalledVersions::getInstallPath($name);
		}
	}
	$buffer = NULL; while(ob_get_level()) { $buffer .= ob_get_clean(); } // examine buffer
	$title = mb_strtolower(htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])); // website title
	$nonce = chr(random_int(97,122)).chr(random_int(97,122)).bin2hex(random_bytes(31)); // security nonce
	echo <<<EOF
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<meta name='description' content='<?php echo $title; ?> ERROR'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<meta name='mobile-web-app-capable' content='yes'>
<meta name='theme-color' content='#336699'>
<meta name='color-scheme' content='light dark'>
<meta http-equiv='Content-Security-Policy' content="default-src 'none'; style-src 'nonce-{$nonce}'; img-src 'self';">
<title><?php echo $title; ?> ERROR</title>
<link rel='icon' href='favicon.ico'>
<style nonce={$nonce}>
*, *::before, *::after { box-sizing: border-box; }
body {	margin:0; padding:0; display:grid; height:100vh; place-items:center; text-align: center; border: 2rem solid #336699;
		color:#333333; background-color:#FFFFFF; font-size:1.125rem; line-height:1.3; font-family:Arial,sans-serif; }
h1 { color: #336699; }
</style>
</head>
<body><div>
<h1>Status</h1>
<p>
My sincere apologies.<br>
I tripped on an expected error.<br>
Try again, wait, or contact webmaster.<br>
<br>
URL: "{$title}"<br>
ERR: "{$exception}"<br>
SYS: "{$system['message']}"<br>
<br>
<a href='/'>Try again from the homepage</a>.
</p></div></body></html>
EOF;
	error_log("ABCMS->COREDUMP()\n" . print_r(array('COREDUMP_EXCEPTION' => $exception, 'COREDUMP_SYSTEM' => $system), TRUE)); // log error
	file_put_contents( // dump corefile
		__DIR__ . "/../private/nainoiainc/abcms/ABCMS.coredump",
		print_r(array(
			'ABCMS_EXCEPTION'	=> $exception,
			'ABCMS_SYSTEM'		=> $system,
			'ABCMS_OBJECT'		=> (abcms()?:'constructor failed'),
			'ABCMS_GLOBALS'		=> $GLOBALS,
			'ABCMS_BUFFER'		=> $buffer,
			'ABCMS_COMPOSER'	=> $composer,
		), TRUE),
	);
	$code = 1; // return failure
}
finally { // clean up
}
exit($code);
 ; })(); // done, function definitions follow







/*************************************************************************************************
SECTION CONSTRUCT: Instantiate object and validate inputs.
*/
function abcms() : ?object {				// abcms() function
static $_abcms = FALSE;						// construct once
if (FALSE === $_abcms) {					// fail once
$_abcms = NULL;								// return FALSE, NULL, or object
$_abcms = new class {						// object assigned
readonly	array	$boots;					// bootstrap input before session
readonly	array	$input;					// sanitized input after session
readonly	array	$settings;				// application settings
private		?array	$compiles	= NULL;		// compile settings
private		?array	$database	= NULL;		// database
private		array	$errors		= [];		// errors
private		array	$debugs		= [];		// debugs
private		array	$stack		= [];		// extension stack
private		bool	$formvalid	= FALSE;	// form valid
private		bool	$formhuman	= FALSE;	// form human
// construct object
function __construct() {
	// read settings, log location, dump buffers
	if (FALSE === $this->settings(TRUE)) { $this->error_wsod("Application settings not found."); }
	if (FALSE === ini_set('error_log', $this->settings['core']['translog'])) { $this->error_log("Set error_log location failed."); }
	while(ob_get_level() > 0) { if (FALSE !== ($buf = ob_get_clean()) && '' !== $buf) { $this->error_log("I got stuff in my buffers."); } }

	// bootstrap inputs for session_start(), then session user validates remaining inputs
	$this->boots = array(
		// current time()
		'time' => time(),
		// user identity
		'uagent' => (($_SERVER['REMOTE_ADDR']??'')?:'unknown').(($_SERVER['HTTP_USER_AGENT']??'')?:'unknown'),
		// auto-loader
		'auto' => $this->settings['core']['auto'],
		// CLI execution
		'cli' => ($cli = ('cli' === PHP_SAPI ? TRUE : FALSE)),
		// CLI arg count
		'argc' => ($_SERVER['argc']??0),
		// CLI args
		'argv' => ($_SERVER['argv']??[]),
		// URL full
		'urlfull' => ($urlfull =
			// CLI domain
			($cli ? ('https://localhost' . 
			// CLI URI validation or default
			($_SERVER['argc']>1 && '/' === ($_SERVER['argv'][1][0]?:'') && FALSE !== filter_var('http://localhost' . $_SERVER['argv'][1], FILTER_VALIDATE_URL) ? $_SERVER['argv'][1] : '/command/help')) :
			// HTTP secure
			((isset($_SERVER['HTTPS']) && mb_strtolower($_SERVER['HTTPS']) !== 'off' ? 'https://' : 'http://') .
			// HTTP domain validation including multibyte to punycode
			(!empty($_SERVER['HTTP_HOST']) && ($host = preg_replace('/:\d*$/u','',$_SERVER['HTTP_HOST'])) &&
			FALSE !== filter_var(idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_DOMAIN) ? $_SERVER['HTTP_HOST'] : 'unknown') .
			// HTTP URI validation, ascii only
			(isset($_SERVER['REQUEST_URI']) && mb_check_encoding($_SERVER['REQUEST_URI'],'ASCII') && FALSE!==filter_var('http://localhost'.$_SERVER['REQUEST_URI'],FILTER_VALIDATE_URL) ? $_SERVER['REQUEST_URI'] : '/unknown')))),
		// URL parse
		'urlparsed' => ($urlparsed = parse_url($urlfull)),
		// URL domain
		'urldomain' => (mb_strtolower(($urlparsed['host']??''), 'UTF-8')),
		// URL port
		'urlport' => ($urlparsed['port']??NULL),
		// URL method
		'urlmethod' => ($cli ? 'CLI' : ((empty($_SERVER['REQUEST_METHOD']) ||
			!in_array($_SERVER['REQUEST_METHOD'], array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE'))) ? 'GET' : $_SERVER['REQUEST_METHOD'])),
		// URL no variables, no trailing slash, and urldecoded
		'urlpathall' => ($urlpathall = ('/'.(trim(preg_replace(ABCMS_REGEX_URLV, '/', ($urldecoded = urldecode(($urlparsed['path']??'')))), '/')))),
		// URL first segment for primary router
		'urlpathone' => (!($ret = preg_match("/^(\/[^\/]*)(\/.+)?$/u", $urlpathall, $matches)) ? '/' : $matches[1]),
		// URL second+ segments for secondary router
		'urlpathext' => (!$ret || empty($matches[2]) ? '/' : $matches[2]),
	);

	// lazy start
	$session = $this->session_start(0);

	// sanitize inputs given user role
	$this->input = array(
		// session result
		'session' => $session,
		// my user
		'user' => $_SESSION[ABCMS_SES]['user']??NULL,
		// my role
		'role' => ($role = ($cli ? ABCMS_ROLE_CLI : $_SESSION[ABCMS_SES]['user']['role']??ABCMS_ROLE_PUBLIC)),
		// URL validate path vars 'v'
		'urlvars' => (!preg_match_all(ABCMS_REGEX_URLV, $urldecoded, $matches, PREG_PATTERN_ORDER) ? array() :
			$this->input_valid('v', array_combine($matches[1], $matches[2]), $role)),
		// URL validate query vars 'q' from parse_str() because CLI has no $_GET
		'urlquery' => ($this->input_valid('q', (mb_parse_str(($urlparsed['query']??''), $result) ? $result : array()), $role)),
		// POST variables 'p'
		'postvars' => array(), // TODO ($this->input_valid('p', $_POST, $role)),
		// style & script security nonce
		'nonce' => $this->get_uniq(),
	);

	// require composer
	if ($this->boots['auto']) { require_once($this->boots['auto']); }
	// URL vars misplaced if !str_starts_with, URL externally constructed
	if (!str_starts_with($urldecoded, $urlpathall)) { $this->set_errors("URL questioned, variables within path"); }

	// done
	return;
}
// Disallowed methods
public function __set(string $name, mixed $value) : void { $this->error_wsod("Dynamic properties disallowed."); }
public function __clone() { $this->error_wsod("Cloning object disallowed."); }
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
			case 'uri'		:	if (!mb_check_encoding($val, 'ASCII') || FALSE === filter_var('http://localhost'.$val, FILTER_VALIDATE_URL)) {	break; }			continue 2;
			case 'url'		:	if (!mb_check_encoding($val, 'ASCII') || FALSE === filter_var($val, FILTER_VALIDATE_URL)) {						break; }			continue 2;
			case 'uuid'		:	if (!preg_match("/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i", $val)) {		break; }			continue 2;			
			// Variable found, but undefined type registered by settings_variable()
			default:			$this->error_wsod("Undefined URL variable type, '{$this->settings[$cat][$var]['type']}'");
		}
		// Variable name and type found, but value is invalid
		$this->set_errors("Ignoring invalid URL variable, '{$this->settings[$cat][$var]['type']}' = '{$var}'");
		unset($vars[$var]);
	}
	return $vars;
}







/*************************************************************************************************
SECTION SETTINGS: Compile core and extension boot settings.
*/
// Read or create the core settings JSON file. 
private function settings(
	bool	$boot = FALSE,	// Bootstrap load existing
) : bool {
	// read settings
	$storage = __DIR__ . "/../private/nainoiainc/abcms/ABCMS.settings";
	if ($boot && file_exists($storage)) {
		if (NULL === ($this->settings = json_decode(file_get_contents($storage), TRUE))) {
			$this->error_wsod("System, ".json_last_error_msg().", ".$this->error_get_last());
		}
		return TRUE;
	}
	$this->compiles = array();
	// recreate settings
	touch(__FILE__);
	$this->compiles['core']['filename']			= (__FILE__); // My filename
	$this->compiles['core']['documentroot']		= (__DIR__); // My documentroot
	$this->compiles['core']['projectroot']		= (dirname(__DIR__)); // My project folder
	$this->compiles['core']['project']			= (basename(dirname(__DIR__))); // My project name
	$this->compiles['core']['auto']				= (realpath(__DIR__ . '/../vendor/autoload.php') ?: FALSE); // auto-loader location
	$this->compiles['core']['getmyinode']		= getmyinode(); // My inode
	$this->compiles['core']['getlastmod']		= getlastmod(); // My modified date
	$password									= $this->get_uniq(); // My clear password
	$this->compiles['core']['password']			= password_hash($password, PASSWORD_DEFAULT); // Reset when rebuild settings
	if (FALSE===$this->set_json(__DIR__ . "/../private/nainoiainc/abcms/ABCMS.deleteme", 'DELETE ASAP: '.$password)) { $this->error_wsod("Settings password failure."); } // Temporary storage
	$password = NULL;
	$this->error_log("Retrieve new password and delete the file please.");
	$this->compiles['core']['secret']			= $this->get_uniq(); // My hashing secret
	if (!is_dir(($dir = __DIR__ . "/../private/nainoiainc/abcms/ABCMS.sessions")) && (!mkdir($dir, 0755, true) || !($dir=realpath($dir)))) { $this->error_wsod("Session folder does not exist."); }
	$this->compiles['core']['session_folder']	= $dir; // session folder
	$this->compiles['core']['session_cookie']	= $this->get_hash('session_cookie'); // session cookie name
	$this->compiles['core']['session_logins']	= $this->get_hash('session_logins'); // login cookie name
	$this->compiles['core']['session_badact']	= $this->get_hash('session_badact'); // bad actor cookie name
	$this->compiles['core']['session_allows']	= $this->get_hash('session_allows'); // user allows cookie name
	$this->compiles['core']['session_killit']	= TRUE; // kill when close browser
	$this->compiles['core']['smtp_host']		= NULL; // SMTP server
	$this->compiles['core']['smtp_port']		= NULL; // SMTP port
	$this->compiles['core']['smtp_name']		= NULL; // SMTP name
	$this->compiles['core']['smtp_user']		= NULL; // SMTP username
	$this->compiles['core']['smtp_pass']		= NULL; // SMTP password
	$this->compiles['core']['smtp_ehlo']		= NULL; // SMTP EHLO
	if (!file_exists(($log=__DIR__."/../private/nainoiainc/abcms/ABCMS.translog")) && (!touch($log) || !($log=realpath($log)))) { $this->error_wsod("Transaction log does not exist."); }
	$this->compiles['core']['translog']			= $log;	// transaction log
	if (!file_exists(($dat=__DIR__."/../private/nainoiainc/abcms/ABCMS.database")) && (!touch($dat) || !($dat=realpath($dat)))) { $this->error_wsod("Database does not exist."); }
	$this->compiles['core']['database']			= $dat;	// database
	if (!file_exists(($lok=__DIR__."/../private/nainoiainc/abcms/ABCMS.lockfile")) && (!touch($lok) || !($lok=realpath($lok)))) { $this->error_wsod("Lockfile does not exist."); }
	$this->compiles['core']['lockfile']			= $lok;	// lockfile
	if (!file_exists(($ove=__DIR__."/../private/nainoiainc/abcms/ABCMS.override")) && (!touch($ove) || !($ove=realpath($ove)))) { $this->error_wsod("Settings override does not exist."); }
	$this->compiles['core']['override']			= $ove;	// overrides
	// register URL PATH variables
	$this->settings_varpath('debug',	'bool',		ABCMS_ROLE_ADMINS);
	// register $_GET variables
	$this->settings_varget('debug',	'bool',		ABCMS_ROLE_ADMINS);
	// register _POST variables
	// extension controls
	// 'I' = Input -OR- 'O' = Output filter, default Input
	// 'E' = Exclusive to my extension or omit me, default anyone
	// 'U' = Uno/single extension, default multiple extensions cooperate 
	// 'D' = Default included, default excluded if extended by $ord < 0
	// bootstrap extensions
	$this->settings_extend(ABCMS_EXT_INITX,	'',			'CLI-GET-POST',	'IEU',	'abcms()->home_theme',		ABCMS_ROLE_PUBLIC,	-10);
	$this->settings_extend(ABCMS_EXT_INITX,	'console',	'CLI-GET-POST',	'IEU',	'abcms()->console_theme',	ABCMS_ROLE_ADMINS,	-20);
	$this->settings_equate(ABCMS_EXT_INITX,	'console',	'/console/');
	$this->settings_extend(ABCMS_EXT_INITX,	'command',	'CLI-GET-POST',	'IEU',	'abcms()->command_router',	ABCMS_ROLE_ADMINS,	-10);
	$this->settings_equate(ABCMS_EXT_INITX,	'command',	'/command/');
	// frontend extensions
	$this->settings_extend(ABCMS_EXT_MAINX,	'home',		'CLI-GET-POST',	'IE',	'abcms()->home_router',		ABCMS_ROLE_PUBLIC,	-10);
	$this->settings_equate(ABCMS_EXT_MAINX,	'home',		'/');
	$this->settings_equate(ABCMS_EXT_MAINX,	'home',		'/account');
	$this->settings_equate(ABCMS_EXT_MAINX,	'home',		'/contact');
	// admin extensions
	$this->settings_extend(ABCMS_EXT_MAINX,	'console',	'CLI-GET-POST',	'IE',	'abcms()->console_router',	ABCMS_ROLE_ADMINS,	-10);
	$this->settings_equate(ABCMS_EXT_MAINX,	'console',	'/console');
	$this->settings_equate(ABCMS_EXT_MAINX,	'console',	'/console/');
	// SETTINGS.php run by composer or at will if ABCMS or plugin changes to rebuild the settings extension array
	// TODO remove mixed non-exclusive or exclusive routes.
	while(0) { ; } // TODO loop through extension SETTINGS.php
	// override settings
	if (($override = json_decode(file_get_contents($this->compiles['core']['override']), TRUE))) { $this->array_walk_merge($this->compiles, $override); }
	// save settings atomically with rename()
	$temp = "{$storage}.".getmypid();
	if (FALSE === $this->set_json($temp, $this->compiles) || !rename($temp, $storage)) {	$this->error_wsod("Settings write failure."); }
	if ($boot) { $this->settings = $this->compiles; }
	$this->compiles = [];
	return TRUE;
}
// Register hook extension
public function settings_extend(
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
	int		$ord = 0,					// Order considered, PHP_INT_MIN >= $ord <= PHP_INT_MAX 
	mixed	...$arg,					// Argument alternatives
) : bool {
	// Control string to array indices
	$ctl = array_flip(($key=str_split(strtoupper($str))));
	$key = array_diff_key($key, array('I','O','E','U','D'));

	// Error checks
	if (!preg_match(ABCMS_REGEX_HOOK, $hok) || // Hook valid
		(!empty($met) && array_diff(explode('-', $met), array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE'))) || // method validation
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
		'ord'	=> $ord,
		'ctl'	=> $ctl,
		'who'	=> $this->extension(),
		'arg'	=> $arg,
	);
	return TRUE;
}
// Equate path to hook extension name
public function settings_equate(
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
// Define path variable
public function settings_varpath(
	string	$var,			// Allowed path variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role
	?array	$reg = NULL,	// Regex validation
) : void {
	$this->settings_variable('v', $var, $type, $role, $reg);
	return;
}
// Define _GET variable
public function settings_varget(
	string	$var,			// Allowed query variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role
	?array	$reg = NULL,	// Regex validation
) : void {
	$this->settings_variable('q', $var, $type, $role, $reg);	
	return;
}
// Define _POST variable
public function settings_varput(
	string	$var,			// Allowed post variable
	string	$type,			// Allowed type
	int		$role,			// Minimum role
	?array	$reg = NULL,	// Regex validation
) : void {
	$this->settings_variable('p', $var, $type, $role, $reg);	
	return;
}
// Register variable
private function settings_variable(
	string	$cat,			// Category
	string	$var,			// Variable name
	string	$type,			// Allowed type
	int		$role,			// Minimum role
	?array	$reg = NULL,	// Regex validation
) : void {
	if (!preg_match("/^[a-z0-9\-_.~]+$/ui", $var) ||
		!empty($this->compiles[$cat][$var]) ||
		!in_array($type, array('mixed','string','array','integer','float','bool','boolean','email','domain','uri','url','ip','mac','uuid','path')) ||
		!in_array($role, ABCMS_ROLE_SET)) {
		$this->error_log("Invalid or duplicate variable.");
		return;
	}
	$this->compiles[$cat][$var] = array('type'=>$type, 'role'=>$role, 'reg'=>$reg);
	return;
}







/*************************************************************************************************
SECTION SESSION: Secure sessions with opt-in/out, validation, CSRF, CAPTCHA, tricks, and login.
*/
// start the session conditionally
public function session_start(
	int $cmd,	// 1=unconditional, -1=destroy, 0=conditional
) : bool {		// TRUE=started, FALSE=destroyed

	// initialize variables
	$active = (session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE);
	$slap = 0;
	static $now = NULL;
	static $posthandled = FALSE;
	static $alreadydenied = FALSE;
	static $options = NULL;
	if (NULL === $options) {
		$now = $this->boots['time'];
		$options = [
			'save_path'			=> $this->settings['core']['session_folder'],	// or .htaccess: php_value session.save_path '/path'
			'name'				=> $this->settings['core']['session_cookie'],	// custom name
			'save_handler'		=> 'files',										// session files
			'gc_probability'	=> '1',											// garbage collection, turn off and replace with cron!
			'gc_divisor'		=> '100',										// garbage collection, turn off and replace with cron!
			'gc_maxlifetime'	=> ABCMS_SES_LIFE,								// garbage collection, turn off and replace with cron!
			'cookie_lifetime'	=> ($this->settings['core']['session_killit'] ? 0 : ABCMS_SES_LIFE), // cookie lifetime, kill when close browser
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

	// early exits
	if ($alreadydenied) { return FALSE; } // already denied
	if ($cmd < 0) { $error = 'You are logged out.'; goto KILL; } // destroy session
	if ($active) { return TRUE; } // already started
	if (headers_sent()) { $this->error_wsod("Session start failed, headers already sent.");	} // already headers
	if (!isset($_COOKIE[$this->settings['core']['session_allows']])) { $this->set_cookie($this->settings['core']['session_allows'], ABCMS_COOK_NAVS, $now + ABCMS_COOK_LIFE, FALSE); }	// TEMP CODE TO ALLOW COOKIES
	if (empty($_COOKIE[$this->settings['core']['session_allows']])) {	$this->set_errors('Session denied without your cookie approval.'); return FALSE; } // cookies not approved
	if (isset($_COOKIE[$this->settings['core']['session_badact']])) {	$this->set_errors('Session denied to suspected bad actor.'); $alreadydenied = TRUE; return FALSE; } // bad actor
	$post = ('POST' === $this->boots['urlmethod'] && !$posthandled ? TRUE : FALSE); // is this a POST?
	if (0 === $cmd && !isset($_COOKIE[$this->settings['core']['session_logins']]) && !$post) { return FALSE; } // conditional start

	// start the session and assign more variables
	if (!session_start($options) || !($_COOKIE[$options['name']] = session_id())) { $this->error_wsod("Session start failed, unknown reason.");	}
	$active = $posthandled = TRUE;
	$error = $formhuman = NULL;
	$csrf = ($post && !empty($_POST['csrf']) ? $_POST['csrf'] : '');
	if (empty($_SESSION[ABCMS_SES]['create'])) { $sess = NULL; } else { $sess = &$_SESSION[ABCMS_SES]; }

	// validate session
	if (!$sess) {
		// cannot POST without session
		if ($post) {																									$error = 'Session ended, POST requires session.';	$slap = 400; }
	}
	else {
		// hit counter
		$gothits = FALSE; $sess['counts'][] = $now; if (count($sess['counts']) > ABCMS_SES_HITS) { array_shift($sess['counts']); $gothits = TRUE; }
		// uagent inconsistent
		if ($sess['uagent'] !== $this->boots['uagent']) {																$error = 'Session ended, IP/Agent or core reset.';	$slap = 400; }
		// secrets differ
		else if (!hash_equals($sess['secret'], ($_COOKIE[$sess['cookie']]??'x'))) {										$error = 'Session ended, secrets differ.';			$slap = 400; }
		// rapid hits
		else if ($gothits && $sess['counts'][ABCMS_SES_HITS-1] - $sess['counts'][0] < ABCMS_SES_TIME) {					$error = 'Session ended, rapid hits.';				$slap = 429; }
		// POST CSRF1
		else if ($post && (!$csrf || !hash_equals($sess['csrf_valu'], $csrf))) {										$error = 'Session ended, CSRF1 error.';				$slap = 400; }
		// POST CSRF2
		else if ($csrf && !hash_equals($sess['csrf_valu'], (($_POST[$sess['csrf_name']]??'x')?:'x'))) {					$error = 'Session ended, CSRF2 error.';				$slap = 400; }
		// POST !HONEY populated
		else if ($csrf && !empty($_POST[$sess['void_name']])) {															$error = "Session ended, CAPTCHA1 error.";			$slap = 400; }
		// POST HONEY differs
		else if ($csrf && !hash_equals($sess['full_valu'], (($_POST[$sess['full_name']]??'x')?:'x'))) {					$error = 'Session ended, CAPTCHA2 error.';			$slap = 400; }
		// POST rapid
		else if ($csrf && ($now - $sess['active']) < ABCMS_SES_WAIT) {													$error = "Session ended, rapid submission.";		$slap = 400; }
		// login failed session ended
		else if (isset($_COOKIE[$this->settings['core']['session_logins']]) &&
			(($_COOKIE[$this->settings['core']['session_logins']]?:'x') !== $sess['logins'] || empty($sess['user']) ||
			// reload user every page to confirm permissions
			!($sess['user'] = $this->get_database(array('user',$sess['user']['email']))))) {							$error = 'Session ended, resume login failed.'; }
		// login expired
		else if (!isset($_COOKIE[$this->settings['core']['session_logins']]) && !empty($sess['user'])) {				$error = 'Session ended, login expired.'; }
		// idle time exceeded
		else if ($now > ($sess['active'] + ABCMS_SES_IDLE)) {															$error = 'Session ended, inactivity threshold.'; }
		// time exceeded
		else if ($now > ($sess['create'] + ABCMS_SES_LIFE)) {															$error = 'Session ended, maxtime threshold.'; }
		// POST image mismatch
		else if ($csrf && empty($sess['user']) && ($sess['test_valu'] !== (($_POST[$sess['test_name']]??'x')?:'x'))) {	$this->set_errors('CAPTCHA failure, please try again.'); }
		// Passed gauntlet must be human
		else {																											$formhuman = TRUE; }
	}

	// destroy by request or for corruption
	if ($error) {
KILL:	// set errors
		$this->set_errors($error);
		// start session to destroy
		if (!$active) { $active = session_start($options); }
		// remove cookies
		$this->set_cookie($options['name'], '', 1); // session
		if (isset($_SESSION[ABCMS_SES]['cookie'])) { $this->set_cookie($_SESSION[ABCMS_SES]['cookie'], '', 1); } // secret
		$this->set_cookie($this->settings['core']['session_logins'], '', 1); // login
		// PHP says mark for garbage collection, but don't want garbage around
		$_SESSION = [];
		if ($active && !session_destroy()) { $this->error_log("Session destroy failed.");	}
		// slap evil and assign bad actor cookie
		if ($slap) {
			$this->set_cookie($this->settings['core']['session_badact'], $this->get_uniq(), $now + ABCMS_SES_BADA, FALSE);
			http_response_code($slap);
			header('Retry-After: ' . ABCMS_SES_BADA);
			$this->error_wsod($error);
		}
		return FALSE;
	}

	// update valid session
	if ($sess) {
		$logout = FALSE;

		// valid POST
		if ($post) {
			$this->formvalid = TRUE;
			$this->formhuman = ($formhuman ? TRUE :FALSE);
			// process account operations on page load
			if ('/account' === $this->boots['urlpathall']) {
				$operation = ($this->formhuman && isset($_POST['clicked']) ? $_POST['clicked'] : 'logout');
				if (!password_verify($_POST['Account_Password'], $this->settings['core']['password']) ||
					empty($_POST['Account_Email']) ||
					empty($_POST['Account_Email2'])) {
					if (++$sess['trys'] > ABCMS_SES_LOGI) {	$error = 'Too many login failures, attack suspected.'; $slap = 400; goto KILL; } // too many failed logins
					$this->set_errors('Login failure, please try again.');
					$logout = TRUE;
				}
				else if (1 || 'register' === $operation) {
					$this->set_database(array('user', $_POST['Account_Email']), array('email'=>$_POST['Account_Email'],'email2'=>$_POST['Account_Email2'],'role'=>ABCMS_ROLE_ADMINS));
					if (($sess['user'] = $this->get_database(array('user', $_POST['Account_Email'])))) {
						$sess['trys'] = 0;
						$sess['logins'] = $this->get_uniq();
						$this->set_cookie($this->settings['core']['session_logins'], $sess['logins'], $sess['create'] + ABCMS_SES_LIFE);
					}
					else {
						$this->set_errors('Login failure, please try again.');
						$logout = TRUE;
					}
				}
				else if ('login'=== $operation) { }
				else if ('reset'=== $operation) { }
				else if ('update'=== $operation) { }
				else if ('delete'=== $operation) { }
			}
		}

		// process logout on page load 
		if ($logout || '/home/logout' === $this->boots['urlpathall']) {
			$sess['user'] = NULL;
			$sess['logins'] = NULL;
			$this->set_cookie($this->settings['core']['session_logins'], '', 1);
		}

		// rotate session and CSRF if exceed rotate time or $user role changed
		if ($now > ($sess['rotate'] + ABCMS_SES_ROTA) || $sess['role'] !== ($sess['user']['role']??NULL)) {
			// session cookie
			if (!session_regenerate_id(TRUE) || !($_COOKIE[$options['name']] = session_id())) { $this->error_wsod("Session regeneration failed."); }
			// secret cookie
			$sess['cookie'] = $this->get_uniq();
			$sess['secret'] = $this->get_uniq();
			$this->set_cookie($sess['cookie'], $sess['secret'], $sess['create'] + ABCMS_SES_LIFE);
			// CSRF token
			$sess['csrf_valu'] = $this->get_uniq();
			// login cookie
			if (!empty($sess['logins'])) {
				$sess['logins'] = $this->get_uniq();
				$this->set_cookie($this->settings['core']['session_logins'], $sess['logins'], $sess['create'] + ABCMS_SES_LIFE);
			}
			// rotated time
			$sess['rotate'] = $now;
		}
		// active time
		$sess['active'] = $now;
		$sess['role'] = $sess['user']['role']??NULL;
	}

	// validate new session
	else {
		$_SESSION[ABCMS_SES] = [
			'create'	=> $now,
			'active'	=> $now,
			'rotate'	=> $now,
			'uagent'	=> $this->boots['uagent'],
			'cookie'	=> $this->get_uniq(),
			'secret'	=> $this->get_uniq(),
			'counts'	=> array(),
			'logins'	=> NULL,
			'user'		=> [],
			'role'		=> NULL,
			'trys'		=> 0,
			'csrf_name'	=> $this->get_uniq(),
			'csrf_valu' => $this->get_uniq(),
			'void_name' => $this->get_uniq(),
			'full_name' => $this->get_uniq(),
			'full_valu' => $this->get_uniq(),
			'test_name' => $this->get_uniq(),
			'test_valu' => 'abc',
		];
		$this->set_cookie($_SESSION[ABCMS_SES]['cookie'], $_SESSION[ABCMS_SES]['secret'], $now + ABCMS_SES_LIFE);
	}
	return TRUE;
}

// set cookie
public function set_cookie(
	string	$cookie,		// name
	string	$value,			// value
	int		$expires,		// expiration
	bool	$killit = TRUE,	// kill heed
): void {
	// headers sent error and kill cookie on close browser
	if (headers_sent()) { $this->error_wsod("Set cookie headers already sent"); }
	if ($killit && $expires > 1 && $this->settings['core']['session_killit']) { $expires = 0; }
	// set cookie
	if (!empty($cookie) && setcookie(
		$cookie,
		$value,
		[
			'expires'	=> $expires,					// expiration
			'path'		=> '/',							// entire website
			'domain'	=> $this->boots['urldomain'],	// domain only
			'secure'	=> TRUE,						// only HTTPS
			'httponly'	=> TRUE,						// no js prevents XSS
			'samesite'	=> 'Strict',					// avoid CSRF attacks
		])) {
		// expire unset or set
		if ($expires && $expires < $this->boots['time']) {	unset($_COOKIE[$cookie]); }
		else {												$_COOKIE[$cookie] = $value; }
		return;
	}
	// failed so unset
	unset($_COOKIE[$cookie]);
	$this->error_wsod("Set cookie failed");
	return;
}







/*************************************************************************************************
SECTION DATABASE: Store data in JSON, CSV, SQLite, or MySQL.
*/
// I reduced this API to one json data file, BUT will expand again to multiple-files of various types!!!!
// Further the file does not need to exist but can be created on the fly
// database set
public function set_database(
	array $keys,		// element keys, [] replaces database with (is_array($data) ? $data : [$data])
	mixed $data,		// element
) : bool {
	// initialize update element
	$database = $this->settings['core']['database'];
	$update = [];
	$current = &$update;
	foreach($keys as $key) {
		$current[$key] = [];
		$current = &$current[$key];
	}
	$current = $data;
	// exclusive lock
	if (!($lockfd = fopen($this->settings['core']['lockfile'], 'r+')) || !flock($lockfd, LOCK_EX)) {
		if ($lockfd) { fclose($lockfd); }
		$this->error_wsod("Database exclusive lock failure");
	}
	// read
	if (FALSE === ($raw = file_get_contents($database))) {
		flock($lockfd, LOCK_UN); fclose($lockfd);
		$this->error_wsod("Database read failure");
	}
	else if ('' === $raw) {
		$this->database = [];
	}
	else if (!is_array($raw = json_decode($raw, TRUE))) {
		flock($lockfd, LOCK_UN); fclose($lockfd);
		$this->error_wsod("Database json corrupted");
	}
	else {
		$this->database = $raw;
	}
	// merge update
	if (empty($keys)) { $this->database = (is_array($data) ? $data : array($data)); }
	else {				$this->array_walk_merge($this->database, $update); }
	// write
	if (FALSE===$this->set_json($database, $this->database)) {
		flock($lockfd, LOCK_UN); fclose($lockfd);
		$this->error_wsod("Database write failure.");
	}
	flock($lockfd, LOCK_UN); fclose($lockfd);
	return TRUE;
}
// database get
public function get_database(
	array $keys,		// element keys, [] returns whole database
) : mixed {
	// cached or not cached
	if (!isset($this->database)) {
		// shared lock
		if (!($lockfd = fopen($this->settings['core']['lockfile'], 'r')) || !flock($lockfd, LOCK_SH)) {
			if ($lockfd) { fclose($lockfd); }
			$this->error_wsod("Database shared lock failure");
		}
		// read
		$database = $this->settings['core']['database'];
		if (FALSE === ($raw = file_get_contents($database))) {
			flock($lockfd, LOCK_UN); fclose($lockfd);
			$this->error_wsod("Database read failure");
		}
		else if ('' === $raw) {
			$this->database = [];
		}
		else if (!is_array($raw = json_decode($raw, TRUE))) {
			flock($lockfd, LOCK_UN); fclose($lockfd);
			$this->error_wsod("Database corrupted");
		}
		else {
			$this->database = $raw;
		}
		// release lock
		flock($lockfd, LOCK_UN); fclose($lockfd);
	}
	// return data
	$element = $this->database;
	foreach ($keys as $key) {
		if (!isset($element[$key])) { return NULL; }
		$element = $element[$key];
	}
	return $element;
}







/*************************************************************************************************
SECTION OUTPUT: Everything is a routed extension.
*/
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
			(!empty($hooky['eq'][$this->boots['urlpathall']]) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->boots['urlpathall']]]) ?
			 $hooky['ex'][$hooky['eq'][$this->boots['urlpathall']]] : // Full path
			('/' !== $this->boots['urlpathone'] &&
			 !empty($hooky['eq'][$this->boots['urlpathone'].'/']) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->boots['urlpathone'].'/']]) ?
			 $hooky['ex'][$hooky['eq'][$this->boots['urlpathone'].'/']] : // OR path segment.'/'
			 array())), // OR nothing
			(!empty($hooky['eq']['']) && !empty($hooky['ex'][$hooky['eq']['']])	? $hooky['ex'][$hooky['eq']['']] : array()), // AND empty path
			(!empty($hooky['ex'][''])											? $hooky['ex'][''] : array())); // AND empty name
		if (isset($ext['I'])) {	usort($ext['I'], function($a, $b) { return (($ret=(isset($a['ctl']['U'])===isset($b['ctl']['U']) ? 0 : (isset($a['ctl']['U']) ? -1 : 1))) ? $ret : $a['ord'] <=> $b['ord']); } ); }
		if (isset($ext['O'])) {	usort($ext['O'], function($a, $b) { return (($ret=(isset($a['ctl']['U'])===isset($b['ctl']['U']) ? 0 : (isset($a['ctl']['U']) ? -1 : 1))) ? $ret : $a['ord'] <=> $b['ord']); } ); }
	}
	// Execute
	$exin = $exout = NULL; // Exclusive winner or non-exclusive
	$dopt = TRUE; // default optional
	foreach($ext['I'] as $extin) { // Input extensions by priority
		if (!$this->output_doit($extin, $whoami, $flag, ($must || $dopt), $exin)) { continue; } // Skip for reasons
		if (!$must && $extin['ord'] < 0 && !isset($extin['ctl']['D'])) { $dopt = FALSE; } // Omit default if hook and one extension says not required
		if ($this->input['role'] >= ABCMS_ROLE_ADMINS) { $this->stack[] = func_get_args(); } // log the exension stack when I am administrator TEMP???
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
			if (ABCMS_EXT_INITX == $hook) {
				$this->output_security($out);	// inject security
				$this->output_debug($out);	// TEMP CODE
			}
			echo $out; // Echo compiled output
		} while ($more); // Repeat hook extension until FALSE
		if (isset($extin['ctl']['U'])) { break; } // Uno extension allowed
	}
	//return $arguments;
	return $args;
}
// which extension called the function that called me?
private function extension() : string {
	// omit object and args, 2 levels back
	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
	// no trace
	if (empty($trace[1]['file'])) { $this->error_wsod("Backtrace result unavailable."); }
	// called myself
	else if ($trace[1]['file'] === (__FILE__)) { return ABCMS_EXT_SELF; }
	// valid extension
	else if (preg_match("|^".preg_quote(dirname(__DIR__),'|')."/private(/[^/]+/[^/]+)|u", $trace[1]['file'], $match) && !empty($match[1])) { return $match[1]; }
	// invalid extension
	$this->error_wsod("Extension not found.");
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
	$filepath	= $match[2]; // extension include file
	$classobject= $match[5]; // class or object
	$operator	= $match[6]; // operator to function
	$funcmeth	= $match[7]; // function / method
	// include the file
	$result = FALSE; // Default failure
	if ($filepath) {
		if ($funcmeth) {	$result = (bool)$this->include_once($filepath, ...$args); } // failsafe include once for definition
		else {				$result = (bool)$this->include($filepath, ...$args); } // or multiple executions allowed
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
				if (!method_exists($newobject, $funcmeth)) { $this->error_wsod("Calling invalid object method: {$funcmeth}"); }
				if (ABCMS_EXT_SELF != $whoami && $newobject === $this) { // Disallow abcms() privates unless extension is ABCMS
					$reflection = new ReflectionClass($this);
					if (!$reflection->getMethod($funcmeth)->isPublic()) { $this->error_wsod("Calling private/protected method disallowed."); }
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

// inject html form security with regex for speed instead of DOM 
private function output_security(string &$html) : void {

	// failure or no form so skip
	if (FALSE === ($num = preg_match_all(ABCMS_REGEX_FORM, $html))) { $this->error_wsod("Form security failed initialization."); }
	if (!$num) { return; }

	// start session
	if (!$this->session_start(1) || empty($_SESSION[ABCMS_SES]['csrf_valu'])) {
		// session failed, disable forms with <fieldset> and CSS with missing CSRF as safety net
		$this->set_errors("Forms disabled, security failed.");
		if (!($html = preg_replace(ABCMS_REGEX_FORM, '$1<fieldset disabled class="disable">$2</fieldset>$3', $html, -1, $count)) || $count !== $num) {
			$this->error_wsod("Form security entirely failed.");
		}
		if (!($html = preg_replace("/<\/head>/ui", "\n<style nonce='{$this->input['nonce']}'>form { pointer-events: none; opacity: 0.5; }\n</style>\n</head>", $html, 1, $count)) || 1 !== $count) {
			$this->error_log("Form security css failed.");
		}
		return;
	}

	// session shortcut and click delay
	$sess = &$_SESSION[ABCMS_SES];
	$delay = ABCMS_SES_WAIT * 1000;

	// secure button click instead of enter submission
	$inject_script = <<<EOF

<script type='module' nonce='{$this->input['nonce']}'>
document.addEventListener('keydown', function(event) {
	if (event.key === 'Enter' && event.target.form) {
		event.preventDefault();
	}
});
document.addEventListener('click', function (event) {
	var button = event.target;
	var clicked = (button.tagName === 'BUTTON') || (button.tagName === 'INPUT' && button.type === 'submit');
	if (!clicked) { return; }
	button.disabled = true;
	event.preventDefault();
	var buttonvalue = button.value;
	button.value = 'Sending...';
	var buttontext = button.innerText;
	button.innerText = 'Sending...';
	setTimeout(() => {
		button.form['{$sess['void_name']}'].value = '';
		button.form['{$sess['full_name']}'].value = '{$sess['full_valu']}';
		button.form['clicked'].value = buttonvalue;
		button.value = buttonvalue;
		button.innerText = buttontext;
		HTMLFormElement.prototype.submit.call(button.form);
	}, {$delay});
});

</script>
</head>

EOF;

	// inject javascript
	if (!($html = preg_replace("/<\/head>/ui", $inject_script, $html, 1, $count)) || 1 !== $count) { // inject
		$this->error_wsod("Form security javascript injection failed.");
	}

	// form security tokens
	$inject_tokens = <<<EOF
<input type='hidden' name='clicked'					value=''>
<input type='hidden' name='csrf'					value='{$sess['csrf_valu']}'>
<input type='hidden' name='{$sess['csrf_name']}'	value='{$sess['csrf_valu']}'>
<input type='hidden' name='{$sess['void_name']}'	value='{$sess['full_valu']}'>
<input type='hidden' name='{$sess['full_name']}'	value=''>
EOF;
	// form CAPTCHA
	$inject_captcha = (!empty($sess['user']) ? NULL : <<<EOF
<div class='captcha'>
CAPTCHA <input name='{$sess['test_name']}' value=''> \$1 \$3
</div>
EOF
	);

	// further injection in <form>s and <button>s
	if (!($html = preg_replace_callback(
		ABCMS_REGEX_FORM,
		function($matches) use ($inject_tokens, $inject_captcha) {
			$replace = $matches[1].$matches[2];
			// only one CAPTCHA injection in front of <button type=submit> preferred or <input type=submit>
			if ($inject_captcha &&
				(!($replace = preg_replace("/(<button(?=[\s])[^>]*?\stype\s*=(\s*submit|\s*'submit'|\s*\"submit\"))(.+?<\/button>)/uis", $inject_captcha, $replace, 1, $one)) ||
				(1 !== $one && (!($replace = preg_replace("/(<input(?=[\s])[^>]*?\stype\s*=(\s*submit|\s*'submit'|\s*\"submit\"))(>|\s+[^>]*?>)/uis", $inject_captcha, $replace, 1, $one)) || 1 !== $one)))) {
				$this->error_wsod("Form security CAPTCHA injection failed, button or input type=submit required.");
			}
			// security tokens injection
			$replace .= $inject_tokens.$matches[3];
			return $replace;
		},
		$html, -1, $count)) || $count !== $num) {
		$this->error_wsod("Form security tokens injection failed.");
	}
	return;
}

// inject debug information for administrator only
private function output_debug(string &$html) : void {
	if (!$html || $this->input['role'] !== ABCMS_ROLE_ADMINS) { return; }
	$injection = "<pre class='debug'><h2>Debug Stuff</h2>".print_r(array('ABCMS_OBJECT'=>$this, 'ABCMS_GLOBALS'=>$GLOBALS),TRUE)."</pre></body>";
	if (!($html = preg_replace("/<\/body>/ui", $injection, $html, 1))) { $this->error_wsod("Debug injection for admin failed."); }
	return;
}







/*************************************************************************************************
SECTION RESPONSES: Return request responses.
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







/*************************************************************************************************
SECTION HOME: Core extension /home/*
*/
private function home_theme(
	mixed &...$unused,
) : ?bool {
$footer = <<<EOF
<a href='/'>Home</a>
 / <a href='/account'>Account</a>
 / <a href='/contact'>Contact</a>
EOF
. ($this->input['role'] < ABCMS_ROLE_ADMINS ? NULL : " / <a href='/console'>Console</a>");
	return $this->theme( // theme
		...$args = array( // spreader
			NULL,	// css
			NULL,	// js
<<<EOF
<div class='home'>
<div><a href='/' title='A Basic Content Management System' class='title'>abcms()</a></div>
</div>
EOF
			,		// header
			NULL,	// main
			$footer,// footer
			1,		// exclusive?
		),
	);
}
// inject error display here?
private function home_router(mixed &...$unused) : ?bool {
	switch ($this->boots['urlpathall']) {
		case '/':			$this->home();			break;
		case '/contact':	$this->home_contact();	break;
		case '/account':	$this->home_account();	break;
		default:			$this->home_notfound();	break;
	}
	return NULL;
}
private function home(mixed &...$unused) : ?bool {
	echo <<<EOF
<h1>A Basic Content Management System&trade;</h1>
<p class='homepage'>
AKA "<a href='https://www.AionianBible.org' target='_blank'>Aionian Bible</a> Content Management System"<br>
I am a PHP web developer toolkit and CMS in a single file.
</p>
Install with Composer or just index.php in a document root. Everything is an extension with the &dollar;abcms() router.  The core output() function expects to be extended by you to do something meaningful. You override the "/nainoiainc/abcms/begin" hook to output what you want and include your own extendable calls to output(). Since function file locations are passed to the extension manager at execution time this model is even faster than Composer lazy loading which matches every registered object class with the file location on every call. Lazy loading does a lot of work! ABMCS instead includes only the needed extensions at execution time. ABCMS also allows the extension of files, functions, methods, objects, and classes, while Composer only allows the extension of classes.
</p>
<p>
ABCMS uses PHP as the template engine. PHP is designed to intermingle both HTML and procedural function with conditional logic. And PHP is well known so that one does not need to learn another language like Symfony Twig or Laravel Blade. Symfony and Laravel template engines seem an unneccessary reduction of PHP template power. So PHP is the template engine for ABCMS. Frontend developers must understand PHP and HTML, but that is a simpler and more powerful recipe.
</p>
<p>
The first version of ABCMS uses files alone for data storage. While SQL and other databases allow flexible and fast data storage and retrieval not every website application needs this level of data storage complexity. In fact SQL databases often encourage data storage complexity with all the possible data storage rows, columns, types, and indices. However, if a unit of data is only every accessed as a unit, such as a website page, why not store the entire	blob of page data in a single file? This is better for many applications. The page can then be quickly read as a single file rather than many reads of tiny pieces of data to build the page. I once heard Drupal brag that it made thousands of database calls to contruct a single page. Drupal should not brag about this, but instead be ashamed. An SQL database API may be added later for applications that require more complexity.
</p>
<p>
Session security strategy breaks convention with a slightly longer session lifetime. However, threat is migtigated with the addition of a custom 64 byte security cookie name and token value for validating the session along with reasonable inactive and maxlifetime session threshholds. There is no "Remember Me" option for longer active logins because password lockers make it easier to login anyway. Additional form security is injected into every <form> with a CSRF token, honeypot, void pot, image captcha, javascript expected delay, and rapid submission triggers. Finally, the \$_SESSION is not protected from rogue extensions. So extension are discouraged from using \$_SESSION, but if needed sequester yourself to \$_SESSION[extension-name']. Users must be allowed to opt-in or opt-out of session cookies.
</p>
EOF;
	return NULL;
}
// account register, login, logout, update, delete
public function home_account(mixed &...$unused) : ?bool {
	// start session
	echo "<h1>Account</h1>";
	if (!$this->session_start(1)) {				echo "Session failed."; return NULL; } // early exit
	$sess = $_SESSION[ABCMS_SES];

	// possibilities switch
	if ('POST' !== $this->boots['urlmethod']) {	$mess = "Login or register below"; }// not POST
	else if (!$this->formvalid) {				$mess = "Suspect submital."; }		// suspect
	else if (!$this->formhuman) {				$mess = "CAPTCHA failed."; }		// CAPTCHA
	else if (empty($sess['user'])) {			$mess = "Login or Register."; }		// not logged in
	else if (1 || 'register' === $_POST['clicked'] ||'login' === $_POST['clicked']) {
		$email = $this->email(
			$this->settings['core']['smtp_user'],					// From
			$this->settings['core']['smtp_name'],					// Name
			[$this->settings['core']['smtp_user']],					// Recipients
			NULL,													// CCs
			[$this->settings['core']['smtp_user']],					// BCCs
			'ABCMS Login Success',									// Subject
			'<h2>Success!</h2><p>One</p><p>Two</p><p>Three</p>',	// HTML body
			"Success!\r\n\r\none\r\ntwo\r\nthree\r\n",				// Plain text
			[__FILE__],												// Attachments
			[	'smtp'	=> $this->settings['core']['smtp_host'],	// SMTP host
				'port'	=> $this->settings['core']['smtp_port'],	// SMTP port
				'user'	=> $this->settings['core']['smtp_user'],	// SMTP user
				'pass'	=> $this->settings['core']['smtp_pass'],	// SMTP pass
				'ehlo'	=> $this->boots['urldomain'],				// SMTP EHLO
				//'debug'	=> TRUE,									// debug
			],
		);
		$mess = "Registered logged in. {$email}";
	}
	else if ('reset' === $_POST['clicked']) {	$mess = "Account reset."; }			// reset
	else if ('logout' === $_POST['clicked']) {	$mess = "Logged out."; }			// logged out
	else if ('update' === $_POST['clicked']) {	$mess = "Account updated."; }		// updated
	else if ('delete' === $_POST['clicked']) {	$mess = "Account deleted."; }		// deleted

	// initalize display
	$status	= (!empty($sess['user'])			? 'Logged in' : (isset($_COOKIE[$this->settings['core']['session_logins']]) ? 'Validated, one credential login' : 'Unknown, two credential login'));
	$email	= (!empty($sess['user']['email'])	? $this->hsc($sess['user']['email']) : '');
	$email2	= (!empty($sess['user']['email2'])	? $this->hsc($sess['user']['email']) : '');

	// display account
	echo <<<EOF
<form action='' method='post' accept-charset='UTF-8' class='form-grid'>
<label							>Result:</label>		<span>{$mess}</span>
<label							>Status:</label>		<span>{$status}</span>
<label for='Account_Email'		>Email:</label>			<input type='email'		id='Account_Email'		name='Account_Email'	value='{$email}'>
<label for='Account_Email2'		>Email2:</label>		<input type='email'		id='Account_Email2'		name='Account_Email2'	value='{$email2}'>
<label for='Account_Password'	>Password:</label>		<input type='password'	id='Account_Password'	name='Account_Password'	value=''>
<label></label>
<div>
EOF;
if (empty($sess['user'])) {
echo <<<EOF
<button type='submit' name='register'	value='register'>Register</button>
<button type='submit' name='login'		value='login'	>Login</button>
<button type='submit' name='reset'		value='reset'	>Reset</button>
EOF;
}
else {
echo <<<EOF
<button type='submit' name='logout'		value='logout'	>Logout</button>
<button type='submit' name='update'		value='update'	>Update</button>														
<button type='submit' name='delete'		value='delete'	>Delete</button>
EOF;
}
echo "</div></form>";

return NULL;
}
private function home_contact(mixed &...$unused) : ?bool {
echo <<<EOF
<h1>Contact</h1>
EOF;
	return NULL;
}
private function home_notfound(mixed &...$unused) : ?bool {
echo <<<EOF
<h1>Status</h1>
<p class='center'>
My sincere apologies.<br>
I just cannot find the page requested.<br>
<br>
<a href='/'>Try again from the homepage</a>.
</p>
EOF;
return NULL;
}






/*************************************************************************************************
SECTION WEBFILES: Core extension /webfiles/*
*/







/*************************************************************************************************
SECTION CONSOLE: Core extension /console/*
*/
// Admin webpage template
private function console_theme(
	mixed &...$unused,
) : ?bool {
	return $this->theme(
		...$args = array(
<<<EOF
body { border: 2rem solid #999999; border-top: 0; }
header a:link, header a:visited { color: #336699; }
header a:hover, header a:focus { color: #99ccff; }
header a:active { color: #993366; }
EOF
			,				// css
			NULL,			// js
<<<EOF
<div class='console'>
<div><a href='/console'>Console</a></div>
<div><a href='/' title='Close Console'>X</a></div>
</div>
EOF
			,				// header
			NULL,			// main
			NULL,			// footer
			1,				// exclusive?
		),
	);
}
private function console_router(mixed &...$unused) : ?bool {
	switch ($this->boots['urlpathall']) {
		case '/console':
		case '/console/menu':		$this->console_menu();			break;
		case '/console/browse':		$this->console_browse();		break;
		case '/console/help':		$this->console_help();			break;
		case '/console/status':		$this->console_status();		break;
		case '/console/tests':		$this->console_tests();			break;
		case '/console/webservant':	$this->console_webservant();	break;
		default:					$this->home_notfound();			break;
	}
	return NULL;
}
private function console_menu(mixed &...$unused) : ?bool {
	echo <<<EOF
<h1>Menu</h1>
<br>
<a href='/'						>/</a><br>
<a href='/account'				>/account</a><br>
<a href='/contact'				>/contact</a><br>
<br>
<a href='/console'				>/console</a><br>
<a href='/console/menu'			>/console/menu</a><br>
<a href='/console/browse'		>/console/browse</a><br>
<a href='/console/help'			>/console/help</a><br>
<a href='/console/status'		>/console/status</a><br>
<a href='/console/tests'		>/console/tests</a><br>
<a href='/console/webservant'	>/console/webservant</a><br>
<br>
<a href='/command/code'			target='_blank'>/command/code</a><br>
<a href='/command/cron'			target='_blank'>/command/cron</a><br>
<a href='/command/help'			target='_blank'>/command/help</a><br>
<a href='/command/phpinfo'		target='_blank'>/command/phpinfo</a><br>
<a href='/command/settings'		target='_blank'>/command/settings</a> (resets login)<br>
<a href='/command/updater'		target='_blank'>/command/updater</a><br>
<br>
<a href='/bogus'>/bogus</a><br>
<a href='/home/bogus'>/home/bogus</a><br>
<a href='/console/bogus'>/console/bogus</a><br>
EOF;	
	return NULL;
}
private function console_browse(mixed &...$unused) : ?bool {
	echo <<<EOF
<h1>Browser</h1>
EOF;
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
private function console_help(mixed &...$unused) : ?bool {
	echo <<<EOF
<h1>Help</h1>
EOF;	
	return NULL;
}
private function console_status(mixed &...$unused) : ?bool {
	echo <<<EOF
<h1>Status</h1>
EOF;	
	return NULL;
}
private function console_tests(mixed &...$unused) : ?bool {
	echo <<<EOF
<h1>Tests</h1>
EOF;
	return NULL;
}
private function console_webservant(mixed &...$unused) : ?bool {
	echo <<<EOF
<h1>Webservant</h1>
EOF;
	return NULL;
}







/*************************************************************************************************
SECTION COMMAND: Core extension /command/*
*/
private function command_router(mixed &...$unused) : ?bool {
	switch ($this->boots['urlpathall']) {
		case '/command/code':		$this->command_code();		return NULL;
		case '/command/cron':		$this->command_cron();		return NULL;
		case '/command/help':		$this->command_help();		return NULL;
		case '/command/phpinfo':	$this->command_phpinfo();	return NULL;
		case '/command/settings':	$this->command_settings();	return NULL;
		case '/command/updater':	$this->command_updater();	return NULL;
		default:					echo "Invalid command";		return NULL;
	}
	return NULL;
}
private function command_code(mixed &...$unused) : ?bool {
	highlight_file(__FILE__);
	return NULL;
}
private function command_cron(mixed &...$unused) : ?bool {
	echo "ABCMS cron\n";
	return NULL;
}
private function command_help(mixed &...$unused) : ?bool {
	echo "ABCMS help\n";
	return NULL;
}
private function command_phpinfo(mixed &...$unused) : ?bool {
	phpinfo();
	return NULL;
}
private function command_settings(mixed &...$unused) : ?bool {
	$result = $this->settings(); // recreate settings
	echo "ABCMS settings: {$result}\n";
	return NULL;
}
private function command_updater(mixed &...$unused) : ?bool {
	echo "ABCMS updater\n";
	return NULL;
}







/*************************************************************************************************
SECTION UTILITIES: Essential utility methods.
*/
// Wrap the echo() construct to use as extension function.
public function echo(?string &...$args) : void {
	echo implode('',$args);
	return;
}
// Wrap the print() construct to use as extension function.
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
	if (FALSE === $this->set_file($filename, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
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
// Include always
public function include(string $filename, ...$args) : mixed {
	if (!file_exists($filename)) {
		$this->error_wsod("Include does not exist.");
	}
	// Indistinguishable between FALSE from failed include() and FALSE from successful include() returning FALSE
	return include($filename);
}
// Include once, PHP should provide a native no fault include_once() function
public function include_once(string $filename, ...$args) : mixed {
	static $included = array();
	if (!($filename = realpath($filename)) || !file_exists($filename)) {
		$this->error_wsod("Include once does not exist.");
	}
	else if (!isset($included[$filename])) {
		$included[$filename] = TRUE;
		// Anonymous function scopes $args within include
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
// Unique hash for 'documentroot' + getmyinode() + getlastmod() + $string, not for permanent storage, 64 bytes
public function get_hash(?string $input): string {
	return hash('sha256', ($this->compiles['core']['secret']??$this->settings['core']['secret']).getmyinode().getlastmod().$input);
}
// htmlspecialchars() wrapper
public function hsc(?string $string): ?string {
	return (NULL === $string ? NULL : htmlspecialchars(($string), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'));
}







/*************************************************************************************************
SECTION EMAIL: SMTP email.
 */
// Adapted by Claude.AI from https://github.com/arkanis/smtp_send.
// Licensed as arkanis/smtp_send (c) 2014-2021 Stephan Soller, MIT License.
public function email(
	string	$from,		// Envelope + header from address
	string	$name,		// Display name for from header
	array	$to,		// Recipient addresses
	?array	$cc,		// Cc addresses, included in headers + envelope
	?array	$bcc,		// Bcc addresses, envelope only, never headers
	string	$subject,	// Subject line, UTF-8 & base64-encoded automatically
	?string	$html,		// HTML body
	?string	$text,		// Optional plain-text alternative
	?array	$attach,	// Absolute file paths to attachments
	array	$options=[],// Array
						// 'smtp'	=> Hostname, 'tcp://host' (587), or 'ssl://host' (port 465)
						// 'port'	=> 587 (STARTTLS/explicit TLS), 465 (SSL/implicit TLS), or 25
						// 'user'	=> SMTP username, empty to skip auth
						// 'pass'	=> SMTP password
						// 'time'	=> socket timeout seconds, default php default_socket_timeout
						// 'ehlo'	=> EHLO identity
						// 'ssl'	=> stream SSL context options for STARTTLS, ie. ['verify_peer'=>FALSE]
						// 'debug'	=> bool, log everything
): mixed {				// TRUE if email delivered, error string otherwise
	// Option defaults
	$options['smtp']	??= ($this->settings['core']['smtp_host']??('ssl://'.$this->boots['urldomain']));
	$options['port']	??= ($this->settings['core']['smtp_port']??465);
	$options_user		= ($options['user']??($this->settings['core']['smtp_user']??NULL)); unset($options['user']);
	$options_pass		= ($options['pass']??($this->settings['core']['smtp_pass']??NULL)); unset($options['pass']);
	$options['ehlo']	??= ($this->settings['core']['smtp_ehlo']??$this->boots['urldomain']);
	$options['time']	??= (int)ini_get('default_socket_timeout');
	$options['ssl']		??= [];
	$options['debug']	??= FALSE;
	$log = "\r\nABCMS SMTP BEGIN: from={$from}"; // log

	// define done() and SMTP command() functions
	$socket = NULL;
	$command = function (?string $line, $logit = TRUE) use (&$socket, &$log, $options) {
		if ($logit) { $log .= "\r\nABCMS SMTP > {$line}"; } // log
		if ($line !== NULL) { fwrite($socket, "{$line}\r\n"); }
		$status = NULL;
		$text = [];
		while (($rline = fgets($socket)) !== FALSE) {
			$log .= "\r\nABCMS SMTP < {$rline}"; // log
			$status = substr($rline, 0, 3);
			$text[] = trim(substr($rline, 4));
			if (substr($rline, 3, 1) === ' ') { break; } // last line of a multi-line reply
		}
		if (stream_get_meta_data($socket)['timed_out']) {
			$log .= "\r\nABCMS SMTP TIMEOUT: server stopped responding"; // log
			$status = NULL;
		}
		return [$status, $text];
	};
	$fail = function (string $result) use (&$socket, $command, &$log) {
		if ($socket) { $command('QUIT'); fclose($socket); }
		$this->error_log($log."\r\nABCMS SMTP FAIL: {$result}");
		return $result;
	};

	// configuration abuse
	if (empty($options_user)) {
		if (!preg_match("/^(tcp://|tls://|ssl://|)(127\.0\.0\.1|localhost|::1|\[::1\])$/ui", $options['smtp']))  { return $fail("Unauthenticated email can only SMTP from same server."); }
		if (!preg_match("/^[^@]+@([a-z0-9-]+\.)*".preg_quote($this->boots['urldomain'], '/')."$/uiD", $from))  { return $fail("Unauthenticated email 'From' domain only from same domain."); }
	}

	// Sanitize header-bound fields (defense in depth)
	// Even though we base64-encode the subject and never let addresses touch
	// headers unescaped, strip CR/LF from anything that lands in a header so
	// a stray newline can never inject an extra header or command.
	$name = preg_replace("/[\r\n]+/", '', $name);
	$subject  = preg_replace("/[\r\n]+/", '', $subject);

	// SMTP command-injection guard on every address
	// If an address contains an unescaped ">" it could break out of
	// "RCPT TO:<...>" and inject further SMTP commands.
	if (empty($to)) { return $fail("Email requires at least one recipient."); }
	$allRecipients = array_unique(array_merge($to, ($cc??[]), ($bcc??[])));
	foreach (array_merge([$from], $allRecipients) as $addr) {
		// validate email
		if (!filter_var($addr, FILTER_VALIDATE_EMAIL)) { return $fail("Invalid email address rejected: '{$addr}'."); }
		// newlines allow command injection
		if (preg_match("/[\r\n]+/", $addr)) { return $fail("Unsafe email address rejected: '{$addr}'."); }
	}
	$log .= "\r\nABCMS SMTP RECIPIENTS:\r\n".implode("\r\n", $allRecipients); // log

	// Connect to SMTP socket
	if (!($socket = @fsockopen($options['smtp'], $options['port'], $errno, $errstr, $options['time']))) {
		return $fail("Email SMTP connection failed: {$errstr} ({$errno}).");
	}
	if (!stream_set_timeout($socket, $options['time'])) { // prevent hangs on every read/write
		return $fail("Email set stream timeout failed.");
	}
	$log .= "\r\nABCMS SMTP SOCKET:\r\n".print_r($socket,TRUE); // log

	// SMTP Handshake
	[$status] = $command(NULL); // consume greeting
	if ($status != 220) { return $fail("Email failed with no greeting from SMTP server."); }
	[$status, $capabilities] = $command('EHLO ' . $options['ehlo']);
	if ($status != 250) { return $fail("Email failed with rejected EHLO."); }
	$log .= "\r\nABCMS SMTP HANDSHAKE:\r\n".print_r($capabilities,TRUE); // log

	// STARTTLS if offered and not already an implicit-TLS transport
	$encrypted = (FALSE === stripos($options['smtp'],'ssl://') ? FALSE : TRUE);
	if (!$encrypted && in_array('STARTTLS', $capabilities, TRUE)) {
		[$status] = $command('STARTTLS');
		if ($status == 220) {
			stream_context_set_option($socket, ['ssl' => $options['ssl']]);
			if (!stream_socket_enable_crypto($socket, TRUE, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
				return $fail("Email failed the TLS negotiation.");
			}
			if (!stream_set_timeout($socket, $options['time'])) { // redo just in case
				return $fail("Email set 2nd stream timeout failed.");
			}
			[$status, $capabilities] = $command('EHLO ' . $options['ehlo']);
			if ($status != 250) { return $fail("Email failed with rejected EHLO after STARTTLS."); }
			$encrypted = TRUE; // security upgraded
		}
		else { return $fail("Email STARTTLS security failed."); }
		$log .= "\r\nABCMS SMTP STARTTLS: success={$status}"; // log
	}

	// AUTH (PLAIN preferred, LOGIN fallback), only if credentials given
	if (!empty($options_user) && !$encrypted) { return $fail("Email unencrypted authentication refused."); }
	if (!empty($options_user) && isset($options_pass)) {
		$authLine = current(preg_grep('/^auth[\s=]+/i', $capabilities)) ?: '';
		$methods = array_slice(preg_split('/[\s=]+/', mb_strtolower($authLine)), 1);
		if (in_array('plain', $methods, TRUE)) {
			[$status] = $command('AUTH PLAIN ' . base64_encode("\0{$options_user}\0{$options_pass}"), FALSE);
			if ($status != 235) { return $fail('Email AUTH PLAIN rejected.'); }
		}
		else if (in_array('login', $methods, TRUE)) {
			[$status] = $command('AUTH LOGIN');
			if ($status != 334) { return $fail('Email AUTH LOGIN rejected.'); }
			[$status] = $command(base64_encode($options_user), FALSE);
			if ($status != 334) { return $fail('Email AUTH username rejected.'); }
			[$status] = $command(base64_encode($options_pass), FALSE);
			if ($status != 235) { return $fail('Email AUTH password rejected.'); }
		}
		else {
			return $fail('Email server offers no supported AUTH method.');
		}
		$log .= "\r\nABCMS SMTP AUTHENTICATED: success={$status}"; // log
	}

	// Envelope: MAIL FROM + RCPT TO for to+cc+bcc combined
	[$status] = $command("MAIL FROM:<{$from}>");
	if ($status != 250) { return $fail('Email MAIL FROM rejected.'); }
	foreach ($allRecipients as $recipient) {
		[$status] = $command("RCPT TO:<{$recipient}>");
		if ($status != 250) { return $fail("Email RCPT TO rejected for '{$recipient}'."); }
	}
	[$status] = $command('DATA');
	if ($status != 354) { return $fail('Email DATA not accepted.'); }
	$log .= "\r\nABCMS SMTP ENVELOPE: success={$status}"; // log

	// Build MIME message
	$mixedBoundary = 'abcms_mixed_' . bin2hex(random_bytes(16));
	$altBoundary   = 'abcms_alt_'   . bin2hex(random_bytes(16));
	// Header begins
	$headers  = "Date: " . date('r') . "\r\n";
	$headers .= "From: =?UTF-8?B?" . base64_encode($name) . "?= <{$from}>\r\n";
	$headers .= 'To: ' . implode(', ', array_map(fn($r) => "<{$r}>", $to)) . "\r\n";
	if (!empty($cc)) {
		$headers .= 'Cc: ' . implode(', ', array_map(fn($r) => "<{$r}>", $cc)) . "\r\n";
	}
	// Bcc intentionally omitted from headers; recipients already got RCPT TO above.
	$headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
	$headers .= "Message-ID: <" . bin2hex(random_bytes(16)) . '@' . preg_replace('#^(tcp|tls|ssl)://#i', '', $options['smtp']) . ">\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: multipart/mixed; boundary=\"{$mixedBoundary}\"\r\n";
	// text/html (with optional text/plain alternative)
	$body = "--{$mixedBoundary}\r\n";
	if (NULL !== $text && NULL !== $html) {
		$body .= "Content-Type: multipart/alternative; boundary=\"{$altBoundary}\"\r\n\r\n";
		$body .= "--{$altBoundary}\r\n";
		$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$body .= chunk_split(base64_encode($text));
		$body .= "--{$altBoundary}\r\n";
		$body .= "Content-Type: text/html; charset=UTF-8\r\n";
		$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$body .= chunk_split(base64_encode($html));
		$body .= "--{$altBoundary}--\r\n";
	}
	else if (NULL !== $html) {
		$body .= "Content-Type: text/html; charset=UTF-8\r\n";
		$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$body .= chunk_split(base64_encode($html));
	}
	else {
		$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$body .= chunk_split(base64_encode(($text??'')));
	}
	$log .= "\r\nABCMS SMTP MESSAGE: success"; // log

	// Add attachments
	foreach (($attach??[]) as $filePath) {
		if (!is_file($filePath) || !is_readable($filePath)) { return $fail("Email attachment file not readable: '{$filePath}'."); }
		$fileName = preg_replace("/[\r\n]+/", '', basename($filePath));
		$fileName = str_replace('"', '', $fileName); // keep the Content-Disposition value well-formed
		$fileNameEncoded = rawurlencode($fileName);
		$content  = file_get_contents($filePath);
		if ($content === FALSE) { return $fail("Email attachment contents not readable: '{$filePath}'."); }
		$finfo    = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = ($finfo ? finfo_file($finfo, $filePath) : FALSE) ?: 'application/octet-stream';
		if ($finfo) finfo_close($finfo);
		$body .= "--{$mixedBoundary}\r\n";
		$body .= "Content-Type: {$mimeType}; name=\"{$fileName}\"\r\n";
		$body .= "Content-Transfer-Encoding: base64\r\n";
		$body .= "Content-Disposition: attachment; filename=\"{$fileName}\"; filename*=utf-8''{$fileNameEncoded}\r\n\r\n";
		$body .= chunk_split(base64_encode($content));
	}
	$body .= "--{$mixedBoundary}--\r\n";
	$log .= "\r\nABCMS SMTP ATTACHMENTS: success"; // log

	// Normalize line endings and dot-stuff in DATA (RFC 5321 §4.5.2)
	$payload = $headers . "\r\n" . $body;
	$payload = preg_replace("/\r\n|\r|\n/", "\r\n", $payload);
	$payload = preg_replace("/^\./m", '..', $payload);
	if (substr($payload, -2) !== "\r\n") $payload .= "\r\n";
	$log .= "\r\nABCMS SMTP NORMALIZE: success"; // log

	// Write the email
	if (FALSE === fwrite($socket, $payload)) { return $fail('Email SMTP send failed.');	}
	[$status] = $command('.');
	if ($status != 250) { return $fail('Email server rejected the message body.'); }
	$log .= "\r\nABCMS SMTP SEND: status={$status} bytes=".strlen($payload); // log

	// Finish
	$command('QUIT');
	fclose($socket);
	if ($options['debug']) { $this->error_log($log."\r\nABCMS SMTP EXIT: success"); }
	return TRUE;
}







/*************************************************************************************************
SECTION THEME: Core webpage template.
*/
public function theme(
	?string	$css	= NULL,	// css override
	?string	$js		= NULL,	// js override
	?string	$head	= NULL,	// header override
	?string	$main	= NULL,	// content override
	?string	$foot	= NULL,	// footer override
	int		$flag	= 1,	// control flag
) : ?bool {					// return boolean
// helpful defaults
$title = mb_strtoupper($this->hsc($this->boots['urldomain']));
$lower = mb_strtolower($title);
$favicon = (is_readable('./favicon.ico') ? '/favicon.ico' : (is_readable('./public/favicon.ico') ? '/public/favicon.ico' : 'data:,'));
// page template
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<meta name='description' content='<?php echo $title; ?>'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<meta name='mobile-web-app-capable' content='yes'>
<link rel="manifest" href="/manifest.json">
<meta name='theme-color' content='#336699'>
<meta name='color-scheme' content='light dark'>
<meta http-equiv="Content-Security-Policy" content="default-src 'self' 'nonce-<?php echo $this->input['nonce']; ?>'; img-src 'self' data:;">
<title><?php echo $title; ?></title>
<link rel="icon" href="<?php echo $favicon; ?>">
<style nonce='<?php echo $this->input['nonce']; ?>'>
*, *::before, *::after { box-sizing: border-box; }
html, body, #page, header, main, footer { margin: 0; padding: 0; width: 100%; text-align: center; }
html { font-size: 100%; overflow-wrap: break-word; word-wrap: break-word; }
body { color: #333333; background-color: #FFFFFF; font-size: 1.125rem; line-height: 1.3; font-family: Arial, sans-serif; }
#page { display: flex; flex-direction: column; min-height: 100vh; }
header a:link, header a:visited { color: #999999; }
header a:hover, header a:focus { color: #99ccff; }
header a:active { color: #993366; }
header .home { width:100%; display: flex; justify-content: center; padding: 10px 0; }
header .title { font-size: 4rem; font-weight: bold; }
header .console { width:100%; display: flex; justify-content: space-between; padding: 10px 0; background-color: #999999; color: #333333; font-size: 2rem; font-weight: bold; }
main { flex: 1;	max-width: 1024px; min-width: min(360px, 100%); margin: 1rem auto; padding: 0rem 3rem 1rem 3rem; text-align: justify; }
main .homepage { line-height: 2rem; text-align: center; }
footer { margin-bottom: 1rem; }
h1, h2, h3, h4 { color: #336699; }
h1 { text-align: center; }
.bold { font-weight: 700; }
.italic { font-style: italic; }
.center { text-align: center; }
a { text-decoration: none; }
a:link { color: #6cc9ff; }
a:visited { color: #996633; }
a:hover, a:focus { color: #99ccff; }
a:active { color: #993366; }
form.form-grid {
	display: grid;
	grid-template-columns: max-content 1fr;
	gap: 15px;
	align-items: center;
	max-width: 600px;
}
label { text-align: right; }
input:required { border: 1px solid blue; }
div.captcha, button { display: inline-block; }
fieldset.disable { border: none; margin: 0; padding: 0; min-width: 0; display: contents; }
pre.debug { margin-top: 7rem; background-color: #EEEEEE; text-align: left; padding: 20px; }
@media screen and (max-width: 1065px) { main { margin: 0; } }
<?php echo $css; ?>
</style>
<script type='module' nonce='<?php echo $this->input['nonce']; ?>'>
<?php echo $js; ?>
</script>
</head>
<body>
<div id='page'>
<header>
<?php echo ($head ?: "<div class='home'><div><a href='/' class='title'>{$title}</a></div></div>"); ?>
</header>
<main>
<?php
if (!$main) { $main = <<<EOF
<h1>Status</h1>
<p class='center'>
My sincere apologies.<br>
I just cannot find the page requested.<br>
<br>
<a href='/'>Try again from the homepage</a>.
</p>
EOF;
}
$this->output(ABCMS_EXT_MAIN, 'CLI-GET-POST', 'abcms()->echo', ABCMS_ROLE_PUBLIC, $flag, FALSE, ...array($main));
echo $this->see_errors();
?>
</main>
<footer>
<?php echo ($foot ?: "<h4><a href='/'>{$lower}</a></h4>"); ?>
</footer>
</div>
</body>
<?php
return NULL; // done
}



// end object
}; }
return $_abcms;
}
