<?php
/**
 * @link              https://wprefers.com/
 * @since             1.0.0
 * @package           Domain_Age_Checker_Tool
 *
 * @wordpress-plugin
 * Plugin Name:       Domain Age Checker Tool
 * Plugin URI:        https://wordpress.org/plugins/domain-age-checker-tool/
 * Description:       <code><strong>Domain Age Checker Tool for WordPress</strong></code> plugin allows you to check the age of multiple domains with registered date, last updated date and expiry date provided by whoisapi.</a>
 * Version:           1.0.0
 * Author:            WPRefers
 * Author URI:        https://wprefers.com/
 * Text Domain:       domain-age-checker-tool
 *
 *
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once("vendor/autoload.php");

new \WPRefers\DomainAgeCheckerTool\DomainAgeCheckerTool();