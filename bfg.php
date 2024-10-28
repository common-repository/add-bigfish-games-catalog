<?php

/*

 Add BigFish Games Catalog for WordPress
 ==============================================================================

This plugin adds game catalog from http://www.bigfishgames.com/?afcode=afd63cd4ad992 to your Blog.

Support of all known types of games: PC, Macintosh, FREE Online games.
Also 5 languages (English, German, Spanish, French, Japanese) are supported all!

The plugin keeps some part of the data in metafields.
If you wish to receive an example how to deduce the data of metafields in template WordPress you need to become my referral BigFishGames.
Under this reference https://pnp.bigfishgames.com/?afcode=afd63cd4ad992 you can become my referral.
If you are already registered in BigFishGames that you can buy a ready template.
The price of a template of $100. Payment is possible through system WebMoney (Z231409672156).
Plus to a template you can receive an additional script cron.php which will automatically add new games without your participation!
If you became my referral, or have paid, for reception of additional files write on an e-mail isestnestmi@mail.ru or ICQ 338900566
To write to me it is necessary in English or in Russian. I do not know other languages!

                See example at http://BigFishTest.com/ or http://www.ShuvaevGames.com

 Info for WordPress:
 ==============================================================================

Plugin Name: Add BigFish Games Catalog
Plugin URI: http://bigfishtest.com/casual-pc-game-plugin-wordpress-add-bigfish-games-catalog.html
Description: This plugin adds game catalog from <a href="http://www.bigfishgames.com/?afcode=afd63cd4ad99">Big Fish Games</a> to your Blog.
Author: XML BigFishTest.com Team
Version: 1.05
Author URI: http://bigfishtest.com
*/

include_once( 'bfg_options.php' );
include_once( 'include/bfg.class.php' );
$bfg = new bfg();

$path_to_php_file_plugin = 'bfg/bfg.php';

$bfg->page_title = 'Add BigFish Games Catalog';

$bfg->menu_title = 'Add Big Fish Games';

$bfg->short_description = 'Add Big Fish Games Catalog to your blog.';

$bfg->access_level = 5; // access level

// 1=main menu 2=options 3=manage 4=templates
$bfg->add_page_to = 2;

add_action('admin_menu', array(&$bfg, 'add_admin_menu'));

add_action('deactivate_' . $path_to_php_file_plugin, array(&$bfg, 'deactivate')); 

add_action('activate_' . $path_to_php_file_plugin, array(&$bfg, 'activate'));

function more_reccurences($schedules) {
	$schedules['bfg_timly'] = array('interval' => 21600, 'display' => __('Every 6 Hours'));
	return $schedules;
}
add_filter('cron_schedules', 'more_reccurences');

add_action('bfg_update_catalog_hook', array(&$bfg, 'update_catalog'));
