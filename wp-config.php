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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dbGuanaca' );

/** MySQL database username */
define( 'DB_USER', 'orion' );

/** MySQL database password */
define( 'DB_PASSWORD', 'master' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         '%4KcR,p 9eF2P<i^7PupX/!et39=}]Nk/(tO-kM4R(Bi{`m)s(}4)e]j;SjpQr;`' );
define( 'SECURE_AUTH_KEY',  'H%_yuTmU%#}p m.<[O[5(}S73XS1~]Nl3*]~Ohx5xm!KF>ueBcj_oIm&Cp7h$OHS' );
define( 'LOGGED_IN_KEY',    'wkN)El3c8fB^&JqQ/5v@CYHsLd6Y@gWTEyAm_C2A;[SPc*sz.pDX#v=xzEEnTlF$' );
define( 'NONCE_KEY',        '.Pr|]W)Oy6@TGbl*+{{H+^RadOoJEK/_xl_X_1*,1MH)wu!m`5pIa@/XhOHaD;06' );
define( 'AUTH_SALT',        'w*f)+tUpoP{M,A>R-f%FlDBTQwwaT=vEb4Mh.rjRd7O{kE22>m4Avg[Nfg&t&U/W' );
define( 'SECURE_AUTH_SALT', 'm>@T-KvL@<7}Kl0ogFI{:dciKfnd&^jl;7XVB,QwTfO)iu02_?ZJRVotgrFkCL`>' );
define( 'LOGGED_IN_SALT',   'u7tY|/T&j?_6|-*qwqz8vjjS2ur*p:T-rdl|#)O[JBQ*oSg)ct(72d/&#[%rF([e' );
define( 'NONCE_SALT',       ' _$GDIGyyw9=~fUh?+EuC!`b*AX}6%|S6fX6+qqGc}hKYUmbrMRQ7~LhReFG~o 1' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
