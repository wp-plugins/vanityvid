<?php
/*
Plugin Name: Vanityvid
Plugin URI: http://vanityvid.com/info/wordpress-plugin
Description: Enable Vanityvid on your Wordpress blog! Turn your profile pic and your readers profile pics into Video!
Version: 1.0.4
Author: Vanityvid
Author URI: http://vanityvid.com
License: GPL2

    Copyright 2010  Vanityvid

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

*/

$vanityvidApiUrl = "http://vanityvid.com";

/*
 * 
 * Main display functions
 * 
 */


/**
 * Writes js-resources script tag in <head>
 *
 * @return null
 * 
 */
function vanityvid_head()
{
	global $vanityvidApiUrl;
	
	// add necessary javascript resources in html <HEAD>
	?>
	<script type="text/javascript">
	vanityvid = {};
	vanityvid.wordpress = true;
	</script>
	<script type="text/javascript" src="<?= $vanityvidApiUrl ?>/video/player/js-resources"></script>
	<?php
	
}
add_action('wp_head', 'vanityvid_head');

/**
 * Adds vanityvid="" attribute of email md5 to <img> tag, which upon page loads enables switching of pic to video
 *
 * Added as a filter to 'get_avatar'
 * 
 * @return string <img> tag with vanityvid attribute of email md5
 */
function vanityvid_show($avatar, $id_or_email, $size, $default, $alt)
{
	global $vanityvidApiUrl;
	
	// extract email
	// adapted from plugabble.php
	$email = '';
	if ( is_numeric($id_or_email) ) {
		$id = (int) $id_or_email;
		$user = get_userdata($id);
		if ( $user )
			$email = $user->user_email;
	} elseif ( is_object($id_or_email) ) {
		if ( !empty($id_or_email->user_id) ) {
			$id = (int) $id_or_email->user_id;
			$user = get_userdata($id);
			if ( $user)
				$email = $user->user_email;
		} elseif ( !empty($id_or_email->comment_author_email) ) {
			$email = $id_or_email->comment_author_email;
		}
	} else {
		$email = $id_or_email;
	}

	$vanityvid = str_replace("<img",'<img vanityvid="email:'.md5($email).'"',$avatar);
	return $vanityvid;
}
add_filter('get_avatar', 'vanityvid_show', 10, 5);


/*
 * 
 * Utility Functions
 * 
 */

/**
 * Makes sure kses can receive "vanityvid" attribute for img tag
 *
 * Added as a filter to 'edit_allowedposttags'
 * 
 * @return array allowedposttags with vanityvid attribute allowed
 */
function vanityvid_kses($tags)
{
	$tags['img']['vanityvid'] = array();
	return $tags;
}
add_filter('edit_allowedposttags','vanityvid_kses');

/**
 * Makes sure tinymce can receive "vanityvid" attribute for img tag
 *
 * Added as a filter to 'tiny_mce_before_init'
 * 
 * @return array initArray with vanityvid attribute allowed in extended_valid_elements
 */
function vanityvid_tinymce($initArray)
{
	$vanityvidArray = array ('extended_valid_elements' => 'img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height|hspace|id|ismap<ismap|lang|longdesc|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|src|style|title|usemap|vspace|width|vanityvid]');
	if ($initArray['extended_valid_elements'])
		$initArray['extended_valid_elements'] = $initArray['extended_valid_elements'] . "," . $vanityvidArray['extended_valid_elements'];
	else
		$initArray['extended_valid_elements'] = $vanityvidArray['extended_valid_elements']; 
	
	return $initArray;
}
add_filter('tiny_mce_before_init','vanityvid_tinymce');

/*
 * 
 * Admin Configuration and Plugin Activation
 * 
 */

function vanityvid_admin_menu()
{
	$plugin_page = add_options_page('Vanityvid', 'Vanityvid', 8, __FILE__, 'vanityvid_admin_page');
	add_action( 'admin_head-'. $plugin_page, 'vanityvid_admin_head' );
}

function vanityvid_admin_head()
{
	global $vanityvidApiUrl, $current_user;
	
	// add necessary javascript resources in html <HEAD>
	?>
	<script type="text/javascript">
	vanityvid = {};
	vanityvid.enable_rec_button = true;
	vanityvid.logged_in_user_id = '<?= md5($current_user->user_email) ?>';
	vanityvid.logged_in_email = '<?= $current_user->user_email ?>';
	</script>
	<script type="text/javascript" src="<?= $vanityvidApiUrl ?>/video/player/js-resources"></script>
	<?php
}

function vanityvid_admin_page()
{
	global $current_user;
	?>
	
	<div class="wrap">
		<h2>Your Vanityvid</h2>
		<div style="border: 1px solid #262626; padding: 5px; float: left;">
			<?= get_avatar($current_user->ID); ?>
		</div>
		<div style="clear:both;"></div>
		<h2>Recording a Vanityvid</h2>
		<div style="width:75%;">
			If you haven't yet recorded a Vanityvid, you can press the 'REC' button on your profile pic above and record or upload a video here.
			<br/><br/>
			After you create a Vanityvid, you will receive an email confirmation to: <strong><?= $current_user->user_email ?></strong>.
			<br/>Upon confirming successfully, your Vanityvid will be visible wherever your profile pic appears on your blog.
			<br/><br/>
			Enjoy!
		</div>
		<h2>Manage Vanityvids</h2>
		<div style="width:75%;">To manage your Vanityvids, please visit the <a target="_blank" href="http://vanityvid.com">Vanityvid Website</a>.</div>
	</div>

	<?php
}
add_action('admin_menu','vanityvid_admin_menu');
?>