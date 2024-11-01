<?
/*
  $Rev: 243384 $
  $Author: alexandrudanfratean $
  $Date: 2010-05-20 09:50:09 +0000 (Thu, 20 May 2010) $
  $Id: ats.php 243384 2010-05-20 09:50:09Z alexandrudanfratean $
*/

/*
User License.
This plugin is not free. If you bought it, use it. If not, delete it. If you want to modify it, don't. Ask us and will do it for you.
(C) Dan Fratean 2010.
*/

  define ("USER_AGENT", 'wp-ats 1.2.7');
  ini_set('user_agent', USER_AGENT);

  define("ATS_UNIQUE_IDENT", 'ATS_Auto_Trackback_Sender');
  define("ATS_DEFAULT_TAG_MESSAGE", "I have something similar about [tag_name] on my blog.");
  define("ATS_DEFAULT_CAT_MESSAGE", "I have something similar about [cat_name] on my blog.");

  function ATS_get_jobs()
  {
    return get_option('ATS_jobs');
  }

  function ATS_get_work()
  {
    return get_option('ATS_work');
  }

  function ATS_save_tags($tag, $url)
  {
    $work = ats_get_work();
    if (!isset($work))
      $work = array();
    if (!isset($work[$url]))
      $work[$url] = array();
    $work[$url][] = $tag;
    update_option('ATS_work', $work);
  }

  function ATS_set_data($data)
  {
    update_option('ATS_data', $data);
  }

  function ATS_get_data()
  {
    return get_option('ATS_data');
  }

  function put_trackback($line)
  {
    $lines = get_option("ATS_trackbacks");
    $new_lines = array();
    $new_lines[] = time()."|".$line;
    $nr = 0;
    foreach($lines as $value)
      if ($nr++ < 49)
        $new_lines[] = $value;
        
    update_option("ATS_trackbacks", $new_lines);
  }

?>