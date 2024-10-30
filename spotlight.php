<?php

/*
Plugin Name: Best Posts Spotlight
Plugin URI: http://g2007.com/blog/gary/index.php/2008/03/30/best-posts-wordpress-widget/
Description: A sidebar widget to show your best posts and a thumbnail of any images in them.
Author: Gary Brewer
Version: 1.0
Author URI: http://g2007.com/blog/gary/
*/

function ara_best_posts($before,$after)
{
	global $wpdb;
	$options = (array) get_option('widget_ara_spotlight');
	$title = $options['title'];
	$list_type = $options['type'] ? $options['type'] : 'ul';
	$numPosts = $options['count'];
	$category = $options['cat'];
	$iwidth = $options [ 'iwidth' ];

	# Articles from database
	$rand_articles	=	ara_get_best_posts($numPosts,$category,$iwidth);

	# Header
	$string_to_echo  =  ($before.$title.$after."\n");

	switch($list_type)
	{
		case "br":
			$string_to_echo	.=	"<p>";
			$line_end	=	"<br />\n";
			$closing	=	"</p>\n";
			break;
		case "p":
			$opening	=	"<p>";
			$line_end	=	"</p>\n";
			break;
		case "ul":
		default:
			$string_to_echo	.=	"<ul>\n";
			$opening	=	"<li>";
			$line_end	=	"</li>\n";
			$closing	=	"</ul>\n";
	}

	for ($x=0;$x<count($rand_articles);$x++ )
	{
		if (strlen($opening) > 0 ) $string_to_echo .= $opening;
		$string_to_echo	.= $rand_articles[$x]['data'];
		if (strlen($line_end) > 0) $string_to_echo .= $line_end;
	}
	if (strlen($closing) > 0) $string_to_echo .= $closing;
	return $string_to_echo;
}

function ara_get_best_posts($numPosts = '5',$category = '', $iwidth = '200') {
	global $wpdb, $wp_db_version;

	if($category == ''):
		$sql = "SELECT $wpdb->posts.ID FROM $wpdb->posts WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'post'";
	else:
		if($wp_db_version >= 6124): // Database structure has changed since WP 2.3
			$sql = "SELECT $wpdb->posts.ID ";
			$sql.= "FROM $wpdb->posts, $wpdb->term_relationships, $wpdb->term_taxonomy ";
			$sql.= "WHERE $wpdb->posts.post_status = 'publish' ";
			$sql.= "AND $wpdb->posts.post_type = 'post' ";
			$sql.= 'AND ';
			$sql.= '( ';
			$sql.= "$wpdb->posts.ID = $wpdb->term_relationships.object_id ";
			$sql.= "AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id ";
			$sql.= "AND $wpdb->term_taxonomy.term_id = $category ";
			$sql.= ')';
		else:
			$sql = "SELECT $wpdb->posts.ID ";
			$sql.= "FROM $wpdb->posts, $wpdb->post2cat ";
			$sql.= "WHERE $wpdb->posts.post_status = 'publish' ";
			$sql.= "AND $wpdb->posts.post_type = 'post'";
			$sql.= "AND $wpdb->post2cat.post_id = $wpdb->posts.ID ";
			$sql.= "AND $wpdb->post2cat.category_id = $category";
		endif;
	endif;
	$the_ids = $wpdb->get_results($sql);

	$luckyPosts = (array) array_rand($the_ids,($numPosts > count($the_ids) ? count($the_ids) : $numPosts));

	$sql = "SELECT $wpdb->posts.post_title,$wpdb->posts.post_content,$wpdb->posts.post_date, $wpdb->posts.ID";
	$sql .=	" FROM $wpdb->posts";
	$sql .=	" WHERE";
	# Here we minimize number of query to the database by using ORs - just one query needed
	foreach ($luckyPosts as $id)
	{
		if($notfirst) $sql .= " OR";
		else $sql .= " (";
		$sql .= " $wpdb->posts.ID = ".$the_ids[$id]->ID;
		$notfirst = true;
	}
	$sql .= ')';
	$rand_articles = $wpdb->get_results($sql);

	# Give it a shuffle just to spice it up
	shuffle($rand_articles);

	if ($rand_articles)
	{
		foreach ($rand_articles as $item)
		{
		
			$d = $item->post_content;
			$haystack = str_ireplace ( 'img src="', "", stristr ( $d, 'img src="' ) );
			$needle = '"';
			$img = substr($haystack, 0, strpos($haystack, $needle));
			$img = str_ireplace ( $siteHost, "", $img );
			
			
			if ( empty ( $img ) )
			{
				$d = $item->post_content;
				$haystack = str_ireplace ( 'img src=\'', "", stristr ( $d, 'img src\'' ) );
				$needle = '\'';
				$img = substr($haystack, 0, strpos($haystack, $needle));
				$img = str_ireplace ( $siteHost, "", $img );
			}
			
			if ( ! empty ( $img ) )
			{
			
				$words = explode ( ' ', strip_tags ( $item->post_content )  );
				
				$pdate = strtotime ( $item->post_date );
				$desc = '';
				for ( $x = 0; $x < count ( $words ); $x++ )
				{
					$desc .= $words [ $x ] . ' ';
					if ( $x == 20 ) break;
				}
				
				$desc = substr ( $desc, 0, -1 ) . "...";
			
				$data =  '<div style="padding:10px;margin:3px;border:solid 1px #efefef;margin-bottom:10px"><table width="100%"><tr><td><div style="font-weight:bold;padding-bottom:3px;"><a href="' . post_permalink($item->ID) . '">' . $item->post_title . '</a></div>' .
				'</td><td style="text-align:right">' . date ( 'jS F Y', $pdate ) . '</td></tr></table>' . 
				'<center><a href="' . post_permalink($item->ID) . '"><img src="' . get_option('siteurl') . '/wp-content/plugins/spotlight/imageThumb.php?i=' . 
				$img . "&w=" . $iwidth . '" style="border:solid 2px black"/></a></center><div style="font-size:90%;padding-top:3px;">' .
					$desc . "</div></div>";
					
					
			
				$posts_results[] = array('data'=>$data,
			 					'permalink'=>post_permalink($item->ID)
								);
			
			} 


		}
		return $posts_results;
	}
	else
	{
		return false;
	}
}

