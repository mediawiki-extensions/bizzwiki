<?php
/**
 * See user.txt
 *
 */

# Number of characters in user_token field
define( 'USER_TOKEN_LENGTH', 32 );

# Serialized record version
define( 'MW_USER_VERSION', 5 );

# Some punctuation to prevent editing from broken text-mangling proxies.
# FIXME: this is embedded unescaped into HTML attributes in various
# places, so we can't safely include ' or " even though we really should.
define( 'EDIT_TOKEN_SUFFIX', '\\' );

/**
 * Thrown by User::setPassword() on error
 *  Exception
 */
class PasswordError extends MWException {
	// NOP
}
/**
 * The User object encapsulates all of the user-specific settings (user_id,
 * name, rights, password, email address, options, last login time). Client
 * classes use the getXXX() functions to access these fields. These functions
 * do all the work of determining whether the user is logged in,
 * whether the requested option can be satisfied from cookies or
 * whether a database query is needed. Most of the settings needed
 * for rendering normal pages are set in the cookie to minimize use
 * of the database.
 */
class User {

	/**
	 * A list of default user toggles, i.e. boolean user preferences that are 
	 * displayed by Special:Preferences as checkboxes. This list can be 
	 * extended via the UserToggles hook or $wgContLang->getExtraUserToggles().
	 */
	static public $mToggles = array(
		'highlightbroken',
		'justify',
		'hideminor',
		'extendwatchlist',
		'usenewrc',
		'numberheadings',
		'showtoolbar',
		'editondblclick',
		'editsection',
		'editsectiononrightclick',
		'showtoc',
		'rememberpassword',
		'editwidth',
		'watchcreations',
		'watchdefault',
		'watchmoves',
		'watchdeletion',
		'minordefault',
		'previewontop',
		'previewonfirst',
		'nocache',
		'enotifwatchlistpages',
		'enotifusertalkpages',
		'enotifminoredits',
		'enotifrevealaddr',
		'shownumberswatching',
		'fancysig',
		'externaleditor',
		'externaldiff',
		'showjumplinks',
		'uselivepreview',
		'forceeditsummary',
		'watchlisthideown',
		'watchlisthidebots',
		'watchlisthideminor',
		'ccmeonemails',
		'diffonly',
	);

	/**
	 * List of member variables which are saved to the shared cache (memcached).
	 * Any operation which changes the corresponding database fields must 
	 * call a cache-clearing function.
	 */
	static $mCacheVars = array(
		# user table
		'mId',
		'mName',
		'mRealName',
		'mPassword',
		'mNewpassword',
		'mNewpassTime',
		'mEmail',
		'mOptions',
		'mTouched',
		'mToken',
		'mEmailAuthenticated',
		'mEmailToken',
		'mEmailTokenExpires',
		'mRegistration',
		'mEditCount',
		# user_group table
		'mGroups',
	);

	/**
	 * The cache variable declarations
	 */
	var $mId, $mName, $mRealName, $mPassword, $mNewpassword, $mNewpassTime, 
		$mEmail, $mOptions, $mTouched, $mToken, $mEmailAuthenticated, 
		$mEmailToken, $mEmailTokenExpires, $mRegistration, $mGroups;

	/**
	 * Whether the cache variables have been loaded
	 */
	var $mDataLoaded;

	/**
	 * Initialisation data source if mDataLoaded==false. May be one of:
	 *    defaults      anonymous user initialised from class defaults
	 *    name          initialise from mName
	 *    id            initialise from mId
	 *    session       log in from cookies or session if possible
	 *
	 * Use the User::newFrom*() family of functions to set this.
	 */
	var $mFrom;

	/**
	 * Lazy-initialised variables, invalidated with clearInstanceCache
	 */
	var $mNewtalk, $mDatePreference, $mBlockedby, $mHash, $mSkin, $mRights,
		$mBlockreason, $mBlock, $mEffectiveGroups;

	/** 
	 * Lightweight constructor for anonymous user
	 * Use the User::newFrom* factory functions for other kinds of users
	 */
	function User() {
		$this->clearInstanceCache( 'defaults' );
	}

