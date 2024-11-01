<?php
/*
Plugin Name: WP-Auto Trackback Sender
Plugin URI: http://www.autowordpress.biz/
Description: Because its highly customization and complex searching server side mechanisms, this plugin is one of the best <b>SEO</b> (Search Engine Optimization) tools for wordpress making your <b>site traffic</b> and <b>income</b> rise skyhigh! Please visit <a href='http://www.autowordpress.biz/' title='Wordpress Auto Plugins - WP-Auto Trackback Sender page'>http://www.autowordpress.biz/</a> for more info. 
Version: 1.2.7
Author: Dan Fratean
Author URI: http://www.dan.fratean.ro/
*/

/* 
  $Rev: 243384 $
  $Author: alexandrudanfratean $
  $Date: 2010-05-20 09:50:09 +0000 (Thu, 20 May 2010) $
  $Id: ats.php 243384 2010-05-20 09:50:09Z alexandrudanfratean $
*/

/*
User License.
This version of the plugin is free to use. If you want to modify it, don't. Ask us and will do it for you.
(C) Dan Fratean 2010.
*/

error_reporting(E_ERROR | E_PARSE);

include_once("include.php");

register_activation_hook( __FILE__, 'auto_trackback_activate' );
register_deactivation_hook( __FILE__, 'auto_trackback_deactivate' );

add_action('publish_post', 'auto_trackback_addpost');
add_action('wp_print_footer_scripts', 'auto_trackback_trackbacks');


global $wpdb, $wp_version;

function auto_trackback_activate() 
{
  global $wpdb, $wp_version;

  $siteurl = get_option('siteurl');
  $admin_email = get_option('admin_email');

  if (version_compare($wp_version, "2.8.0", "<")) 
  {
    $error = "Your version of Wordpress is " . $wp_version . " and this plugin requires at least version 2.8.0 -- Please use your browser's back button and then upgrade your version of Wordpress";
    wp_die($error);
  }
  add_option("ATS_max_blogs", '5', '', 'yes');
  add_option("ATS_excerpt", 'I found your entry interesting thus I\'ve added a Trackback to it on my weblog :)', '', 'yes');
  add_option("ATS_encoding", 'UTF-8', '', 'yes');
  add_option("ATS_language", 'lang_en', '', 'yes');
  add_option("ATS_key", md5(get_option('home').time()), '', 'yes');
  add_option("ATS_tag_messages", array(), '', 'yes');
  add_option("ATS_cat_messages", array(), '', 'yes');
  add_option("ATS_tag_messages_on", 'yes', '', 'yes');
  add_option("ATS_cat_messages_on", 'no', '', 'yes');
  add_option("ATS_jobs",array(),'','yes');
  add_option("ATS_tags",array(),'','yes');
  add_option("ATS_trackbacks",array(),'','yes');
  add_option("ATS_work",array(),'','yes');
  add_option("ATS_data",array(),'','yes');
  add_option("ATS_socket_timeout",5,'','yes');
  add_option("ATS_send_timeout",3,'','yes');
}

function auto_trackback_deactivate()
{
  delete_option("ATS_max_blogs");
  delete_option("ATS_excerpt");
  delete_option("ATS_encoding");
  delete_option("ATS_language");
  delete_option("ATS_key");
  delete_option("ATS_tag_messages");
  delete_option("ATS_cat_messages");
  delete_option("ATS_tag_messages_on");
  delete_option("ATS_cat_messages_on");
  delete_option("ATS_jobs");
  delete_option("ATS_tags");
  delete_option("ATS_trackbacks");
  delete_option("ATS_work");
  delete_option("ATS_data");
  delete_option("ATS_socket_timeout");
  delete_option("ATS_send_timeout");
}

