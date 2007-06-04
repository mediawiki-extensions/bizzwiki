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


?>
