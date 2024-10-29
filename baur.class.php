<?php

class baur_Plugin {
    function __construct() {
        
		add_action('admin_menu', array($this,'baur_admin_menu'));
        add_action('admin_init',  array($this, 'baur_init'));
		add_action('transition_post_status',array($this, 'clear_user_post_ranks'),10,3);
		add_action('transition_comment_status',array($this, 'clear_user_comments_ranks'),10,3);
		//shortcode
		add_shortcode('user_rank',array($this,'user_rank_shortcode'));
		add_shortcode('user_rank_top',array($this,'user_rank_top_shortcode'));
		add_filter('the_content',array($this,'automagically_rank'));
    }
	 
	//init
	public function baur_init(){
		register_setting( 'baur_Options', 'baur',array($this,'ba_update_users_data'));
		$options = $this->ba_ur_get_option('baur');
	}
	
	//admin menu
	public function baur_admin_menu() {
		add_options_page('Bainternet User Ranks', 'Bainternet User Ranks', 'manage_options', 'ba_user_rank', array($this,'ba_user_rank_options'));
	}
	
	//get user points
	public function ba_get_user_points($user_id ,$title = false){
		if (!isset($user_id)){return false;}
		$re = get_user_meta($user_id, 'ba_ur', true);
		if ($re){
			if($title){
				return $re;
			}else{
				return $re['points'];
			}
		}else{
			$options = $this->ba_ur_get_option();
			switch($options['count']){
				case '1':
				//only posts
					$user_count_points = $this->get_user_posts_count($user_id) * $options['post'];
				break;
				case '2':
				//only comments
					$user_count_points = $this->get_user_comment_count($user_id) * $options['comment'];
				break;
				case '3':
				//Both posts and comments
				$user_count_points = ($this->get_user_posts_count($user_id) * $options['post']) + ($this->get_user_comment_count($user_id) * $options['comment']);
				break;
			}
			$te = __('Not Ranked Yet');
			foreach($options['levels'] as $key ){
				if ($user_count_points > $key['count']){
					$te = $key['title'];
				}else{
					break;
				}
			}
			$re = array('points' => $user_count_points, 'title' => $te);
			update_user_meta($user_id, 'ba_ur', $re);
			if ($title){
				return $re;
			}
			return $user_count_points;
		}
	}
	
	//options
	public function ba_ur_get_option(){
		$temp = array(
		'levels' => array(
			array('title' => 'noobie' ,'count'=> 100),
			array('title' => 'advanced' ,'count'=> 1000),
			array('title' => 'expert' ,'count'=> 3000)),
		'count' => 1,
		'post' => 10,
		'comment' => 2,
		'auto' => 0,
		'location' => 0,
		'template' => '<span class="user_login">[user-login]<span><br/><span class="user_points"><small>[points]</small></span><br /><span class="user_title">[title]</span>'
		);
		
		$options = get_option('baur');
		 if (!empty($options)){
		 
			if (!empty($options['levels'])){
				 unset($temp['levels']);
				 $temp['levels'] = $options['levels'];
			}
			if (!empty($options['count'])){
				$temp['count'] = $options['count'];
			}
			if (!empty($options['post'])){
				$temp['post'] = $options['post'];
			}
			if (!empty($options['comment'])){
				$temp['comment'] = $options['comment'];
			}
			if (!empty($options['auto'])){
				$temp['auto'] = $options['auto'];
			}
			if (!empty($options['template'])){
				$temp['template'] = $options['template'];
			}
			if (!empty($options['location'])){
				$temp['location'] = $options['location'];
			}
		}
		
		update_option('baur', $temp);
		//delete_option('baur');
		return $temp;
		
		
	}
	
	//user post count
	public function get_user_posts_count($user_ID){
		$args = array('author' => $user_ID, 'posts_per_page' => -1);
		$the_query = new WP_Query( $args );
		if ($the_query->post_count > 0){
			return $the_query->post_count;
		}
		else{
			return 0;
		}
	}

