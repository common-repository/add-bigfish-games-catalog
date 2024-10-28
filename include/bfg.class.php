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
To address on an e-mail isestnestmi at mail.ru or ICQ 338900566
To write to me it is necessary in English or in Russian. I do not know other languages!

                See example at http://BigFishTest.com/ or http://www.ShuvaevGames.com
*/

class bfg
{
	var $page_title;
	var $menu_title;
	var $access_level;
	var $add_page_to;
	var $short_description;
	var $admin_options_name = 'bfg_options';
	var $admin_options;

	function bfg()
	{
	}

	function get_options()
	{
	}
	function update_options()
	{
	}

    function add_admin_menu()
	{
	global $path_to_php_file_plugin;
	if ( $this->add_page_to == 1 )
		add_menu_page($this->page_title,
			$this->menu_title, $this->access_level,
			$path_to_php_file_plugin, array(&$this, 'admin_page'));

	elseif ( $this->add_page_to == 2 )
		add_options_page($this->page_title,
			$this->menu_title, $this->access_level,
			$path_to_php_file_plugin, array(&$this, 'admin_page'));

	elseif ( $this->add_page_to == 3 )
		add_management_page($this->page_title,
			$this->menu_title, $this->access_level,
			$path_to_php_file_plugin, array(&$this, 'admin_page'));			

	elseif ( $this->add_page_to == 4 )
		add_theme_page($this->page_title,
			$this->menu_title, $this->access_level,
			$path_to_php_file_plugin, array(&$this, 'admin_page'));
	}
	function activate()
	{
		global $bfg_cron;
		if(!$bfg_cron) {
			return;
		}
		if (!wp_next_scheduled('bfg_update_catalog_hook')) {
			wp_schedule_event( time(), $bfg_cron, 'bfg_update_catalog_hook' );
		}
	}