	/**
	 * Load the user table data for this object from the source given by mFrom
	 */
	function load() {
		if ( $this->mDataLoaded ) {
			return;
		}
		wfProfileIn( __METHOD__ );

		# Set it now to avoid infinite recursion in accessors
		$this->mDataLoaded = true;

		switch ( $this->mFrom ) {
			case 'defaults':
				$this->loadDefaults();
				break;
			case 'name':
				$this->mId = self::idFromName( $this->mName );
				if ( !$this->mId ) {
					# Nonexistent user placeholder object
					$this->loadDefaults( $this->mName );
				} else {
					$this->loadFromId();
				}
				break;
			case 'id':
				$this->loadFromId();
				break;
			case 'session':
				$this->loadFromSession();
				break;
			default:
				throw new MWException( "Unrecognised value for User->mFrom: \"{$this->mFrom}\"" );
		}
		wfProfileOut( __METHOD__ );
	}

	/**
	 * Load user table data given mId
	 * @return false if the ID does not exist, true otherwise
	 * @private
	 */
	function loadFromId() {
		global $wgMemc;
		if ( $this->mId == 0 ) {
			$this->loadDefaults();
			return false;
		} 

		# Try cache
		$key = wfMemcKey( 'user', 'id', $this->mId );
		$data = $wgMemc->get( $key );
		if ( !is_array( $data ) || $data['mVersion'] < MW_USER_VERSION ) {
			# Object is expired, load from DB
			$data = false;
		}
		
		if ( !$data ) {
			wfDebug( "Cache miss for user {$this->mId}\n" );
			# Load from DB
			if ( !$this->loadFromDatabase() ) {
				# Can't load from ID, user is anonymous
				return false;
			}

			# Save to cache
			$data = array();
			foreach ( self::$mCacheVars as $name ) {
				$data[$name] = $this->$name;
			}
			$data['mVersion'] = MW_USER_VERSION;
			$wgMemc->set( $key, $data );
		} else {
			wfDebug( "Got user {$this->mId} from cache\n" );
			# Restore from cache
			foreach ( self::$mCacheVars as $name ) {
				$this->$name = $data[$name];
			}
		}
		return true;
	}

	/**
	 * Static factory method for creation from username.
	 *
	 * This is slightly less efficient than newFromId(), so use newFromId() if
	 * you have both an ID and a name handy. 
	 *
	 * @param string $name Username, validated by Title:newFromText()
	 * @param mixed $validate Validate username. Takes the same parameters as 
	 *    User::getCanonicalName(), except that true is accepted as an alias 
	 *    for 'valid', for BC.
	 * 
	 * @return User object, or null if the username is invalid. If the username 
	 *    is not present in the database, the result will be a user object with
	 *    a name, zero user ID and default settings. 
	 * @static
	 */
	static function newFromName( $name, $validate = 'valid' ) {
		if ( $validate === true ) {
			$validate = 'valid';
		}
		$name = self::getCanonicalName( $name, $validate );
		if ( $name === false ) {
			return null;
		} else {
			# Create unloaded user object
			$u = new User;
			$u->mName = $name;
			$u->mFrom = 'name';
			return $u;
		}
	}

	static function newFromId( $id ) {
		$u = new User;
		$u->mId = $id;
		$u->mFrom = 'id';
		return $u;
	}


	/**
	 * Factory method to fetch whichever user has a given email confirmation code.
	 * This code is generated when an account is created or its e-mail address
	 * has changed.
	 *
	 * If the code is invalid or has expired, returns NULL.
	 *
	 * @param string $code
	 * @return User
	 * @static
	 */
	static function newFromConfirmationCode( $code ) {
		$dbr = wfGetDB( DB_SLAVE );
		$id = $dbr->selectField( 'user', 'user_id', array(
			'user_email_token' => md5( $code ),
			'user_email_token_expires > ' . $dbr->addQuotes( $dbr->timestamp() ),
			) );
		if( $id !== false ) {
			return User::newFromId( $id );
		} else {
			return null;
		}
	}
	
	/**
	 * Create a new user object using data from session or cookies. If the
	 * login credentials are invalid, the result is an anonymous user.
	 *
	 * @return User
	 * @static
	 */
	static function newFromSession() {
		$user = new User;
		$user->mFrom = 'session';
		return $user;
	}

	/**
	 * Get username given an id.
	 * @param integer $id Database user id
	 * @return string Nickname of a user
	 * @static
	 */
	static function whoIs( $id ) {
		$dbr = wfGetDB( DB_SLAVE );
		return $dbr->selectField( 'user', 'user_name', array( 'user_id' => $id ), 'User::whoIs' );
	}

