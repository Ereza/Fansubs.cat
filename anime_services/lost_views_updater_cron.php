<?php
require_once('db.inc.php');

log_action('lost-views-updater-started', "S'ha iniciat la importació de visualitzacions perdudes");

$result = query("SELECT ps.play_id, ps.link_id, ps.time_spent, UNIX_TIMESTAMP(ps.last_update) last_update, ps.last_update last_update_date,e.duration FROM play_session ps LEFT JOIN link l ON ps.link_id=l.id LEFT JOIN episode e ON l.episode_id=e.id WHERE last_update<=DATE_SUB(NOW(), INTERVAL 2 HOUR)");

$count = 0;
while ($row = mysqli_fetch_assoc($result)) {
	//Treat
	// -- ADAPTATION FROM counter.php close case --
	$min_time = !empty($row['duration']) ? ($row['duration']*60/2) : 30;

	if (!empty($row['time_spent']) && is_numeric($row['time_spent']) && $row['time_spent']>=$min_time) {
		//No need to check max as it's already done in counter.php
		query("REPLACE INTO views SELECT ".$row['link_id'].", '".date('Y-m-d', $row['last_update'])."', IFNULL((SELECT clicks FROM views WHERE link_id=".$row['link_id']." AND day='".date('Y-m-d', $row['last_update'])."'),0), IFNULL((SELECT views+1 FROM views WHERE link_id=".$row['link_id']." AND day='".date('Y-m-d', $row['last_update'])."'),1), IFNULL((SELECT time_spent+".$row['time_spent']." FROM views WHERE link_id=".$row['link_id']." AND day='".date('Y-m-d', $row['last_update'])."'),".$row['time_spent'].")");
		query("INSERT INTO view_log (link_id, date) VALUES (".$row['link_id'].", '".$row['last_update_date']."')");
		$count++;
	}
	//Else, discard and not even report it: opened and closed in too little time (less than min)
	// -- END ADAPTATION FROM counter.php close case --
	query("DELETE FROM play_session WHERE play_id='".escape($row['play_id'])."'");
}
mysqli_free_result($result);

log_action('lost-views-updater-finished', "S'ha completat la importació de visualitzacions perdudes (recuperades: $count)");

echo "All done!\n";
?>