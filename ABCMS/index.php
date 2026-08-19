<?php

/*************************************************************************************************
SECTION INTRO: A Basic Content Management System and PHP toolkit.

Copyright (c) 2026 Nainoia Inc. All rights reserved.
Search for "SECTION" and "function" below for documentation.
Copy index.php to a docroot or run "composer install nainoia-inc/abcms".
Visit index.php in a browser or run "php index.php /command/help".
Download the super user password from "ABMCS.deleteme", then delete.
Extend imitating setup(), home_*(), webfiles_*(), console_*(), command_*().
Access $_SESSION[extension] with s() API, but $_SESSION remains exposed.
Run extension SETUP.php with /command/setup and CRON.php with /command/cron.
Schedule "php index.php /command/cron" every 15 minutes to 1x per day.
*/







/*************************************************************************************************
SECTION CONSTANTS: Immutable constants defined.
*/

// core extensions
const ABCMS_EXT_SELF	= '/nainoiainc/abcms';					// even abcms is an extension
const ABCMS_EXT_INIT	= '/init';								// initial extension hook
const ABCMS_EXT_INITX	= '/nainoiainc/abcms'.ABCMS_EXT_INIT;	// initial extension fullname
const ABCMS_EXT_MAIN	= '/theme_main';						// default html <main> extension hook
const ABCMS_EXT_MAINX	= '/nainoiainc/abcms'.ABCMS_EXT_MAIN;	// default html <main> extension fullname
const ABCMS_EXT_PRIVATE	= '/private/nainoiainc/abcms/';			// core private files
// user roles
const ABCMS_ROLE_PUBLIC	= 0;
const ABCMS_ROLE_AUTHEN	= 1;
const ABCMS_ROLE_READER	= 2;
const ABCMS_ROLE_WRITER	= 3;
const ABCMS_ROLE_EDITOR	= 4;
const ABCMS_ROLE_MANAGE	= 5;
const ABCMS_ROLE_ADMINS	= 6;
const ABCMS_ROLE_CLI	= 7;
const ABCMS_ROLE_SET	= array(0,1,2,3,4,5,6,7);
// regex output_extend() includefile?function #^(|/vendor/package/filepath)(|?(|classobject(::|->|()->))funcmeth)#
const ABCMS_REGEX_FUNC	= '/^(((?:\/(?!\.\.?(?:\/|$))[^?\/\\\\\x00\r\n]+)+)\?)?((([a-z_\x{7f}-\x{ff}][a-z0-9_\x{7f}-\x{ff}]*)(::|\->|\(\)\->))?([a-z_\x{7f}-\x{ff}][a-z0-9_\x{7f}-\x{ff}]*))?$/uiD';
// regex extension name patterns, lower case only
const ABCMS_REGEX_HOOK	= '/^\/[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9]([_.-]?[a-z0-9]+)*$/uD';	// extension /vendor/extension/hookname
const ABCMS_REGEX_FOLD	= '(/[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*)';	// extension /vendor/extension (used with '|' regex delimiters validating extension path)
const ABCMS_REGEX_NICK	= '/^[a-z0-9]([_.-]?[a-z0-9]+)*$/uD'; // extension nickname
// regex other
const ABCMS_REGEX_URLV	= '/\/([a-z0-9\-_.~]+)=([a-z0-9\-_.~=]+)/ui';	// URL variable
const ABCMS_REGEX_FORM	= '/(<form(?=[\s>])[^>]*>)(.+?)(<\/form>)/uis';	// form security injection
const ABCMS_REGEX_DATA	= '/^[a-z0-9\-_]+\.[a-z0-9\-_]+$/uiD';			// Database filename
// cookie permissions
const ABCMS_COOK_LIFE	= 60*60*24*365;		// choice for 1 year
const ABCMS_COOK_NONE	= 0;				// none
const ABCMS_COOK_FORM	= 1;				// security
const ABCMS_COOK_NAVS	= 2;				// navigation
const ABCMS_COOK_TRAK	= 3;				// tracking
// TODO - move session controls to overridable $settings
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







/*************************************************************************************************
SECTION TRY/CATCH: Anonymous function boot for global footprint = abcms().
*/