	/**
	 * Get real username given an id.
	 * @param integer $id Database user id
	 * @return string Realname of a user
	 * @static
	 */
	static function whoIsReal( $id ) {
		$dbr = wfGetDB( DB_SLAVE );
		return $dbr->selectField( 'user', 'user_real_name', array( 'user_id' => $id ), 'User::whoIsReal' );
	}

	/**
	 * Get database id given a user name
	 * @param string $name Nickname of a user
	 * @return integer|null Database user id (null: if non existent
	 * @static
	 */
	static function idFromName( $name ) {
		$nt = Title::newFromText( $name );
		if( is_null( $nt ) ) {
			# Illegal name
			return null;
		}
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow( 'user', array( 'user_id' ), array( 'user_name' => $nt->getText() ), __METHOD__ );

		if ( $s === false ) {
			return 0;
		} else {
			return $s->user_id;
		}
	}

	/**
	 * Does the string match an anonymous IPv4 address?
	 *
	 * This function exists for username validation, in order to reject
	 * usernames which are similar in form to IP addresses. Strings such
	 * as 300.300.300.300 will return true because it looks like an IP 
	 * address, despite not being strictly valid.
	 * 
	 * We match \d{1,3}\.\d{1,3}\.\d{1,3}\.xxx as an anonymous IP
	 * address because the usemod software would "cloak" anonymous IP
	 * addresses like this, if we allowed accounts like this to be created
	 * new users could get the old edits of these anonymous users.
	 *
	 * @static
	 * @param string $name Nickname of a user
	 * @return bool
	 */
	static function isIP( $name ) {
		return preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.(?:xxx|\d{1,3})$/',$name) || User::isIPv6($name);
		/*return preg_match("/^
			(?:[01]?\d{1,2}|2(:?[0-4]\d|5[0-5]))\.
			(?:[01]?\d{1,2}|2(:?[0-4]\d|5[0-5]))\.
			(?:[01]?\d{1,2}|2(:?[0-4]\d|5[0-5]))\.
			(?:[01]?\d{1,2}|2(:?[0-4]\d|5[0-5]))
		$/x", $name);*/
	}

	/**
	 * Check if $name is an IPv6 IP.
	 */
	static function isIPv6($name) {
		/* 
		 * if it has any non-valid characters, it can't be a valid IPv6  
		 * address.
		 */
		if (preg_match("/[^:a-fA-F0-9]/", $name))
			return false;

		$parts = explode(":", $name);
		if (count($parts) < 3)
			return false;
		foreach ($parts as $part) {
			if (!preg_match("/^[0-9a-fA-F]{0,4}$/", $part))
				return false;
		}
		return true;
	}

