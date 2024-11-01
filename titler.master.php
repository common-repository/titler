<?php  
/* 
Plugin Name: WP Custom Headlines (aka Titler) 
Plugin URI: http://omniwp.com/plugins/titler-a-wordpress-plugin/ 
Description: Titler gives you complete control over how look and relevant your page/post title look. 
Version: 1.5
Author: Nimrod Tsabari / omniWP
Author URI: http://omniwp.com
*/  
/*  Copyright 2012  omniWP (email : yo@omniwp.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/?>
<?php

define('TITLER_VER', '1.5');
define('TITLER_DIR', plugin_dir_url( __FILE__ ));

/* Titler : Activation */
/* -------------------- */

define('TITLER_NAME', 'Titler');
define('TITLER_SLUG', 'titler_registration');

register_activation_hook(__file__,'omni_titler_admin_activate');
add_action('admin_notices', 'omni_titler_admin_notices');	

function omni_titler_admin_activate() {
	$reason = get_option('omni_plugin_reason');
	if ($reason == 'nothanks') { 
		update_option('omni_plugin_on_list',0);
	} else {		
		add_option('omni_plugin_on_list',0);
		add_option('omni_plugin_reason','');
	}
}

function omni_titler_admin_notices() {
	if ( get_option('omni_plugin_on_list') < 2 ){		
		echo "<div class='updated'><p>" . sprintf(__('<a href="%s">' . TITLER_NAME . '</a> needs your attention.'), "options-general.php?page=" . TITLER_SLUG). "</p></div>";
	}
} 

/*  Titler : Admin Part  */
/* --------------------- */
/* Inspired by Purwedi Kurniawan's SEO Searchterms Tagging 2 Pluging */

function titler_admin() {
	if (omni_titler_list_status()) omni_titler_thank_you(); 
}            

function titler_admin_init() {
	$onlist = get_option('omni_plugin_on_list');
	if ($onlist < '2') add_options_page("Titler| Registration", "Titler| Registration", 1, "titler_registration", "titler_admin");
}

add_action('admin_menu', 'titler_admin_init');

function omni_titler_thank_you() {
	wp_redirect(admin_url());
}

function omni_titler_list_status() {
	$onlist = get_option('omni_plugin_on_list');
	$reason = get_option('omni_plugin_reason');
	if ( trim($_GET['onlist']) == 1 || $_GET['no'] == 1 ) {
		$onlist = 2;
		if ($_GET['onlist'] == 1) update_option('omni_plugin_reason','onlist');
		if ($_GET['no'] == 1) {
			 if ($reason != 'onlist') update_option('omni_plugin_reason','nothanks');
		}
		update_option('omni_plugin_on_list', $onlist);
	} 
	if ( ((trim($_GET['activate']) != '' && trim($_GET['from']) != '') || trim($_GET['activate_again']) != '') && $onlist != 2 ) { 
		update_option('omni_plugin_list_name', $_GET['name']);
		update_option('omni_plugin_list_email', $_GET['from']);
		$onlist = 1;
		update_option('omni_plugin_on_list', $onlist);
	}
	if ($onlist == '0') {
		if (isset($_GET['noheader'])) require_once(ABSPATH . 'wp-admin/admin-header.php');
		omni_titler_register_form_1('titler_registration');
	} elseif ($onlist == '1') {
		if (isset($_GET['noheader'])) require_once(ABSPATH . 'wp-admin/admin-header.php');
		$name = get_option('omni_plugin_list_name');
		$email = get_option('omni_plugin_list_email');
		omni_titler_do_list_form_2('titler_confirm',$name,$email);
	} elseif ($onlist == '2') {
		return true;
	}
}

