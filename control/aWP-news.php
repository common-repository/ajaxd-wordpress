<?php

/*
	Plugin Name: AWP News
	Plugin URI: http://ajaxedwp.com
	Description: Downloads updtates from the AJAXed WordPress twitter account.
	Author: Aaron Harun
	Version: 1.0
	Author URI: http://anthologyoi.com/
*/

if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
	AWP_news::init();
}

class AWP_news{

	function init(){
	global $aWP;
		$now = time(); //Version of the current file.
		$news = get_option('awp_news');
		$last = $news[last];
		$twitterID = $news[id];

		if(($now - $last) < 3600)
			return;

		$since = $twitterID;

		if(!$since)
			$since = 1221765925;

		if(ini_get('allow_url_fopen') != '1')
			return;

	
			
		if(version_compare(PHP_VERSION, '5.0.0', '<')){
			$updates_xml = @file_get_contents('http://twitter.com/statuses/user_timeline/ajaxedwp.xml?since_id='.$since,0);
		}else{
			$ctx = stream_context_create(array('http' => array('timeout' => 10)));
			$updates_xml = @file_get_contents('http://twitter.com/statuses/user_timeline/ajaxedwp.xml?since_id='.$since,0,$ctx);
		}

		if($updates_xml == false)
			return;

		require_once(ABSPATH . PLUGINDIR . '/'. AWP_BASE . '/xmlparser.php');
		$updates = AWP::XML($updates_xml);

		if(!is_array($updates['statuses']['status']))
			return;

		$updates = $updates['statuses']['status'];
		$messages = get_option('awp_messages');

		if($updates[1]){
			foreach($updates as $up){
				if($up[id] <= $twitterID){
					$newID = $twitterID; continue;}

				if(!$newID)
					$newID = $up['id'];

				$date = explode ('+', $up['created_at']);
				$items[] = $up['text'] . ' &emdash; Posted: ' . $date;
			}
		}else{
			$newID =  $updates['id'];
			$items[] = $updates['text'];
		}

		for($x = count($items)-1; $x >= 0; $x--){

			if(substr($items[$x],0,9) == 'Donation:'){

				$items[$x] = str_replace('Donation:','',$items[$x]);

				if($awpall['news_no_donation'])
					continue;

			}elseif(substr($items[$x],0,5) == 'Test:'){
				continue;
			}

			$items[$x] = preg_replace('/(http:\/\/\S*)/', '<a href="$1">$1</a>',$items[$x]);
			$messages[] = $items[$x];
		}

		update_option('awp_news',array('last' => $now, 'id' => $newID));

		if($messages){
			$messages[] = time();
			update_option('awp_messages',$messages);
		}
	}



}

?>