function auto_trackback_trackbacks()
{
  flush();

  $ATS_did_something = 0;

  $jobs = ATS_get_jobs();
  if (sizeof($jobs) > 1)
  {
    $url = "http://www.autowordpress.biz/wp-ats/query-blogs";
    $ch = curl_init ($url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,"array=".urlencode(serialize($jobs)));
    $server_output = curl_exec ($ch);
    
    if (strpos($server_output, '<error>0</error>'))
    {
      update_option('ATS_jobs', array());
      if (is_array($jobs))
        foreach($jobs as $job)
          if (is_array($job))
            foreach($job['tags'] as $my_tag)
              ATS_save_tags($my_tag, $job['url']);
    }
    $ATS_did_something = 1;
  }

  if (get_option("ATS_last_query_data", "DEFAULT_VALUE") == "DEFAULT_VALUE")
    add_option("ATS_last_query_data", time(), '', 'yes');
  
  if (!$ATS_did_something && (get_option("ATS_last_query_data") < (time() - 1800)))
  {
    $url = "http://www.autowordpress.biz/wp-ats/query-trackbacks_".get_option('ATS_key');

    $ch = curl_init ($url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    $server_output = curl_exec ($ch);
    curl_close($ch);
    $data = unserialize($server_output);

    if (is_array($data))
    {
      $ch = curl_init ("http://www.autowordpress.biz/wp-ats/recieved-trackbacks_".get_option('ATS_key'));
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
      $server_output = curl_exec ($ch);
      curl_close($ch);
    }
    
    $old_data = ATS_get_data();

    if (is_array($data))
      foreach($data as $value)
        if (!in_array($value, $old_data))
          $old_data[] = $value;

    ATS_set_data($old_data);
    update_option("ATS_last_query_data", time());
    $ATS_did_something = 1;
  }
  
  if (!$ATS_did_something)
  {
    $data = ATS_get_data();
    $new_data = array();
    if (is_array($data))
    foreach($data as $job)
    {
      
      if (!$ATS_did_something)
      {
        if (is_array($job))
        foreach($job as $id => $value)
          $$id = $value;

        if ($blog_url[strlen($blog_url) - 1] != "/")
          $tb = $blog_url."/trackback/";
        else
          $tb = $blog_url."trackback/";

        if (empty($excerpt))
          $excerpt = "I found your entry interesting do I've added a Trackback to it on my weblog :)";

        $target = parse_url($tb);

        if ((isset($target["query"])) && ($target["query"] != ""))
          $target["query"] = "?" . $target["query"];
        else
          $target["query"] = "";

        if ((isset($target["port"]) && !is_numeric($target["port"])) || (!isset($target["port"])))
          $target["port"] = 80;

        // Open the socket
        $tb_sock = fsockopen($target["host"], $target["port"], $errno, $errstr, get_option("ATS_socket_timeout", 5));
        // Something didn't work out, return
        if (is_resource($tb_sock))
        {
          // Put together the things we want to send
          $tb_send = "url=" . rawurlencode($post_url) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blog_name) . "&excerpt=" . rawurlencode($excerpt);
          // Send the trackback
          fputs($tb_sock, "POST " . $target["path"] . $target["query"] . " HTTP/1.1\r\n");
          fputs($tb_sock, "Host: " . $target["host"] . "\r\n");
          fputs($tb_sock, "Content-type: application/x-www-form-urlencoded\r\n");
          fputs($tb_sock, "Content-length: " . strlen($tb_send) . "\r\n");
          fputs($tb_sock, "Connection: close\r\n\r\n");

          fputs($tb_sock, $tb_send);
          // Gather result

          stream_set_blocking($tb_sock, TRUE);
          stream_set_timeout($tb_sock,get_option("ATS_send_timeout", 5));
          $info = stream_get_meta_data($tb_sock);

          $response = "";
          while ((!feof($tb_sock)) && (!$info['timed_out']))
          {
            $response .= fgets($tb_sock, 128);
            $info = stream_get_meta_data($tb_sock);
            flush();
          }

          // Close socket
          fclose($tb_sock);
          // Did the trackback ping work
          strpos($response, '<error>0</error>') ? $return = true : $return = false;
          // send result
          if ($return)
          {
            put_trackback($tag."|".$tb);
          }
        }
        $ATS_did_something = 1;
      }
      else
      {
        $new_data[] = $job;
      }
    }
    ATS_set_data($new_data);
  }
}


