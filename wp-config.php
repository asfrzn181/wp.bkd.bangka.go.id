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
define( 'DB_NAME', 'bkpsdmdBangka' );

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
define( 'AUTH_KEY',         'di76Curxosou5ripnd6BE43FyjSzbuHlgZlBszbgzgX0ksiq0kMWRAp0jwBRAPCa' );
define( 'SECURE_AUTH_KEY',  'T21KtA3hHCDfVR9sQzjOw3GSjPf2QSHvkE0BR8lVD34d2VjFXFx5nGDDj3InR377' );
define( 'LOGGED_IN_KEY',    'aYtv9iFiifMgKrVpsClduFGXTArWBD2f11kU8sZ1GbnRisWmzXR244Uv8WLDeQxR' );
define( 'NONCE_KEY',        'EJkKAwcNhtGrYymJdwfctpO2Eh8VyO14zKUO0nZVJPmvtkHSKcJi8Y1RDRaqoIH2' );
define( 'AUTH_SALT',        'ltfqrCNxzDWcOZcMHStplotepOtndd1eCpJPxW0zNBszaNI9yxuqE5elDohWGVaX' );
define( 'SECURE_AUTH_SALT', 'mkURFQFCs2vw9PBH8HMHPYBRZQqcRPkmdpNviOqyTkhVrFmHFenLda3JlFdav6TN' );
define( 'LOGGED_IN_SALT',   'IuZ1TxXMSY1M0QlS4ACFMjC3geaO3ulLWgSlZ7DK1BjZScMs0pKSSSJEuglhduuV' );
define( 'NONCE_SALT',       'oMPVvmxhT9PBEhFTWWxIlf7El8vNdmH9csdR3IYg0P6lT9PxXVgcAzZtlCh6sPkX' );

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
