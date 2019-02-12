<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'fF(`ziN;uDz;}W(|!lHM@5__:pw#|ha/26,T:V3H5}p Xb39y>MKETC}B?({:xr6' );
define( 'SECURE_AUTH_KEY',   'Fu;nLm[IMS15R_D]9([LwA[<35P85.NyUUaMT{*%_/UW7@IPFX$VM&YN%aFCgUB=' );
define( 'LOGGED_IN_KEY',     '=R53 -Imsy[{8y61HSP T$@2?gzq&[+8n]Cvc.]&O9&tFJ9+^u&S?zW 9OT<kO)`' );
define( 'NONCE_KEY',         '.*Vf9P)y/dv#cFD!KPyJ{!;+j_Y^Y*k2=C*Hn, z }Jv.gyhuF`CWRq#A@uq&d.a' );
define( 'AUTH_SALT',         '0%.d|@$o$SG_K7;3i_/6}suNZe^q;?vF124F*eOA_9oG&3J[ybFJ-3<0)-/deYG]' );
define( 'SECURE_AUTH_SALT',  'FuwP1vXWQ=>jwR))?!A&yG_dv>JGNAbA@/jB<i)R]^ur`-lcBJm-1_uL9QLuR|9Y' );
define( 'LOGGED_IN_SALT',    'q12mB=<UM}|(=C2% z5/%g>#cSgUbWzYH)M%d@YQ*rc4,oVzT(-%v20op28E7{cR' );
define( 'NONCE_SALT',        'MF8p{*tXon29eXVJ6hS;E{%2<DC2@xYw7<`-F%zoY.p-CD23[(7clcMrXzh8zc4M' );
define( 'WP_CACHE_KEY_SALT', ':}ezxrWsua2w.;Wr:a;WR$5@|d:B~PlPc%:6q+x?tmvDRj!^xWi%gfysI^FYmQpN' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