function omni_titler_register_form_1($fname) {
	global $current_user;
	get_currentuserinfo();
	$name = $current_user->user_firstname;
	$email = $current_user->user_email;
?>
	<div class="register" style="width:50%; margin: 100px auto; border: 1px solid #BBB; padding: 20px;outline-offset: 2px;outline: 1px dashed #eee;box-shadow: 0 0 10px 2px #bbb;">
		<p class="box-title" style="margin: -20px; background: #489; padding: 20px; margin-bottom: 20px; border-bottom: 3px solid #267; color: #EEE; font-size: 30px; text-shadow: 1px 2px #267;">
			Please register the plugin...
		</p>
		<p>Registration is <strong style="font-size: 1.1em;">Free</strong> and only has to be done <strong style="font-size: 1.1em;">once</strong>. If you've register before or don't want to register, just click the "No Thank You!" button and you'll be redirected back to the Dashboard.</p>
		<p>In addition, you'll receive a a detailed tutorial on how to use the plugin and a complimentary subscription to our Email Newsletter which will give you a wealth of tips and advice on Blogging and Wordpress. Of course, you can unsubscribe anytime you want.</p>
		<p><?php omni_titler_registration_form($fname,$name,$email);?></p>
		<p style="background: #F8F8F8; border: 1px dotted #ddd; padding: 10px; border-radius: 5px; margin-top: 20px;"><strong>Disclaimer:</strong> Your contact information will be handled with the strictest of confidence and will never be sold or shared with anyone.</p>
	</div>	
<?php
}

function omni_titler_registration_form($fname,$uname,$uemail,$btn='Register',$hide=0, $activate_again='') {
	$wp_url = get_bloginfo('wpurl');
	$wp_url = (strpos($wp_url,'http://') === false) ? get_bloginfo('siteurl') : $wp_url;
	$thankyou_url = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'].'&amp;noheader=true';
	$onlist_url   = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'].'&amp;onlist=1'.'&amp;noheader=true';
	$nothankyou_url   = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'].'&amp;no=1'.'&amp;noheader=true';
	?>
	
	<?php if ( $activate_again != 1 ) { ?>
	<script><!--
	function trim(str){ return str.replace(/(^\s+|\s+$)/g, ''); }
	function imo_validate_form() {
		var name = document.<?php echo $fname;?>.name;
		var email = document.<?php echo $fname;?>.from;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		var err = ''
		if ( trim(name.value) == '' )
			err += '- Name Required\n';
		if ( reg.test(email.value) == false )
			err += '- Valid Email Required\n';
		if ( err != '' ) {
			alert(err);
			return false;
		}
		return true;
	}
	//-->
	</script>
	<?php } ?>
	<form name="<?php echo $fname;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($activate_again!=1){;?>onsubmit="return imo_validate_form();"<?php }?> style="text-align:center;" >
		<input type="hidden" name="meta_web_form_id" value="1222167085" />
		<input type="hidden" name="listname" value="omniwp_plugins" />  
		<input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
		<input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
		<input type="hidden" name="meta_adtracking" value="omniwp_plugins_adtracking" />
		<input type="hidden" name="meta_message" value="1">
		<input type="hidden" name="meta_required" value="from,name">
		<input type="hidden" name="meta_forward_vars" value="1">	
		 <?php if ( $activate_again == 1 ) { ?> 	
			 <input type="hidden" name="activate_again" value="1">
		 <?php } ?>		 
		<?php if ( $hide == 1 ) { ?> 
			<input type="hidden" name="name" value="<?php echo $uname;?>">
			<input type="hidden" name="from" value="<?php echo $uemail;?>">
		<?php } else { ?>
			<p>Name: </td><td><input type="text" name="name" value="<?php echo $uname;?>" size="25" maxlength="150" />
			<br />Email: </td><td><input type="text" name="from" value="<?php echo $uemail;?>" size="25" maxlength="150" /></p>
		<?php } ?>
		<input class="button-primary" type="submit" name="activate" value="<?php echo $btn; ?>" style="font-size: 14px !important; padding: 5px 20px;" />
	</form>
    <form name="nothankyou" method="post" action="<?php echo $nothankyou_url;?>" style="text-align:center;">
	    <input class="button" type="submit" name="nothankyou" value="No Thank You!" />
    </form>
	<?php
}