function auto_trackback_addpost($post_id) 
{
  global $wpdb;

  if (get_post_type($post_id) != 'post') 
  {
    return;
  }

//Get the new post's info from the wp db
  $sql = "SELECT ID, post_title, post_content, post_author, guid FROM $wpdb->posts WHERE ID='$post_id'";
  $results = $wpdb->get_results($sql);
  $postobject = $results[0];
  $posttags = get_the_tags($postobject->ID);
  $postcats = get_the_category($postobject->ID);
  $post_title = $postobject -> post_title;

  $sql = "select display_name from wp_users where ID = ".$postobject -> post_author;
  $results = $wpdb->get_results($sql);
  $author_object = $results[0];
  $author = $author_object -> display_name;

  $perma_link = get_permalink($postobject->ID);
  $admin_email = get_option('admin_email');
  $blog_name = get_option('blogname ');

  $excerpt = get_option('ATS_excerpt');

  if (is_array($posttags)) 
  {
    $jobs = ATS_get_jobs();
    $work = ATS_get_work();
    foreach ($posttags as $taginfo) 
      if ((!$work[$perma_link]) || !in_array($taginfo->name, $work[$perma_link]))
      {
        $tag = $taginfo->name;
        $my_data = array(
              blog_name => $blog_name,
              author => $author,
              encoding => get_option('ATS_encoding'),
              url => $perma_link,
              title => $post_title,
              excerpt => $excerpt,
              tags => $tag,
              max_blogs => get_option('ATS_max_blogs'),
              language => get_option('ATS_language'),
              key => get_option('ATS_key'),
            );

        if (get_option('ATS_tag_messages_on') == "yes")
        {
          $tag_messages = get_option('ATS_tag_messages');
          if (isset($tag_messages[$tag]) && $tag_messages[$tag] != ATS_DEFAULT_TAG_MESSAGE)
            $my_data['excerpt'] = str_replace('[tag_name]', $tag, $tag_messages[$tag]);
        }
        $jobs[] = $my_data;
        ATS_save_tags($tag, $perma_link);
        update_option('ATS_jobs', $jobs);
      }
  }
  
  if (is_array($postcats))
    if (get_option('ATS_cat_messages_on') == "yes")
      foreach ($postcats as $taginfo) 
        if ((!$work[$perma_link]) || !in_array($taginfo->name, $work[$perma_link]))
        {
          $tag = $taginfo->name;
          $my_data = array(
              premium => 'yes',
              blog_name => $blog_name,
              author => $author,
              encoding => get_option('ATS_encoding'),
              url => $perma_link,
              title => $post_title,
              excerpt => $excerpt,
              tags => $tag,
              max_blogs => get_option('ATS_max_blogs'),
              language => get_option('ATS_language'),
              key => get_option('ATS_key'),
          );

          if (get_option('ATS_cat_messages_on') == "yes")
          {
            $tag_messages = get_option('ATS_cat_messages');
            if (isset($tag_messages[$tag]) && $tag_messages[$tag] != ATS_DEFAULT_CAT_MESSAGE)
              $my_data['excerpt'] = str_replace('[cat_name]', $tag, $tag_messages[$tag]);
          }

          $jobs[] = $my_data;
          ATS_save_tags($tag, $perma_link);
          update_option('ATS_jobs', $jobs);
        }
}

add_action('admin_menu', 'ATS_plugin_menu');

function ATS_plugin_menu() 
{
  add_options_page('WP-Auto Trackback Sender', 'Auto Trackback Sender', 'administrator', ATS_UNIQUE_IDENT, "auto_trackback_html_page");
}

function ctc_get_tags($args = '') {
	extract($args);
	$alltags = get_terms('post_tag', $args);

	$tags = array();

	foreach ($alltags as $tag) {
		if ($tag->count < $minnum || $tag->count > $maxnum)
			continue;
			
		array_push($tags, $tag);
	}

	if (empty($tags)) {
		$return = array();
		return $return;
	}

	$tags = apply_filters('get_tags', $tags, $args);
	return $tags;
}

