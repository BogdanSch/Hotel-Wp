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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'hotel-bs' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
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
define( 'AUTH_KEY',         '62?mwF>gP4{`a;M<7hE6G(?}#{M*pFK_M*IOc)pDbwXk{F^RrR]Cn^ChAldCKt==' );
define( 'SECURE_AUTH_KEY',  '1NP8s2d.bYCA76lmW^/S&n_ZO~6iDB*FcmD{5o+>=k7vC|O-[~>#Rw!m-x8>*-wo' );
define( 'LOGGED_IN_KEY',    'JoHRl5n01@I7TjUHm`*c}k/*TV!OuXce!6wT#p|Uku%dkqghW~qiAYU9o*)hCG?;' );
define( 'NONCE_KEY',        '1|?#EMrRx~wa/[%pIO;u[](.12)+YC%nU4fv3xH*%EbzTQx<d`^.Ic=xEHiv`H[r' );
define( 'AUTH_SALT',        '5yRNccY3/5^-J46CMV3Q>7`IE.7:_2K3KaWljgi$+Gys^=gkKqO:QilumQfW>Wf1' );
define( 'SECURE_AUTH_SALT', 'Y,ojzahHpgspN_|RJ(_tiPy3O}LUo.{2s3AP.ZWOxcNE>UohG8M<a qBz%,BuSmB' );
define( 'LOGGED_IN_SALT',   ')A|m{!kE<p{xp~$}KA!4ltwf$BhaZ?]`f;y[3J@poOKL7*7w964YDcAR3J7^f-[c' );
define( 'NONCE_SALT',       ']4x%Qto$z|7@Tkkb+t1S<kB(iEGdhj{}Z>;{xUs$7rp!dwJP7?2MgFQ/^6Dh5?WW' );

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