	/**
	 * Is the input a valid username?
	 *
	 * Checks if the input is a valid username, we don't want an empty string,
	 * an IP address, anything that containins slashes (would mess up subpages),
	 * is longer than the maximum allowed username size or doesn't begin with
	 * a capital letter.
	 *
	 * @param string $name
	 * @return bool
	 * @static
	 */
	static function isValidUserName( $name ) {
		global $wgContLang, $wgMaxNameChars;

		if ( $name == ''
		|| User::isIP( $name )
		|| strpos( $name, '/' ) !== false
		|| strlen( $name ) > $wgMaxNameChars
		|| $name != $wgContLang->ucfirst( $name ) )
			return false;

		// Ensure that the name can't be misresolved as a different title,
		// such as with extra namespace keys at the start.
		$parsed = Title::newFromText( $name );
		if( is_null( $parsed )
			|| $parsed->getNamespace()
			|| strcmp( $name, $parsed->getPrefixedText() ) )
			return false;
		
		// Check an additional blacklist of troublemaker characters.
		// Should these be merged into the title char list?
		$unicodeBlacklist = '/[' .
			'\x{0080}-\x{009f}' . # iso-8859-1 control chars
			'\x{00a0}' .          # non-breaking space
			'\x{2000}-\x{200f}' . # various whitespace
			'\x{2028}-\x{202f}' . # breaks and control chars
			'\x{3000}' .          # ideographic space
			'\x{e000}-\x{f8ff}' . # private use
			']/u';
		if( preg_match( $unicodeBlacklist, $name ) ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Usernames which fail to pass this function will be blocked
	 * from user login and new account registrations, but may be used
	 * internally by batch processes.
	 *
	 * If an account already exists in this form, login will be blocked
	 * by a failure to pass this function.
	 *
	 * @param string $name
	 * @return bool
	 */
	static function isUsableName( $name ) {
		global $wgReservedUsernames;
		return
			// Must be a usable username, obviously ;)
			self::isValidUserName( $name ) &&
			
			// Certain names may be reserved for batch processes.
			!in_array( $name, $wgReservedUsernames );
	}
	
	/**
	 * Usernames which fail to pass this function will be blocked
	 * from new account registrations, but may be used internally
	 * either by batch processes or by user accounts which have
	 * already been created.
	 *
	 * Additional character blacklisting may be added here
	 * rather than in isValidUserName() to avoid disrupting
	 * existing accounts.
	 *
	 * @param string $name
	 * @return bool
	 */
	static function isCreatableName( $name ) {
		return
			self::isUsableName( $name ) &&
			
			// Registration-time character blacklisting...
			strpos( $name, '@' ) === false;
	}

	/**
	 * Is the input a valid password?
	 *
	 * @param string $password
	 * @return bool
	 */
	function isValidPassword( $password ) {
		global $wgMinimalPasswordLength, $wgContLang;

		$result = null;
		if( !wfRunHooks( 'isValidPassword', array( $password, &$result ) ) ) return $result;
		if ($result === false) return false;
		return (strlen( $password ) >= $wgMinimalPasswordLength) &&
			($wgContLang->lc( $password ) !== $wgContLang->lc( $this->mName ));
	}

	/**
	 * Does the string match roughly an email address ?
	 *
	 * There used to be a regular expression here, it got removed because it
	 * rejected valid addresses. Actually just check if there is '@' somewhere
	 * in the given address.
	 *
	 * @todo Check for RFC 2822 compilance (bug 959)
	 *
	 * @param string $addr email address
	 * @static
	 * @return bool
	 */
	static function isValidEmailAddr ( $addr ) {
		return ( trim( $addr ) != '' ) &&
			(false !== strpos( $addr, '@' ) );
	}

	/**
	 * Given unvalidated user input, return a canonical username, or false if 
	 * the username is invalid.
	 * @param string $name
	 * @param mixed $validate Type of validation to use:
	 *                         false        No validation
	 *                         'valid'      Valid for batch processes
	 *                         'usable'     Valid for batch processes and login
	 *                         'creatable'  Valid for batch processes, login and account creation
	 */
	static function getCanonicalName( $name, $validate = 'valid' ) {
		# Force usernames to capital
		global $wgContLang;
		$name = $wgContLang->ucfirst( $name );

		# Clean up name according to title rules
		$t = Title::newFromText( $name );
		if( is_null( $t ) ) {
			return false;
		}

		# Reject various classes of invalid names
		$name = $t->getText();
		global $wgAuth;
		$name = $wgAuth->getCanonicalName( $t->getText() );

		switch ( $validate ) {
			case false:
				break;
			case 'valid':
				if ( !User::isValidUserName( $name ) ) {
					$name = false;
				}
				break;
			case 'usable':
				if ( !User::isUsableName( $name ) ) {
					$name = false;
				}
				break;
			case 'creatable':
				if ( !User::isCreatableName( $name ) ) {
					$name = false;
				}
				break;
			default:
				throw new MWException( 'Invalid parameter value for $validate in '.__METHOD__ );
		}
		return $name;
	}

	/**
	 * Count the number of edits of a user
	 *
	 * It should not be static and some day should be merged as proper member function / deprecated -- domas
	 * 
	 * @param int $uid The user ID to check
	 * @return int
	 * @static
	 */
	static function edits( $uid ) {
		wfProfileIn( __METHOD__ );
		$dbr = wfGetDB( DB_SLAVE );
		// check if the user_editcount field has been initialized
		$field = $dbr->selectField(
			'user', 'user_editcount',
			array( 'user_id' => $uid ),
			__METHOD__
		);

		if( $field === null ) { // it has not been initialized. do so.
			$dbw = wfGetDb( DB_MASTER );
			$count = $dbr->selectField(
				'revision', 'count(*)',
				array( 'rev_user' => $uid ),
				__METHOD__
			);
			$dbw->update(
				'user',
				array( 'user_editcount' => $count ),
				array( 'user_id' => $uid ),
				__METHOD__
			);
		} else {
			$count = $field;
		}
		wfProfileOut( __METHOD__ );
		return $count;
	}

	/**
	 * Return a random password. Sourced from mt_rand, so it's not particularly secure.
	 * @todo hash random numbers to improve security, like generateToken()
	 *
	 * @return string
	 * @static
	 */
	static function randomPassword() {
		global $wgMinimalPasswordLength;
		$pwchars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
		$l = strlen( $pwchars ) - 1;

		$pwlength = max( 7, $wgMinimalPasswordLength );
		$digit = mt_rand(0, $pwlength - 1);
		$np = '';
		for ( $i = 0; $i < $pwlength; $i++ ) {
			$np .= $i == $digit ? chr( mt_rand(48, 57) ) : $pwchars{ mt_rand(0, $l)};
		}
		return $np;
	}

	/**
	 * Set cached properties to default. Note: this no longer clears 
	 * uncached lazy-initialised properties. The constructor does that instead.
	 *
	 * @private
	 */
	function loadDefaults( $name = false ) {
		wfProfileIn( __METHOD__ );

		global $wgCookiePrefix;

		$this->mId = 0;
		$this->mName = $name;
		$this->mRealName = '';
		$this->mPassword = $this->mNewpassword = '';
		$this->mNewpassTime = null;
		$this->mEmail = '';
		$this->mOptions = null; # Defer init

		if ( isset( $_COOKIE[$wgCookiePrefix.'LoggedOut'] ) ) {
			$this->mTouched = wfTimestamp( TS_MW, $_COOKIE[$wgCookiePrefix.'LoggedOut'] );
		} else {
			$this->mTouched = '0'; # Allow any pages to be cached
		}

		$this->setToken(); # Random
		$this->mEmailAuthenticated = null;
		$this->mEmailToken = '';
		$this->mEmailTokenExpires = null;
		$this->mRegistration = wfTimestamp( TS_MW );
		$this->mGroups = array();

		wfProfileOut( __METHOD__ );
	}
	
	/**
	 * Initialise php session
	 * @deprecated use wfSetupSession()
	 */
	function SetupSession() {
		wfSetupSession();
	}

	/**
	 * Load user data from the session or login cookie. If there are no valid
	 * credentials, initialises the user as an anon.
	 * @return true if the user is logged in, false otherwise
	 */
	private function loadFromSession() {
		global $wgMemc, $wgCookiePrefix;

		if ( isset( $_SESSION['wsUserID'] ) ) {
			if ( 0 != $_SESSION['wsUserID'] ) {
				$sId = $_SESSION['wsUserID'];
			} else {
				$this->loadDefaults();
				return false;
			}
		} else if ( isset( $_COOKIE["{$wgCookiePrefix}UserID"] ) ) {
			$sId = intval( $_COOKIE["{$wgCookiePrefix}UserID"] );
			$_SESSION['wsUserID'] = $sId;
		} else {
			$this->loadDefaults();
			return false;
		}
		if ( isset( $_SESSION['wsUserName'] ) ) {
			$sName = $_SESSION['wsUserName'];
		} else if ( isset( $_COOKIE["{$wgCookiePrefix}UserName"] ) ) {
			$sName = $_COOKIE["{$wgCookiePrefix}UserName"];
			$_SESSION['wsUserName'] = $sName;
		} else {
			$this->loadDefaults();
			return false;
		}

		$passwordCorrect = FALSE;
		$this->mId = $sId;
		if ( !$this->loadFromId() ) {
			# Not a valid ID, loadFromId has switched the object to anon for us
			return false;
		}
		
		if ( isset( $_SESSION['wsToken'] ) ) {
			$passwordCorrect = $_SESSION['wsToken'] == $this->mToken;
			$from = 'session';
		} else if ( isset( $_COOKIE["{$wgCookiePrefix}Token"] ) ) {
			$passwordCorrect = $this->mToken == $_COOKIE["{$wgCookiePrefix}Token"];
			$from = 'cookie';
		} else {
			# No session or persistent login cookie
			$this->loadDefaults();
			return false;
		}

		if ( ( $sName == $this->mName ) && $passwordCorrect ) {
			wfDebug( "Logged in from $from\n" );
			return true;
		} else {
			# Invalid credentials
			wfDebug( "Can't log in from $from, invalid credentials\n" );
			$this->loadDefaults();
			return false;
		}
	}

?>