function omni_titler_do_list_form_2($fname,$uname,$uemail) {
	$msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to register the plugin.';
	if ( trim($_GET['activate_again']) != '' && $msg != '' ) {
		echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
	}
	?>
	<div class="register" style="width:50%; margin: 100px auto; border: 1px dotted #bbb; padding: 20px;">
		<p class="box-title" style="margin: -20px; background: #489; padding: 20px; margin-bottom: 20px; border-bottom: 3px solid #267; color: #EEE; font-size: 30px; text-shadow: 1px 2px #267;">Thank you...</p>
		<p>A confirmation email has just been sent to your email @ "<?php echo $uemail;?>". In order to register the plugin, check your email and click on the link in that email.</p>
		<p>Click on the button below to Verify and Activate the plugin.</p>
		<p><?php omni_titler_registration_form($fname.'_0',$uname,$uemail,'Verify and Activate',$hide=1,$activate_again=1);?></p>
		<p>Disclaimer: Your contact information will be handled with the strictest confidence and will never be sold or shared with third parties.</p>
	</div>	
	<?php
}

/* Titler : Action Code
 * by Nimrod Tsanari
 * Since ver 1.0
 */

function set_titler($atts,$content=null) {
	global $post;
	$pid = $post->ID;

	if ($content != '') {
		update_post_meta($pid,'titler',$content);
		update_post_meta($pid,'_titler_params',serialize($atts));
	}
}

function elapsed_time($timestamp) {
  $time = time() - $timestamp;
  $result = $time / 3600;
  return $result;
}