	//user comment count
	public function get_user_comment_count($user_ID){
		global $wpdb;
		$where = 'WHERE comment_approved = 1 AND user_id = '.$user_ID;
		$comment_counts = $wpdb->get_results("SELECT user_id, COUNT( * ) AS total
				FROM {$wpdb->comments}
				{$where}
				GROUP BY user_id
			", object);
		if ($comment_counts[0]->total > 0 ){
			return $comment_counts[0]->total;
		}else{
			return 0;
		}
	}

	//options page
	public function ba_user_rank_options(){
		
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32">
			<?php if (isset($_POST['Update_data'])){echo 'good'; }?>
			</div><h2><?php echo __('Bainternet User Ranks'); ?></h2>
			<h3><?php echo __('General settings'); ?></h3>
			<form method="post" action="options.php">
			<?php settings_fields('baur_Options');
				$options = $this->ba_ur_get_option('baur');
			?>
			<?php //print_r($options); ?>

			<table class="form-table">

			<tr valign="top">
			<th scope="row"><?php echo __('Rank Based on:'); ?></th>
			<td>
				<input type="radio" name="baur[count]" value="1" <?php if (isset($options['count']) && $options['count'] == 1 ) echo ' checked'; ?> /><?php echo __('Posts count.'); ?> <br />
				<input type="radio" name="baur[count]" value="2" <?php if (isset($options['count']) && $options['count'] == 2 ) echo ' checked'; ?> /><?php echo __('Comments count.'); ?> <br />
				<input type="radio" name="baur[count]" value="3" <?php if (isset($options['count']) && $options['count'] == 3 ) echo ' checked'; ?> /><?php echo __('Both Posts and Comments count.'); ?> </td>
			</tr>
			 
			<tr valign="top">
			<th scope="row"><?php echo __('The Rank Titles:'); ?></th>
			<td><div id="ranks">
				<?php
				$c = 0;
				
				foreach($options['levels'] as $key ){
				
					echo '<p> Title <input type="text" name="baur[levels]['.$c.'][title]" value="'.$key['title'].'" /> -- Count Above : <input type="text" name="baur[levels]['.$c.'][count]" value="'.$key['count'].'" /><span class="remove">Remove</span></p>';
					$c = $c +1;
				}?>
				<span id="here"></span>
				<span class="add"><?php echo __('Add Titles'); ?></span>
				<script>
					jQuery(document).ready(function() {
						var count = <?php echo $c; ?>;
						jQuery(".add").click(function() {
							count = count + 1;
							//jQuery("#ranks > p:first-child").clone(true).insertBefore("#here");
							jQuery('#here').append('<p> Title <input type="text" name="baur[levels]['+count+'][title]" value="" /> -- Count Above : <input type="text" name="baur[levels]['+count+'][count]" value="" /><span class="remove">Remove</span></p>' );
							return false;
						});
						jQuery(".remove").click(function() {
							jQuery(this).parent().remove();
						});
					});
				</script>
			</div></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php echo __('Each Post counts as how many points'); ?></th>
			<td><input type="text" name="baur[post]" value="<?php echo $options['post']; ?>" /></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php echo __('Each Comment counts as how many points'); ?></th>
			<td><input type="text" name="baur[comment]" value="<?php echo $options['comment']; ?>" /></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php echo __('Auto insert options'); ?></th>
			<td>
			<p><?php _e('Insert Automagically?:'); ?> <input type="checkbox" name="baur[auto]" value="1" <?php if ($options['auto'] == 1){ echo ' checked';} ?> /></p><br/>
			<p><?php _e('Location:'); ?><br />
				<input type="radio" name="baur[location]" value="0" <?php if ($options['location'] == 0 ) echo ' checked'; ?> /><?php echo __('Before Post Content.'); ?> <br />
				<input type="radio" name="baur[location]" value="1" <?php if ($options['location'] == 1 ) echo ' checked'; ?> /><?php echo __('After Post Content.'); ?> <br />
				<input type="radio" name="baur[location]" value="2" <?php if ($options['location'] == 2 ) echo ' checked'; ?> /><?php echo __('Both Before and After Post Content'); ?><br /></p>
			<p><?php _e('Template:'); ?> </p><textarea  style="width: 320px; height: 120px;" name="baur[template]" id="baur[template]"><?php echo $options['template']; ?></textarea><br />
			</td>
			</tr>
			</table>
			<div>
				<h3><?php echo __('Bainternet User Ranks notes:'); ?></h3>
				<ul style="background-color: #ffffff;">
					<li>* Any feedback or suggestions are welcome at <a href="http://en.bainternet.info/2011/wordpress-user-ranking">plugin homepage</a></li>
					<li>* <a href="http://wordpress.org/tags/bainternet-user-ranks?forum_id=10">Support forum</a> for help and bug submittion</li>
					<li>* Also check out <a href="http://en.bainternet.info/category/plugins">my other plugins</a></li>
					<li>* And if you like my work <a href="http://en.bainternet.info/donations">make a donation</a></li>
				</ul>
				<p><?php echo __('Since Version 1.0 and higher the plugin "cache\'s" the user ranks and titles at the user meta table <br />in the database to speed up the plugin\'s functions and make less call to the database, instead of running all calculations on the fly. <br /> The plugin will update every time a user\'s post is published or when a comment is approved.<br /><strong>If you changed or update the settings the saved user meta will be removed and recreated on its own.</strong>'); ?></p>
			</div>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			</form>
		</div>
		<?php
	}
	
	//update users meta from panel
	public function ba_update_users_data($input){
		global $wpdb;
		$r = $wpdb->get_results( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'ba_ur'");
		foreach ($r as $u){
			delete_user_meta( $u->user_id, 'ba_ur');
			delete_option('ba_ur_top');
			//ba_update_user_data($u->user_id);
		}
		return $input;
		//__('Saved user data updated!');
	}
	
	//update user meta from panel
	public function ba_update_user_data($user_id){
		$options = $this->ba_ur_get_option();
		switch($options['count']){
			case '1':
				//only posts
				$user_count_points = $this->get_user_posts_count($user_id) * $options['post'];
				$user_count_points = apply_filters('user_ranks_post_only_count',$user_count_points,$user_id);
				break;
			case '2':
				//only comments
				$user_count_points = $this->get_user_comment_count($user_id) * $options['comment'];
				$user_count_points = apply_filters('user_ranks_comment_only_count',$user_count_points,$user_id);
				break;
			case '3':
				//Both posts and comments
				$user_count_points = ($this->get_user_posts_count($user_id) * $options['post']) + ($this->get_user_comment_count($user_id) * $options['comment']);
				$user_count_points = apply_filters('user_ranks_both_count',$user_count_points,$user_id);
				break;
		}
		$te = __('Not Ranked Yet');
		foreach($options['levels'] as $key ){
			if ($user_count_points > $key['count']){
				$te = $key['title'];
			}else{
				break;
			}
		}
		$te = apply_filters('user_rank_title',$te,$user_id);
		$re = array('points' => $user_count_points, 'title' => $te);
		$re = apply_filters('user_rank_count_and_title',$re,$user_id);
		update_user_meta($user_id, 'ba_ur', $re);
	}
	
	//remove saved data on post status changed
	public function clear_user_post_ranks($new_status, $old_status, $post){
		delete_user_meta( $post->post_author, 'ba_ur');
		delete_option('ba_ur_top');
	}
	
	//remove saved data on comment status changed
	public function clear_user_comments_ranks($new_status, $old_status, $comment){
		delete_user_meta( $comment->user_id, 'ba_ur');
		delete_option('ba_ur_top');
	}
	//get top users
	public function get_top_users($num){
		$top = get_option('ba_ur_top');		
		if (!empty($top)){
			return $top;
		}
		//if not
		global $wpdb;
		$r = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->users LIMIT $num"));
		foreach ($r as $u){
			$re = array();
			$temp = $this->ba_get_user_points($u->ID,true);
			$re['user_id'] = $u->ID;
			$re['points'] = $temp['points'];
			$re['title'] = $temp['title'];
			$top_u[] = $re;
		}
		//sort order
		usort($top_u, array($this,"arr_sort"));
		update_option('ba_ur_top',$top_u);
		return $top_u;
	}
	
	//array sort helper
	function arr_sort($a, $b){
		if ($a['points'] == $b['points']) {
			return 0;
		}
		return ($a['points'] < $b['points']) ? -1 : 1;
	}
	
	//user_rank shortcode
	public function user_rank_shortcode($atts,$content = null){
		 extract(shortcode_atts(array(
			"user_id" => ''
		), $atts));
		
		if ($content == null){
			$content = '<span class="user_login">[user-login]<span><br/><span class="user_points"><small>[points]</small></span><br /><span class="user_title">[title]</span>';
		}
		
		if ($user_id == ''){
			global $post;
			$user_id = $post->post_author;
		}
		$u = $this->ba_get_user_points($user_id,true);
		$user_info = get_userdata($user_id);
		$re = str_replace('[points]', $u['points'],$content);
		$re = str_replace('[title]', $u['title'],$re);
		$re = str_replace('[user-login]', $user_info->user_login,$re);
		$re = str_replace('[user-nicename]', $user_info->user_nicename,$re);
		$re = str_replace('[user-email]', $user_info->user_email,$re);
		$re = str_replace('[display-name]', $user_info->display_name,$re);
		$re = str_replace('[user-firstname]', $user_info->user_firstname,$re);
		$re = str_replace('[user-lastname]', $user_info->user_lastname,$re);
		$re = str_replace('[user-description]', $user_info->user_description,$re);
		return apply_filters('user_rank_shortcode',$re,$user_id);
	}
	
	//top ranked shortcode
	public function user_rank_top_shortcode($atts,$content = null){
		 extract(shortcode_atts(array(
			"number" => '5',
			"item_wrapper" => 'li',
			"container" => 'ul'
		), $atts));
		//default template
		if ($content == null){
			$content = '<span class="user_login">[user-login]<span><br/><span class="user_points"><small>[points]</small></span><br /><span class="user_title">[title]</span>';
		}
	/*	
	ID
	user_login
    user_pass
    user_nicename
    user_email
    user_url
    user_registered
    display_name 
	user_firstname
	user_lastname
	user_description
	*/
		$return = '';
		$users = $this->get_top_users($number);
		foreach ($users as $us){
			$user_info = get_userdata($us['user_id']);
			$re = "<".$item_wrapper.">";
			$re .= str_replace('[points]', $us['points'],$content);
			$re = str_replace('[title]', $us['title'],$re);
			$re = str_replace('[user-login]', $user_info->user_login,$re);
			$re = str_replace('[user-nicename]', $user_info->user_nicename,$re);
			$re = str_replace('[user-email]', $user_info->user_email,$re);
			$re = str_replace('[display-name]', $user_info->display_name,$re);
			$re = str_replace('[user-firstname]', $user_info->user_firstname,$re);
			$re = str_replace('[user-lastname]', $user_info->user_lastname,$re);
			$re = str_replace('[user-description]', $user_info->user_description,$re);
			$re .= "</".$item_wrapper.">";
			$return .= $re;
		}
		$re = "<".$container.">".$return."</".$container.">";
		return apply_filters('user_rank_top',$re);
	}
	
	public function automagically_rank($content){
		global $post;
		$options = $this->ba_ur_get_option('baur');
		if ($options['auto'] != 1){
			return $content;
		}
		//if ($post->post_type == 'page'){return $content;}
		$template = $options['template'];
		$u = $this->ba_get_user_points($post->post_author,true);
		$user_info = get_userdata($post->post_author);
		$re = str_replace('[points]', $u['points'],$template);
		$re = str_replace('[title]', $u['title'],$re);
		$re = str_replace('[user-login]', $user_info->user_login,$re);
		$re = str_replace('[user-nicename]', $user_info->user_nicename,$re);
		$re = str_replace('[user-email]', $user_info->user_email,$re);
		$re = str_replace('[display-name]', $user_info->display_name,$re);
		$re = str_replace('[user-firstname]', $user_info->user_firstname,$re);
		$re = str_replace('[user-lastname]', $user_info->user_lastname,$re);
		$re = str_replace('[user-description]', $user_info->user_description,$re);
		$ret = '<div class="ba_user_rank">'.$re.'</div>';
		switch($options['location']){
			case '0':
				//above posts
				$return = $ret . $content;
				break;
			case '1':
				//under posts
				$return = $content . $ret;
				break;
			case '2':
				//Both before and after
				$return = $ret .  $content . $ret;
				break;
		}
		return apply_filters('user_rank_shortcode',$return,$post->post_author);
	}
	
}//end class