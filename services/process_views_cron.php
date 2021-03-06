<?php
require_once('db.inc.php');

function is_mobile($user_agent) {
	return preg_match('/android|bb\d+|meego|mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$user_agent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($user_agent,0,4));
}

function get_view_type($user_agent, $user_agent_read) {
	if (!empty($user_agent_read) && $user_agent!=$user_agent_read) {
		return 'cast';
	}
	if(is_mobile($user_agent)){
		return 'mobile';
	}
	return 'desktop';
}

function get_read_type($user_agent) {
	if(preg_match('/\[via API\]/',$user_agent)){
		return 'api';
	}
	if(is_mobile($user_agent)){
		return 'mobile';
	}
	return 'desktop';
}

$result = query("SELECT ps.play_id, ps.link_id, ps.method, ps.time_spent, ps.total_time, ps.bytes_read, ps.total_bytes, UNIX_TIMESTAMP(ps.created) created, UNIX_TIMESTAMP(ps.last_update) last_update, ps.last_update last_update_date, ps.ip, ps.user_agent, ps.user_agent_read, ps.view_counted FROM play_session ps WHERE archived=0 AND IF(ps.method='time',ps.total_time>0,ps.total_bytes>0)");

while ($row = mysqli_fetch_assoc($result)) {
	if ($row['view_counted']==0) {
		if ($row['method']=='time') {
			$valid_view = ($row['time_spent']/$row['total_time']*100)>50;
		} else { //size
			$valid_view = ($row['bytes_read']/$row['total_bytes']*100)>65;
		}
		if ($valid_view) {
			query("UPDATE play_session SET view_counted=1 WHERE play_id='".escape($row['play_id'])."'");
			query("REPLACE INTO views SELECT ".$row['link_id'].", '".date('Y-m-d', $row['last_update'])."', IFNULL((SELECT clicks FROM views WHERE link_id=".$row['link_id']." AND day='".date('Y-m-d', $row['last_update'])."'),0), IFNULL((SELECT views+1 FROM views WHERE link_id=".$row['link_id']." AND day='".date('Y-m-d', $row['last_update'])."'),1), IFNULL((SELECT time_spent+".$row['total_time']." FROM views WHERE link_id=".$row['link_id']." AND day='".date('Y-m-d', $row['last_update'])."'),".$row['total_time'].")");
			query("INSERT INTO view_log (link_id, date, ip, user_agent, user_agent_read, view_type) VALUES (".$row['link_id'].", '".$row['last_update_date']."', '".escape($row['ip'])."', '".escape($row['user_agent'])."', '".escape($row['user_agent_read'])."', '".get_view_type($row['user_agent'], $row['user_agent_read'])."')");
		}
	}
	if ($row['created']<(date('U')-3600*24*3)){ //3 days
		query("UPDATE play_session SET archived=1 WHERE play_id='".escape($row['play_id'])."'");
	}
}
mysqli_free_result($result);

$result = query("SELECT rs.read_id, rs.file_id, rs.pages_read, rs.total_pages, UNIX_TIMESTAMP(rs.created) created, UNIX_TIMESTAMP(rs.last_update) last_update, rs.last_update last_update_date, rs.ip, rs.user_agent, rs.read_counted, rs.reader_closed FROM read_session rs LEFT JOIN file fi ON rs.file_id=fi.id WHERE rs.archived=0");

while ($row = mysqli_fetch_assoc($result)) {
	if ($row['read_counted']==0) {
		$valid_read = ($row['pages_read']/$row['total_pages']*100)>50;
		if ($valid_read) {
			query("UPDATE read_session SET read_counted=1 WHERE read_id='".escape($row['read_id'])."'");
			query("REPLACE INTO manga_views SELECT ".$row['file_id'].", '".date('Y-m-d', $row['last_update'])."', IFNULL((SELECT clicks FROM manga_views WHERE file_id=".$row['file_id']." AND day='".date('Y-m-d', $row['last_update'])."'),0), IFNULL((SELECT views+1 FROM manga_views WHERE file_id=".$row['file_id']." AND day='".date('Y-m-d', $row['last_update'])."'),1), IFNULL((SELECT pages_read+".$row['total_pages']." FROM manga_views WHERE file_id=".$row['file_id']." AND day='".date('Y-m-d', $row['last_update'])."'),".$row['total_pages'].")");
			query("INSERT INTO manga_view_log (file_id, date, ip, user_agent, read_type) VALUES (".$row['file_id'].", '".$row['last_update_date']."', '".escape($row['ip'])."', '".escape($row['user_agent'])."', '".get_read_type($row['user_agent'])."')");
		}
	}
	if ($row['created']<(date('U')-3600*24*3) || $row['reader_closed']==1){ //3 days or player closed
		query("UPDATE read_session SET archived=1 WHERE read_id='".escape($row['read_id'])."'");
	}
}
mysqli_free_result($result);

echo "All done!\n";
?>
