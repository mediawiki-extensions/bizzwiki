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
?>
