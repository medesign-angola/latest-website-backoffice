<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'medesign-ao' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'TL0ZyoezLUwkaDLfR84lzIdfWdFRND51Rn9QyBogG2RF0WXSY3W2tbWw5o4Nczk8' );
define( 'SECURE_AUTH_KEY',  'V0A1D35rrV5RmFzZdfG15pUnFav6RxWEAFhYM5ObjucUPJfy1uYDT2eeT5E6VEfX' );
define( 'LOGGED_IN_KEY',    'Xy21HGUj8XCnyHmJznkFz8Bvn8lkUqNhiWr61xe7WXx0vxye3WZfiQvWILfnEIaO' );
define( 'NONCE_KEY',        'l5dMar2vocurd9JlTY3oGqM4WzMVXzZUyyPSAlLlBmCGLKuenJ96JEmxfdFmWKly' );
define( 'AUTH_SALT',        'SK7VCqlKVIkuJFg5sCxXx7TTkH7phIx0TBef9Qgbgyi48MOPpLxjKhFUZPaiEorr' );
define( 'SECURE_AUTH_SALT', 'GS6GFffSW0HUbaTp6U1RHDjqd5s8FG5UV1tffNv8XXEAdH2vSrMNWb0tXkglxx2Z' );
define( 'LOGGED_IN_SALT',   'tbInjqjNUVGJSsi8fOUoIj97Wi3fBrXXyWwKXOyVDZuxTQm4phmOfHYGFoJBJYdU' );
define( 'NONCE_SALT',       'NB0gJAIjLRlFEsFr6CecePYkvQTdfEbkc822oNSNm8ccE1ADTyYU3ptUyOJhBGYw' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