function widget_ara_spotlight_control() {
	$options = $newoptions = get_option('widget_ara_spotlight');
	if ( $_POST['spotlight-submit'] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST['spotlight-title']));
		$newoptions['type'] = $_POST['spotlight-type'];
		$newoptions['count'] = (int) $_POST['spotlight-count'];
		$newoptions['cat'] = $_POST['spotlight-category'];
		$newoptions [ 'iwidth' ] = $_POST['spotlight-iwidth'];
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_ara_spotlight', $options);
	}
	$list_type = $options['type'] ? $options['type'] : '<ul>';
	$category = $options['cat'] ? $options['cat'] : '';

	# Get categories from the database
	$all_categories = get_categories();
?>
			<div style="text-align:right">
			<label for="spotlight-title" style="line-height:25px;display:block;"><?php _e('Widget title:', 'widgets'); ?> <input style="width: 200px;" type="text" id="spotlight-title" name="spotlight-title" value="<?php echo ($options['title'] ? wp_specialchars($options['title'], true) : 'Best Posts'); ?>" /></label>
			<label for="spotlight-iwidth" style="line-height:25px;display:block;"><?php _e('Thumb Width:', 'widgets'); ?> <input style="width: 200px;" type="text" id="spotlight-iwidth" name="spotlight-iwidth" value="<?php echo ($options['iwidth'] ? wp_specialchars($options['iwidth'], true) : '200'); ?>" /></label>
			<label for="spotlight-type" style="line-height:25px;display:block;">
				<?php _e('List Type:', 'widgets'); ?>
					<select style="width: 200px;" id="spotlight-type" name="spotlight-type">
						<option value="ul"<?php if ($options['type'] == 'ul') echo ' selected' ?>>&lt;ul&gt;</option>
						<option value="br"<?php if ($options['type'] == 'br') echo ' selected' ?>>&lt;br/&gt;</option>
						<option value="p"<?php if ($options['type'] == 'p') echo ' selected' ?>>&lt;p&gt;</option>
					</select>
			</label>
			<label for="spotlight-count" style="line-height:25px;display:block;">
				<?php _e('Post count:', 'widgets'); ?>
					<select style="width: 200px;" id="spotlight-count" name="spotlight-count"/>
						<?php for($cnt=1;$cnt<=10;$cnt++): ?>
							<option value="<?php echo $cnt ?>"<?php if($options['count'] == $cnt) echo ' selected' ?>><?php echo $cnt ?></option>
						<?php endfor; ?>
					</select>
			</label>
			<label for="spotlight-category" style="line-height:25px;display:block;">
				<?php _e('Category:', 'widgets'); ?>
					<select style="width: 200px;" id="spotlight-category" name="spotlight-category"/>
						<option value="">Ignore</option>
						<?php foreach ($all_categories as $cat) { ?>
							<option value="<?php echo $cat->cat_ID ?>"<?php if($options['cat'] == $cat->cat_ID) echo ' selected' ?>><?php echo $cat->cat_name ?></option>
						<?php } ?>
					</select>
			</label>
			<input type="hidden" name="spotlight-submit" id="spotlight-submit" value="1" />
			</div>
<?php
}

function bp_microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function widget_ara_spotlight_init() {

	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This prints the widget
	function widget_ara_spotlight($args) {
		extract($args);
		$start = bp_microtime_float();
		echo $before_widget;
		echo "\n".'<!-- G2007 Spotlight: START -->'."\n";
		echo ara_best_posts($before_title, $after_title);
		echo "\n".'<!-- G2007 Spotlight: END -->'."\n";
		echo $after_widget;
		$end = bp_microtime_float();
		echo "\n".'<!-- Time taken for the 2 queries to complete is '.($end - $start).' seconds -->'."\n";
	}

	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget(array('Spotlight', 'widgets'), 'widget_ara_spotlight');
	register_widget_control(array('Spotlight', 'widgets'), 'widget_ara_spotlight_control');
}

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'widget_ara_spotlight_init');

?>