function auto_trackback_html_page()
{
  if (get_option("ATS_socket_timeout", "ATS_DEFAULT_VALUE") == "ATS_DEFAULT_VALUE")
    add_option("ATS_socket_timeout", 5, '', 'yes');
  if (get_option("ATS_send_timeout", "ATS_DEFAULT_VALUE") == "ATS_DEFAULT_VALUE")
    add_option("ATS_send_timeout", 3, '', 'yes');

  $tags = ctc_get_tags(array('minnum' => 0, 'maxnum' => 100, 'orderby' => 'count', 'order' => 'DESC'));
  $cats = get_categories("show_count=1&use_desc_for_title=0&hierarchical=0hide_empty=1");
?>
<div class="wrap">
<b><a class='ATS_BUTTON' href="http://www.autowordpress.biz/">Upgrade to premium</a></b> - <a class='ATS_BUTTON' href="#main">Settings</a> - <a class='ATS_BUTTON' href="#jq">Job queue</a> - <a class='ATS_BUTTON' href="#50t">Last 50 sent trackbacks</a> - <a class='ATS_BUTTON' href="#50tags">Last 50 tags</a><br>
<div id="icon-tools" class="icon32"><br /></div><h2>Settings</h2>
<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<div class="postbox">
<table>
  <tr>
    <th width="92" scope="row" align="right">Blogs:</th>
    <td>
      <input size="3" name="ATS_max_blogs" type="text" id="ATS_max_blogs" value="<?php echo get_option('ATS_max_blogs'); ?>" />
    </td>
  </tr>
  <tr><th></th><td><span class="description">Maximum number of blogs we will try to send trackbacks for each tag. We recommend a value between <b>5</b> and <b>15</b> (max 40).<br /></span></td></tr>
  <tr>
    <th width="92" scope="row" align="right">Message:</th>
    <td>
      <input size="90" name="ATS_excerpt" type="text" id="ATS_excerpt" value="<?php echo get_option('ATS_excerpt'); ?>" />
    </td>
  <tr><th></th><td><span class="description">The message that we will send with the trackback - ex. "I found your entry interesting so I've added a Trackback to it on my weblog :)".</span></td></tr>
  </tr>
  <tr>
    <th width="92" scope="row" align="right">Encoding:</th>
    <td>
      <input size="10" name="ATS_encoding" type="text" id="ATS_encoding" value="<?php echo get_option('ATS_encoding'); ?>" />
    </td>
  </tr>
  <tr><th></th><td><span class="description"><a href="http://codex.wordpress.org/Glossary#Character_set" target="_blank">Character set</a> used to send the trackback (UTF-8 is recommended. If you need to change it, you can always check <a href="http://en.wikipedia.org/wiki/Character_set">here</a>).</span></td>
  </tr>
  <tr>
    <th width="92" scope="row" align="right">Language:</th>
    <td>
    <select name="ATS_language">
<?
  $languages = array(
          "" => "any language",
          "lang_af" => "Afrikaans",
          "lang_ar" => "Arabic",
          "lang_hy" => "Armenian",
          "lang_be" => "Belarusian",
          "lang_bg" => "Bulgarian",
          "lang_ca" => "Catalan",
          "lang_zh-CN" => "Chinese&nbsp;(Simplified)",
          "lang_zh-TW" => "Chinese&nbsp;(Traditional)",
          "lang_hr" => "Croatian",
          "lang_cs" => "Czech",
          "lang_da" => "Danish",
          "lang_nl" => "Dutch",
          "lang_en" => "English",
          "lang_eo" => "Esperanto",
          "lang_et" => "Estonian",
          "lang_tl" => "Filipino",
          "lang_fi" => "Finnish",
          "lang_fr" => "French",
          "lang_de" => "German",
          "lang_el" => "Greek",
          "lang_iw" => "Hebrew",
          "lang_hi" => "Hindi",
          "lang_hu" => "Hungarian",
          "lang_is" => "Icelandic",
          "lang_id" => "Indonesian",
          "lang_it" => "Italian",
          "lang_ja" => "Japanese",
          "lang_ko" => "Korean",
          "lang_lv" => "Latvian",
          "lang_lt" => "Lithuanian",
          "lang_no" => "Norwegian",
          "lang_fa" => "Persian",
          "lang_pl" => "Polish",
          "lang_pt" => "Portuguese",
          "lang_ro" => "Romanian",
          "lang_ru" => "Russian",
          "lang_sr" => "Serbian",
          "lang_sk" => "Slovak",
          "lang_sl" => "Slovenian",
          "lang_es" => "Spanish",
          "lang_sw" => "Swahili",
          "lang_sv" => "Swedish",
          "lang_th" => "Thai",
          "lang_tr" => "Turkish",
          "lang_uk" => "Ukrainian",
          "lang_vi" => "Vietnamese",
  );
  foreach ($languages as $id => $value)
    if ($id == get_option("ATS_language"))
      echo "<option value='$id' selected>$value</option>";
    else
      echo "<option value='$id'>$value</option>";
?>
    </select>
    </td>
  </tr>
  <tr><th></th><td><span class="description">The plugin will try to send trackbacks towards blogs using the selected language.</span></td>
  </tr>
  <tr>
    <th width="92" scope="row" align="right">Site key:</th>
    <td>
      <? echo get_option('ATS_key'); ?>
    </td>
  </tr>
  <tr><th></th><td><span class="description">Unique key used to identify your blog in our network.</span></td>
  </tr>
  <tr>
    <th width="183" scope="row" align="right">Socket open timeout:</th>
    <td>
      <input size="3" name="ATS_socket_timeout" type="text" id="ATS_socket_timeout" value="<?php echo get_option('ATS_socket_timeout'); ?>" />
    </td>
  </tr>
  <tr><th></th><td><span class="description"><b>Default: 5.</b> The connection timeout, in seconds. Only applies while connecting the socket.</span></td>
  </tr>
  <tr>
    <th width="183" scope="row" align="right">Send trackback timeout:</th>
    <td>
      <input size="3" name="ATS_send_timeout" type="text" id="ATS_send_timeout" value="<?php echo get_option('ATS_send_timeout'); ?>" />
    </td>
  </tr>
  <tr><th></th><td><span class="description"><b>Default: 3.</b> Sets the timeout value in seconds (sending trackback)</span></td>
  </tr>
  <tr><th colspan="3">
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="ATS_max_blogs,ATS_excerpt,ATS_encoding,ATS_language,ATS_socket_timeout,ATS_send_timeout" />
<input type="submit" value="<?php _e('Save Changes') ?>" />
  </th></tr>
</table>
</form>
</div>

<div id="icon-edit-pages" class="icon32"><br /></div><h2 id="jq">Job queue <a class='button-secondary' href="#top">Top</a></h2>
<div class="postbox">
<?
  $jobs = ATS_get_jobs();
?>
<p><b>Articles to be analyzed:</b></p>
<table>
<?
  if (!sizeof($jobs))
  {
?>
Nothing in article queue.
<?
  }
  else
  {
    foreach($jobs as $job)
    {
      if (strlen($job['excerpt']) < 1)
        $job['excerpt'] = get_option("ATS_excerpt");
?>
<tr><th colspan="2" style="color:#DD1212;background:#DDDDDD"><? echo $job['title'];?></th></tr>
<tr><th width="92" scope="row" align="right">Author:</th><td><? echo $job['author'];?></td></tr>
<tr><th width="92" scope="row" align="right">Encoding:</th><td><? echo $job['encoding'];?></td></tr>
<tr><th width="92" scope="row" align="right">Post url:</th><td><? echo $job['url'];?></td></tr>
<tr><th width="92" scope="row" align="right">Blogs/Tag:</th><td><? echo $job['max_blogs'];?></td></tr>
<tr><th width="92" scope="row" align="right">Message:</th><td><? echo $job['excerpt'];?></td></tr>
<tr><th width="92" scope="row" align="right">Tags:</th><td><? echo $job['tags'];?></td></tr>
<tr><th width="92" scope="row" align="right">Language:</th><td><? echo $languages[$job['language']];?></td></tr>
<?
    }
  }
?>
</table>
<?
  $jobs = ATS_get_data();
?>
<p><b>Trackbacs to be sent:</b></p>
<table>
<?
  if (!sizeof($jobs))
  {
?>
Nothing in trackback queue.
<?
  }
  else
  {
    $ii = 0;
    foreach($jobs as $job)
    {
      if (!$ii++)
      {
?>
<tr><td>Tag</td><td>Post url</td></tr>
<?
      }
?>
<tr><td>[<? echo $job['tag'];?>]</td><td><a href='<? echo $job['blog_url'];?>'><? echo $job['blog_url'];?></a></td></tr>
<?
    }
  }
?>
</table>
</div>

<div id="icon-edit-pages" class="icon32"><br /></div><h2 id='50t'>Last 50 sent trackbacks <a class='button-secondary' href="#top">Top</a></h2>
<div class="postbox">
<?
  $lines = get_option("ATS_trackbacks");
  if (!sizeof($lines))
  {
?>
Nothing in trackback log.
<?
  }      
  else
  {
?>
<table>
<tr><th scope="row">Date</th><td>Tag</td><td>Tracked URL</td></tr>
<?
    foreach ($lines as $line)
    {
      $tmp = explode("|", $line);
?>
<tr><th scope="row" align="right"><? echo date("m.d.Y H:i", $tmp[0]);?></th><td>[<? echo $tmp[1]; ?>]</td><td><span class="description"><a href='<? echo $tmp[2];?>'><? echo $tmp[2];?></a></span></td></tr>
<?
    }
?>
</table>
<?
  }
?>
</div>

<div id="icon-edit-pages" class="icon32"><br /></div><h2 id='50tags'>Last 50 tags <a class='button-secondary' href="#top">Top</a></h2>
<div class="postbox">
<?
  $work = ATS_get_work();
  if (!sizeof($work))
  {
?>
Nothing in tag log.
<?
  }      
  else
  {
?>
<table>
<tr><td>Tag</td><td>Post url</td></tr>
<?
    $work = array_reverse($work);

    $nr = 0;
    foreach ($work as $url => $value)
    {
      foreach($value as $tag)
        if ($nr++ < 49)
        {
          
?>
<tr><td>[<? echo $tag; ?>]</td><td><span class="description"><a href='<? echo $url;?>'><? echo $url;?></a></span></td></tr>
<?
        }
    }
?>
</table>
<?
  }
?>
</div>
</div>
<?
}
?>