(function() {				// wrapper reduces globals
$code = 0;					// assume success
try {						// try output
	abcms()->output(		// extension router
		ABCMS_EXT_INIT,		// default extension
		'CLI-GET-POST',		// methods available
		'abcms()->theme',	// default function
		ABCMS_ROLE_PUBLIC,	// minimum role
		1,					// exclusive control
		FALSE,				// default required
		...$args = array(NULL,NULL,NULL,NULL,NULL,1), // css, js, header, main, footer, exclusive?
	);
}

catch (\Throwable $e) {		// catch exceptions
	// gather information
	$exception = (htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') ?: 'Unknown exception.'); // thrown error
	$system = (error_get_last() ?? array('message' => 'No system error reported.')); // system error
	$composer = array(); // composer extensions
	if (class_exists(\Composer\InstalledVersions::class)) {
		foreach (Composer\InstalledVersions::getInstalledPackagesByType('abcms-extension') as $name) {
			$composer[$name] = Composer\InstalledVersions::getInstallPath($name);
		}
	}
	$buffer = NULL; while(ob_get_level()) { $buffer .= ob_get_clean(); } // retrieve buffer
	$title = mb_strtolower(htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), 'UTF-8'); // website title
	$nonce = chr(random_int(97,122)).chr(random_int(97,122)).bin2hex(random_bytes(31)); // security nonce
	// graceful WSOD
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
	// file output
	error_log("ABCMS->COREDUMP()\n" . print_r(array('COREDUMP_EXCEPTION' => $exception, 'COREDUMP_SYSTEM' => $system))); // log error
	file_put_contents( // dump corefile
		str_replace('\\', '/', __DIR__).'/..'.ABCMS_EXT_PRIVATE.'ABCMS.coredump',
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

finally { // always clean up
	session_commit(); $_SESSION = []; // disallow deferred session access
}
exit($code); // script return 0 = success or 1 = failure
 ; })();







/*************************************************************************************************
SECTION INPUT: Validate input with construct object methods() 1st and properties 2nd.
*/

function abcms() : ?object {						// abcms() the only global
static $_abcms = FALSE;								// construct once
if (FALSE === $_abcms) {							// fail once
$_abcms = NULL;										// return NULL or object
$_abcms = new class {								// abcms object assigned
public				?Closure $oneshot	= NULL;		// oneshot construction
readonly			array	$boots;					// bootstrap input before session
readonly			array	$input;					// sanitize input with session
private readonly	array	$settings;				// application settings
private				?array	$compiles	= NULL;		// compile settings
private				array	$database	= [];		// database
private				array	$ss			= [];		// core session pointer
private				array	$errors		= [];		// TODO combine error info
private				array	$debugs		= [];		// TODO combine debug info
private				array	$stackarg	= [];		// TODO combine debug stack args
private				array	$stackwho	= [];		// extension stack
private				bool	$formvalid	= FALSE;	// form valid
private				bool	$formhuman	= FALSE;	// form human

function __construct() { $this->oneshot = function() { $this->input_construct(); }; } // 1st construct object methods, so extension SETUP.php can use abcms() methods

private function input_construct() { // 2nd construct object properties
	$this->stackwho[] = ABCMS_EXT_SELF; // push core on extension stack
	$this->setup(TRUE); // assign $settings
	if (FALSE === ini_set('error_log', $this->settings['core']['translog'])) { $this->error_log("Set error_log location failed."); } // locate logs
	while(ob_get_level() > 0) { if (FALSE !== ($buf = ob_get_clean()) && '' !== $buf) { $this->error_log("I got stuff in my buffers."); } } // empty buffers
	// bootstrap inputs for session_start(), then session user validates remaining inputs
	$this->boots = array(
		'time' => time(), // current time()
		'uagent' => (($_SERVER['REMOTE_ADDR']??'')?:'unknown').(($_SERVER['HTTP_USER_AGENT']??'')?:'unknown'), // user identity
		'auto' => $this->settings['core']['auto'], // auto-loader
		'cli' => ($cli = ('cli' === PHP_SAPI ? TRUE : FALSE)), // CLI execution
		'argc' => ($_SERVER['argc']??0), // CLI arg count
		'argv' => ($_SERVER['argv']??[]), // CLI args
		'urlfull' => ($urlfull = // URL full
			($cli ? ('https://localhost' . // CLI domain
			($_SERVER['argc']>1 && '/' === ($_SERVER['argv'][1][0]?:'') && FALSE !== filter_var('http://localhost' . $_SERVER['argv'][1], FILTER_VALIDATE_URL) ? $_SERVER['argv'][1] : '/command/help')) : // CLI URI validation
			((isset($_SERVER['HTTPS']) && mb_strtolower($_SERVER['HTTPS'], 'UTF-8') !== 'off' ? 'https://' : 'http://') . // HTTPS secure
			// HTTP domain validation including multibyte to punycode
			(!empty($_SERVER['HTTP_HOST']) && ($host = preg_replace('/:\d*$/u','',$_SERVER['HTTP_HOST'])) && // remove ports
			FALSE !== filter_var(idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_DOMAIN) ? $_SERVER['HTTP_HOST'] : 'unknown') . // filter domain
			// HTTP URI validation, ascii only
			(isset($_SERVER['REQUEST_URI']) && mb_check_encoding($_SERVER['REQUEST_URI'],'ASCII') && // check encoding
			FALSE!==filter_var('http://localhost'.$_SERVER['REQUEST_URI'],FILTER_VALIDATE_URL) ? $_SERVER['REQUEST_URI'] : '/unknown')))), // filter URI
		'urlparsed' => ($urlparsed = parse_url($urlfull)), // URL parse
		'urldomain' => (mb_strtolower(($urlparsed['host']??''), 'UTF-8')), // URL domain
		'urlport' => ($urlparsed['port']??NULL), // URL port
		'urlmethod' => ($cli ? 'CLI' : ((empty($_SERVER['REQUEST_METHOD']) || // URL method
			!in_array($_SERVER['REQUEST_METHOD'], array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE'))) ? 'GET' : $_SERVER['REQUEST_METHOD'])), // validate method
		'urlpathall' => ($urlpathall = ('/'.(trim(preg_replace(ABCMS_REGEX_URLV, '/', ($urldecoded = urldecode(($urlparsed['path']??'')))), '/')))), // URL no variables, no trailing slash, and urldecoded
		'urlpathone' => (!($ret = preg_match('/^(\/[^\/\x00-\x1f]*)(\/[^\x00-\x1f]+)?$/uD', $urlpathall, $matches)) ? '/' : $matches[1]), // URL first segment for core router
		'urlpathext' => (!$ret || empty($matches[2]) ? '/' : $matches[2]), // URL second+ segments for extension router
	);
	// possibly start session after boots and validate user
	$session = $this->session_start(0); // lazy session start
	// sanitize inputs given user permissions
	$this->input = array(
		'user' => $this->ss['user']??NULL, // my user
		'role' => ($role = ($cli ? ABCMS_ROLE_CLI : $this->ss['user']['role']??ABCMS_ROLE_PUBLIC)), // my role
		'urlvars' => (!preg_match_all(ABCMS_REGEX_URLV, $urldecoded, $matches, PREG_PATTERN_ORDER) ? array() : // validate URL vars 'u'
			$this->input_valid('U', array_combine($matches[1], $matches[2]), $role)),
		'urlquery' => ($this->input_valid('G', (mb_parse_str(($urlparsed['query']??''), $result) ? $result : array()), $role)), // URL validate query vars 'q' from parse_str() because CLI has no $_GET
		'postvars' => array(), // TODO ($this->input_valid('P', $_POST, $role)), // validate $_POST vars 'p'
		'nonce' => $this->get_uniq(), // style & script security nonce
	);
	// complete initialization
	if ($this->boots['auto']) { require_once($this->boots['auto']); } // require composer
	if (!str_starts_with($urldecoded, $urlpathall)) { $this->set_errors("URL questioned, variables within path"); } // URL vars misplaced if !str_starts_with, URL externally constructed
	array_pop($this->stackwho); // pop core off extension stack
	return;
}

public function __set(string $name, mixed $value) : void { $this->error_wsod("Dynamic properties disallowed."); }	// disallow dynamic properties

public function __clone() { $this->error_wsod("Cloning object disallowed."); }										// disallow cloning

private function input_valid(	// validate input variables
string	$cat,					// 'U'=URL, 'G'=$_GET, 'P'=$_POST
array	$vars,					// variable array to validate
int		$role,					// user role
) : array {						// return $vars array or WSOD
	// loop input variables
	$last = NULL;
	foreach($vars as $var => $val) {
		if ($var < $last) {									$this->set_errors("URL variables not alphabetical as expected"); } // expected alphabetical
		$last = $var;
		if (empty($this->settings[$cat][$var]['type'])) {	$this->set_errors("Ignoring undefined URL variable, '{$var}'");						unset($vars[$var]);	continue; } // ignore undefined
		if ($role < $this->settings[$cat][$var]['role']) {	$this->set_errors("Insufficient permission for URL variable, '{$var}'");			unset($vars[$var]);	continue; }	// no permission
		if ('null' == mb_strtolower($val, 'UTF-8')) {																							$vars[$var] = NULL;	continue; } // NULL special case
		// switch possibilities
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
			case 'uuid'		:	if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/iD', $val)) {		break; }			continue 2;			
			default:			$this->error_wsod("Undefined URL variable type, '{$this->settings[$cat][$var]['type']}'"); // type undefined
		}
		// variable name and type found, but with invalid value
		$this->set_errors("Ignoring invalid URL variable, '{$this->settings[$cat][$var]['type']}' = '{$var}'");
		unset($vars[$var]);
	}
	return $vars;
}







/*************************************************************************************************
SECTION SETUP: Setup core and extension readonly settings.
*/

private function setup(	// read or create core settings, executed by Composer, construct(), command_setup()
bool	$boot = FALSE,	// TRUE = load existing, else recreate
) : void {				// return void or WSOD
	// $this->boots and $this->input not yet initialized
	// load settings from var_dump() file for speed, beware of injection
	$this->error_log('SETUP: Begin');
	$storage = $this->rp(dirname(__DIR__)).ABCMS_EXT_PRIVATE.'ABCMS.settings.php';
	$this->compiles = array(); // initialize
	$this->compiles['core']['projectroot'] = $this->rp(dirname(__DIR__)); // projectroot, needed early for chk_file()
	$data = [];
	if ($boot && $this->get_dump($storage, $data)) {
		if (!is_array($data) || empty($data['core']['projectroot'])) { $this->error_wsod("Settings file corrupted."); }
		$this->settings = $data;
		$this->compiles = NULL;
		return;
	}
	// register core settings
	$this->error_log('SETUP: Core settings');
	$this->compiles['core']['filename']			= $this->rp(__FILE__); // my filename
	$this->compiles['core']['documentroot']		= $this->rp(__DIR__); // my documentroot
	$corefold = $this->compiles['core']['projectroot'].ABCMS_EXT_PRIVATE;
	$this->compiles['core']['project']			= (basename(dirname(__DIR__))); // my project name
	$this->compiles['core']['auto']				= $this->rp(realpath(__DIR__ . '/../vendor/autoload.php')); // auto-loader location
	$this->compiles['core']['getmyinode']		= getmyinode(); // my inode
	$this->compiles['core']['getlastmod']		= getlastmod(); // my modified date
	$password									= $this->get_uniq(); // my clear password
	$this->compiles['core']['passhash']			= password_hash($password, PASSWORD_DEFAULT); // my password hash
	$this->set_json($corefold.'ABCMS.deleteme', 'DELETE ASAP: '.$password); // temp password storage
	$password = NULL;
	$this->error_log("Retrieve new password and delete the file please.");
	$this->compiles['core']['secret']			= $this->get_uniq(); // my hash secret
	if (!is_dir(($file = ($corefold.'ABCMS.sessions'))) && (!mkdir($file, 0755, true))) { $this->error_wsod("Session folder does not exist."); }
	$this->compiles['core']['session_folder']	= $file; // session folder
	$this->compiles['core']['session_cookie']	= $this->get_uniq(); // session cookie name
	$this->compiles['core']['session_secret']	= $this->get_uniq(); // session secret name
	$this->compiles['core']['session_logins']	= $this->get_uniq(); // login cookie name
	$this->compiles['core']['session_badact']	= $this->get_uniq(); // bad actor cookie name
	$this->compiles['core']['session_allows']	= $this->get_uniq(); // user allows cookie name
	$this->compiles['core']['session_killit']	= TRUE; // kill on close browser
	$this->compiles['core']['session_domain']	= ''; // '' = host-only; or 'example.com' shared across subdomains
	$this->compiles['core']['smtp_host']		= NULL; // SMTP server
	$this->compiles['core']['smtp_port']		= NULL; // SMTP port
	$this->compiles['core']['smtp_name']		= NULL; // SMTP name
	$this->compiles['core']['smtp_user']		= NULL; // SMTP username
	$this->compiles['core']['smtp_pass']		= NULL; // SMTP password
	$this->compiles['core']['smtp_ehlo']		= NULL; // SMTP EHLO
	$this->new_database('BASIC.json');
	$this->compiles['core']['translog']			= $corefold.'ABCMS.translog'; // transaction log
	$this->touch($this->compiles['core']['translog']);
	$this->compiles['core']['override']			= $corefold.'ABCMS.override.php'; // overrides
	if (!file_exists($this->compiles['core']['override'])) { $this->set_dump($this->compiles['core']['override'], []); }
	// register variables
	$this->error_log('SETUP: Core variables');
	$this->setup_variable('U',	'debug', 'bool', ABCMS_ROLE_ADMINS); // register URL PATH variables
	$this->setup_variable('G',	'debug', 'bool', ABCMS_ROLE_ADMINS); // register $_GET variables
	$this->setup_variable('P', 'debug', 'bool', ABCMS_ROLE_ADMINS); // register $_POST variables
	// extension controls
	// 'I' = Input -OR- 'O' = Output filter, default Input
	// 'E' = Exclusive to my extension or omit me, default anyone
	// 'U' = Uno/single extension, default multiple extensions cooperate 
	// 'D' = Default included, default excluded if extended by $ord < 0
	$this->error_log('SETUP: Core extensions');
	// register core and command extensions
	$this->setup_extend(ABCMS_EXT_INITX,	'',			'CLI-GET-POST',	'IEU',	'abcms()->home_theme',		ABCMS_ROLE_PUBLIC,	-10);
	$this->setup_extend(ABCMS_EXT_INITX,	'console',	'CLI-GET-POST',	'IEU',	'abcms()->console_theme',	ABCMS_ROLE_ADMINS,	-20);
	$this->setup_equate(ABCMS_EXT_INITX,	'console',	'/console/');
	$this->setup_extend(ABCMS_EXT_INITX,	'command',	'CLI-GET-POST',	'IEU',	'abcms()->command_router',	ABCMS_ROLE_ADMINS,	-10);
	$this->setup_equate(ABCMS_EXT_INITX,	'command',	'/command/');
	// register homepage extensions
	$this->setup_extend(ABCMS_EXT_MAINX,	'home',		'CLI-GET-POST',	'IE',	'abcms()->home_router',		ABCMS_ROLE_PUBLIC,	-10);
	$this->setup_equate(ABCMS_EXT_MAINX,	'home',		'/');
	$this->setup_equate(ABCMS_EXT_MAINX,	'home',		'/account');
	$this->setup_equate(ABCMS_EXT_MAINX,	'home',		'/contact');
	// register console extensions
	$this->setup_extend(ABCMS_EXT_MAINX,	'console',	'CLI-GET-POST',	'IE',	'abcms()->console_router',	ABCMS_ROLE_ADMINS,	-10);
	$this->setup_equate(ABCMS_EXT_MAINX,	'console',	'/console');
	$this->setup_equate(ABCMS_EXT_MAINX,	'console',	'/console/');
	// run SETUP.php for each extension
	$this->error_log('SETUP: Contrib extensions');
	$exts = glob("{$this->compiles['core']['projectroot']}/private/*/*/");
	foreach ($exts?:[] as $fold) {
		// valid extension name
		if (!preg_match('|^'.preg_quote($this->compiles['core']['projectroot'],'|').'/private'.ABCMS_REGEX_FOLD.'/$|uD', $fold, $match) || empty($match[1])) {
			$this->error_log("SETUP: Extension name invalid, {$fold}");
			continue;
		}
		// valid file
		$temp = $fold . "SETUP.php";
		if (!is_file($temp)) {
			$this->error_log("SETUP: Invalid extensions, SETUP.php is not a file, {$temp}");
			continue;
		}
		// reject symlinks
		if (($file = $this->rp(realpath($temp))) !== $this->rp($temp)) {
			$this->error_log("SETUP: Extension symlinks rejected, {$temp}");
			continue;
		}
		// push extension stackwho so s() returns valid $_SESSION storage
		$this->stackwho[] = $match[1];
		try {
			$this->include($file);
			$this->error_log("SETUP: Extension setup succeeded, {$file}");
		}
		// failed extension setup
		catch (Throwable $e) {
			$exception = ($e->getMessage() ?: 'Unknown exception.');
			$this->error_log("SETUP: Extension setup failed: {$file} {$exception}");
		}
		// pop stackwho
		finally {
			array_pop($this->stackwho);
		}
	}
	// TODO optimize and remove mixed non-exclusive and exclusive routes
	$this->error_log('SETUP: Optimize settings');
	// load custom settings from var_dump file for speed, beware of injection
	$this->error_log('SETUP: Custom overrides');
	if (function_exists('opcache_invalidate')) { opcache_invalidate($this->compiles['core']['override'], TRUE); } // clear php cache
	$override = [];
	if (!$this->get_dump($this->compiles['core']['override'], $override) || !is_array($override)) { $this->error_wsod("Custom settings file missing or corrupted."); }
	$this->array_walk_merge($this->compiles, $override);
	// verify custom session_domain
	if (!is_string($this->compiles['core']['session_domain'])) { $this->error_wsod("Session domain must be a string."); }
	$dom = $this->compiles['core']['session_domain'] = mb_strtolower(ltrim($this->compiles['core']['session_domain'],'.'), 'UTF-8');
	$host = mb_strtolower(parse_url('http://'.($_SERVER['HTTP_HOST']??''), PHP_URL_HOST)?:'', 'UTF-8');
	if ('' !== $dom && '' !== $host && $dom !== $host && !str_ends_with($host, '.'.$dom)) {
		$this->error_wsod("Session domain '{$dom}' does not match host '{$host}'.");
	}
	// __Host- prefix locks cookies to this host, browser rejects any subdomain attempt to set them
	if ('' === $dom) {
		foreach (array('session_cookie','session_secret','session_logins','session_badact','session_allows') as $name) {
			if (!str_starts_with($this->compiles['core'][$name], '__Host-')) { $this->compiles['core'][$name] = '__Host-'.$this->compiles['core'][$name]; }
		}
	}
	// save settings as fast op cachable php include file with atomic with rename(), beware of injection
	$this->error_log('SETUP: Save settings');
	$this->set_dump($storage, $this->compiles);
	if ($boot) { $this->settings = $this->compiles; }
	$this->compiles = NULL;
	// warning: op cache setting requires manual cache refresh
	if (function_exists('opcache_get_configuration') && !ini_get('opcache.validate_timestamps')) {
		$this->error_log("WARNING: opcache.validate_timestamps=0 — reload PHP-FPM for settings to take effect on the web.");
	}
	return;
}

public function setup_extend(		// register hook extension
string	$hok,						// /vendor/package/hook | TODO combine $hok & $ext ?
string	$ext,						// extension or '' for all
string	$met,						// HTTP methods, '' = all = "CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE" | TODO make $met and $str similar structure?
string	$str,						// control string | TODO constants?
									// 'I' = input -OR- 'O' = output filter, default input
									// 'E' = exclusive to my extension or omit me, default anyone
									// 'U' = uno/single extension, default multiple extensions cooperate 
									// 'D' = include default, default excluded if extended by $ord < 0
string	$fun,						// includefile?function
int		$rol = ABCMS_ROLE_PUBLIC,	// minimum role permission
int		$ord = 0,					// order considered, PHP_INT_MIN >= $ord <= PHP_INT_MAX 
mixed	...$arg,					// argument alternatives
) : bool {							// return success or failure
	// wrong context or parse control string
	$ctl = ('' === $str ? array() : array_flip(str_split(strtoupper($str))));
	$key = array_diff_key($ctl, array('I'=>0,'O'=>0,'E'=>0,'U'=>0,'D'=>0));
	// validate
	$a = $b = $c = $d = $e = $f = $g = FALSE;
	if (($a=(!is_array($this->compiles))) || // bad context
		($b=(!preg_match(ABCMS_REGEX_HOOK, $hok))) || // hook
		($c=('' !== $ext && !preg_match(ABCMS_REGEX_NICK, $ext))) || // extension
		($d=(!empty($met) && array_diff(explode('-', $met), array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE')))) || // method
		($e=(isset($ctl['I']) && isset($ctl['O']))) || // input or output
		($f=(!empty($key))) || // control
		($g=(!empty($fun) && !preg_match(ABCMS_REGEX_FUNC, $fun)))) { // function
		$this->error_log("Invalid extension: {$hok} {$ext} {$fun}, err: bad={$a} hok={$b} ext={$c} met={$d} exc={$e} con={$f} fun={$g}");
		return FALSE;
	}
	// assign extension
	unset($ctl['I']);
	$this->compiles['route'][$hok]['ex'][$ext][(isset($ctl['O']) ? 'O' : 'I')][] = array(
		'met'	=> $met,
		'fun'	=> $fun,
		'rol'	=> $rol,
		'ord'	=> $ord,
		'ctl'	=> $ctl,
		'who'	=> $this->output_extension(),
		'arg'	=> $arg,
	);
	return TRUE;
}

public function setup_equate(	// equate path to hook extension name
string	$hok,					// /vendor/package/hook TODO combine $hok & $ext ?
string	$ext,					// extension or '' for all
string	$pat,					// unique URL path, trailing '/' for 1st segment only, otherwise no trailing slash
) : bool {						// return success or failure
	// validate
	$a = $b = $c = $d = $e = $f = FALSE;
	if (($a=(!is_array($this->compiles))) || // bad context
		($b=(!preg_match(ABCMS_REGEX_HOOK, $hok))) || // hook
		($c=('' !== $ext && !preg_match(ABCMS_REGEX_NICK, $ext))) || // extension
		($d=(substr_count($pat, '/')>2 && '/' == $pat[-1])) || // trailing slash matches 1st path segment only
		($e=('' !== $pat && ('/' !== $pat[0] || FALSE === filter_var('http://localhost'.$pat, FILTER_VALIDATE_URL)))) || // path
		($f=isset($this->compiles['route'][$hok]['eq'][$pat]))) { // duplicate
		$this->error_log("Invalid extension path: {$hok} {$ext} {$pat} err: bad={$a} hok={$b} ext={$c} p//={$d} pat={$e} dup={$f}");
		return FALSE;
	}
	// assign equate path
	$this->compiles['route'][$hok]['eq'][$pat] = $ext;
	return TRUE;
}

private function setup_variable(// register variable
string	$cat,					// category 'U','G','P' TODO constants?
string	$var,					// variable
string	$typ,					// type
int		$rol,					// min role
?array	$reg = NULL,			// regex validation
) : bool {						// return success or failure
	// validate
	$a = $b = $c = $d = $e = $f = FALSE;
	if (($a=(!is_array($this->compiles))) || // bad context
		($b=(!in_array($cat, array('U','G','P')))) || // category
		($c=(!preg_match('/^[a-z0-9\-_.~]+$/uiD', $var))) || // variable
		($d=(!in_array($typ, array('mixed','string','array','integer','float','bool','boolean','email','domain','uri','url','ip','mac','uuid','path')))) || // type
		($e=(!in_array($rol, ABCMS_ROLE_SET))) || // role
		($f=(!empty($this->compiles[$cat][$var])))) { // duplicate
		$this->error_log("Invalid extension variable: {$cat} {$var} {$typ} err: bad={$a} cat={$b} var={$c} typ={$d} rol={$e} dup={$f}");
		return FALSE;
	}
	// assign variable
	$this->compiles[$cat][$var] = array('type'=>$typ, 'role'=>$rol, 'reg'=>$reg);
	return TRUE;
}







/*************************************************************************************************
SECTION SESSION: Secure sessions with opt-in/out, validation, CSRF, CAPTCHA, and login.
*/

public function session_start(	// start session conditionally
int $cmd,						// -1 = destroy, 0 = start if, 1 = start 
) : bool {						// return TRUE=started, FALSE=destroyed
	// initialize
	$active = (session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE);
	$slap = 0;
	static $now = NULL;
	static $posthandled = FALSE; // post already handled
	static $deny = FALSE; // deny further session whether bad actor or failed session_destroy()
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
			'cookie_domain'		=> $this->settings['core']['session_domain'],	// '' = host-only; or 'example.com' shared across subdomains
			'cookie_secure'		=> '1',											// HTTPS only
			'cookie_httponly'	=> '1',											// No JS
			'cookie_samesite'	=> 'Strict',									// No cross-site
			'use_strict_mode'	=> '1',											// Reject unknown SIDs
			'use_cookies'		=> '1',											// No SID in URL
			'use_only_cookies'	=> '1',											// No SID in URL
			'use_trans_sid'		=> '0',											// Disable URL rewriting
			];
	}

	// early exit
	if ($deny || isset($_COOKIE[$this->settings['core']['session_badact']])) { if (!($deny)) { $this->set_errors('Session denied to suspected bad actor.'); } $deny = TRUE; return FALSE; } // bad actor
	if ($cmd < 0) { $error = 'You are logged out.'; goto KILL; } // destroy session
	if ($active) { if (0 === $cmd) { $this->error_wsod("Session started to early."); } return TRUE; } // already started, but ABCMS must start
	if (headers_sent()) { $this->error_wsod("Session start failed, headers already sent.");	} // already headers
	if (!isset($_COOKIE[$this->settings['core']['session_allows']])) { $this->set_cookie($this->settings['core']['session_allows'], ABCMS_COOK_NAVS, $now + ABCMS_COOK_LIFE, FALSE); }	// TODO TEMP CODE TO ALLOW COOKIES
	if (empty($_COOKIE[$this->settings['core']['session_allows']])) {	$this->set_errors('Session denied without your cookie approval.'); return FALSE; } // cookies not approved
	$post = ('POST' === $this->boots['urlmethod'] && !$posthandled ? TRUE : FALSE); // is this a POST?
	if (0 === $cmd && !isset($_COOKIE[$this->settings['core']['session_logins']]) && !$post) { return FALSE; } // conditional start

	// start session, more variables
	if (!session_start($options) || !($_COOKIE[$options['name']] = session_id())) { $this->error_wsod("Session start failed, unknown reason.");	}
	$active = $posthandled = TRUE;
	$error = $gauntlet = NULL;
	$csrf = ($post && !empty($_POST['csrf']) ? $_POST['csrf'] : '');
	if (empty($_SESSION[ABCMS_EXT_SELF]['create'])) { $this->ss = []; } else { $this->ss = &$_SESSION[ABCMS_EXT_SELF]; }

	// validate session
	if (!$this->ss) {
		// cannot POST without session
		if ($post) {																									$error = 'Session ended, POST requires session.';	$slap = 400; }
	}
	else {
		// hit counter
		$gothits = FALSE; $this->ss['counts'][] = $now; if (count($this->ss['counts']) > ABCMS_SES_HITS) { array_shift($this->ss['counts']); $gothits = TRUE; }
		// uagent inconsistent
		if ($this->ss['uagent'] !== $this->boots['uagent']) {															$error = 'Session ended, IP/Agent or core reset.';	$slap = 400; }
		// secrets differ
		else if (!hash_equals($this->ss['secret'], ($_COOKIE[$this->settings['core']['session_secret']]??'x'))) {		$error = 'Session ended, secrets differ.';			$slap = 400; }
		// rapid hits
		else if ($gothits && $this->ss['counts'][ABCMS_SES_HITS-1] - $this->ss['counts'][0] < ABCMS_SES_TIME) {			$error = 'Session ended, rapid hits.';				$slap = 429; }
		// POST CSRF1
		else if ($post && (!$csrf || !hash_equals($this->ss['csrf_valu'], $csrf))) {									$error = 'Session ended, CSRF1 error.';				$slap = 400; }
		// POST CSRF2
		else if ($csrf && !hash_equals($this->ss['csrf_valu'], (($_POST[$this->ss['csrf_name']]??'x')?:'x'))) {			$error = 'Session ended, CSRF2 error.';				$slap = 400; }
		// POST !HONEY populated
		else if ($csrf && !empty($_POST[$this->ss['void_name']])) {														$error = "Session ended, CAPTCHA1 error.";			$slap = 400; }
		// POST HONEY differs
		else if ($csrf && !hash_equals($this->ss['full_valu'], (($_POST[$this->ss['full_name']]??'x')?:'x'))) {			$error = 'Session ended, CAPTCHA2 error.';			$slap = 400; }
		// POST rapid
		else if ($csrf && ($now - $this->ss['active']) < ABCMS_SES_WAIT) {												$error = "Session ended, rapid submission.";		$slap = 400; }
		// fail resume login, cookies or session expired, always reload user to confirm permissions
		else if (isset($_COOKIE[$this->settings['core']['session_logins']]) &&
			(($_COOKIE[$this->settings['core']['session_logins']]?:'x') !== $this->ss['logins'] || empty($this->ss['user']) ||
			!($this->ss['user'] = $this->get_database('BASIC.json', array('user',$this->ss['user']['email']))))) {		$error = 'Session ended, resume login failed.'; }
		// login expired
		else if (!isset($_COOKIE[$this->settings['core']['session_logins']]) && !empty($this->ss['user'])) {			$error = 'Session ended, login expired.'; }
		// idle time exceeded
		else if ($now > ($this->ss['active'] + ABCMS_SES_IDLE)) {														$error = 'Session ended, inactivity threshold.'; }
		// time exceeded
		else if ($now > ($this->ss['create'] + ABCMS_SES_LIFE)) {														$error = 'Session ended, maxtime threshold.'; }
		// POST image mismatch
		else if ($csrf && empty($this->ss['user']) && ($this->ss['test_valu'] !== (($_POST[$this->ss['test_name']]??'x')?:'x'))) {	$this->set_errors('CAPTCHA failure, please try again.'); }
		// Passed gauntlet so maybe human
		else {																											$gauntlet = TRUE; }
	}

	// destroy by request or for corruption
	if ($error) {
KILL:	// set errors
		$this->set_errors($error);
		// start session to destroy it, weird
		if (!$active) { $active = session_start($options); }
		// remove cookies
		$this->set_cookie($options['name'], '', 1); // session
		$this->set_cookie($this->settings['core']['session_secret'], '', 1); // secret
		$this->set_cookie($this->settings['core']['session_logins'], '', 1); // login
		// PHP says mark for garbage collection, but I don't want garbage laying around
		$_SESSION = $this->ss = []; // access directly exception to clear entire session
		if ($active && !session_destroy()) { $deny = TRUE; $this->error_log("Session destroy failed.");	}
		// slap evil and assign bad actor cookie
		if ($slap) {
			$deny = TRUE;
			$this->set_cookie($this->settings['core']['session_badact'], $this->get_uniq(), $now + ABCMS_SES_BADA, FALSE);
			http_response_code($slap);
			header('Retry-After: ' . ABCMS_SES_BADA);
			$this->error_wsod($error);
		}
		return FALSE;
	}

	// update valid session
	if ($this->ss) {
		// valid POST, though POST data validated in form handler
		if ($post) {
			$this->formvalid = TRUE;
			$this->formhuman = ($gauntlet ? TRUE :FALSE);
		}
		// rotate session and CSRF if exceed rotate time or $user role changed
		if ($now > ($this->ss['rotate'] + ABCMS_SES_ROTA) || $this->ss['role'] !== ($this->ss['user']['role']??NULL)) {
			// session cookie
			if (!session_regenerate_id(TRUE) || !($_COOKIE[$options['name']] = session_id())) { $this->error_wsod("Session regeneration failed."); }
			// secret cookie
			$this->ss['secret'] = $this->get_uniq();
			$this->set_cookie($this->settings['core']['session_secret'], $this->ss['secret'], $this->ss['create'] + ABCMS_SES_LIFE);
			// CSRF token
			$this->ss['csrf_valu'] = $this->get_uniq();
			// login cookie
			if (!empty($this->ss['logins'])) {
				$this->ss['logins'] = $this->get_uniq();
				$this->set_cookie($this->settings['core']['session_logins'], $this->ss['logins'], $this->ss['create'] + ABCMS_SES_LIFE);
			}
			// rotated time
			$this->ss['rotate'] = $now;
		}
		// active time
		$this->ss['active'] = $now;
		$this->ss['role'] = $this->ss['user']['role']??NULL;
	}

	// validate new session
	else {
		$_SESSION[ABCMS_EXT_SELF] = [
			'create'	=> $now,
			'active'	=> $now,
			'rotate'	=> $now,
			'uagent'	=> $this->boots['uagent'],
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
		$this->ss = &$_SESSION[ABCMS_EXT_SELF];
		$this->set_cookie($this->settings['core']['session_secret'], $this->ss['secret'], $now + ABCMS_SES_LIFE);
	}
	return TRUE;
}

// Extensions safely segregated with $_SESSION[extension].
// Use $copysess = $this->s() to copy $_SESSION[extension].
// Copy session extension element: $value = $copysess['element'];
// Use $writesess = &$this->s(TRUE) to read/write $_SESSION[extension].
// Assign extension element: $writesess['element'] = FALSE;
// Assign whole extension: $writesess = array('element' => TRUE);
public function &s(		// segregated $_SESSION by extension
bool $assign = FALSE,	// TRUE to initialize $_SESSION[extension]
) : array {				// return $_SESSION[extension], empty, or WSOD
	$ext = $this->output_extension(); // segregation key
	$bad = TRUE; // allow only one call to session_status()
	if ($assign) {
		if (($bad = (session_status() !== PHP_SESSION_ACTIVE))) { $this->error_wsod("Session assignment but session doesn't exist."); }
		if (!isset($_SESSION[$ext])) { $_SESSION[$ext] = []; } // assignment expected
	}
	if ((!$bad || (session_status() === PHP_SESSION_ACTIVE)) && isset($_SESSION[$ext])) {
		if (!is_array($_SESSION[$ext])) { $this->error_wsod("Session extension corrupted: {$ext}."); }
		return $_SESSION[$ext]; // return extension element
	}
	$empty = []; return $empty; // return fail-safe emptiness
}

public function set_cookie(	// set cookie
string	$cookie,			// name
string	$value,				// value
int		$expires,			// expiration
bool	$killit = TRUE,		// kill heed
): void {					// return void or WSOD
	// headers sent error and kill cookie on close browser
	if (headers_sent()) { $this->error_wsod("Set cookie headers already sent"); }
	if ($killit && $expires > 1 && $this->settings['core']['session_killit']) { $expires = 0; }
	// set cookie
	if (!empty($cookie) && setcookie(
		$cookie,
		$value,
		[
			'expires'	=> $expires,									// expiration
			'path'		=> '/',											// entire website
			'domain'	=> $this->settings['core']['session_domain'],	// '' = host-only; or 'example.com' shared across subdomains
			'secure'	=> TRUE,										// only HTTPS
			'httponly'	=> TRUE,										// no js prevents XSS
			'samesite'	=> 'Strict',									// avoid CSRF attacks
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
SECTION DATABASE: Store data in VAR_DUMP, JSON, CSV, SQLite, and MySQL.
*/

public function new_database(	// create new database
string $file,					// filename within extension
) : void {						// return void or WSOD
	if (!preg_match(ABCMS_REGEX_DATA, $file)) { $this->error_wsod("Database name invalid: {$file}"); } // invalid file
	$fold = ($this->compiles['core']['projectroot']??$this->settings['core']['projectroot']).'/private'.$this->output_extension().'/ABCMS.database';
	if (!is_dir($fold) && !mkdir($fold, 0750, true)) { $this->error_wsod("Database folder does not exist: {$fold}"); }
	$file = $fold.'/'.$file;
	$this->touch($file);
	$this->touch($file.'.lock');
}

public function set_database(	// write to database
	string	$file,				// filename within extension
	array	$keys,				// element keys, [] replaces database with (is_array($data) ? $data : [$data])
	mixed	$data,				// new or updated element
	bool	$new = TRUE,		// TRUE to add new record (fails if exists), FALSE to update existing (fails if doesn't exist)
) : bool {						// return success or failure
	// errors
	if (!preg_match(ABCMS_REGEX_DATA, $file)) { $this->error_wsod("Database name invalid: {$file}"); } // invalid file
	if ($new && NULL === $data) { return FALSE; } // may not new NULL
	// initialize update element
	$file = $this->output_extension().'/ABCMS.database/'.$file;
	$base = ($this->compiles['core']['projectroot']??$this->settings['core']['projectroot']).'/private'.$file;
	$update = [];
	$current = &$update;
	foreach($keys as $key) {
		$current[$key] = [];
		$current = &$current[$key];
	}
	$current = $data;
	// exclusive lock
	if (!($lockfd = fopen($base.'.lock', 'r+')) || !flock($lockfd, LOCK_EX)) {
		if ($lockfd) { fclose($lockfd); }
		$this->error_wsod("Database exclusive lock failure");
	}
	// read
	if (FALSE === ($raw = file_get_contents($base))) {
		flock($lockfd, LOCK_UN); fclose($lockfd);
		$this->error_wsod("Database read failure");
	}
	else if ('' === $raw) {
		$this->database[$file] = [];
	}
	else if (!is_array($raw = json_decode($raw, TRUE))) {
		flock($lockfd, LOCK_UN); fclose($lockfd);
		$this->error_wsod("Database json corrupted");
	}
	else {
		$this->database[$file] = $raw;
	}
	// merge update TODO handle add new or update existing records
	if (empty($keys)) {
		// may not write entirely new database if database exists
		if ($new && !empty($this->database[$file])) {
			flock($lockfd, LOCK_UN); fclose($lockfd);
			return FALSE;
		}
		// but you can update an empty or full database
		$this->database[$file] = (is_array($data) ? $data : (NULL === $data ? [] : array($data)));
	}
	else {
		// search to find or not find element
		$element = &$this->database[$file];
		$previous = $key = NULL;
		$found = TRUE;
		foreach ($keys as $key) {
			$previous = &$element;
			if (!isset($element[$key])) { $found = FALSE; break; }
			$element = &$element[$key];
		}
		if (($new && $found) || (!$new && !$found)) {
			flock($lockfd, LOCK_UN); fclose($lockfd);
			return FALSE;
		}
		else if (NULL === $data) {
			unset($previous[$key]);
		}
		else {
			$this->array_walk_merge($this->database[$file], $update);
		}
	}
	// write
	$this->set_json($base, $this->database[$file]);
	flock($lockfd, LOCK_UN); fclose($lockfd);
	return TRUE;
}

public function get_database(	// read database
string	$file,					// filename within extension
array	$keys,					// read element from key path or [] returns whole database
) : mixed {						// return element or NULL for failure
	// errors
	if (!preg_match(ABCMS_REGEX_DATA, $file)) { $this->error_wsod("Database name invalid: {$file}"); } // invalid file
	// cached or not cached
	$file = $this->output_extension().'/ABCMS.database/'.$file;
	if (!isset($this->database[$file])) {
		// shared lock
		$base = ($this->compiles['core']['projectroot']??$this->settings['core']['projectroot']).'/private'.$file;
		if (!($lockfd = fopen($base.'.lock', 'r')) || !flock($lockfd, LOCK_SH)) {
			if ($lockfd) { fclose($lockfd); }
			$this->error_wsod("Database shared lock failure");
		}
		// read
		if (FALSE === ($raw = file_get_contents($base))) {
			flock($lockfd, LOCK_UN); fclose($lockfd);
			$this->error_wsod("Database read failure");
		}
		else if ('' === $raw) {
			$this->database[$file] = [];
		}
		else if (!is_array($raw = json_decode($raw, TRUE))) {
			flock($lockfd, LOCK_UN); fclose($lockfd);
			$this->error_wsod("Database corrupted");
		}
		else {
			$this->database[$file] = $raw;
		}
		// release lock
		flock($lockfd, LOCK_UN); fclose($lockfd);
	}
	// return data
	$element = $this->database[$file];
	foreach ($keys as $key) {
		if (!isset($element[$key])) { return NULL; }
		$element = $element[$key];
	}
	return $element;
}







/*************************************************************************************************
SECTION OUTPUT: Everything is a routed extension.
*/

public function output(	// hookable output path router extension function manager
string	$hook,			// /vendor/extension/$hook name, only create hooks for your own extension
string	$meth,			// HTTP methods, '' = ALL = "CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE"
string	$default,		// default function, '' = no default
int		$role,			// minimum role permissions
int		$flag,			// <0 = extender exclusive, 0 = anyone, 1 = extender exclusive allowed
bool	$must,			// must do default, TRUE = required -OR- FALSE = optional
mixed	&...$args,		// default arguments
) : array {				// return input $args
	// stack start essential even for core
	$pushed = FALSE; if (empty($this->stackwho)) { $this->stackwho[] = ABCMS_EXT_SELF; $pushed = TRUE; } try {
	// Initialize
	$whoami = $this->output_extension(); // which extension?
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
		if ($this->input['role'] >= ABCMS_ROLE_ADMINS) { $this->stackarg[] = func_get_args(); } // log the exension stack when I am administrator
		if (isset($extin['arg'])) { $this->array_walk_merge($args, $extin['arg']); } // Extend arguments
		if (empty($extin['fun'])) { continue; } // Extension only grabs exclusivity or set args
		do { // Repeat hook extension until FALSE -OR- NULL
			if (FALSE === ob_start()) { $this->error_wsod("Buffer start failure."); } // Buffer output
			$more = $this->output_call($extin['who'], $extin['fun'], ...$args); // Execute hook extension
			if (FALSE === ($out = ob_get_clean())) { $this->error_wsod("Buffer clean failure."); } // Retrieve buffer
			// Output filter extensions by priority
			foreach($ext['O'] as $extout) {
				if (!$this->output_doit($extout, $whoami, $flag, TRUE, $exout)) { continue; } // Skip for reasons
				$this->output_call($extout['who'], $extout['fun'], $out, ...$args); // Execute output filter
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
	// stack pop
	} finally { if ($pushed) { array_pop($this->stackwho); } }
	//return $arguments
	return $args;
}

private function output_extension() : string { // return callers extension name
	if (empty($this->stackwho)) { $this->error_wsod("Session stack identity missing."); }
	return end($this->stackwho);
}

private function output_doit(	// shall we execute hook extension?
array	$ext,					// extension definition
string	$whoami,				// is this extender allowed
int		$flag,					// <0 = extender exclusive, 0 = anyone, 1 = extender exclusive allowed
bool	$must,					// must also do default
?string	&$excl,					// exclusive extension winner
) : bool {						// return TRUE or FALSE
	// Exit before exclusive selection
	if (!$must && !$ext['ord']) {																	return FALSE; }	// No default extension
	if (!empty($ext['met']) && FALSE === stripos($ext['met'], $this->boots['urlmethod'])) { 		return FALSE; }	// HTTP method | TODO consider this instead in_array($method, explode('-', $met)
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

private function output_call(	// call extension function
string	$who,					// extension stack
string	$filefunc,				// includefile?function
mixed	&...$args,				// arguments passed
) : ?bool {						// return function result
	// Parse includefile?function
	if (!preg_match(ABCMS_REGEX_FUNC, $filefunc, $match)) { $this->error_wsod("Calling invalid function name."); }
	$filepath	= $match[2]; // extension include file
	$classobject= $match[5]; // class or object
	$operator	= $match[6]; // operator to function
	$funcmeth	= $match[7]; // function / method
	// include the file
	$result = FALSE; // Default failure
	// push who stack
	$this->stackwho[] = $who; try {
	// includes
	if ($filepath) {
		$filepath = $this->settings['core']['projectroot'].'/private'.$who.$filepath; // resolved filename
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
				if (ABCMS_EXT_SELF != $who && $newobject === $this) { // Disallow abcms() privates unless extension is ABCMS
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
	// pop who stack
	} finally { array_pop($this->stackwho); }
	return $result;
}


private function output_security(	// inject html form security with regex for speed instead of DOM 
string &$html,						// inject into output HTML
) : void {							// return void
	// failure or no form so skip
	if (FALSE === ($num = preg_match_all(ABCMS_REGEX_FORM, $html))) { $this->error_wsod("Form security failed initialization."); }
	if (!$num) { return; }
	// start session
	if (!$this->session_start(1)) {
		// session failed, disable forms with <fieldset> and CSS with missing CSRF as safety net
		$this->set_errors("Forms disabled, security failed.");
		if (!($html = preg_replace(ABCMS_REGEX_FORM, '$1<fieldset disabled class="disable">$2</fieldset>$3', $html, -1, $count)) || $count !== $num) {
			$this->error_wsod("Form security entirely failed.");
		}
		$regex_safe = str_replace(['\\', '$'], ['\\\\', '\\$'], $this->input['nonce']);
		if (!($html = preg_replace('/<\/head>/ui', "\n<style nonce='{$regex_safe}'>form { pointer-events: none; opacity: 0.5; }\n</style>\n</head>", $html, 1, $count)) || 1 !== $count) {
			$this->error_log("Form security css failed.");
		}
		return;
	}
	// session shortcut and click delay
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
		button.form['{$this->ss['void_name']}'].value = '';
		button.form['{$this->ss['full_name']}'].value = '{$this->ss['full_valu']}';
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
	$inject_script = str_replace(['\\', '$'], ['\\\\', '\\$'], $inject_script);
	if (!($html = preg_replace('/<\/head>/ui', $inject_script, $html, 1, $count)) || 1 !== $count) { // inject
		$this->error_wsod("Form security javascript injection failed.");
	}
	// form security tokens
	$inject_tokens = <<<EOF
<input type='hidden' name='clicked'					value=''>
<input type='hidden' name='csrf'					value='{$this->ss['csrf_valu']}'>
<input type='hidden' name='{$this->ss['csrf_name']}'	value='{$this->ss['csrf_valu']}'>
<input type='hidden' name='{$this->ss['void_name']}'	value='{$this->ss['full_valu']}'>
<input type='hidden' name='{$this->ss['full_name']}'	value=''>
EOF;
	// form CAPTCHA
	$regex_safe = str_replace(['\\', '$'], ['\\\\', '\\$'], $this->ss['test_name']);
	$inject_captcha = (!empty($this->ss['user']['valid']) ? NULL : <<<EOF
<div class='captcha'>
CAPTCHA <input name='{$regex_safe}' value=''> \$1 \$3
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
				(!($replace = preg_replace('/(<button(?=[\s])[^>]*?\stype\s*=(\s*submit|\s*\'submit\'|\s*"submit"))(.+?<\/button>)/uis', $inject_captcha, $replace, 1, $one)) ||
				(1 !== $one && (!($replace = preg_replace('/(<input(?=[\s])[^>]*?\stype\s*=(\s*submit|\s*\'submit\'|\s*"submit"))(>|\s+[^>]*?>)/uis', $inject_captcha, $replace, 1, $one)) || 1 !== $one)))) {
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

private function output_debug(	// inject debug information for administrator only
string &$html,					// inject into HTML output string
) : void {						// return void
	if (!$html || $this->input['role'] !== ABCMS_ROLE_ADMINS) { return; }
	$injection = "<pre class='debug'><h2>Coredump</h2>".print_r(array('ABCMS_OBJECT'=>$this, 'ABCMS_GLOBALS'=>$GLOBALS),TRUE)."</pre></body>";
	$injection = str_replace(['\\', '$'], ['\\\\', '\\$'], $injection);
	if (!($html = preg_replace('/<\/body>/ui', $injection, $html, 1))) { $this->error_wsod("Debug injection for admin failed."); }
	return;
}







/*************************************************************************************************
SECTION RESPONSES: Return request responses.
*/

private function error_trace() : array { // return backtrace info for error log
	// Omit object, include args, 3 levels back
	$back = debug_backtrace(0, 3);
	$function = (empty($back[1]['function']) ? 'unknown' : $back[1]['function']);
	$args = (empty($back[2]['args']) ? array('unknown') : $back[2]['args']);
	// Truncate long strings
	array_walk_recursive($args, function (&$value) {
		if (is_string($value) && mb_strlen($value, 'UTF-8') > 256) {
			$value = mb_substr($value, 0, 256, 'UTF-8') . '...';
		}
	});
	return [$function, $args];
}

public function error_wsod(	// throw exception
string $mess,				// message
) : void {					// never returns
	[$function, $args] = $this->error_trace();
	error_log("{$function}->error_wsod() {$mess}\n".print_r($args,TRUE));
	throw new Exception($mess);
	return;
}

public function error_log(	// log error
string $mess,				// message
) : void {					// return void
	[$function, $args] = $this->error_trace();
	error_log(($mess = "{$function}->error_log() {$mess}\n".print_r($args,TRUE)));
	return;
}

public function set_errors(	// set user errors
	string ...$errors,		// message
) : void {					// return void
	array_push($this->errors, ...$errors);
	return;
}

public function get_debugs() : array { // return debugs for public
	return $this->debugs;
}

public function get_errors() : array { // return errors for public
	return $this->errors;
}

public function error_get_last() : ?string { // return last error message
	$error = error_get_last();
	return ($error ? "{$error['message']} [type={$error['type']}] in {$error['file']} on line {$error['line']}" : NULL);
}

public function see_errors() : ?string { // return formatted errors for public
	if (!empty($this->errors)) { return '<br><br>Errors:<br>'.implode('<br>',$this->errors); }
	return NULL;
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
// TODO, the above line is wrong when first logging in because don't know I am authenticated till later...
// how can I do that without putting the authentication into session_start()????
	return $this->theme( // theme
		...$args = array( // spreader
			NULL,	// css
			NULL,	// js
<<<EOF
<h1><a href='/' title='A Basic Content Management System'>A Basic Content Management System&trade;</h1></a></h1>
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
<p class='center margin-top-0'>
AKA "<a href='https://www.AionianBible.org' target='_blank'>Aionian Bible</a> Content Management System"<br>
A PHP web developer toolkit and CMS in one file.
</p>
Composer install, or drop index.php in docroot and go. What's included?
<ul class='line-height-16'>
<li>Zero dependencies in one file with no node_modules, no build, no vendor lock-in, for a working CMS.</li>
<li>Routed extensions via output(); one call routes pages, APIs, CLI, anything, and without controller boilerplate.</li>
<li>Automatic input validation and sanitization on query strings, form data, and path variables.</li>
<li>Path variables live in the URL itself as /key=value segments with no router configuration needed.</li>
<li>Realtime file-based routing because extensions load on demand without Composer autoload overhead.</li>
<li>Secure sessions done right with timeout, idle detection, rotation, cookie-consent, and bad-actor detection.</li>
<li>Form security with CSRF, session rotation, honeypot, reverse-honeypot, and CAPTCHA injected always.</li>
<li>Built-in abuse detection with rapid-hit throttling and bad-actor tags for 400/429 responses, not just login limits.</li>
<li>Fails closed, never fails open, if the security subsystem can't verify itself, forms disable visibly and safely.</li>
<li>Trifecta authentication with account email, security email, and system-generated 64-byte passwords.</li>
<li>CSP-ready by default with nonces wired into every injected script and style tag.</li>
<li>Concurrency-safe JSON database, with locked read/write and CSV, SQLite, and MySQL on the roadmap.</li>
<li>Graceful failure handling with clean, safe error pages for visitors, and full debug coredumps for autopsy.</li>
<li>Core /homepage, /contact, /account, /webfiles, /console, and /CLI, all use the same output() mechanism.</li>
<li>Built-in SMTP, hardened against header injection, with no mail library required.</li>
<li>Essential utilities include fail-safe include_once(), unique token generation, and more.</li>
<li>PHP itself is the template engine so no new syntax to learn, and no templating DSL to fight.</li>
</ul>
No frameworks. No ceremony. Just PHP, HTML, JavaScript. Why can't a CMS be simple? Yee Haw!
</p>
EOF;
	return NULL;
}

// account register, login, logout, update, delete
public function home_account(mixed &...$unused) : ?bool {

	// initialize
	echo "<h2>Account</h2>";
	$switch =
		(!$this->session_start(1) ? 'nosession' :
		('POST' !== $this->boots['urlmethod'] ? 'form' :
		(!$this->formvalid ? 'invalid' :
		(!$this->formhuman ? 'inhuman' :
		(!empty($_POST['clicked']) ? $_POST['clicked'] : 'unknown')))));
	$mess = $email = $email2 = $subject = $body = NULL;

	// switch
	switch ($switch) {
		case 'nosession':	$this->set_errors('Login system is unavailable. Try again.'); return NULL;
		case 'invalid':		$mess = "Suspect form submital. Try again."; break;
		case 'inhuman':		$mess = "CAPTCHA or form security alert. Try again."; break;
		case 'login':		if (!empty($_POST['Account_Email']) && !empty($_POST['Account_Email2']) &&
								password_verify($_POST['Account_Password'], $this->settings['core']['passhash']) &&
								($this->ss['user'] = $this->get_database('BASIC.json', array('user', $_POST['Account_Email'])))) {
								$this->ss['trys'] = 0;
								$this->ss['logins'] = $this->get_uniq();
								$this->set_cookie($this->settings['core']['session_logins'], $this->ss['logins'], $this->ss['create'] + ABCMS_SES_LIFE);
								$mess = "Login success.";
								$email = $this->hsc($_POST['Account_Email']);
								$email2 = $this->hsc($_POST['Account_Email2']);
								$subject = "ABCMS Login Success by {$_POST['Account_Email']}";
								$body = "<h4>Hello</h4>You are logged into " . $this->boots['urldomain'];
							}
							else if (++$this->ss['trys'] > ABCMS_SES_LOGI) {
								$this->session_start(-1);
								$this->error_wsod("Too many failed logins, good bye.");
							}
							else {
								$mess = "Login failure, please try again.";
							}
							break;

		case 'register':	$okay = TRUE;
							$user = array('valid'=>TRUE,'email'=>$_POST['Account_Email'],'email2'=>$_POST['Account_Email2'],'role'=>ABCMS_ROLE_ADMINS);
							if (!empty($_POST['Account_Email']) && !empty($_POST['Account_Email2']) &&
								password_verify($_POST['Account_Password'], $this->settings['core']['passhash']) &&
								($okay = $this->set_database('BASIC.json', array('user', $_POST['Account_Email']), $user, TRUE))) {
								$this->ss['trys'] = 0;
								$this->ss['logins'] = $this->get_uniq();
								$this->set_cookie($this->settings['core']['session_logins'], $this->ss['logins'], $this->ss['create'] + ABCMS_SES_LIFE);
								$this->ss['user'] = $user;
								$mess = "Registration success.";
								$email = $this->hsc($_POST['Account_Email']);
								$email2 = $this->hsc($_POST['Account_Email2']);
								$subject = "ABCMS Registration Success by {$_POST['Account_Email']}";
								$body = "<h4>Hello</h4>You are registered and logged into " . $this->boots['urldomain'];
							}
							else if (!$okay) {	$mess = "Registration database failure, please try again."; }
							else {				$mess = "Registration failure, please try again."; }
							break;

		case 'delete':		if (empty($this->ss['user']['email']) ||
								empty($_POST['Account_Email']) ||
								$_POST['Account_Email'] !== $this->ss['user']['email'] ||
								!$this->set_database('BASIC.json', array('user', $this->ss['user']['email']), NULL, FALSE)) {
								$mess = "Delete failure, please try again.";
								break;
							}
							$mess = "Account deleted.";
							$subject = "ABCMS Account Deleted: {$_POST['Account_Email']}";
							$body = "<h4>Hello</h4>Your account is deleted at " . $this->boots['urldomain'];

		case 'logout':		$this->session_start(-1);
							$mess = ($mess??"You are logged out.");
							break;

		case 'reset':
		case 'update':
		case 'form':
		case 'unknown':
		default:			if (!empty($this->ss['user']['valid'])) {
								$email = $this->hsc($this->ss['user']['email']);
								$email2 = $this->hsc($this->ss['user']['email2']);
							}
							$mess = "Login or register below.";
							break;
	}
	
	// send email
	$emailerror = "No email sent";
	if ($subject) {
		$emailerror = $this->email(
			$this->settings['core']['smtp_user'],								// from
			($this->settings['core']['smtp_name']??$this->boots['urldomain']),	// name
			$this->settings['core']['smtp_user'],								// recipients
			NULL,																// cc
			$this->settings['core']['smtp_user'],								// bcc
			$subject,															// subject
			$body,																// HTML body
			$this->html_text($body),											// text body
			$this->settings['core']['projectroot'].'/private'.ABCMS_EXT_SELF.'/ABCMS.translog', // attachments
			[	'smtp'	=> $this->settings['core']['smtp_host'],				// SMTP host
				'port'	=> $this->settings['core']['smtp_port'],				// SMTP port
				'user'	=> $this->settings['core']['smtp_user'],				// SMTP user
				'pass'	=> $this->settings['core']['smtp_pass'],				// SMTP pass
				'ehlo'	=> $this->boots['urldomain'],							// SMTP EHLO
			],
		);
		$emailerror = (TRUE === $emailerror ? "Email sent" : $emailerror);
	}

	// display account
	$stat = (empty($this->ss['user']) ? "Logged out" : (empty($this->ss['user']['valid']) ? "Logged in validating" : "Logged in validated"));
	echo <<<EOF
<form action='' method='post' accept-charset='UTF-8' class='form-grid'>
<label							>Status:</label>		<span>{$stat}</span>
<label							>Result:</label>		<span>{$mess}</span>
<label							>Notification:</label>	<span>{$emailerror}</span>
<label for='Account_Email'		>Email:</label>			<input type='email'		id='Account_Email'		name='Account_Email'	value='{$email}'>
<label for='Account_Email2'		>Email2:</label>		<input type='email'		id='Account_Email2'		name='Account_Email2'	value='{$email2}'>
<label for='Account_Password'	>Password:</label>		<input type='password'	id='Account_Password'	name='Account_Password'	value=''>
<label></label>
<div>
EOF;
if (empty($this->ss['user']['valid'])) {
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
<h2>Contact</h2>
EOF;
	return NULL;
}
private function home_notfound(mixed &...$unused) : ?bool {
echo <<<EOF
<h2>Status</h2>
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
<a href='/command/setup'		target='_blank'>/command/setup</a> (resets login)<br>
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
		case '/command/code':		$this->command_code();		break;
		case '/command/cron':		$this->command_cron();		break;
		case '/command/phpinfo':	$this->command_phpinfo();	break;
		case '/command/setup':		$this->command_setup();		break;
		case '/command/updater':	$this->command_updater();	break;
		case '/command/help':
		default:					$this->command_help();		break;
	}
	return NULL;
}
private function command_code(mixed &...$unused) : ?bool {
	highlight_file($this->rp(__FILE__));
	return NULL;
}
private function command_cron(mixed &...$unused) : ?bool {
	if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
	echo "ABCMS cron\n\nDone.\n\n";
	return NULL;
}
private function command_help(mixed &...$unused) : ?bool {
	if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
	echo "ABCMS help\n\nDone.\n\n";
	return NULL;
}
private function command_phpinfo(mixed &...$unused) : ?bool {
	phpinfo();
	return NULL;
}
private function command_setup(mixed &...$unused) : ?bool {
	if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
	// TODO call this command from the web here using cURL to force cache reload
	// if validate_timestamps = 0;
	$this->setup(); // recreate settings
	// op cache warning
	$mess = NULL;
	if (function_exists('opcache_get_configuration') && !ini_get('opcache.validate_timestamps')) {
		$mess = "WARNING: opcache.validate_timestamps=0, manually reload PHP/FPM to realize changes.\n\n";
		$this->error_log($mess);
	}
	echo "ABCMS settings:\n\nrefresh screen to see updated settings changes\n\n{$mess}Done.\n\n";
	return NULL;
}
private function command_updater(mixed &...$unused) : ?bool {
	if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
	echo "ABCMS updater\n\nDone.\n\n";
	return NULL;
}







/*************************************************************************************************
SECTION UTILITIES: Utility helper methods.
*/
// Wrap the echo() construct to use as extension function.
public function echo(?string ...$args) : void {
	if (NULL !== $args) { echo implode('',$args); } return;
}
// Wrap the print() construct to use as extension function.
public function print(?string $string = NULL) : bool {
	return (NULL === $string ? TRUE : print($string));
}
// Set url with persistant path variables
public function set_url(?string $path = NULL) : ?string {
	return $path;
}
// Get url with persistant path variables
public function get_url(?string $path = NULL) : ?string {
	return $path;
}
// linux style slashes
public function rp(string|false $path) : string|false {
	return ($path === FALSE ? FALSE : str_replace('\\', '/', $path));
}
// Check file
private function chk_file(string $filename, bool $must = FALSE) : bool {
	$starts = ($this->compiles['core']['projectroot']??$this->settings['core']['projectroot']).'/private'.$this->output_extension().'/';
	if (!str_starts_with($filename, $starts)) { $this->error_wsod("Access outside of extension folder disallowed: {$filename}"); }
	if (preg_match('/(^|[\/\\\\])\.\.([\/\\\\]|$)/', $filename)) { $this->error_wsod("Relative filenames disallowed: {$filename}"); }
	if (is_link($filename)) { $this->error_wsod("Symbolic link filenames disallowed: {$filename}"); }
	if ($must && ($this->rp(realpath($filename)) !== $filename || !is_file($filename) || !is_readable($filename))) { return FALSE; }
	return TRUE;
}
// Set file
public function set_file(string $filename, string $value) : void {
	$this->chk_file($filename);
	$temp = "{$filename}.".getmypid();
	if (FALSE === file_put_contents($temp, $value) || !chmod($temp, 0640) || !rename($temp, $filename)) {
		if (file_exists($temp)) { unlink($temp); }
		$this->error_wsod("System, ".$this->error_get_last());
	}
	return;
}
// Get file
public function get_file(string $filename, string &$data) : void {
	if (!$this->chk_file($filename, TRUE)) { $this->error_wsod("Filename does not exist or not readable: {$filename}"); }
	if (FALSE === ($data = file_get_contents($filename))) { $this->error_wsod("System, ".$this->error_get_last()); }
	return;
}
// touch file permissions
public function touch(string $filename) : void {
	$this->chk_file($filename);
	if (!touch($filename) || !chmod($filename, 0640)) { $this->error_wsod("Touch file failed: {$filename}"); }
	return;
}
// Set json
public function set_json(string $filename, mixed $value) : void {
	$this->set_file($filename, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	if (json_last_error() !== JSON_ERROR_NONE) {
		$this->error_wsod("System json_encode(), ".json_last_error_msg());
	}
	return;
}
// Get json
public function get_json(string $filename, mixed &$data) : void {
	if (!$this->chk_file($filename, TRUE)) { $this->error_wsod("Filename does not exist or not readable: {$filename}"); }
	if (NULL === ($data = json_decode(file_get_contents($filename), TRUE))) { $this->error_wsod("System, ".json_last_error_msg().", ".$this->error_get_last()); }
	return;
}
// set var_dump
public function set_dump(string $filename, mixed $data) : void {
	// partial validity check at top level, nested elements unvalidated
	if (is_object($data) || is_resource($data)) { $this->error_wsod("set_dump() supports scalars, arrays, and NULL only."); }
	$this->set_file($filename, "<?php return " . var_export($data, TRUE) . ";\n");
	if (function_exists('opcache_invalidate')) { opcache_invalidate($filename, TRUE); }
}
// get var_dump
public function get_dump(string $filename, mixed &$data) : bool {
	if (!$this->chk_file($filename, TRUE)) { return FALSE; }
	// beware, failed include() = FALSE = successful include() returning FALSE
	$fn = Closure::bind(static function($f) { return include($f); }, NULL, NULL);
	$data = $fn($filename);
	return TRUE;
}
// Include always
public function include(string $filename, ...$args) : mixed {
	if (!$this->chk_file($filename, TRUE)) { $this->error_wsod("Filename does not exist or not readable: {$filename}"); }
	// beware, failed include() = FALSE = successful include() returning FALSE
	// anonymous scopes $args within include, hides $this, and protects abmcs() privates
	$anonymous = Closure::bind(function($filename, ...$args) { return include($filename); }, NULL, NULL);
	return $anonymous($filename, ...$args);
}
// Include once, PHP should provide a native no fault include_once() function
public function include_once(string $filename, ...$args) : mixed {
	static $included = array();
	if (!isset($included[$filename])) {
		if (!$this->chk_file($filename, TRUE)) { $this->error_wsod("Filename does not exist or not readable: {$filename}"); }
		$included[$filename] = TRUE;
		// anonymous scopes $args within include, hides $this, and protects abmcs() privates
		$anonymous = Closure::bind(function($filename, ...$args) { return include($filename); }, NULL, NULL);
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
// Derived deterministic hash key, permanent and segregated by extension, 64 bytes
public function get_pkey(?string $input): string {
	return hash('sha256', $this->output_extension().$input);
}
// Derived deterministic hash key, temporal with settings secret and segregated by extension, 64 bytes
public function get_ckey(?string $input): string {
	return hash('sha256', ($this->compiles['core']['secret']??$this->settings['core']['secret']).$this->output_extension().$input);
}
// htmlspecialchars() wrapper
public function hsc(?string $string): ?string {
	return (NULL === $string ? NULL : htmlspecialchars(($string), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'));
}
// HTML to plain text
public function html_text(string $html): string {
	// remove JavaScript, CSS, and head
	$html = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $html);
	$html = preg_replace('/<style[^>]*?>.*?<\/style>/is', '', $html);
	$html = preg_replace('/<head[^>]*?>.*?<\/head>/is', '', $html);
	// add spacing for block-level endings
	$html = preg_replace('/<\/(p|div|h[1-6]|li)>\s*/i', "\n\n", $html);
	$html = preg_replace('/<\/(tr|blockquote)>\s*/i', "\n", $html);
	$html = preg_replace('/<(br|br\s*\/)>\s*/i', "\n", $html);
	// strip remaining tags
	$text = strip_tags($html);
	// clean up spacing
	$text = preg_replace('/[ \t]+/', ' ', $text); // Collapse multiple inline spaces/tabs
	$text = preg_replace('/\n{3,}/', "\n\n", $text); // Limit max consecutive newlines to two
	// decode special HTML characters
	$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	return trim($text);
}







/*************************************************************************************************
SECTION EMAIL: SMTP emailer.
 */
// Adapted by Claude.AI from https://github.com/arkanis/smtp_send.
// Licensed as arkanis/smtp_send (c) 2014-2021 Stephan Soller, MIT License.
public function email(
	string				$from,		// from, envelope + header
	string				$name,		// from name, header
	array|string		$to,		// recipients
	array|string|NULL	$cc,		// cc, envelope + header
	array|string|NULL	$bcc,		// bcc, envelope
	string				$subject,	// subject, auto UTF-8 & base64
	string|NULL			$html,		// HTML body
	string|NULL			$text,		// plain-text, optional
	array|string|NULL	$attach,	// attachments, absolute path
	array				$options=[],// options
									// 'smtp'	=> hostname, 'tcp://host' (587), or 'ssl://host' (port 465)
									// 'port'	=> 587 (STARTTLS/explicit TLS), 465 (SSL/implicit TLS), or 25
									// 'user'	=> SMTP username, empty to skip auth
									// 'pass'	=> SMTP password
									// 'time'	=> socket timeout seconds, default php: default_socket_timeout
									// 'ehlo'	=> EHLO identity
									// 'ssl'	=> stream SSL context options for STARTTLS, ie. ['verify_peer'=>FALSE]
									// 'debug'	=> bool, log everything
): bool|string {					// TRUE if delivered or error string
	// argument and option defaults
	if (NULL !== $to && is_string($to)) { $to = array($to); }
	if (NULL !== $cc && is_string($cc)) { $cc = array($cc); }
	if (NULL !== $bcc && is_string($bcc)) { $bcc = array($bcc); }
	if (NULL !== $attach && is_string($attach)) { $attach = array($attach); }
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
		if (!preg_match('/^(tcp:\/\/|tls:\/\/|ssl:\/\/|)(127\.0\.0\.1|localhost|::1|\[::1\])$/uiD', $options['smtp']))  { return $fail("Unauthenticated email can only SMTP from same server."); }
		if (!preg_match('/^[^@]+@([a-z0-9-]+\.)*'.preg_quote($this->boots['urldomain'], '/').'$/uiD', $from))  { return $fail("Unauthenticated email 'From' domain only from same domain."); }
	}

	// Sanitize header-bound fields (defense in depth)
	// Even though we base64-encode the subject and never let addresses touch
	// headers unescaped, strip CR/LF from anything that lands in a header so
	// a stray newline can never inject an extra header or command.
	$name = preg_replace('/[\r\n]+/', '', $name);
	$subject  = preg_replace('/[\r\n]+/', '', $subject);

	// SMTP command-injection guard on every address
	// If an address contains an unescaped ">" it could break out of
	// "RCPT TO:<...>" and inject further SMTP commands.
	if (empty($to)) { return $fail("Email requires at least one recipient."); }
	$allRecipients = array_unique(array_merge($to, ($cc??[]), ($bcc??[])));
	foreach (array_merge([$from], $allRecipients) as $addr) {
		// validate email
		if (!filter_var($addr, FILTER_VALIDATE_EMAIL)) { return $fail("Invalid email address rejected: '{$addr}'."); }
		// newlines allow command injection
		if (preg_match('/[\r\n]+/', $addr)) { return $fail("Unsafe email address rejected: '{$addr}'."); }
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
		$methods = array_slice(preg_split('/[\s=]+/', mb_strtolower($authLine, 'UTF-8')), 1);
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
	$headers .= "Message-ID: <" . bin2hex(random_bytes(16)) . '@' . preg_replace('/^(tcp|tls|ssl):\/\//i', '', $options['smtp']) . ">\r\n";
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
		try { if (!$this->chk_file($filePath, TRUE)) { return $fail("Filename does not exist or not readable: {$filePath}"); } }
		catch (Throwable $e) { return $fail($e->getMessage() ?: "Suspect filename disallowed: {$filePath}"); }
		$fileName = preg_replace('/[\r\n]+/', '', basename($filePath));
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
	$payload = preg_replace('/\r\n|\r|\n/', "\r\n", $payload);
	$payload = preg_replace('/^\./m', '..', $payload);
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
	int		$flag	= 1,	// exclusive control
) : ?bool {					// return boolean
// helpful defaults
$title = mb_strtoupper($this->hsc($this->boots['urldomain']), 'UTF-8');
$lower = mb_strtolower($title, 'UTF-8');
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
header a:link, header a:visited { color: #336699; }
header a:hover, header a:focus { color: #99ccff; }
header a:active { color: #993366; }
header .console { width:100%; display: flex; justify-content: space-between; padding: 10px 0; background-color: #999999; color: #333333; font-size: 2rem; font-weight: bold; }
main { flex: 1;	max-width: 1024px; min-width: min(360px, 100%); margin: 1rem auto; padding: 0rem 3rem 1rem 3rem; text-align: justify; }
footer { margin-bottom: 1rem; }
h1, h2, h3, h4 { color: #336699; }
h1, h2 { text-align: center; }
.bold { font-weight: 700; }
.italic { font-style: italic; }
.center { text-align: center; }
.margin-top-0 { margin-top: 0; }
.margin-bottom-0 { margin-bottom: 0; }
.line-height-14 { line-height: 1.4; }
.line-height-16 { line-height: 1.6; }
.line-height-18 { line-height: 1.8; }
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
};







// methods constructed, now properties
$oneshot = $_abcms->oneshot;
$_abcms->oneshot = NULL;
try { if ($oneshot) { $oneshot(); } }
catch (Throwable $e) { $_abcms = NULL; throw $e; }
}
// return fully constructed object or NULL
return $_abcms;
}
