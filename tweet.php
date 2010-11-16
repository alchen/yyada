<?php
require_once('config.php');
require_once('core/twitteroauth.php');
require_once('core/settings.php');
require_once('core/theme.php');
require_once('util/settings.php');
require_once('util/url.php');
require_once('util/tweet.php');
require_once('util/tag.php');

session_start();
$theme = get_theme();
$access_token = load_access_token();
$content = array();
$settings = get_settings();
$conn = get_twitter_conn();

function update() {
  global $conn;
  $post_data = array("status" => $_POST['status']);
  if (!empty($_POST['in_reply_to_id']))
    $post_data = array_merge($post_data, array("in_reply_to_status_id" => $_POST['in_reply_to_id']));
  if (!empty($_POST['location'])) {
    list($lat, $long) = explode(',', $_POST['location']);
    $post_data = array_merge($post_data, array("lat" => $lat, "long" => "$long"));
  }
  $conn->post('statuses/update', $post_data);
  header('Location: /');
}

function get_reply_thread($tweet_id) {
  global $conn;
  $ret = array();
  do {
    $t = $conn->get('statuses/show/'.$tweet_id);
    array_push($ret, $t);
    $tweet_id = $t->in_reply_to_status_id_str;
  } while (!empty($tweet_id));
  return $ret;
}

function get_reply_users($tweet_id) {
  global $conn;
  $user_pattern = '/(@[a-zA-Z0-9_]*)/';
  $t = $conn->get('statuses/show/'.$tweet_id);
  preg_match_all($user_pattern, '@'.$t->user->screen_name.' '.$t->text, $matches, PREG_SET_ORDER);
  $ret = array();
  foreach ($matches as $user) {
    if (!in_array($user[0], $ret))
      array_push($ret, $user[0]);
  }
  return implode($ret, ' ').' ';
}

if (!empty($_POST)) {
  update();
} else {
  switch ($_GET['action']) {
  case 'reply':
    $tweets = get_reply_thread($_GET['args']);
    $content['reply_tweet_id'] = $_GET['args'];
    $content['reply_tweet_name'] = '@'.$tweets[0]->user->screen_name.' ';
    break;
  case 'replyall':
    $tweets = get_reply_thread($_GET['args']);
    $content['reply_tweet_id'] = $_GET['args'];
    $content['reply_tweet_name'] = get_reply_users($_GET['args']);
    break;
  default:
    $tweets = $conn->get('statuses/home_timeline');
    break;
  }
  $content = array_merge($content, array('tweets' => $tweets));

  include($theme->get_html_path('tweets'));
}