	function deactivate()
	{
		if (wp_next_scheduled('bfg_update_catalog_hook')) {
			wp_clear_scheduled_hook('bfg_update_catalog_hook');
		}
	}

function admin_page()
{
global $bfg_xml_url;
	echo <<<EOF
<div class="wrap">
<h2>{$this->page_title}</h2>
{$this->short_description}
EOF;

	if (isset($_POST['UPDATE'])) {

	$myuser=$_REQUEST['login'];
	$typegames=$_REQUEST['typegames'];
	$bfg_max_games_once=$_REQUEST['count'];

	$file_name=$file_name=dirname(__FILE__).'/login.txt'; $w=fopen($file_name,'w');	fwrite($w,$myuser); fclose($w);
	$file_name=$file_name=dirname(__FILE__).'/typegames.txt'; $w=fopen($file_name,'w'); fwrite($w,$typegames); fclose($w);
	$file_name=$file_name=dirname(__FILE__).'/count.txt'; $w=fopen($file_name,'w'); fwrite($w,$bfg_max_games_once); fclose($w);

	if ($typegames=='mac') {$bfg_xml_url = 'http://rss.bigfishgames.com/rss.php?username='.$myuser.'&type=4&gametype=mac&local=en';} else {
        $typest = explode("_",$typegames);
	$bfg_xml_url = 'http://rss.bigfishgames.com/rss.php?username='.$myuser.'&type=4&gametype='.$typest[1].'&local='.$typest[0];
	if ($typest[0]=='de') {$bfg_genres = array(); $bfg_genres=$bfg_genres_de;}
	if ($typest[0]=='es') {$bfg_genres = array(); $bfg_genres=$bfg_genres_es;}
	if ($typest[0]=='fr') {$bfg_genres = array(); $bfg_genres=$bfg_genres_fr;}
	if ($typest[0]=='jp') {$bfg_genres = array(); $bfg_genres=$bfg_genres_jp; $bfg_images = array(); $bfg_images=$bfg_images_jp;}
	}

	print($this->update_catalog($bfg_xml_url,$bfg_max_games_once,false));
		echo '</p>';
	}
	else {
		$this->view_options_page();
	}
	echo '</div>';
}
function update_catalog($bfg_xml_url,$bfg_max_games_once,$mail_report = true) {
	$output = '';
	if($mail_report) {
		$NL = "\n";
	}
	else {
		$NL = '<br />';
	}
	set_time_limit(600);
	require_once(dirname(__FILE__).'/site_getXml.class.php');
	require_once(dirname(__FILE__).'/site_cache.class.php');
	require_once(dirname(__FILE__).'/site_gameXml.class.php');
	require_once(dirname(__FILE__).'/site_parse.class.php');
	global $bfg_cache_lifetime;
	$xmlUrl = $bfg_xml_url;

	$sg = new site_getXml();
	if(!$mail_report) {
		$sg->_showDebug = 1;
	}
	$sc = new site_cache(); 
	$sxml = new site_gameXML(); 
	if(!$mail_report) {
		$sxml->_showDebug = 1;
	}
	$sc->set_path($bfg_cache_path);
	$sc->set_lifetime($bfg_cache_lifetime);
	if(!$mail_report) {
		$sc->_showDebug = 1;
	}
	$sc->set_file('bfgxml');
	$sp = new site_parse(); 
	if(!$mail_report) {
		$sp->_showDebug = 1;
	}
		if($sc->require_newfile()){
			$xmlSource = $sg->getRemoteXmlFile($xmlUrl);
			$sc->set_source($xmlSource);
			$sc->write_file();
			if(empty($xmlSource)){
				$xmlSource = $sc->read_file();
			}
		}else{
			$xmlSource = $sc->read_file();
		}
		  $games = $sxml->xml_parser_init($xmlSource);
		 
			global $wpdb;
			global $bfg_post;
			global $bfg_images;
			global $bfg_genres;
			global $bfg_add_meta;
			global $bfg_update_meta;
			global $bfg_min_rating;
			global $bfg_start_date;
			$output .= 'Feed URL: '. $xmlUrl. $NL;
			$num_games=0;
			$added_games=0;

			foreach ($games as $game ) {
				$skip = 0;
				if($game['GAMENAME'] == '') {
					continue;
				}
				if($added_games >= $bfg_max_games_once)
					break;
				$output .= $game['GAMENAME'].' - ';
				$num_games++;
				if($game['GAMERANK'] < 1)
					$game['GAMERANK'] = 5555;
				if($game['GAMERANK'] > $bfg_min_rating) {
					$output .= 'skipped (rating)'. $NL;
					$skip = 1;
				}
				if(strtotime($game['RELEASEDATE']) < strtotime($bfg_start_date)) {
					$output .= 'skipped (date)' . $NL;
					$skip = 1;
				}



				
				$post = array();
				$post['post_title'] = $wpdb->escape(trim($sp->parse_layout($bfg_post['post_title'], $game)));
				$post['post_name'] = sanitize_title($sp->parse_layout($bfg_post['post_name'], $game));
				$game['POSTNAME'] = $post['post_name'];
				$post['post_date_gmt'] = strtotime($game['RELEASEDATE']);
				$post['post_date_gmt'] = gmdate('Y-m-d H:i:s', $post['post_date_gmt']);
				$post['post_date'] = get_date_from_gmt($post['post_date_gmt']);
				$category = array();
				$numcat = 0;
				$category[0] = $bfg_genres["{$game['GENREID']}"]['name'];
				$numcat ++;
				$post['post_excerpt'] = $wpdb->escape(trim($sp->parse_layout($bfg_post['post_excerpt'], $game)));
                                $post['post_content'] = $wpdb->escape(trim($sp->parse_layout($bfg_post['post_content'], $game)));
				$post['post_author'] = 1;
				$post['post_status'] = $bfg_post['post_status'];
				$post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '".$post['post_title']."'");
				if(!$post_id) {
					$post_id = $wpdb->get_var("SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '".$bfg_add_meta['GAMEID']."' AND wpostmeta.meta_value = '".$game['GAMEID']."'");
				}
				if($post_id) {
//					$output .= 'already added..' . $NL;
					foreach($bfg_update_meta as $key => $value) {
						if($game[$key] != '') {
							if(get_post_meta($post_id, $value, true) <> '') {
								update_post_meta($post_id, $value, $game[$key]);
							}
							else {
								add_post_meta($post_id, $value, $game[$key]);
							}
						}
				    }
				unset($post);
			}
			elseif($skip == 0) {

				$post_id = wp_insert_post($post);
				if ( is_wp_error( $post_id ) ) {
					$output .= 'WP Error' . $NL;
				}
				elseif (!$post_id) {
					$output .= 'Couldn\'t get post ID' . $NL;
				}
				else {
					foreach($bfg_add_meta as $key => $value) {
						if($game[$key] != '') {
							add_post_meta($post_id, $value, $game[$key]);
						}
				    }
					foreach($bfg_images as $key => $value) {
						if($game[$key] != '') {
					    	@copy($game[$key], ABSPATH . $sp->parse_layout($value, $game));

					    }
				    }
					if (0 != count($category)) {
						wp_set_object_terms($post_id, $category, 'category', false);
					}
				}
				$output .= 'addeed..' . $NL;
				unset($post);
				$added_games++;

			}
		}
	$output .= "Games addeed: {$added_games} (total {$num_games})$NL";
	if($mail_report) {
		mail(get_option('admin_email'), get_option('siteurl') . ' updating..', $output);
	}
	return $output;
}
function view_options_page()
{
global $bfg_xml_url;
$file_name=dirname(__FILE__).'/login.txt'; $w=fopen($file_name,'r'); $login = fgets($w); fclose($w);
$file_name=dirname(__FILE__).'/typegames.txt'; $w=fopen($file_name,'r'); $typegames = fgets($w); fclose($w);
$file_name=dirname(__FILE__).'/count.txt'; $w=fopen($file_name,'r'); $count = fgets($w); fclose($w);
echo '
<form action="" method="POST">
<br />Your BigFishGames login: <input type="text" name="login" value="'.$login.'"><br />
<br />Max BigFish games once: <input type="text" name="count" value="'.$count.'"><br />
<br />Type BigFish games: <input type="text" name="typegames" value="'.$typegames.'"><br /><br />
Availible en_pc, de_pc, es_pc, fr_pc, jp_pc, mac, en_og, de_og, es_og, fr_og, jp_og.<br />
Example: en_pc - English PC Games, mac - Macintosh Games, en_og - FREE Online English Games.
<br /><br />
<input type="submit" name="UPDATE" value="Add Games">
</form>';
}
} // class




