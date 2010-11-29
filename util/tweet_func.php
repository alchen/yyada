<?php

function array_get($array, $key, $default=null) {
  if (isset($array[$key])) return $array[$key];
  return $default;
}

function echo_global_var($varname) {
  return $GLOBALS[$varname];
}

function pluralise($word, $count, $show) {
  if ($show) $word = "{$count} {$word}";
  return $word . (($count > 1) ? 's' : '');
}

function local_date($format, $timestamp, $offset) {
  $offset = $offset * 3600;
  return gmdate($format, $timestamp + $offset);
}

function format_time($time, $offset) {
  $units = array(
    'sec'  => array(1, 60),
    'min'  => array(60, 3600),
    'hour' => array(3600, 86400),
  );
  $delta = time() - $time;
  $ret = '';
  foreach ($units as $key => $value) {
    if ($delta < $value[1]) {
      $ret = pluralise($key, floor($delta / $value[0]), true) . ' ago';
      break;
    }
  }
  if ($ret == '') {
    $ret = local_date("m-d", $time, $offset);
  }
  return $ret;
}

function format_tweet($tweet) {
  $list_pattern = '/@([a-zA-Z0-9_]+)\/([a-zA-Z0-9_]+)/';
  $list_replace = '@\1/<a href="/list/\1/\2">\2</a>';
  $user_pattern = '/@([a-zA-Z0-9_]+)/';
  $user_replace = '@<a href="/user/show/\1">\1</a>';
  $tag_pattern = '/#([a-zA-Z0-9_]+)/';
  $tag_replace = '#<a href="/search?data=\1">\1</a>';
  $url_pattern = '/((http|https)\:\/\/[a-zA-Z0-9_\-\+\.\/\?\&\$\@\:\=]+)/';
  $url_replace = '<a href="\1">\1</a>';

  $tweet = preg_replace($list_pattern, $list_replace, $tweet);
  $tweet = preg_replace($user_pattern, $user_replace, $tweet);
  $tweet = preg_replace($tag_pattern, $tag_replace, $tweet);
  $tweet = preg_replace($url_pattern, $url_replace, $tweet);

  return $tweet;
}

function get_mentioned_all($tweet) {
  preg_match_all('/(?P<name>@[a-zA-Z0-9_]+)/', $tweet, $users);
  $ret = array();
  foreach ($users['name'] as $user) {
    if (!in_array($user, $ret))
      array_push($ret, $user);
  }
  return $ret;
}

function is_reply_all($tweet) {
  global $access_token;
  $users = get_mentioned_all($tweet);
  $num = count($users);
  if in_array('@'.$access_token['screen_name'], $users) $num-- ;
  return $num > 1;
}

function is_mentioned($tweet) {
  global $access_token;
  $users = get_mentioned_all($tweet);
  return in_array('@'.$access_token['screen_name'], $users);
}

function get_mentioned_users($tweet) {
  global $access_token;
  $users = get_mentioned_all($tweet);
  $key = array_search('@'.$access_token['screen_name'], $users);
  if ($key) unset($users[$key]);
  return $users > 1;
}

?>


