<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
/*
Template Name: Membership Template
*/

/**
 * Pages Template
 *
 *
 * @file           page.php
 * @package        Responsive
 * @author         Emil Uzelac
 * @copyright      2003 - 2014 CyberChimps
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/responsive/membership_page.php
 * @link           http://codex.wordpress.org/Theme_Development#Pages_.28page.php.29
 * @since          available since Release 1.0
 */

get_header(); ?>

<div id="content" class="<?php echo esc_attr( implode( ' ', responsive_get_content_classes() ) ); ?>">
<h1 class="entry-title post-title">Canberra Brewers Membership</h1>
<?php 
require($_SERVER["DOCUMENT_ROOT"] . "/membership_files/membership_form.php");
?>

</div><!-- end of #content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
