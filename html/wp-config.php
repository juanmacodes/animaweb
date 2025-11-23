<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'qank863' );

/** Database username */
define( 'DB_USER', 'qank863' );

/** Database password */
define( 'DB_PASSWORD', 'Iguisado28' );

/** Database hostname */
define( 'DB_HOST', 'qank863.animaavataragency.com' );

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
define( 'AUTH_KEY',         'i0v?8lM/wK(YkH8!@h<R:_J4m^nH7^9nws[QhG>(1^@!9B?yS0&)wHSd_;um] tp' );
define( 'SECURE_AUTH_KEY',  '%O-rBb8Z~_5;9UahTPK8s&rY|5ZZ>f.D9pM]sfJ9haDiA@|8j+|_[kStJK![nROg' );
define( 'LOGGED_IN_KEY',    'aZ&wBoS1G>h;WB/H?nTVU4cIp;1(qsfkEpU fWL;d?a(f2xA6Ud1;-GU`x5 T}mT' );
define( 'NONCE_KEY',        '4b`59{CS=L,,2%N#H{:zOLEB/FmfSP=R[I1ie-va6^|<(3sOf{|pj6fYVprku{3C' );
define( 'AUTH_SALT',        '!osY@i4U<wO=+OF{.xr;)oTZ2&dM2>R7@f=da%jrD8tr{Ckm[ef%Zo:g!J[Z>%VM' );
define( 'SECURE_AUTH_SALT', '`kcsD1LnZ]THu~n;QUl:yriw1pp2QA|)LlxjT9JQwyE!n8~XdiagEr>-7-n zj|=' );
define( 'LOGGED_IN_SALT',   'V~oiQ0yMGosf=IopZ~KS[#{b+MIG5b@jCw_Oo|bs8.#hu[.q_A6%MbV=~ABJ`WB}' );
define( 'NONCE_SALT',       'UbTJhJ{(Av-.NK5xLH#TIIE!03|0/[m9Ma:EQ2^GkI.6k6`x[9i|hsJ&#ug}F+B7' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 1 );

/* Add any custom values between this line and the "stop editing" line. */

// Permitir CORS para llamadas desde el front (ajusta tu dominio)
define('JWT_AUTH_CORS_ENABLE', true);

// Opcional: fuerza URLs si las necesitas
// define('WP_HOME', 'https://wp.animaavataragency.com');
// define('WP_SITEURL', 'https://wp.animaavataragency.com');
define( 'ANIMA_OPENAI_KEY', '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' );
// --- ONE SIGNAL (Para Notificaciones Móviles) ---
define( 'ANIMA_ONESIGNAL_APP_ID', '2d6fa714-4274-4434-b7a5-b6d5e27b343d' );
// Endurecimiento básico
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