function gen_titler($pid,$title) {
	$et = elapsed_time(get_the_time('U'));
	
	$titler = get_post_meta($pid, 'titler', true);
	$output = $titler;
	$params = get_post_meta($pid, '_titler_params',true);
	$params = unserialize($params);
	
	extract(shortcode_atts(array(
		'elapsed_array'		=> '',
		'elapsed_cond'		=> 'gap',
		'elapsed_text'		=> '',
		'user'				=> '',
		'days'				=> '',
		'days_text'			=> '',
		'months'			=> '',
		'months_text'		=> '',
		'years'				=> '',
		'years_text'		=> '',
		'dates'				=> '',
		'dates_text'		=> '',
		'comments'			=> '',
		'comments_text'		=> '',
		'random_text'		=> '',
		'only_single'		=> 'yes',
		'only_loop'			=> 'yes'
		), $params));
	
	$only_single = trim($only_single);
	$only_loop = trim($only_loop);
	
	if ($elapsed_array !== '') {
		$replace = '';
		$elapsed_array = explode('||',$elapsed_array);
		$elapsed_text = explode('||',$elapsed_text);
		for ($i=0; $i < count($elapsed_array); $i++) { 
			$elapsed_array[$i] = intval($elapsed_array[$i]);
			$elapsed_text[$i] = trim($elapsed_text[$i]);
			if ($i != 0) {
				if ($i != (count($elapsed_array) - 1)) {
					if (($elapsed_array[$i-1] <= $et) && ($et <= $elapsed_array[$i])) $replace = $elapsed_text[$i-1];
				} else {
					if (($elapsed_array[$i-1] <= $et) && ($et <= $elapsed_array[$i])) {
						 $replace = $elapsed_text[$i-1];
					} else {
						if ($elapsed_array[$i] < $et) $replace = $elapsed_text[$i];
					}
				}
			}
		}

		$output = str_replace('@elapsed',$replace,$output);
	}
		
	if ($user !== '') {
		global $current_user;
		get_currentuserinfo();
		$user = explode('||',$user);
		$replace = '';
		switch ($user[0]) {
			case 'first':
				$replace = $current_user->user_firstname;
				break;
			case 'last':
				$replace = $current_user->user_lastname;
				break;
			case 'firstlast':
				$replace = $current_user->user_firstname . ' ' . $current_user->user_lastname;
				break;
			case 'lastfirst':
				$replace = $current_user->user_lastname . ' ' . $current_user->user_firstname;
				break;
			case 'last':
				$replace = $current_user->display_name;
				break;
			default:
				break;
		}

		if ((count($user) >= 2) && (trim($replace) === '')) $replace = $user[1];
		
		$output = str_replace('@user',$replace,$output);
	}

	if ($comments !== '') {
		$cn = get_comments_number($pid);
		$replace = '';

		$comments = explode('||',$comments);
		$comments_text = explode('||',$comments_text);
		for ($i=0; $i < count($comments); $i++) { 
			$comments[$i] = intval($comments[$i]);
			$comments_text[$i] = trim($comments_text[$i]);
			if ($i != 0) {
				if ($i != (count($comments) - 1)) {
					if (($comments[$i-1] <= $cn) && ($cn <= $comments[$i])) $replace = $comments_text[$i-1];
				} else {
					if (($comments[$i-1] <= $cn) && ($en <= $comments[$i])) {
						 $replace = $comments_text[$i-1];
					} else {
						if ($comments[$i] < $cn) $replace = $comments_text[$i];
					}
				}
			}
		}

		$output = str_replace('@comment',$replace,$output);
	}

	if ($days !== '') {
		$cur_day = date('N',current_time('timestamp'));
		$replace = '';

		$days = explode('||',$days);
		$days_text = explode('||',$days_text);
		
		for ($i=0; $i < count($days); $i++) { 
			$days[$i] = intval($days[$i]);
			$days_text[$i] = trim($days_text[$i]);
			if ($cur_day == $days[$i]) $replace = $days_text[$i];
		}
		
		$output = str_replace('@day',$replace,$output);
	}

	if ($months !== '') {
		$cur_month = date('n',current_time('timestamp'));
		$replace = '';

		$months = explode('||',$months);
		$months_text = explode('||',$months_text);
		
		for ($i=0; $i < count($months); $i++) { 
			$months[$i] = intval($months[$i]);
			$months_text[$i] = trim($months_text[$i]);
			if ($cur_month == $months[$i]) $replace = $months_text[$i];
		}
		
		$output = str_replace('@month',$replace,$output);
	}

	if ($years !== '') {
		$cur_year = date('Y',current_time('timestamp'));
		$replace = '';

		$years = explode('||',$years);
		$years_text = explode('||',$years_text);
		
		for ($i=0; $i < count($years); $i++) { 
			$years[$i] = intval($years[$i]);
			$years_text[$i] = trim($years_text[$i]);
			if ($cur_year == $years[$i]) $replace = $years_text[$i];
		}
		
		$output = str_replace('@year',$replace,$output);
	}

	if ($dates !== '') {
		$cur_day = date('d',current_time('timestamp'));
		$cur_mon = date('m',current_time('timestamp'));
		$cur_date = $cur_day . $cur_mon;
		$replace = '';

		$dates = explode('||',$dates);
		$dates_text = explode('||',$dates_text);
		
		for ($i=0; $i < count($dates); $i++) { 
			$dates_text[$i] = trim($dates_text[$i]);
			if ($cur_date == $dates[$i]) $replace = $dates_text[$i];
		}
		
		$output = str_replace('@date',$replace,$output);
	}

	if ($random_text !== '') {
		$rands = explode('||',$random_text);
		$replace = '';

		$i = rand(0,count($rands)-1);
		
		$replace = $rands[$i];
		
		$output = str_replace('@random',$replace,$output);
	}

	$show_single = true;
	$show_loop = true;

	$hide_single = (($only_single == 'yes') && (!is_page()) && (!is_single()));
	$hide_loop = (($only_loop == 'yes') && (!in_the_loop()));

	if ($hide_single || $hide_loop) {
		return $title;
	}
	
	return $output;
}

function the_titler($title) {
	if (!is_admin()) {	
		global $post;
		
		$pid = $post->ID;
		
		$titler = get_post_meta($pid, 'titler', true);
		
		if ($title != $post->post_title) return $title;

		if ($titler === '') {
			return $title;
		} else {
			return gen_titler($pid, $post->post_title);
		}
	} else {
		return $title;
	}
}

add_shortcode('titler', 'set_titler');
add_filter('the_title', 'the_titler');
?>