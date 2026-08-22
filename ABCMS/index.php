<?php

/*************************************************************************************************
SECTION INTRO: A Basic Content Management System and PHP toolkit.

Copyright (c) 2026 Nainoia Inc. All rights reserved.
Search for 'SECTION' and 'function' below for documentation.
Copy index.php to a docroot or run 'composer install nainoia-inc/abcms'.
Visit index.php in a browser or run 'php index.php /command/help'.
Download the super user password from 'ABMCS.deleteme', then delete.
Extend imitating setup(), home_*(), webfiles_*(), console_*(), command_*().
Everything is a routed extension, but extensions also do their own routing.
Access $_SESSION[extension] with s() API, but $_SESSION remains exposed.
Run extension SETUP.php with /command/setup and CRON.php with /command/cron.
Schedule 'php index.php /command/cron' every 15 minutes to 1x per day.
*/







/*************************************************************************************************
SECTION REQUIREMENTS: Minimum requirements for operation.

PHP version 8.1.0 or greater
Filesystem supporting flock($fd, LOCK_EX)
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
const ABCMS_EXT_PRIVATE	= '/private/nainoiainc/abcms/';			// core private file folder
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
// regex
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
// cookie permissions, TODO use
const ABCMS_COOK_LIFE	= 60*60*24*365;		// choice for 1 year
const ABCMS_COOK_NONE	= 0;				// none
const ABCMS_COOK_FORM	= 1;				// security
const ABCMS_COOK_NAVS	= 2;				// navigation
const ABCMS_COOK_TRAK	= 3;				// tracking
// response types
const ABCMS_LOG_DEBUG	= 0;				// silent log, if URL debug=TRUE
const ABCMS_LOG_TRACE	= 1;				// silent log
const ABCMS_LOG_INFO	= 2;				// log || echo user
const ABCMS_LOG_WARN	= 3;				// log || echo user
const ABCMS_LOG_ERROR	= 4;				// log || echo user
const ABCMS_LOG_FATAL	= 5;				// log && echo user
const ABCMS_LOG			= array('Debug','Trace','Info','Warning','Error','Fatal'); // log type map
const ABCMS_LOGTO_LOGS	= 0;				// to logs
const ABCMS_LOGTO_USER	= 1;				// to user
const ABCMS_LOGTO_BOTH	= 2;				// to both
const ABCMS_LOGTO		= array('Logs','User','Both'); // logto map
// session controls, TODO - move to overridable $settings
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
	// graceful WSOD
	$title = mb_strtolower(htmlspecialchars(((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://').($_SERVER['HTTP_HOST']??'unknown').($_SERVER['REQUEST_URI']??'')), ENT_QUOTES, 'UTF-8')); // title
	$nonce = chr(random_int(97,122)).chr(random_int(97,122)).bin2hex(random_bytes(31)); // security nonce
	$exception = htmlspecialchars(($e->getMessage() ?: 'Fatal exception, details logged.'), ENT_QUOTES, 'UTF-8'); // thrown error
	$buffer = NULL; while(ob_get_level()) { $buffer .= ob_get_clean(); } // retrieve buffer
	if ('cli' !== PHP_SAPI) { echo <<<EOF
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<meta name='description' content='{$title} ERROR'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<meta name='mobile-web-app-capable' content='yes'>
<meta name='theme-color' content='#336699'>
<meta name='color-scheme' content='light dark'>
<meta http-equiv='Content-Security-Policy' content="default-src 'none'; style-src 'nonce-{$nonce}'; img-src 'self';">
<title>{$title} ERROR</title>
<link rel='icon' href='favicon.ico'>
<style nonce='{$nonce}'>
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
<br>
<a href='/'>Try again from the homepage</a>.
</p></div></body></html>
EOF;
	}
	// CLI echo
	else {
		echo (abcms() ? abcms()->response_plain() : $exception)."\n\n";
	}
	// file output
	$composer = array(); // composer extensions
	if (class_exists(\Composer\InstalledVersions::class)) {
		foreach (Composer\InstalledVersions::getInstalledPackagesByType('abcms-extension') as $name) {
			$composer[$name] = Composer\InstalledVersions::getInstallPath($name);
		}
	}
	file_put_contents( // dump corefile
		str_replace('\\', '/', __DIR__).'/..'.ABCMS_EXT_PRIVATE.'ABCMS.coredump',
		print_r(array(
			'ABCMS_EXCEPTION'	=> $exception,
			'ABCMS_SYSTEM'		=> error_get_last(),
			'ABCMS_OBJECT'		=> (abcms()?:'Constructor failed.'),
			'ABCMS_GLOBALS'		=> $GLOBALS,
			'ABCMS_BUFFER'		=> $buffer,
			'ABCMS_COMPOSER'	=> $composer,
		), TRUE),
	);
	$code = 1; // return failure
}

finally { // clean up
	if (abcms()) { abcms()->response_flush(); } // write and clear log buffer after all
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
private				array	$stackarg	= [];		// TODO combine debug stack args
private				array	$stackwho	= [];		// extension stack
private				array	$resplogs	= [];		// response log
private				array	$respuser	= [];		// response user
private				bool	$formvalid	= FALSE;	// form valid
private				bool	$formhuman	= FALSE;	// form human

function __construct() { $this->oneshot = function() { $this->input_construct(); }; } // 1st construct object methods, so extension SETUP.php can use abcms() methods

private function input_construct() { // 2nd construct object properties
	// initialize
	$this->stackwho[] = ABCMS_EXT_SELF; // push core on extension stack
	$this->setup(TRUE); // assign $settings
	if (FALSE === ini_set('error_log', $this->settings['core']['translog'])) { $this->response('CORE: ini_set error_log failed, '.$this->error_get_last(), ABCMS_LOG_ERROR); } // set log destination after setup
	while(ob_get_level() > 0) { if (FALSE !== ($buf = ob_get_clean()) && '' !== $buf) { $this->response('CORE: unexpected output buffers discarded', ABCMS_LOG_ERROR); } } // empty buffers
	// bootstrap inputs for session_start(), then session user validates remaining inputs
	$this->boots = array(
		'time'			=> time(), // execution time()
		'ip'			=> ($ip = ($_SERVER['REMOTE_ADDR'] ?? 'unknown')), // caller ip
		'uagent'		=> ($ip.(($_SERVER['HTTP_USER_AGENT']??'')?:'unknown')), // user identity
		'auto'			=> $this->settings['core']['auto'], // auto-loader
		'cli'			=> ($cli = ('cli' === PHP_SAPI ? TRUE : FALSE)), // CLI execution
		'argc'			=> ($_SERVER['argc']??0), // CLI arg count
		'argv'			=> ($_SERVER['argv']??[]), // CLI args
		'urlfull'		=> ($urlfull = // URL full
			// localhost
			($cli ? ('https://localhost' . // CLI domain
			($_SERVER['argc']>1 && '/' === ($_SERVER['argv'][1][0]?:'') && FALSE !== filter_var('http://localhost' . $_SERVER['argv'][1], FILTER_VALIDATE_URL) ? $_SERVER['argv'][1] : '/command/help')) : // CLI URI validation
			// HTTPS or HTTP
			((isset($_SERVER['HTTPS']) && mb_strtolower($_SERVER['HTTPS'], 'UTF-8') !== 'off' ? 'https://' : 'http://') . // HTTPS secure
			// domain validation with multibyte to punycode
			(!empty($_SERVER['HTTP_HOST']) && ($host = preg_replace('/:\d*$/u','',$_SERVER['HTTP_HOST'])) && // remove ports
			FALSE !== filter_var(idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_DOMAIN) ? $_SERVER['HTTP_HOST'] : 'unknown') . // filter domain
			// URI validation, ascii only
			(isset($_SERVER['REQUEST_URI']) && mb_check_encoding($_SERVER['REQUEST_URI'],'ASCII') && // check encoding
			FALSE!==filter_var('http://localhost'.$_SERVER['REQUEST_URI'],FILTER_VALIDATE_URL) ? $_SERVER['REQUEST_URI'] : '/unknown')))), // filter URI
		'urlparsed'		=> ($urlparsed = parse_url($urlfull)), // URL parse
		'urldomain'		=> (mb_strtolower(($urlparsed['host']??''), 'UTF-8')), // URL domain
		'urlport'		=> ($urlparsed['port']??NULL), // URL port
		'urlmethod'		=> ($cli ? 'CLI' : ((empty($_SERVER['REQUEST_METHOD']) || // URL method
			!in_array($_SERVER['REQUEST_METHOD'], array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE'))) ? 'GET' : $_SERVER['REQUEST_METHOD'])), // validate method
		'urlpathall'	=> ($urlpathall = ('/'.(trim(preg_replace(ABCMS_REGEX_URLV, '/', ($urldecoded = urldecode(($urlparsed['path']??'')))), '/')))), // URL without variables, no trailing slash, and urldecoded
		'urlpathone'	=> (!($ret = preg_match('/^(\/[^\/\x00-\x1f]*)(\/[^\x00-\x1f]+)?$/uD', $urlpathall, $matches)) ? '/' : $matches[1]), // URL first segment for core router
		'urlpathext'	=> (!$ret || empty($matches[2]) ? '/' : $matches[2]), // URL second+ segments for extension routers
	);
	// possibly start session after boots and validate user
	$session = $this->session_start(0); // lazy session start
	// sanitize inputs with user permissions
	$this->input = array(
		'user'			=> $this->ss['user']??NULL, // my user
		'role'			=> ($role = ($cli ? ABCMS_ROLE_CLI : $this->ss['user']['role']??ABCMS_ROLE_PUBLIC)), // my role
		'urlvars'		=> (!preg_match_all(ABCMS_REGEX_URLV, $urldecoded, $matches, PREG_PATTERN_ORDER) ? array() : // validate URL vars 'U'
			$this->input_valid('U', array_combine($matches[1], $matches[2]), $role)),
		'urlquery'		=> ($this->input_valid('G', (mb_parse_str(($urlparsed['query']??''), $result) ? $result : array()), $role)), // URL validate query vars 'q' from parse_str() because CLI has no $_GET
		'postvars'		=> array(), // TODO ($this->input_valid('P', $_POST, $role)), // validate $_POST vars 'p'
		'nonce'			=> $this->get_uniq(), // style & script security nonce
	);
	// initialize completion
	if ($this->boots['auto']) { require_once($this->boots['auto']); } // require composer
	if (!str_starts_with($urldecoded, $urlpathall)) { $this->response('Some settings in that link were out of place and may be ignored.', ABCMS_LOG_WARN, ABCMS_LOGTO_USER); } // warn user, if !str_starts_with() URL is externally constructed
	array_pop($this->stackwho); // pop core off extension stack
	return;
}

public function __set(string $name, mixed $value) : void { $this->response("CORE: dynamic property disallowed, name={$name}", ABCMS_LOG_FATAL); } // disallow dynamic properties

public function __clone() { $this->response('CORE: clone disallowed', ABCMS_LOG_FATAL); } // disallow cloning

private function iamsuper() : bool { return (PHP_SAPI === 'cli' || (isset($this->input['role']) && $this->input['role'] >= ABCMS_ROLE_ADMINS)); } // user is admin

private function input_valid(	// validate input variables
string	$cat,					// 'U'=URL, 'G'=$_GET, 'P'=$_POST
array	$vars,					// variable array to validate
int		$role,					// user role
) : array {						// return $vars array or WSOD
	// loop input variables
	$last = NULL;
	foreach($vars as $var => $val) {
		if ($var < $last) { $this->response("Link settings must be in alphabetical order: '{$var}'.", ABCMS_LOG_WARN, ABCMS_LOGTO_USER); } // warn user, continue
		$last = $var;
		// security, do not distinguish between not found and no permissions
		if (empty($this->settings[$cat][$var]['type']) || $role < $this->settings[$cat][$var]['role']) {
			$this->response("Unknown link setting ignored: '{$var}'.", ABCMS_LOG_WARN, ABCMS_LOGTO_USER);	unset($vars[$var]);	continue; // warn user, unset
		}
		// $val type checks
		if (is_string($val) && 'null' == mb_strtolower($val, 'UTF-8')) { $vars[$var] = NULL; continue; } // NULL special case
		if (!is_string($val) && ('array' !== $this->settings[$cat][$var]['type'])) {
			$this->response("Link setting is not text: '{$var}'.", ABCMS_LOG_WARN, ABCMS_LOGTO_USER); unset($vars[$var]); continue; // warn user, unset
		}
		// switch possibilities
		switch($this->settings[$cat][$var]['type']) {
			case 'array'	:	if (!is_array($val)) { $val = '!array';																			break; }			continue 2;
			case 'bool'		:
			case 'boolean'	:	if (NULL  === filter_var($val, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)) {									break; }			continue 2;
			case 'domain'	:	if (FALSE === filter_var(idn_to_ascii($val, IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_DOMAIN)) {	break; }			continue 2;
			case 'email'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_EMAIL)) {														break; }			continue 2;
			case 'explode'	:	$vars[$var] = explode(',', $val);																									continue 2;
			case 'float'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_FLOAT)) {														break; }			continue 2;
			case 'integer'	:	if (FALSE === filter_var($val, FILTER_VALIDATE_INT)) {															break; }			continue 2;
			case 'ip'		:	if (FALSE === filter_var($val, FILTER_VALIDATE_IP)) {															break; }			continue 2;
			case 'mac'		:	if (FALSE === filter_var($val, FILTER_VALIDATE_MAC)) {															break; }			continue 2;
			case 'mixed'	:
			case 'string'	:																																		continue 2;
			case 'path'		:	if (!str_starts_with($val, '/') || FALSE === filter_var('http://localhost'.$val, FILTER_VALIDATE_URL)) {		break; }			continue 2;
			case 'uri'		:	if (!mb_check_encoding($val, 'ASCII') || FALSE === filter_var('http://localhost'.$val, FILTER_VALIDATE_URL)) {	break; }			continue 2;
			case 'url'		:	if (!mb_check_encoding($val, 'ASCII') || FALSE === filter_var($val, FILTER_VALIDATE_URL)) {						break; }			continue 2;
			case 'uuid'		:	if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/iD', $val)) {		break; }			continue 2;			
			default:			$this->response("INPUT: settings type unknown, var={$var} type={$this->settings[$cat][$var]['type']}", ABCMS_LOG_FATAL); // bad settings, fatal
		}
		// value invalid
		$this->response("Link setting value is not valid: '{$var}'.", ABCMS_LOG_WARN, ABCMS_LOGTO_USER); unset($vars[$var]); // warn user, unset
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
	$storage = $this->rp(dirname(__DIR__)).ABCMS_EXT_PRIVATE.'ABCMS.settings.php';
	$this->compiles = array(); // initialize
	$this->compiles['core']['projectroot'] = $this->rp(dirname(__DIR__)); // projectroot, needed early for chk_file()
	$data = [];
	if ($boot && $this->get_dump($storage, $data)) {
		if (!is_array($data) || empty($data['core']['projectroot'])) { $this->response("SETUP: settings file corrupted, storage={$storage}", ABCMS_LOG_FATAL); }
		$this->settings = $data;
		$this->compiles = NULL;
		return;
	}
	// register core settings
	$this->response('SETUP: begin', ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
	$this->compiles['core']['filename']			= $this->rp(__FILE__); // my filename
	$this->compiles['core']['documentroot']		= $this->rp(__DIR__); // my documentroot
	$this->compiles['core']['project']			= (basename(dirname(__DIR__))); // my project name
	$this->compiles['core']['auto']				= $this->rp(realpath(__DIR__ . '/../vendor/autoload.php')); // auto-loader location
	$this->compiles['core']['getmyinode']		= getmyinode(); // my inode
	$this->compiles['core']['getlastmod']		= getlastmod(); // my modified date
	$password									= $this->get_uniq(); // my clear password
	$this->compiles['core']['passhash']			= password_hash($password, PASSWORD_DEFAULT); // my password hash
	$corefold = $this->compiles['core']['projectroot'].ABCMS_EXT_PRIVATE;
	$this->set_json($corefold.'ABCMS.deleteme', 'DELETE ASAP: '.$password); // temp password storage
	$password = NULL;
	$this->response('SETUP: superuser password written, retrieve and delete file', ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
	$this->compiles['core']['secret']			= $this->get_uniq(); // my hash secret
	if (!is_dir(($file = ($corefold.'ABCMS.sessions'))) && (!mkdir($file, 0755, true))) { $this->response("SETUP: session folder missing, file={$file}, ".$this->error_get_last(), ABCMS_LOG_FATAL); }
	$this->compiles['core']['session_folder']	= $file; // session folder
	$this->compiles['core']['session_cookie']	= $this->get_uniq(); // session cookie name
	$this->compiles['core']['session_secret']	= $this->get_uniq(); // session secret name
	$this->compiles['core']['session_logins']	= $this->get_uniq(); // login cookie name
	$this->compiles['core']['session_badact']	= $this->get_uniq(); // bad actor cookie name
	$this->compiles['core']['session_allows']	= $this->get_uniq(); // user allows cookie name
	$this->compiles['core']['session_killit']	= TRUE; // kill on close browser
	$this->compiles['core']['session_domain']	= NULL; // NULL || '' = host-only; or 'example.com' shared across subdomains
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
	// register variables
	// 'U' = URL variable
	// 'G' = $_GET variable
	// 'P' = $_POST variable
	$this->response('SETUP: core variables', ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
	$this->setup_variable('U', 'debug', 'bool', ABCMS_ROLE_ADMINS); // register URL PATH variables
	$this->setup_variable('U', 'abcms', 'bool', ABCMS_ROLE_ADMINS); // register URL PATH variables
	//$this->setup_variable('G', 'debug', 'bool', ABCMS_ROLE_ADMINS); // register URL PATH variables
	//$this->setup_variable('G', 'abcms', 'bool', ABCMS_ROLE_ADMINS); // register URL PATH variables
	//$this->setup_variable('G', 'debug', 'bool', ABCMS_ROLE_ADMINS); // register $_GET variables
	//$this->setup_variable('P', 'debug', 'bool', ABCMS_ROLE_ADMINS); // register $_POST variables
	// extension controls
	// 'I' = Input -OR- 'O' = Output filter, default Input
	// 'E' = Exclusive to my extension or omit me, default anyone
	// 'U' = Uno/single extension, default multiple extensions cooperate 
	// 'D' = Default included, default excluded if extended by $ord < 0
	// HTTP methods, '' = all = 'CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE'
	$this->response('SETUP: core extensions', ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
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
	$this->response('SETUP: contrib extensions', ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
	$exts = glob("{$this->compiles['core']['projectroot']}/private/*/*/");
	foreach ($exts?:[] as $fold) {
		// skip myself
		if (preg_match('|^'.preg_quote($this->compiles['core']['projectroot'],'|').ABCMS_EXT_PRIVATE.'$|uD', $fold)) { continue; }
		// valid extension name
		if (!preg_match('|^'.preg_quote($this->compiles['core']['projectroot'],'|').'/private'.ABCMS_REGEX_FOLD.'/$|uD', $fold, $match) || empty($match[1])) {
			$this->response("SETUP: extension name invalid, folder={$fold}", ABCMS_LOG_WARN, ABCMS_LOGTO_LOGS);
			continue;
		}
		// valid file
		$temp = $fold . 'SETUP.php';
		if (!is_file($temp)) {
			$this->response("SETUP: extension SETUP.php invalid, file={$temp}", ABCMS_LOG_WARN, ABCMS_LOGTO_LOGS);
			continue;
		}
		// reject symlinks
		if (($file = $this->rp(realpath($temp))) !== $this->rp($temp)) {
			$this->response("SETUP: extension symlink rejected, file={$temp}", ABCMS_LOG_WARN, ABCMS_LOGTO_LOGS);
			continue;
		}
		// push extension stackwho so s() returns valid $_SESSION storage
		$this->stackwho[] = $match[1];
		$mark = $this->response_splice();
		try {
			$this->include($file);
			$this->response("SETUP: extension setup ok, file={$file}", ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
		}
		// failed extension setup
		catch (Throwable $e) {
			$exception = ($e->getMessage() ?: 'Unknown extension SETUP.php exception.');
			$this->response("SETUP: extension setup failed, file={$file}, {$exception}", ABCMS_LOG_ERROR, ABCMS_LOGTO_LOGS);
		}
		// pop stackwho
		finally {
			array_pop($this->stackwho);
			$this->response_splice($mark); // splice off $this->respuser in finally so extension SETUP.php cannot message end users
		}
	}
	// TODO optimize and remove mixed non-exclusive and exclusive routes
	$this->response('SETUP: optimize settings', ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
	// load custom settings from var_dump file for speed, beware of injection
	$this->response('SETUP: custom overrides', ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
	if (function_exists('opcache_invalidate')) { opcache_invalidate($this->compiles['core']['override'], TRUE); } // clear php cache
	$override = [];
	// read override settings
	if (file_exists($this->compiles['core']['override'])) {
		if (!$this->get_dump($this->compiles['core']['override'], $override) || !is_array($override)) {
			$this->response("SETUP: override file unreadable or corrupted, file={$this->compiles['core']['override']}", ABCMS_LOG_FATAL);
		}
	}
	// build default override settings TODO fix once $this->settings array is segregated by extension
	else {
		$override['core']['session_killit']	= $this->compiles['core']['session_killit'];
		$override['core']['session_domain']	= $this->compiles['core']['session_domain'];
		$override['core']['smtp_host']		= $this->compiles['core']['smtp_host'];
		$override['core']['smtp_port']		= $this->compiles['core']['smtp_port'];
		$override['core']['smtp_name']		= $this->compiles['core']['smtp_name'];
		$override['core']['smtp_user']		= $this->compiles['core']['smtp_user'];
		$override['core']['smtp_pass']		= $this->compiles['core']['smtp_pass'];
		$override['core']['smtp_ehlo']		= $this->compiles['core']['smtp_ehlo'];
		$this->set_dump($this->compiles['core']['override'], $override);
	}
	$this->array_walk_merge($this->compiles, $override);
	// verify custom session_domain
	if (NULL === ($this->compiles['core']['session_domain']??NULL)) { $this->compiles['core']['session_domain'] = ''; }
	if (!is_string($this->compiles['core']['session_domain'])) { $this->response('SETUP: override session_domain not a string', ABCMS_LOG_FATAL); }
	$dom = $this->compiles['core']['session_domain'] = mb_strtolower(ltrim($this->compiles['core']['session_domain'],'.'), 'UTF-8');
	$host = mb_strtolower(parse_url('http://'.($_SERVER['HTTP_HOST']??''), PHP_URL_HOST)?:'', 'UTF-8');
	if ('' !== $dom && '' !== $host && $dom !== $host && !str_ends_with($host, '.'.$dom)) {
		$this->response("SETUP: override session_domain mismatch, domain={$dom} host={$host}", ABCMS_LOG_FATAL);
	}
	// __Host- prefix locks cookies to this host, browser rejects any subdomain attempt to set them
	if ('' === $dom) {
		foreach (array('session_cookie','session_secret','session_logins','session_badact','session_allows') as $name) {
			if (!str_starts_with($this->compiles['core'][$name], '__Host-')) { $this->compiles['core'][$name] = '__Host-'.$this->compiles['core'][$name]; }
		}
	}
	// save settings as fast op cachable php include file with atomic with rename(), beware of injection
	$this->response('SETUP: save settings', ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);
	$this->set_dump($storage, $this->compiles);
	if ($boot) { $this->settings = $this->compiles; }
	$this->compiles = NULL;
	// warning: op cache setting requires manual cache refresh
	if (function_exists('opcache_get_configuration') && !ini_get('opcache.validate_timestamps')) {
		$this->response('SETUP: opcache stale, reload php-fpm to apply settings', ABCMS_LOG_WARN, ABCMS_LOGTO_LOGS);
	}
	return;
}

public function setup_extend(		// register hook extension
string	$hok,						// /vendor/package/hook | TODO combine $hok & $ext ?
string	$ext,						// extension or '' for all
string	$met,						// HTTP methods, '' = all = 'CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE' | TODO make $met and $str similar structure?
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
	$a = $b = $c = $d = $e = $f = $g = 0;
	if (($a=(!is_array($this->compiles))) || // bad context
		($b=(!preg_match(ABCMS_REGEX_HOOK, $hok))) || // hook
		($c=('' !== $ext && !preg_match(ABCMS_REGEX_NICK, $ext))) || // extension
		($d=(!empty($met) && array_diff(explode('-', $met), array('CLI','GET','POST','PUT','HEAD','DELETE','PATCH','OPTIONS','CONNECT','TRACE')))) || // method
		($e=(isset($ctl['I']) && isset($ctl['O']))) || // input or output
		($f=(!empty($key))) || // control
		($g=(!empty($fun) && !preg_match(ABCMS_REGEX_FUNC, $fun)))) { // function
		$this->response("SETUP: setup_extend invalid, hok={$hok} ext={$ext} fun={$fun}, bad={$a} hok={$b} ext={$c} met={$d} exc={$e} con={$f} fun={$g}", ABCMS_LOG_ERROR, ABCMS_LOGTO_LOGS);
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
	$a = $b = $c = $d = $e = $f = 0;
	if (($a=(!is_array($this->compiles))) || // bad context
		($b=(!preg_match(ABCMS_REGEX_HOOK, $hok))) || // hook
		($c=('' !== $ext && !preg_match(ABCMS_REGEX_NICK, $ext))) || // extension
		($d=(substr_count($pat, '/')>2 && str_ends_with($pat, '/'))) || // trailing slash matches 1st path segment only
		($e=('' !== $pat && (!str_starts_with($pat, '/') || FALSE === filter_var('http://localhost'.$pat, FILTER_VALIDATE_URL)))) || // path
		($f=isset($this->compiles['route'][$hok]['eq'][$pat]))) { // duplicate
		$this->response("SETUP: setup_equate invalid, hok={$hok} ext={$ext} pat={$pat}, bad={$a} hok={$b} ext={$c} p//={$d} pat={$e} dup={$f}", ABCMS_LOG_ERROR, ABCMS_LOGTO_LOGS);
		return FALSE;
	}
	// assign equate path
	$this->compiles['route'][$hok]['eq'][$pat] = $ext;
	return TRUE;
}

private function setup_variable(// register variable
string	$cat,					// category 'U' = URL variable,'G' = $_GET variable,'P' = $_POST variable
string	$var,					// variable
string	$typ,					// type
int		$rol,					// min role
?array	$reg = NULL,			// regex validation
) : bool {						// return success or failure
	// validate
	$a = $b = $c = $d = $e = $f = 0;
	if (($a=(!is_array($this->compiles))) || // bad context
		($b=(!in_array($cat, array('U','G','P')))) || // category
		($c=(!preg_match('/^[a-z0-9\-_.~]+$/uiD', $var))) || // variable
		($d=(!in_array($typ, array('array','bool','boolean','domain','email','explode','float','integer','ip','mac','mixed','path','string','uri','url','uuid')))) || // type
		($e=(!in_array($rol, ABCMS_ROLE_SET))) || // role
		($f=(!empty($this->compiles[$cat][$var])))) { // duplicate
		$this->response("SETUP: setup_variable invalid, cat={$cat} var={$var} typ={$typ}, bad={$a} cat={$b} var={$c} typ={$d} rol={$e} dup={$f}", ABCMS_LOG_ERROR, ABCMS_LOGTO_LOGS);
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
	$slap = FALSE;
	static $now = NULL;
	static $posthandled = FALSE; // post already handled
	static $deny = FALSE; // deny further session whether bad actor or failed session_destroy()
	static $options = NULL;
	if (NULL === $options) {
		$now = $this->boots['time'];
		$options = [
			'save_path'			=> $this->settings['core']['session_folder'],		// or .htaccess: php_value session.save_path '/path'
			'name'				=> $this->settings['core']['session_cookie'],		// custom name
			'save_handler'		=> 'files',											// session files
			'gc_probability'	=> '1',												// garbage collection, turn off and replace with cron!
			'gc_divisor'		=> '100',											// garbage collection, turn off and replace with cron!
			'gc_maxlifetime'	=> ABCMS_SES_LIFE,									// garbage collection, turn off and replace with cron!
			'cookie_lifetime'	=> ($this->settings['core']['session_killit'] ? 0 : ABCMS_SES_LIFE), // cookie lifetime, kill when close browser
			'cookie_path'		=> '/',												// whole domain
			'cookie_domain'		=> $this->settings['core']['session_domain']?:'',	// '' = host-only; or 'example.com' shared across subdomains
			'cookie_secure'		=> '1',												// HTTPS only
			'cookie_httponly'	=> '1',												// No JS
			'cookie_samesite'	=> 'Strict',										// No cross-site
			'use_strict_mode'	=> '1',												// Reject unknown SIDs
			'use_cookies'		=> '1',												// No SID in URL
			'use_only_cookies'	=> '1',												// No SID in URL
			'use_trans_sid'		=> '0',												// Disable URL rewriting
			];
	}
	// early exit
	if ($deny || isset($_COOKIE[$this->settings['core']['session_badact']])) { if (!($deny)) { $this->response('Access is temporarily blocked. Please try again later.', ABCMS_LOG_WARN, ABCMS_LOGTO_USER); } $deny = TRUE; return FALSE; } // bad actor
	if ($cmd < 0) { $error = 'You are logged out.'; goto KILL; } // destroy session
	if ($active) { if (0 === $cmd) { $this->response('SESSION: unauthorized start encountered', ABCMS_LOG_FATAL); } return TRUE; } // already started, but ABCMS must start
	if (headers_sent()) { $this->response('SESSION: start failed, headers already sent', ABCMS_LOG_FATAL); } // already headers
	if (!isset($_COOKIE[$this->settings['core']['session_allows']])) { $this->set_cookie($this->settings['core']['session_allows'], ABCMS_COOK_NAVS, $now + ABCMS_COOK_LIFE, FALSE); }	// TODO TEMP CODE TO ALLOW COOKIES
	if (empty($_COOKIE[$this->settings['core']['session_allows']])) {	$this->response('Cookies must be accepted before you can submit forms or login.', ABCMS_LOG_WARN, ABCMS_LOGTO_USER); return FALSE; } // cookies not approved
	$post = ('POST' === $this->boots['urlmethod'] && !$posthandled ? TRUE : FALSE); // is this a POST?
	if (0 === $cmd && !isset($_COOKIE[$this->settings['core']['session_logins']]) && !$post) { return FALSE; } // conditional start
	// start session, more variables
	if (!session_start($options) || !($_COOKIE[$options['name']] = session_id())) { $this->response('SESSION: start failed, '.$this->error_get_last(), ABCMS_LOG_FATAL); }
	$active = $posthandled = TRUE;
	$error = $gauntlet = NULL;
	$csrf = ($post && !empty($_POST['csrf']) ? $_POST['csrf'] : '');
	if (empty($_SESSION[ABCMS_EXT_SELF]['create'])) { $this->ss = []; } else { $this->ss = &$_SESSION[ABCMS_EXT_SELF]; }
	// validate session
	if (!$this->ss) {
		// cannot POST without session
		if ($post) {																									$error = 'SESSION: ended, reason=post-without-session';		$slap = TRUE; }
	}
	else {
		// hit counter
		$gothits = FALSE; $this->ss['counts'][] = $now; if (count($this->ss['counts']) > ABCMS_SES_HITS) { array_shift($this->ss['counts']); $gothits = TRUE; }
		// uagent inconsistent
		if ($this->ss['uagent'] !== $this->boots['uagent']) {															$error = 'SESSION: ended, reason=agent-mismatch';			$slap = TRUE; }
		// secrets differ
		else if (!hash_equals($this->ss['secret'], ($_COOKIE[$this->settings['core']['session_secret']]??'x'))) {		$error = 'SESSION: ended, reason=secret-mismatch';			$slap = TRUE; }
		// rapid hits
		else if ($gothits && $this->ss['counts'][ABCMS_SES_HITS-1] - $this->ss['counts'][0] < ABCMS_SES_TIME) {			$error = 'SESSION: ended, reason=rapid-hits';				$slap = TRUE; }
		// POST CSRF1
		else if ($post && (!$csrf || !hash_equals($this->ss['csrf_valu'], $csrf))) {									$error = 'SESSION: ended, reason=csrf-missing';				$slap = TRUE; }
		// POST CSRF2
		else if ($csrf && !hash_equals($this->ss['csrf_valu'], (($_POST[$this->ss['csrf_name']]??'x')?:'x'))) {			$error = 'SESSION: ended, reason=csrf-mismatch';			$slap = TRUE; }
		// POST !HONEY populated
		else if ($csrf && !empty($_POST[$this->ss['void_name']])) {														$error = 'SESSION: ended, reason=reverse-honeypot-filled';	$slap = TRUE; }
		// POST HONEY differs
		else if ($csrf && !hash_equals($this->ss['full_valu'], (($_POST[$this->ss['full_name']]??'x')?:'x'))) {			$error = 'SESSION: ended, reason=honeypot-mismatched';		$slap = TRUE; }
		// POST rapid
		else if ($csrf && ($now - $this->ss['active']) < ABCMS_SES_WAIT) {												$error = 'SESSION: ended, reason=rapid-submit';				$slap = TRUE; }
		// fail resume login, cookies or session expired, always reload user to confirm permissions
		else if (isset($_COOKIE[$this->settings['core']['session_logins']]) &&
			(($_COOKIE[$this->settings['core']['session_logins']]?:'x') !== $this->ss['logins'] || empty($this->ss['user']) ||
			!($this->ss['user'] = $this->get_database('BASIC.json', array('user',$this->ss['user']['email']))))) {		$error = 'Your login could not be resumed. Please log in again.'; }
		// login expired
		else if (!isset($_COOKIE[$this->settings['core']['session_logins']]) && !empty($this->ss['user'])) {			$error = 'Your login expired. Please log in again.'; }
		// idle time exceeded
		else if ($now > ($this->ss['active'] + ABCMS_SES_IDLE)) {														$error = 'Your session ended after inactivity. Please log in again.'; }
		// time exceeded
		else if ($now > ($this->ss['create'] + ABCMS_SES_LIFE)) {														$error = 'Your session reached its time limit. Please log in again.'; }
		// POST image mismatch
		else if ($csrf && empty($this->ss['user']) && ($this->ss['test_valu'] !== (($_POST[$this->ss['test_name']]??'x')?:'x'))) {
			$this->response('That CAPTCHA answer was not correct. Please try again.', ABCMS_LOG_WARN, ABCMS_LOGTO_USER);
		}
		// Passed gauntlet so maybe human
		else {																											$gauntlet = TRUE; }
	}
	// destroy by request or for corruption
	if ($error) {
KILL:	// start session to destroy it, weird
		if (!$active) { $active = session_start($options); }
		// remove cookies
		$this->set_cookie($options['name'], '', 1); // session
		$this->set_cookie($this->settings['core']['session_secret'], '', 1); // secret
		$this->set_cookie($this->settings['core']['session_logins'], '', 1); // login
		// PHP says mark for garbage collection, but I don't want garbage laying around
		$_SESSION = $this->ss = []; // access directly exception to clear entire session
		if ($active && !session_destroy()) { $deny = TRUE; $this->response('SESSION: destroy failed, '.$this->error_get_last(), ABCMS_LOG_WARN, ABCMS_LOGTO_LOGS);	}
		// slap all evil with same 429 + bad actor cookie
		if ($slap) {
			$deny = TRUE;
			$this->set_cookie($this->settings['core']['session_badact'], $this->get_uniq(), $now + ABCMS_SES_BADA, FALSE);
			http_response_code(429);
			header('Retry-After: ' . ABCMS_SES_BADA);
			$this->response($error, ABCMS_LOG_FATAL, ABCMS_LOGTO_BOTH, 429); // TODO beware log flooding, might change to ABCMS_LOGTO_USER
		}
		else {
			$this->response($error, ABCMS_LOG_WARN, ABCMS_LOGTO_USER); // dont log rifraf
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
			if (!session_regenerate_id(TRUE) || !($_COOKIE[$options['name']] = session_id())) { $this->response('SESSION: regenerate failed, '.$this->error_get_last(), ABCMS_LOG_FATAL); }
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
		if (($bad = (session_status() !== PHP_SESSION_ACTIVE))) { $this->response('SESSION: access without existing session', ABCMS_LOG_FATAL); }
		if (!isset($_SESSION[$ext])) { $_SESSION[$ext] = []; } // assignment expected
	}
	if ((!$bad || (session_status() === PHP_SESSION_ACTIVE)) && isset($_SESSION[$ext])) {
		if (!is_array($_SESSION[$ext])) { $this->response("SESSION: extension storage corrupted, ext={$ext}", ABCMS_LOG_FATAL); }
		return $_SESSION[$ext]; // return extension element
	}
	$empty = []; return $empty; // return fail-safe emptiness
}

public function set_cookie(	// set cookie
string	$cookie,			// name
string	$value,				// value
int		$expires,			// expiration
bool	$killit = TRUE,		// kill heed
) : void {					// return void or WSOD
	// headers sent error and kill cookie on close browser
	if (headers_sent()) { $this->response('COOKIE: set failed, headers already sent', ABCMS_LOG_FATAL); }
	if ($killit && $expires > 1 && $this->settings['core']['session_killit']) { $expires = 0; }
	// set cookie
	if (!empty($cookie) && setcookie(
		$cookie,
		$value,
		[
			'expires'	=> $expires,										// expiration
			'path'		=> '/',												// entire website
			'domain'	=> $this->settings['core']['session_domain']?:'',	// '' = host-only; or 'example.com' shared across subdomains
			'secure'	=> TRUE,											// only HTTPS
			'httponly'	=> TRUE,											// no js prevents XSS
			'samesite'	=> 'Strict',										// avoid CSRF attacks
		])) {
		if ($expires && $expires < $this->boots['time']) {	unset($_COOKIE[$cookie]); } // expire unset
		else { $_COOKIE[$cookie] = $value; } // set for remainder
		return;
	}
	// failed so unset
	unset($_COOKIE[$cookie]);
	$this->response('COOKIE: set failed', ABCMS_LOG_FATAL);
	return;
}







/*************************************************************************************************
SECTION DATABASE: Store data in VAR_DUMP, JSON, CSV, SQLite, and MySQL.
*/

public function new_database(	// create new database
string $file,					// filename within extension
) : void {						// return void or WSOD
	if (!preg_match(ABCMS_REGEX_DATA, $file)) { $this->response("DATABASE: new name invalid, file={$file}", ABCMS_LOG_FATAL); } // invalid file
	$ext = $this->output_extension();
	$fold = ($this->compiles['core']['projectroot']??$this->settings['core']['projectroot'])."/private{$ext}/ABCMS.database";
	if (!is_dir($fold) && !mkdir($fold, 0750, true)) { $this->response("DATABASE: folder create failed, folder={$fold}, ".$this->error_get_last(), ABCMS_LOG_FATAL); }
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
	if (!preg_match(ABCMS_REGEX_DATA, $file)) { $this->response("DATABASE: set name invalid, file={$file}", ABCMS_LOG_FATAL); } // invalid file
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
	if (!($lockfd = fopen($base.'.lock', 'r+')) || !flock($lockfd, LOCK_EX)) { // assume filesystem support
		if ($lockfd) { fclose($lockfd); }
		$this->response("DATABASE: set exclusive lock failed, base={$base}, ".$this->error_get_last(), ABCMS_LOG_FATAL);
	}
	// read
	if (FALSE === ($raw = file_get_contents($base))) {
		flock($lockfd, LOCK_UN); fclose($lockfd);
		$this->response("DATABASE: set read failed, base={$base}, ".$this->error_get_last(), ABCMS_LOG_FATAL);
	}
	else if ('' === $raw) {
		$this->database[$file] = [];
	}
	else if (!is_array($raw = json_decode($raw, TRUE))) {
		flock($lockfd, LOCK_UN); fclose($lockfd);
		$this->response("DATABASE: set json corrupted, base={$base}, ".$this->error_get_last(), ABCMS_LOG_FATAL);
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
	if (!preg_match(ABCMS_REGEX_DATA, $file)) { $this->response("DATABASE: get name invalid, file={$file}", ABCMS_LOG_FATAL); } // invalid file
	// cached or not cached
	$file = $this->output_extension().'/ABCMS.database/'.$file;
	if (!isset($this->database[$file])) {
		// shared lock
		$base = ($this->compiles['core']['projectroot']??$this->settings['core']['projectroot']).'/private'.$file;
		if (!($lockfd = fopen($base.'.lock', 'r')) || !flock($lockfd, LOCK_SH)) { // assume filesystem support
			if ($lockfd) { fclose($lockfd); }
			$this->response("DATABASE: get shared lock failed, base={$base}, ".$this->error_get_last(), ABCMS_LOG_FATAL);
		}
		// read
		if (FALSE === ($raw = file_get_contents($base))) {
			flock($lockfd, LOCK_UN); fclose($lockfd);
			$this->response("DATABASE: get read failed, base={$base}, ".$this->error_get_last(), ABCMS_LOG_FATAL);
		}
		else if ('' === $raw) {
			$this->database[$file] = [];
		}
		else if (!is_array($raw = json_decode($raw, TRUE))) {
			flock($lockfd, LOCK_UN); fclose($lockfd);
			$this->response("DATABASE: get json corrupted, base={$base}, ".$this->error_get_last(), ABCMS_LOG_FATAL);
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
string	$meth,			// HTTP methods, '' = ALL = 'CLI-GET-POST-PUT-HEAD-DELETE-PATCH-OPTIONS-CONNECT-TRACE'
string	$default,		// default function, '' = no default
int		$role,			// minimum role permissions
int		$flag,			// <0 = extender exclusive, 0 = anyone, 1 = extender exclusive allowed
bool	$must,			// must do default, TRUE = required -OR- FALSE = optional
mixed	&...$args,		// default arguments
) : array {				// return input $args
	// initialize stack and variables
	$pushed = FALSE; if (empty($this->stackwho)) { $this->stackwho[] = ABCMS_EXT_SELF; $pushed = TRUE; } try {
	$whoami = $this->output_extension(); // which extension?
	$hook = $whoami . $hook; // Full hook name
	$ext = array( // Default
		'I' => (empty($default) ? array() : array( array(	// empty default allowed
				'met'	=> $meth,							// HTTP methods
				'fun'	=> $default,						// function
				'rol'	=> $role,							// role
				'ord'	=> 0,								// order
				'ctl'	=> NULL,							// control
				'who'	=> $whoami,							// default for each caller
				'arg'	=> NULL,							// none
		))),
		'O' => array(),										// no default output filter
	);
	// prioritize
	if (isset($this->settings['route'][$hook])) {			// build hook extensions
		$hooky = $this->settings['route'][$hook];			// shortened reference
		$ext = array_merge_recursive(						// merge extensions with matches
			$ext,											// Default
			(!empty($hooky['eq'][$this->boots['urlpathall']]) &&
			 !empty($hooky['ex'][$hooky['eq'][$this->boots['urlpathall']]]) ?
			 $hooky['ex'][$hooky['eq'][$this->boots['urlpathall']]] : // full path
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
	// execute
	$exin = $exout = NULL; // exclusive winner or non-exclusive
	$dopt = TRUE; // default optional
	foreach($ext['I'] as $extin) { // input extensions by priority
		if (!$this->output_doit($extin, $whoami, $flag, ($must || $dopt), $exin)) { continue; } // skip for reasons
		if (!$must && $extin['ord'] < 0 && !isset($extin['ctl']['D'])) { $dopt = FALSE; } // omit default if hook and one extension says not required
		if ($this->input['role'] >= ABCMS_ROLE_ADMINS) { $this->stackarg[] = func_get_args(); } // log the extension stack for administrator
		if (isset($extin['arg'])) { $this->array_walk_merge($args, $extin['arg']); } // extend arguments
		if (empty($extin['fun'])) { continue; } // extension only grabs exclusivity or set args
		// loop only applies to registered extension functions, internal extension dispatch is its own business
		do { // repeat hook extension until FALSE -OR- NULL || TODO invert the continue test
			if (FALSE === ob_start()) { $this->response('OUTPUT: buffer start failed, '.$this->error_get_last(), ABCMS_LOG_FATAL); } // buffer output, TODO optionally stream or file output
			$more = $this->output_call($extin['who'], $extin['fun'], ...$args); // execute hook extension
			if (FALSE === ($out = ob_get_clean())) { $this->response('OUTPUT: buffer get clean failed, '.$this->error_get_last(), ABCMS_LOG_FATAL); } // retrieve buffer, TODO optionally stream or file output
			// output filter extensions by priority
			foreach($ext['O'] as $extout) {
				if (!$this->output_doit($extout, $whoami, $flag, TRUE, $exout)) { continue; } // skip for reasons
				$this->output_call($extout['who'], $extout['fun'], $out, ...$args); // execute output filter
			}
			// ABCMS security output filter and injection, <FORM> security, and XSS checks, etc. TODO replace with function and switch on output type
			if (ABCMS_EXT_INITX == $hook) {
				$this->output_security($out);	// inject security
				$this->output_debug($out);	// debug output
			}
			echo $out; // echo compiled output, TODO optionally stream or file output
		} while ($more); // repeat hook extension until FALSE || TODO invert the test
		if (isset($extin['ctl']['U'])) { break; } // uno extension allowed
	}
	// stack pop and return $arguments
	} finally { if ($pushed) { array_pop($this->stackwho); } }
	return $args;
}

private function output_extension() : string { // return callers extension name
	if (empty($this->stackwho)) { $this->response('DISPATCH: extension stack identity missing', ABCMS_LOG_FATAL); }
	return end($this->stackwho);
}

private function output_doit(	// shall we execute hook extension?
array	$ext,					// extension definition
string	$whoami,				// is this extender allowed
int		$flag,					// <0 = extender exclusive, 0 = anyone, 1 = extender exclusive allowed
bool	$must,					// must also do default
?string	&$excl,					// exclusive extension winner
) : bool {						// return TRUE or FALSE
	// exit before exclusive selection
	if (!$must && !$ext['ord']) {																				return FALSE; }	// no default extension
	if (!empty($ext['met']) && FALSE === stripos($ext['met'], $this->boots['urlmethod'])) { 					return FALSE; }	// HTTP method | TODO consider this instead in_array($method, explode('-', $met)
	if ($flag < 0 && $whoami !== $ext['who']) {
		$this->response("DISPATCH: extender not self, whoami={$whoami} who={$ext['who']}", ABCMS_LOG_INFO, ABCMS_LOGTO_LOGS);	return FALSE; }	// extender no match
	if (!$flag && isset($ext['ctl']['E'])) {																	return FALSE; }	// non-exclusive, cancel request
	// exclusive winner or non-exclusive
	if ($flag > 0) {
		if (NULL === $excl) { $excl = (isset($ext['ctl']['E']) ? $ext['who'] : FALSE); }
		if (!$excl && isset($ext['ctl']['E'])) {																return FALSE; }	// non-exclusive, cancel request
		if ($excl && $ext['who'] !== $excl) {																	return FALSE; }	// exclusive, but not winner
	}
	if ($this->input['role'] < $ext['rol']) { $this->response('You do not have permission to a requested resource.', ABCMS_LOG_WARN, ABCMS_LOGTO_USER); return FALSE; }	// No permission, TODO might change to ABCMS_LOGTO_BOTH
	// do it
	return TRUE;
}

private function output_call(	// call extension function
string	$who,					// extension stack
string	$filefunc,				// includefile?function
mixed	&...$args,				// arguments passed
) : ?bool {						// return function result
	// parse includefile?function
	if (!preg_match(ABCMS_REGEX_FUNC, $filefunc, $match)) { $this->response("DISPATCH: invalid function name, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
	$filepath	= $match[2]; // extension include file
	$classobject= $match[5]; // class or object
	$operator	= $match[6]; // operator to function
	$funcmeth	= $match[7]; // function / method
	// initialize
	$result = FALSE; // default is failure
	$this->stackwho[] = $who; try { // push who stack
	// include
	if ($filepath) {
		$filepath = $this->settings['core']['projectroot'].'/private'.$who.$filepath; // resolved filename
		if ($funcmeth) {	$result = (bool)$this->include_once($filepath, ...$args); } // failsafe include once for definition
		else {				$result = (bool)$this->include($filepath, ...$args); } // or multiple executions allowed
	}
	// call function
	if ($funcmeth) { // function attempt
		if ($classobject) { // class or object method
			if ('::' === $operator) { // class operator
				if (!class_exists($classobject) || !method_exists($classobject, $funcmeth)) { $this->response("DISPATCH: invalid class method, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
				$result = (bool)$classobject::$funcmeth(...$args); // Execute
			}
			else { // non-class methods
				if ('->' === $operator) { // instance or object operator
					if (!isset($GLOBALS[$classobject]) || !is_object($GLOBALS[$classobject])) { $this->response("DISPATCH: invalid object, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
					$newobject = $GLOBALS[$classobject];
				}
				else if ('()->' === $operator) { // function returned object operator
					if (!function_exists($classobject)) { $this->response("DISPATCH: invalid function to object, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
					if (!is_object(($newobject = $classobject()))) { $this->response("DISPATCH: invalid function object, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
				}
				else { $this->response("DISPATCH: invalid operator, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
				// execute function/method
				if (!method_exists($newobject, $funcmeth)) { $this->response("DISPATCH: invalid object method, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
				if (ABCMS_EXT_SELF != $who && $newobject === $this) { // disallow abcms() privates unless extension is ABCMS
					$reflection = new ReflectionClass($this);
					if (!$reflection->getMethod($funcmeth)->isPublic()) { $this->response("DISPATCH: private method disallowed, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
				}
				$result = (bool)$newobject->$funcmeth(...$args); // execute
			}
		}
		else {
			if (!function_exists($funcmeth)) { $this->response("DISPATCH: invalid function, who={$who} func={$filefunc}", ABCMS_LOG_FATAL); }
			$result = (bool)$funcmeth(...$args); // execute
		}
	}
	// pop who stack and return result
	} finally { array_pop($this->stackwho); }
	return $result;
}

private function output_security(	// inject html form security with fast regex instead of DOM 
string &$html,						// inject into output HTML || TODO not all output is HTML
) : void {							// return void
	// failure or no form so skip
	if (FALSE === ($num = preg_match_all(ABCMS_REGEX_FORM, $html))) { $this->response('FORM: security init failed', ABCMS_LOG_FATAL); }
	if (!$num) { return; }
	// start session
	if (!$this->session_start(1)) {
		// session failed, disable forms with <fieldset> and CSS with missing CSRF as safety net
		$this->response('FORM: security failed, forms disabled', ABCMS_LOG_ERROR, ABCMS_LOGTO_LOGS);
		$this->response('Forms are temporarily disabled. Please try again later.', ABCMS_LOG_ERROR, ABCMS_LOGTO_USER);
		if (!($html = preg_replace(ABCMS_REGEX_FORM, '$1<fieldset disabled class="disable">$2</fieldset>$3', $html, -1, $count)) || $count !== $num) {
			$this->response('FORM: security fallback failed', ABCMS_LOG_FATAL);
		}
		$regex_safe = str_replace(['\\', '$'], ['\\\\', '\\$'], $this->input['nonce']);
		if (!($html = preg_replace('/<\/head>/ui', "\n<style nonce='{$regex_safe}'>form { pointer-events: none; opacity: 0.5; }\n</style>\n</head>", $html, 1, $count)) || 1 !== $count) {
			$this->response('FORM: security css fallback failed', ABCMS_LOG_ERROR, ABCMS_LOGTO_LOGS);
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
		$this->response('FORM: javascript injection failed', ABCMS_LOG_FATAL);
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
				$this->response('FORM: captcha injection failed', ABCMS_LOG_FATAL);
			}
			// security tokens injection
			$replace .= $inject_tokens.$matches[3];
			return $replace;
		},
		$html, -1, $count)) || $count !== $num) {
		$this->response('FORM: token injection failed', ABCMS_LOG_FATAL);
	}
	return;
}

private function output_debug(	// inject debug info for admin
string &$html,					// inject into HTML output string
) : void {						// return void
	if (!$html || $this->input['role'] !== ABCMS_ROLE_ADMINS) { return; }
	$injection = '<pre class="debug"><h2>Coredump</h2>'.print_r(array('ABCMS_OBJECT'=>$this, 'ABCMS_GLOBALS'=>$GLOBALS),TRUE).'</pre></body>';
	$injection = str_replace(['\\', '$'], ['\\\\', '\\$'], $injection);
	if (!($html = preg_replace('/<\/body>/ui', $injection, $html, 1))) { $this->response('OUTPUT: admin coredump injection failed', ABCMS_LOG_FATAL); }
	return;
}







/*************************************************************************************************
SECTION RESPONSE: Return request response.
*/

public function response(			// request response
string	$mess,						// message
int		$levs,						// level is ABCMS_LOG_(DEBUG||TRACE||INFO||WARN||ERROR||FATAL)
int		$goto = ABCMS_LOGTO_LOGS,	// ABCMS_LOGTO_(LOGS||USER||BOTH)
int		$code = 200,				// log the http request code returned
) : void {
	// fix levs and goto
	if (ABCMS_LOG_DEBUG === $levs) {	if (!($this->input['urlvars']['debug']??FALSE)) { return; } $goto = ABCMS_LOGTO_LOGS; } // debug early exit or log only
	else if (ABCMS_LOG_TRACE === $levs) {	$goto = ABCMS_LOGTO_LOGS; } // log only
	else if (ABCMS_LOG_FATAL === $levs) {	if (ABCMS_LOGTO_USER !== $goto) { $goto = ABCMS_LOGTO_BOTH; } } // both, role filtered later
	else if (!isset(ABCMS_LOG[$levs])) {	$levs = ABCMS_LOG_FATAL; $goto = ABCMS_LOGTO_BOTH; } // bad level fatal
	else if (!isset(ABCMS_LOGTO[$goto])) {	$goto = ABCMS_LOGTO_LOGS; } // bad goto, log only
	// logs entry
	if (ABCMS_LOGTO_BOTH === $goto || ABCMS_LOGTO_LOGS === $goto) {
		static $rids = NULL;
		static $nums = 1;
		if (NULL === $rids) { $rids = $this->get_dbid(); } else { ++$nums; }
		[$file, $line, $func, $args] = $this->response_trace();
		$entry = [
			'@timestamp'				=> gmdate('Y-m-d\TH:i:s.000\Z', ($this->boots['time']??time())),
			'event.sequence'			=> $nums,
			'log.level'					=> ABCMS_LOG[$levs].'-'.ABCMS_LOGTO[$goto],
			'message'					=> mb_substr($mess, 0, 1024, 'UTF-8'), // json escapes
			'ext'						=> (empty($this->stackwho) ? 'unknown' : end($this->stackwho)), // prevents recursion loop
			'log.origin.function'		=> $func,
			'log.origin.file.name'		=> basename(dirname($file)).'/'.basename($file),
			'log.origin.file.line'		=> $line,
			'user.roles'				=> ($this->input['role']??'unknown'),
			'event.severity'			=> $levs,
			'trace.id'					=> $rids,
			'service.name'				=> ($this->settings['core']['project']??'abcms'),
			'url.domain'				=> ($this->boots['urldomain']??'unknown'),
			'url.path'					=> ($this->boots['urlpathall']??'unknown'),
			'http.request.method'		=> ($this->boots['urlmethod']??'unknown'),
			'client.ip'					=> ($this->boots['ip']??'unknown'),
			'http.response.status_code'	=> $code,
		];
		$this->resplogs[] = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
	}
	// stop message leaks, user entry not escaped, throw entry
	if (ABCMS_LOG_FATAL === $levs) {
		if (!$this->iamsuper()) { $mess = 'Fatal exception, details logged.'; }
		if (!isset($this->input)) { $this->response_flush(); } // boot fatal, finally cannot flush
	}
	if (ABCMS_LOGTO_BOTH === $goto || ABCMS_LOGTO_USER === $goto) { $this->respuser[] = ['level' => ABCMS_LOG[$levs], 'message' => $mess]; }
	if (ABCMS_LOG_FATAL === $levs) { throw new Exception($mess); }
	return;
}

public function response_flush() : void { // write response logs
	if ($this->resplogs??NULL) {
		$file = ($this->settings['core']['translog'] ?? str_replace('\\', '/', __DIR__).'/..'.ABCMS_EXT_PRIVATE.'ABCMS.translog');
		if (FALSE === file_put_contents($file, implode("\n", $this->resplogs)."\n", FILE_APPEND | LOCK_EX)) {
			error_log(print_r($this->resplogs,TRUE)); // fallback
		}
		$this->resplogs = [];
	}
	return;
}

public function response_plain() : string { // return formatted log
	return implode("\n", array_map(function($row) { return implode(': ', $row); }, $this->respuser));
}

public function response_html() : string { // return formatted log
	return implode("<br>\n", array_map(function($row) { return $this->hsc(implode(': ', $row)); }, $this->respuser));
}

public function response_splice(?int $mark = NULL) : int|array { // first mark $this->respuser, then splice off additional and return them
	if (NULL === $mark) { return count($this->respuser); }
	else if (($splice = ($mark - count($this->respuser))) < 0) { return array_splice($this->respuser, $splice); }
	return [];
}

private function response_trace(	// get backtrace info
bool	$fast = TRUE,			// omit object and args
) : array {						// return info
	// 3 levels back
	$back = debug_backtrace(($fast ? DEBUG_BACKTRACE_IGNORE_ARGS : 0), 3);
	$file = ($back[1]['file']		?? 'unknown');
	$line = ($back[1]['line']		?? 0);
	$func = ($back[2]['function']	?? 'unknown');
	// arguments?
	if ($fast || empty($back[2]['args'])) { $args = array('unknown'); }
	else {
		$args = $back[2]['args'];
		array_walk_recursive($args, function (&$value) {
			if (is_string($value) && mb_strlen($value, 'UTF-8') > 256) {
				$value = mb_substr($value, 0, 256, 'UTF-8') . '...';
			}
		});
	}
	// return
	return [$file, $line, $func, $args];
}

public function error_get_last() : string { // return last error message
	$error = error_get_last();
	return $error['message']??'';
}







/*************************************************************************************************
SECTION HOME: Core extension /home/*
*/

private function home_theme(mixed &...$unused) : ?bool { // default home theme
	$footer = <<<EOF
<a href='/'>Home</a>
 / <a href='/account'>Account</a>
 / <a href='/contact'>Contact</a>
EOF
. ($this->input['role'] < ABCMS_ROLE_ADMINS ? NULL : ' / <a href="/console">Console</a>');
// TODO, the above line is wrong when first logging in because don't know I am authenticated till later...
// TODO where do I inject user error results into HTML display?
// how can I do that without putting the authentication into session_start()????
	$this->theme( // theme
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
	return NULL;
}

private function home_router(mixed &...$unused) : ?bool { // home router
	// internal extension dispatch bypasses core routing for speed
	switch ($this->boots['urlpathall']) {
		case '/':			$this->home();			break;
		case '/contact':	$this->home_contact();	break;
		case '/account':	$this->home_account();	break;
		default:			$this->home_notfound();	break;
	}
	return NULL;
}

private function home(mixed &...$unused) : void { // home homepage
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
	return;
}

public function home_account(mixed &...$unused) : void { // home register, login, logout, update, delete
	// initialize
	echo '<h2>Account</h2>';
	$switch =
		(!$this->session_start(1) ? 'nosession' :
		('POST' !== $this->boots['urlmethod'] ? 'form' :
		(!$this->formvalid ? 'invalid' :
		(!$this->formhuman ? 'inhuman' :
		(!empty($_POST['clicked']) ? $_POST['clicked'] : 'unknown')))));
	$mess = $email = $email2 = $subject = $body = NULL;
	// switch
	switch ($switch) {
		case 'nosession':	$this->response('ACCOUNT: login unavailable, session start failed', ABCMS_LOG_ERROR, ABCMS_LOGTO_LOGS);
							$this->response('The login system is unavailable. Please try again later.', ABCMS_LOG_ERROR, ABCMS_LOGTO_USER); return;
		case 'invalid':		$mess = 'That form submission looked suspect. Please try again.'; break;
		case 'inhuman':		$mess = 'CAPTCHA or form security check failed. Please try again.'; break;
		case 'login':		if (!empty($_POST['Account_Email']) && !empty($_POST['Account_Email2']) &&
								password_verify($_POST['Account_Password'], $this->settings['core']['passhash']) &&
								($this->ss['user'] = $this->get_database('BASIC.json', array('user', $_POST['Account_Email'])))) {
								$this->ss['trys'] = 0;
								$this->ss['logins'] = $this->get_uniq();
								$this->set_cookie($this->settings['core']['session_logins'], $this->ss['logins'], $this->ss['create'] + ABCMS_SES_LIFE);
								$mess = 'Login successful.';
								$email = $this->hsc($_POST['Account_Email']);
								$email2 = $this->hsc($_POST['Account_Email2']);
								$subject = "ABCMS Login Success by {$_POST['Account_Email']}";
								$body = '<h4>Hello</h4>You are logged into ' . $this->boots['urldomain'];
							}
							else if (++$this->ss['trys'] > ABCMS_SES_LOGI) {
								$this->session_start(-1);
								$this->response('Too many failed login attempts. Please try again later.', ABCMS_LOG_FATAL, ABCMS_LOGTO_USER); // TODO might change to ABCMS_LOGTO_BOTH
							}
							else {
								$mess = 'Login failed. Please try again.';
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
								$mess = 'Registration successful.';
								$email = $this->hsc($_POST['Account_Email']);
								$email2 = $this->hsc($_POST['Account_Email2']);
								$subject = "ABCMS Registration Success by {$_POST['Account_Email']}";
								$body = '<h4>Hello</h4>You are registered and logged into ' . $this->boots['urldomain'];
							}
							else if (!$okay) {	$mess = 'Registration could not be saved. Please try again.'; }
							else {				$mess = 'Registration failed. Please try again.'; }
							break;

		case 'delete':		if (empty($this->ss['user']['email']) ||
								empty($_POST['Account_Email']) ||
								$_POST['Account_Email'] !== $this->ss['user']['email'] ||
								!$this->set_database('BASIC.json', array('user', $this->ss['user']['email']), NULL, FALSE)) {
								$mess = 'Account could not be deleted. Please try again.';
								break;
							}
							$mess = 'Account deleted.';
							$subject = "ABCMS Account Deleted: {$_POST['Account_Email']}";
							$body = '<h4>Hello</h4>Your account is deleted at ' . $this->boots['urldomain'];

		case 'logout':		$this->session_start(-1);
							$mess = ($mess??'You are logged out.');
							break;

		case 'reset':
		case 'update':
		case 'form':
		case 'unknown':
		default:			if (!empty($this->ss['user']['valid'])) {
								$email = $this->hsc($this->ss['user']['email']);
								$email2 = $this->hsc($this->ss['user']['email2']);
							}
							$mess = 'Login or register below.';
							break;
	}
	// send email
	$emailerror = 'No email sent';
	if ($subject) {
		if (!($this->settings['core']['smtp_user']?:FALSE)) {
			$emailerror = 'Email not available';
		}
		else if ($this->email(
			$this->settings['core']['smtp_user']?:'',							// from
			($this->settings['core']['smtp_name']?:$this->boots['urldomain']),	// name
			$this->settings['core']['smtp_user']?:'',							// recipients
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
			)) {
			$emailerror = 'Email sent';
		}
		else { $emailerror = 'Email not sent'; }
	}
	// display account
	$stat = (empty($this->ss['user']) ? 'Logged out' : (empty($this->ss['user']['valid']) ? 'Logged in validating' : 'Logged in validated'));
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
	echo '</div></form>';
	return;
}

private function home_contact(mixed &...$unused) : void { // home contact form
	echo <<<EOF
<h2>Contact</h2>
EOF;
	return;
}

private function home_notfound(mixed &...$unused) : void { // home page not found
	echo <<<EOF
<h2>Status</h2>
<p class='center'>
My sincere apologies.<br>
I just cannot find the page requested.<br>
<br>
<a href='/'>Try again from the homepage</a>.
</p>
EOF;
	return;
}






/*************************************************************************************************
SECTION WEBFILES: Core extension /webfiles/*
*/







/*************************************************************************************************
SECTION CONSOLE: Core extension /console/*
*/

private function console_theme(mixed &...$unused) : ?bool { // default console theme
	$this->theme(
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
	return NULL;
}

private function console_router(mixed &...$unused) : ?bool { // console router
	// internal extension dispatch bypasses core routing for speed
	switch ($this->boots['urlpathall']) {
		case '/console':
		case '/console/menu':		$this->console_menu();			break;
		case '/console/browse':		$this->console_browse();		break;
		case '/console/help':		$this->console_help();			break;
		case '/console/status':		$this->console_status();		break;
		case '/console/webservant':	$this->console_webservant();	break;
		default:					$this->home_notfound();			break;
	}
	return NULL;
}

private function console_menu(mixed &...$unused) : void { // console menu
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
<a href='/console/webservant'	>/console/webservant</a><br>
<br>
<a href='/command/code'			target='_blank'>/command/code</a><br>
<a href='/command/coredump'		target='_blank'>/command/coredump</a><br>
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
	return;
}

private function console_browse(mixed &...$unused) : void { // console browser
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
	return;
}

private function console_help(mixed &...$unused) : void { // console help
	echo <<<EOF
<h1>Help</h1>
EOF;	
	return;
}

private function console_status(mixed &...$unused) : void { // console status
	echo <<<EOF
<h1>Status</h1>
EOF;	
	return;
}

private function console_webservant(mixed &...$unused) : void { // console webservant
	echo <<<EOF
<h1>Webservant</h1>
EOF;
	return;
}







/*************************************************************************************************
SECTION COMMAND: Core extension /command/*
*/
private function command_router(mixed &...$unused) : ?bool { // command router
	// internal extension dispatch bypasses core routing for speed
	switch ($this->boots['urlpathall']) {
		case '/command/code':		$this->command_code();		break;
		case '/command/coredump':	$this->command_coredump();	break;
		case '/command/cron':		$this->command_cron();		break;
		case '/command/phpinfo':	$this->command_phpinfo();	break;
		case '/command/setup':		$this->command_setup();		break;
		case '/command/updater':	$this->command_updater();	break;
		case '/command/help':
		default:					$this->command_help();		break;
	}
	return NULL;
}

private function command_code(mixed &...$unused) : void { // command code
	highlight_file($this->rp(__FILE__));
	return;
}

private function command_coredump(mixed &...$unused) : void { // command code
	$this->response('CORE: forced coredump', ABCMS_LOG_FATAL);
	return;
}

private function command_cron(mixed &...$unused) : void { // command cron
	if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
	echo "ABCMS cron\n\nDone.\n\n";
	return;
}

private function command_help(mixed &...$unused) : void { // command help
	if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
	echo "ABCMS help\n\nDone.\n\n";
	return;
}

private function command_phpinfo(mixed &...$unused) : void { // command phpinfo
	phpinfo();
	return;
}

private function command_setup(mixed &...$unused) : void { // command setup
	if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
	$this->setup(); // recreate settings
	// op cache warning
	$mess = '';
	if (function_exists('opcache_get_configuration') && !ini_get('opcache.validate_timestamps')) {
		$this->response('SETUP: opcache stale, reload php-fpm to apply settings', ABCMS_LOG_WARN, ABCMS_LOGTO_LOGS);
		$mess = "Reload PHP-FPM to refresh OpCache and apply these settings.\n\n";
	}
	echo "ABCMS settings:\n\nRefresh the screen to see updated settings.\n\n{$mess}Done.\n\n";
	return;
}

private function command_updater(mixed &...$unused) : void { // command updater
	if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
	echo "ABCMS updater\n\nDone.\n\n";
	return;
}







/*************************************************************************************************
SECTION UTILITIES: Utility helper methods.
*/

public function echo(?string ...$args) : void { // wrap the echo() construct for extension function.
	if (NULL !== $args) { echo implode('',$args); } return;
}

public function print(?string $string = NULL) : bool { // wrap the print() construct for extension function.
	return (NULL === $string ? TRUE : print($string));
}

public function get_url(?string $path = NULL) : ?string { // construct url with persistant path variables
	// TODO persistent URL vars to be listed in $this->inputs
	return $path;
}

public function rp(string|false $path) : string|false { // linux style slashes from windows
	return ($path === FALSE ? FALSE : str_replace('\\', '/', $path));
}

private function chk_file(string $filename, bool $must = FALSE) : bool {// check file valid in my extension folder
	$starts = ($this->compiles['core']['projectroot']??$this->settings['core']['projectroot']).'/private'.$this->output_extension().'/';
	if (!str_starts_with($filename, $starts)) { $this->response("FILE: access outside extension folder, file={$filename}", ABCMS_LOG_FATAL); }
	if (preg_match('/(^|[\/\\\\])\.\.([\/\\\\]|$)/', $filename)) { $this->response("FILE: relative filename disallowed, file={$filename}", ABCMS_LOG_FATAL); }
	if (is_link($filename)) { $this->response("FILE: symlink disallowed, file={$filename}", ABCMS_LOG_FATAL); }
	if ($must && ($this->rp(realpath($filename)) !== $filename || !is_file($filename) || !is_readable($filename))) { return FALSE; }
	return TRUE;
}

public function set_file(string $filename, string $value) : void { // write file
	$this->chk_file($filename);
	$temp = "{$filename}.".getmypid();
	if (FALSE === file_put_contents($temp, $value) || !chmod($temp, 0640) || !rename($temp, $filename)) {
		if (file_exists($temp)) { unlink($temp); }
		$this->response("FILE: write failed, file={$filename}, ".$this->error_get_last(), ABCMS_LOG_FATAL);
	}
	return;
}

public function get_file(string $filename, string &$data) : void { // read file
	if (!$this->chk_file($filename, TRUE)) { $this->response("FILE: not readable, file={$filename}", ABCMS_LOG_FATAL); }
	if (FALSE === ($data = file_get_contents($filename))) { $this->response("FILE: read failed, file={$filename}, ".$this->error_get_last(), ABCMS_LOG_FATAL); }
	return;
}

public function touch(string $filename) : void { // touch/create file with permissions
	$this->chk_file($filename);
	if (!touch($filename) || !chmod($filename, 0640)) {
		$this->response("FILE: touch failed, file={$filename}, ".$this->error_get_last(), ABCMS_LOG_FATAL);
	}
	return;
}

public function set_json(string $filename, mixed $value) : void { // write json
	$this->set_file($filename, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	if (json_last_error() !== JSON_ERROR_NONE) {
		$this->response("FILE: json encode failed, file={$filename}, ".json_last_error_msg(), ABCMS_LOG_FATAL);
	}
	return;
}

public function get_json(string $filename, mixed &$data) : void { // read json
	if (!$this->chk_file($filename, TRUE)) { $this->response("FILE: json not readable, file={$filename}", ABCMS_LOG_FATAL); }
	if (FALSE === ($data = file_get_contents($filename))) { $this->response("FILE: json read failed, file={$filename}, ".$this->error_get_last(), ABCMS_LOG_FATAL); }
	if (NULL === ($data = json_decode($data, TRUE))) { $this->response("FILE: json decode failed, file={$filename}, ".json_last_error_msg(), ABCMS_LOG_FATAL); }
	return;
}

public function set_dump(string $filename, mixed $data) : void { // write var_export
	// partial validity check at top level, nested elements unvalidated
	if (is_object($data) || is_resource($data)) {
		$this->response("FILE: var_export supports scalars, arrays and NULL only, file={$filename}", ABCMS_LOG_FATAL);
	}
	$this->set_file($filename, '<?php return ' . var_export($data, TRUE) . ";\n");
	if (function_exists('opcache_invalidate')) { opcache_invalidate($filename, TRUE); }
}

public function get_dump(string $filename, mixed &$data) : bool { // read var_dump
	if (!$this->chk_file($filename, TRUE)) { return FALSE; }
	// beware, failed include() = FALSE = successful include() returning FALSE
	$fn = Closure::bind(static function($f) { return include($f); }, NULL, NULL);
	$data = $fn($filename);
	return TRUE;
}

public function include(string $filename, ...$args) : mixed { // include always
	if (!$this->chk_file($filename, TRUE)) {
		$this->response("FILE: include not readable, file={$filename}", ABCMS_LOG_FATAL);
	}
	// beware, failed include() = FALSE = successful include() returning FALSE
	// anonymous scopes $args within include, hides $this, and protects abmcs() privates
	$anonymous = Closure::bind(function($filename, ...$args) { return include($filename); }, NULL, NULL);
	return $anonymous($filename, ...$args);
}

public function include_once(string $filename, ...$args) : mixed { // PHP should have no fault include_once()
	static $included = array();
	if (!isset($included[$filename])) {
		if (!$this->chk_file($filename, TRUE)) {
			$this->response("FILE: include_once not readable, file={$filename}", ABCMS_LOG_FATAL);
		}
		$included[$filename] = TRUE;
		// anonymous scopes $args within include, hides $this, and protects abmcs() privates
		$anonymous = Closure::bind(function($filename, ...$args) { return include($filename); }, NULL, NULL);
		return $anonymous($filename, ...$args);
	}
	return FALSE;
}

public function array_walk_merge(array &$destiny, array $source) : void { // array_walk_recursive() cannot copy multi-dimensional source, array_map() cannot edit destination
	foreach($destiny as $key => $value) { // overwrite
		if (!array_key_exists($key, $source)) { continue; } // no source
		else if (is_array($destiny[$key]) && is_array($source[$key])) { $this->array_walk_merge($destiny[$key], $source[$key]); } // recurse branch
		else { $destiny[$key] = $source[$key]; } // overwrite leaf
	}
	foreach($source as $key => $value) { // extend
		if (!array_key_exists($key, $destiny)) { $destiny[$key] = $source[$key]; continue; } // extend branch/leaf
		else if (is_array($destiny[$key]) && is_array($source[$key])) { $this->array_walk_merge($destiny[$key], $source[$key]); } // recurse branch
	}
	return;
}

public function get_uuid() : string { // RFC 4122 compliant Version 4 UUIDs, globally unique
	// generate 16 bytes (128 bits) of random data
	$data = random_bytes(16);
	if (strlen($data) !== 16) { $this->response('CORE: uuid4 random_bytes short', ABCMS_LOG_FATAL); }
    // set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    // output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

public function get_uniq() : string { // unique 64 byte token for transients
	return chr(random_int(97,122)).chr(random_int(97,122)).bin2hex(random_bytes(31));
}

public function get_dbid() : string {// unique 32 byte token for permanents
	return chr(random_int(97,122)).chr(random_int(97,122)).bin2hex(random_bytes(15));
}

public function get_pkey(?string $input) : string { // derived deterministic immutable 64 byte hash key, segregated by extension
	return hash('sha256', $this->output_extension().$input);
}

public function get_ckey(?string $input) : string { // derived deterministic immutable 64 byte hash key, segregated by extension, keyed on session secret
	return hash('sha256', ($this->compiles['core']['secret']??$this->settings['core']['secret']).$this->output_extension().$input);
}

public function hsc(?string $string) : ?string { // htmlspecialchars() name shortener
	return (NULL === $string ? NULL : htmlspecialchars(($string), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'));
}

public function html_text(string $html) : string { // HTML to plain text
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
public function email(			// SMTP mailer function
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
) : bool {						// TRUE if delivered
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
	$this->response("EMAIL: begin, from={$from}", ABCMS_LOG_DEBUG); // log
	// define done() and SMTP command() functions
	$socket = NULL;
	$command = function (?string $line, $logit = TRUE) use (&$socket) {
		if ($logit) { $this->response("EMAIL: > {$line}", ABCMS_LOG_DEBUG); } // log
		if ($line !== NULL) { fwrite($socket, "{$line}\r\n"); }
		$status = NULL;
		$text = [];
		while (($rline = fgets($socket)) !== FALSE) {
			$this->response("EMAIL: < {$rline}", ABCMS_LOG_DEBUG); // log
			$status = substr($rline, 0, 3);
			$text[] = trim(substr($rline, 4));
			if (substr($rline, 3, 1) === ' ') { break; } // last line of a multi-line reply
		}
		if (stream_get_meta_data($socket)['timed_out']) {
			$this->response('EMAIL: timeout, server stopped responding', ABCMS_LOG_DEBUG); // log
			$status = NULL;
		}
		return [$status, $text];
	};
	$fail = function (string $result) use (&$socket, $command) {
		if ($socket) { $command('QUIT'); fclose($socket); }
		$this->response("EMAIL: {$result}", ABCMS_LOG_WARN, ABCMS_LOGTO_LOGS);
		$this->response('Email could not be sent. Please try again later.', ABCMS_LOG_WARN, ABCMS_LOGTO_USER);
		return FALSE;
	};
	// configuration abuse
	if (empty($options_user)) {
		if (!preg_match('/^(tcp:\/\/|tls:\/\/|ssl:\/\/|)(127\.0\.0\.1|localhost|::1|\[::1\])$/uiD', $options['smtp']))  { return $fail('unauthenticated smtp requires same server'); }
		if (!preg_match('/^[^@]+@([a-z0-9-]+\.)*'.preg_quote($this->boots['urldomain'], '/').'$/uiD', $from))  { return $fail('unauthenticated from domain must match server'); }
	}
	// sanitize header-bound fields (defense in depth) even though we base64-encode the
	// subject and never let addresses touch headers unescaped, strip CR/LF from anything
	// that lands in a header so a stray newline can never inject an extra header or command.
	$name = preg_replace('/[\r\n]+/', '', $name);
	$subject  = preg_replace('/[\r\n]+/', '', $subject);
	// SMTP command-injection guard on every address
	// if an address contains an unescaped '>' it could break out of
	// 'RCPT TO:<...>' and inject further SMTP commands.
	if (empty($to)) { return $fail('no recipients'); }
	$allRecipients = array_unique(array_merge($to, ($cc??[]), ($bcc??[])));
	foreach (array_merge([$from], $allRecipients) as $addr) {
		// validate email
		if (!filter_var($addr, FILTER_VALIDATE_EMAIL)) { return $fail("invalid address rejected, addr={$addr}"); }
		// newlines allow command injection
		if (preg_match('/[\r\n]+/', $addr)) { return $fail("unsafe address rejected, addr={$addr}"); }
	}
	$this->response('EMAIL: recipients, '.implode(', ', $allRecipients), ABCMS_LOG_DEBUG); // log
	// connect to SMTP socket
	if (!($socket = @fsockopen($options['smtp'], $options['port'], $errno, $errstr, $options['time']))) {
		return $fail("connect failed, errno={$errno}, {$errstr}");
	}
	if (!stream_set_timeout($socket, $options['time'])) { // prevent hangs on every read/write
		return $fail('set stream timeout failed');
	}
	$this->response('EMAIL: socket, '.(string)$socket, ABCMS_LOG_DEBUG); // log
	// SMTP Handshake
	[$status] = $command(NULL); // consume greeting
	if ($status != 220) { return $fail('no server greeting'); }
	[$status, $capabilities] = $command('EHLO ' . $options['ehlo']);
	if ($status != 250) { return $fail('ehlo rejected'); }
	$this->response('EMAIL: handshake, starttls='.(in_array('STARTTLS', $capabilities, TRUE)?'yes':'no'), ABCMS_LOG_DEBUG); // log
	// STARTTLS if offered and not already an implicit-TLS transport
	$encrypted = (FALSE === stripos($options['smtp'],'ssl://') ? FALSE : TRUE);
	if (!$encrypted && in_array('STARTTLS', $capabilities, TRUE)) {
		[$status] = $command('STARTTLS');
		if ($status == 220) {
			stream_context_set_option($socket, ['ssl' => $options['ssl']]);
			if (!stream_socket_enable_crypto($socket, TRUE, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
				return $fail('tls negotiation failed');
			}
			if (!stream_set_timeout($socket, $options['time'])) { // redo just in case
				return $fail('set stream timeout failed after starttls');
			}
			[$status, $capabilities] = $command('EHLO ' . $options['ehlo']);
			if ($status != 250) { return $fail('ehlo rejected after starttls'); }
			$encrypted = TRUE; // security upgraded
		}
		else { return $fail('starttls unavailable'); }
		$this->response("EMAIL: starttls, status={$status}", ABCMS_LOG_DEBUG); // log
	}
	// AUTH (PLAIN preferred, LOGIN fallback), only if credentials given
	if (!empty($options_user) && !$encrypted) { return $fail('unencrypted auth refused'); }
	if (!empty($options_user) && isset($options_pass)) {
		$authLine = current(preg_grep('/^auth[\s=]+/i', $capabilities)) ?: '';
		$methods = array_slice(preg_split('/[\s=]+/', mb_strtolower($authLine, 'UTF-8')), 1);
		if (in_array('plain', $methods, TRUE)) {
			[$status] = $command('AUTH PLAIN ' . base64_encode("\0{$options_user}\0{$options_pass}"), FALSE);
			if ($status != 235) { return $fail('auth plain rejected'); }
		}
		else if (in_array('login', $methods, TRUE)) {
			[$status] = $command('AUTH LOGIN');
			if ($status != 334) { return $fail('auth login rejected'); }
			[$status] = $command(base64_encode($options_user), FALSE);
			if ($status != 334) { return $fail('auth username rejected'); }
			[$status] = $command(base64_encode($options_pass), FALSE);
			if ($status != 235) { return $fail('auth password rejected'); }
		}
		else {
			return $fail('no supported auth method');
		}
		$this->response("EMAIL: authenticated, status={$status}", ABCMS_LOG_DEBUG); // log
	}
	// envelope: MAIL FROM + RCPT TO for to+cc+bcc combined
	[$status] = $command("MAIL FROM:<{$from}>");
	if ($status != 250) { return $fail('mail from rejected'); }
	foreach ($allRecipients as $recipient) {
		[$status] = $command("RCPT TO:<{$recipient}>");
		if ($status != 250) { return $fail("rcpt to rejected, addr={$recipient}"); }
	}
	[$status] = $command('DATA');
	if ($status != 354) { return $fail('data not accepted'); }
	$this->response("EMAIL: envelope, status={$status}", ABCMS_LOG_DEBUG); // log
	// build MIME message
	$mixedBoundary = 'abcms_mixed_' . bin2hex(random_bytes(16));
	$altBoundary   = 'abcms_alt_'   . bin2hex(random_bytes(16));
	// header begins
	$headers  = 'Date: ' . date('r') . "\r\n";
	$headers .= 'From: =?UTF-8?B?' . base64_encode($name) . "?= <{$from}>\r\n";
	$headers .= 'To: ' . implode(', ', array_map(fn($r) => "<{$r}>", $to)) . "\r\n";
	if (!empty($cc)) {
		$headers .= 'Cc: ' . implode(', ', array_map(fn($r) => "<{$r}>", $cc)) . "\r\n";
	}
	// Bcc intentionally omitted from headers; recipients already got RCPT TO above.
	$headers .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
	$headers .= 'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . preg_replace('/^(tcp|tls|ssl):\/\//i', '', $options['smtp']) . ">\r\n";
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
	$this->response('EMAIL: message, status=ok', ABCMS_LOG_DEBUG); // log
	// add attachments
	foreach (($attach??[]) as $filePath) {
		$mark = $this->response_splice(); // mark the keepers
		try { if (!$this->chk_file($filePath, TRUE)) { return $fail("attachment not readable, file={$filePath}"); } }
		catch (Throwable $e) {
			$this->response_splice($mark); // splice off $this->respuser in catch because chk_file() is only actor and catch returns
			return $fail("attachment disallowed, file={$filePath}"); // chk_file() already logged detail
		}
		$fileName = preg_replace('/[\r\n]+/', '', basename($filePath));
		$fileName = str_replace('"', '', $fileName); // keep the Content-Disposition value well-formed
		$fileNameEncoded = rawurlencode($fileName);
		$content  = file_get_contents($filePath);
		if ($content === FALSE) { return $fail("attachment contents not readable, file={$filePath}"); }
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
	$this->response('EMAIL: attachments, status=ok', ABCMS_LOG_DEBUG); // log
	// normalize line endings and dot-stuff in DATA (RFC 5321 §4.5.2)
	$payload = $headers . "\r\n" . $body;
	$payload = preg_replace('/\r\n|\r|\n/', "\r\n", $payload);
	$payload = preg_replace('/^\./m', '..', $payload);
	if (substr($payload, -2) !== "\r\n") $payload .= "\r\n";
	$this->response('EMAIL: normalize, status=ok', ABCMS_LOG_DEBUG); // log
	// write the email
	if (FALSE === fwrite($socket, $payload)) { return $fail('send failed');	}
	[$status] = $command('.');
	if ($status != 250) { return $fail('message body rejected'); }
	$this->response("EMAIL: send, status={$status} bytes=".strlen($payload), ABCMS_LOG_DEBUG); // log
	// finish
	$command('QUIT');
	fclose($socket);
	$this->response('EMAIL: exit, status=ok', ABCMS_LOG_DEBUG);
	return TRUE;
}







/*************************************************************************************************
SECTION THEME: Core webpage template.
*/

public function theme(	// default HTML template
?string	$css	= NULL,	// css override
?string	$js		= NULL,	// js override
?string	$head	= NULL,	// header override
?string	$main	= NULL,	// content override
?string	$foot	= NULL,	// footer override
int		$flag	= 1,	// exclusive control
) : ?bool {				// return boolean
// initialize
$title = mb_strtoupper($this->hsc($this->boots['urldomain']), 'UTF-8');
$lower = mb_strtolower($title, 'UTF-8');
$favicon = (is_readable('./favicon.ico') ? '/favicon.ico' : (is_readable('./public/favicon.ico') ? '/public/favicon.ico' : 'data:,'));
// HTML template
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<meta name='description' content='<?php echo $title; ?>'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<meta name='mobile-web-app-capable' content='yes'>
<link rel='manifest' href='/manifest.json'>
<meta name='theme-color' content='#336699'>
<meta name='color-scheme' content='light dark'>
<meta http-equiv='Content-Security-Policy' content="default-src 'self' 'nonce-<?php echo $this->input['nonce']; ?>'; img-src 'self' data:;">
<title><?php echo $title; ?></title>
<link rel='icon' href='<?php echo $favicon; ?>'>
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
echo $this->response_html();
?>
</main>
<footer>
<?php echo ($foot ?: "<h4><a href='/'>{$lower}</a></h4>"); ?>
</footer>
</div>
</body>
<?php
return NULL;
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
