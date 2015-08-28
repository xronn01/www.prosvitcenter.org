<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'Pro_svit');

/** MySQL database username */
define('DB_USER', 'Prosvit_center');

/** MySQL database password */
define('DB_PASSWORD', 'SSauej5bqfYCenAQ');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '-9Ac.4h~!xqJMoRg-F7 y9_2T+q72[9mrDrUe/e@o|<!WUQv?7dhuORKePe7{BWz');
define('SECURE_AUTH_KEY',  'W5=b/QSOCkoVjw[>:P.8O[~#F5dZv!yt#2!$e%60}4|4f|br|JMya$H7yb!7PCTh');
define('LOGGED_IN_KEY',    'peB0l,nz51adWOuTo.0Sn&Yc<_0N;X%3wr$TZa3C6d*&/V&T6%z[DM:zf]bglSmr');
define('NONCE_KEY',        '>b8!LKsMsB)$Mm>>+m{]4Sj=+8#?:TTIG&dY[,7`A4`41~?[W_aif-8eG-xXICaN');
define('AUTH_SALT',        'JXWdK%Yx[HSs.a?8V2Qmh*UpL)>3HPN/K</9gI%w2=]g//NfS,w}RH:mQ.cfSE3{');
define('SECURE_AUTH_SALT', 'T fiL4Ml%2@I]:n-5?|ABFkz4p/[u|A5t{}tr|yB8Oa5<c&ybU&Nl}nn;^On}1K ');
define('LOGGED_IN_SALT',   '4;r,NVQ!l>O5 JAea@c(I^!>;|&Cpsy7Lo,ifk%fULb8#^<F^u0Q?}}3GaJW[ND*');
define('NONCE_SALT',       '|C>ys5zm2b`xf=~]MYn|aQ>&u-!3x*v-=i[$Kh&lIgsF~@%h.?VSqkMjFy8cUZ=k');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
