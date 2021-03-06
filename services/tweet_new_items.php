<?php
require_once('db.inc.php');
require_once('common.inc.php');
require_once("libs/codebird.php");

function publish_tweet($tweet){
	global $twitter_consumer_key, $twitter_consumer_secret, $twitter_access_token, $twitter_access_token_secret;

	\Codebird\Codebird::setConsumerKey($twitter_consumer_key, $twitter_consumer_secret);
	$cb = \Codebird\Codebird::getInstance();
	$cb->setToken($twitter_access_token, $twitter_access_token_secret);

	$params = array(
		'status' => $tweet
	);
	$cb->statuses_update($params);
}

function get_shortened_tweet($tweet){
	//Check that it will not exceed 280 characters... and ellipsize if needed
	//280: max tweet limit
	//-23: shortened link
	// -1: line feed
	if (mb_strlen($tweet)>(280-23-1)){
		return mb_substr($tweet, 0, (280-23-1-3-1)).'...';
	} else {
		return $tweet;
	}
}

function exists_more_than_one_version($series_id){
	global $db_connection;
	$result = mysqli_query($db_connection, "SELECT COUNT(*) cnt FROM version WHERE series_id=$series_id AND hidden=0") or die(mysqli_error($db_connection));
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);	
	return ($row['cnt']>1);
}

function exists_more_than_one_version_manga($manga_id){
	global $db_connection;
	$result = mysqli_query($db_connection, "SELECT COUNT(*) cnt FROM manga_version WHERE manga_id=$manga_id AND hidden=0") or die(mysqli_error($db_connection));
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);	
	return ($row['cnt']>1);
}

$last_tweeted_manga_id=(int)file_get_contents('last_tweeted_manga_id.txt');
$last_tweeted_anime_id=(int)file_get_contents('last_tweeted_anime_id.txt');

$new_manga_tweets = array(
	'Tenim un nou manga editat per %2$s a manga.fansubs.cat: «%1$s»!',
	'Hi ha disponible un nou manga editat per %2$s a manga.fansubs.cat: «%1$s»!',
	'Ja podeu llegir el nou manga «%1$s» editat per %2$s a manga.fansubs.cat!',
	'Hem afegit un nou manga editat per %2$s a manga.fansubs.cat: «%1$s»!',
	'Nou manga: «%1$s», editat per %2$s! Seguiu-lo a manga.fansubs.cat!'
);

$new_chapter_number_tweets = array(
	'Ja hi ha disponible el capítol %4$d del manga «%1$s» (editat per %3$s), «%2$s» al web de manga.fansubs.cat!',
	'S\'ha afegit el capítol %4$d del manga «%1$s» (editat per %3$s), «%2$s» al web de manga.fansubs.cat!',
	'Ja podeu llegir el capítol %4$d del manga «%1$s» (editat per %3$s), «%2$s». al web de manga.fansubs.cat!'
);

$new_chapter_number_no_name_tweets = array(
	'Ja hi ha disponible el capítol %4$d del manga «%1$s» (editat per %3$s) al web de manga.fansubs.cat!',
	'Hem afegit el capítol %4$d del manga «%1$s» (editat per %3$s) al web de manga.fansubs.cat!',
	'Ja podeu llegir el capítol %4$d del manga «%1$s» (editat per %3$s) al web de manga.fansubs.cat!'
);

$new_chapter_no_number_tweets = array(
	'Ja hi ha disponible un nou capítol del manga «%1$s» (editat per %3$s) a manga.fansubs.cat: «%2$s».',
	'Hem afegit un nou capítol del manga «%1$s» (editat per %3$s) al web de manga.fansubs.cat: «%2$s».',
	'Ja podeu llegir un nou capítol del manga «%1$s» (editat per %3$s) a manga.fansubs.cat: «%2$s».'
);

$new_chapters_tweets = array(
	'Ja hi ha disponibles %2$d capítols nous del manga «%1$s» (editat per %3$s) al web de manga.fansubs.cat!',
	'Hem afegit %2$d capítols nous del manga «%1$s» (editat per %3$s) al web de manga.fansubs.cat!',
	'Ja podeu llegir %2$d capítols nous del manga «%1$s» (editat per %3$s) al web de manga.fansubs.cat!'
);

$new_anime_tweets = array(
	'Tenim un nou anime %TYPE% per %2$s a anime.fansubs.cat: «%1$s»!',
	'Hi ha disponible un nou anime %TYPE% per %2$s a anime.fansubs.cat: «%1$s»!',
	'Ja podeu mirar l\'anime «%1$s» %TYPE% per %2$s a anime.fansubs.cat!',
	'Hem afegit un nou anime %TYPE% per %2$s a anime.fansubs.cat: «%1$s»!',
	'Nou anime: «%1$s», %TYPE% per %2$s! Seguiu-lo a anime.fansubs.cat!'
);

$new_episode_number_tweets = array(
	'Ja hi ha disponible el capítol %4$d de l\'anime «%1$s» (%TYPE% per %3$s), «%2$s». El trobareu a anime.fansubs.cat!',
	'Hem afegit el capítol %4$d de l\'anime «%1$s» (%TYPE% per %3$s), «%2$s». Mireu-lo al web d\'anime.fansubs.cat!',
	'Ja podeu mirar el capítol %4$d de l\'anime «%1$s» (%TYPE% per %3$s), «%2$s». El teniu al web d\'anime.fansubs.cat!'
);

$new_episode_number_no_name_tweets = array(
	'Ja hi ha disponible el capítol %4$d de l\'anime «%1$s» (%TYPE% per %3$s). El trobareu a anime.fansubs.cat!',
	'Hem afegit el capítol %4$d de l\'anime «%1$s» (%TYPE% per %3$s). Mireu-lo al web d\'anime.fansubs.cat!',
	'Ja podeu mirar el capítol %4$d de l\'anime «%1$s» (%TYPE% per %3$s). El teniu al web d\'anime.fansubs.cat!'
);

$new_episode_no_number_tweets = array(
	'Ja hi ha disponible un nou capítol de l\'anime «%1$s» (%TYPE% per %3$s) a anime.fansubs.cat: «%2$s».',
	'Hem afegit un nou capítol de l\'anime «%1$s» (%TYPE% per %3$s) al web d\'anime.fansubs.cat: «%2$s».',
	'Ja podeu mirar un nou capítol de l\'anime «%1$s» (%TYPE% per %3$s) a anime.fansubs.cat: «%2$s».'
);

$new_episodes_tweets = array(
	'Ja hi ha disponibles %2$d capítols nous de l\'anime «%1$s» (%TYPE% per %3$s) al web d\'anime.fansubs.cat!',
	'Hem afegit %2$d capítols nous de l\'anime «%1$s» (%TYPE% per %3$s) al web d\'anime.fansubs.cat!',
	'Ja podeu mirar %2$d capítols nous de l\'anime «%1$s» (%TYPE% per %3$s) al web d\'anime.fansubs.cat!'
);

$result = mysqli_query($db_connection, "SELECT IF(v.show_volumes=1,vo.name,NULL), m.name, v.manga_id, m.type, m.slug, MAX(fi.id) id, fi.manga_version_id, COUNT(DISTINCT fi.id) cnt,GROUP_CONCAT(DISTINCT f.twitter_handle SEPARATOR ' + ') fansub_handles, c.number, IF(ct.title IS NOT NULL, ct.title, IF(c.number IS NULL,c.name,ct.title)) title, v.show_chapter_numbers, NOT EXISTS(SELECT fi2.id FROM file fi2 WHERE fi2.id<=$last_tweeted_manga_id AND fi2.manga_version_id=fi.manga_version_id AND fi2.original_filename IS NOT NULL) new_manga
FROM file fi
LEFT JOIN manga_version v ON fi.manga_version_id=v.id
LEFT JOIN rel_manga_version_fansub vf ON v.id=vf.manga_version_id
LEFT JOIN fansub f ON vf.fansub_id=f.id
LEFT JOIN manga m ON v.manga_id=m.id
LEFT JOIN chapter_title ct ON fi.chapter_id=ct.chapter_id AND ct.manga_version_id=fi.manga_version_id
LEFT JOIN chapter c ON fi.chapter_id=c.id
LEFT JOIN volume vo ON vo.id=c.volume_id
WHERE fi.id>$last_tweeted_manga_id AND fi.original_filename IS NOT NULL AND fi.chapter_id IS NOT NULL GROUP BY fi.manga_version_id ORDER BY MAX(fi.id) ASC") or die(mysqli_error($db_connection));
while ($row = mysqli_fetch_assoc($result)){
	if ($row['new_manga']==1) {
		$random = array_rand($new_manga_tweets, 1);
		try{
			publish_tweet(get_shortened_tweet(sprintf($new_manga_tweets[$random], $row['name'], $row['fansub_handles']))."\nhttps://manga.fansubs.cat/".($row['type']=='oneshot' ? 'one-shots' : 'serialitzats')."/".$row['slug'].(exists_more_than_one_version_manga($row['manga_id']) ? "?v=".$row['manga_version_id'] : ""));
			file_put_contents('last_tweeted_manga_id.txt', $row['id']);
		} catch(Exception $e) {
			break;
		}
	} else if ($row['cnt']>1){ //Multiple chapters
		$random = array_rand($new_chapters_tweets, 1);
		try{
			publish_tweet(get_shortened_tweet(sprintf($new_chapters_tweets[$random], $row['name'], $row['cnt'], $row['fansub_handles']))."\nhttps://manga.fansubs.cat/".($row['type']=='oneshot' ? 'one-shots' : 'serialitzats')."/".$row['slug'].(exists_more_than_one_version_manga($row['manga_id']) ? "?v=".$row['manga_version_id'] : ""));
			file_put_contents('last_tweeted_manga_id.txt', $row['id']);
		} catch(Exception $e) {
			break;
		}
	} else { //Single chapter
		if ($row['show_chapter_numbers']==1) {
			if (!empty($row['title']) && empty($row['number'])) {
				$random = array_rand($new_chapter_no_number_tweets, 1);
				try{
					publish_tweet(get_shortened_tweet(sprintf($new_chapter_no_number_tweets[$random], $row['name'], $row['title'], $row['fansub_handles']))."\nhttps://manga.fansubs.cat/".($row['type']=='oneshot' ? 'one-shots' : 'serialitzats')."/".$row['slug'].(exists_more_than_one_version_manga($row['manga_id']) ? "?v=".$row['manga_version_id'] : ""));
					file_put_contents('last_tweeted_manga_id.txt', $row['id']);
				} catch(Exception $e) {
					break;
				}
			} else if (!empty($row['title'])) { //and has a number (normal case)
				$random = array_rand($new_chapter_number_tweets, 1);
				try{
					publish_tweet(get_shortened_tweet(sprintf($new_chapter_number_tweets[$random], $row['name'], $row['title'], $row['fansub_handles'], str_replace('.',',',floatval($row['number']))))."\nhttps://manga.fansubs.cat/".($row['type']=='oneshot' ? 'one-shots' : 'serialitzats')."/".$row['slug'].(exists_more_than_one_version_manga($row['manga_id']) ? "?v=".$row['manga_version_id'] : ""));
					file_put_contents('last_tweeted_manga_id.txt', $row['id']);
				} catch(Exception $e) {
					break;
				}
			} else {
				$random = array_rand($new_chapter_number_no_name_tweets, 1);
				try{
					publish_tweet(get_shortened_tweet(sprintf($new_chapter_number_no_name_tweets[$random], $row['name'], '', $row['fansub_handles'], str_replace('.',',',floatval($row['number']))))."\nhttps://manga.fansubs.cat/".($row['type']=='oneshot' ? 'one-shots' : 'serialitzats')."/".$row['slug'].(exists_more_than_one_version_manga($row['manga_id']) ? "?v=".$row['manga_version_id'] : ""));
					file_put_contents('last_tweeted_manga_id.txt', $row['id']);
				} catch(Exception $e) {
					break;
				}
			}
		} else {
			$random = array_rand($new_chapter_no_number_tweets, 1);
			try{
				publish_tweet(get_shortened_tweet(sprintf($new_chapter_no_number_tweets[$random], $row['name'], $row['title'], $row['fansub_handles']))."\nhttps://manga.fansubs.cat/".($row['type']=='oneshot' ? 'one-shots' : 'serialitzats')."/".$row['slug'].(exists_more_than_one_version_manga($row['manga_id']) ? "?v=".$row['manga_version_id'] : ""));
				file_put_contents('last_tweeted_manga_id.txt', $row['id']);
			} catch(Exception $e) {
				break;
			}
		}
	}
}

mysqli_free_result($result);

$result = mysqli_query($db_connection, "SELECT IF(v.show_seasons=1, IFNULL(se.name,s.name), s.name) name, v.series_id, s.type, s.slug, MAX(l.id) id, l.version_id, COUNT(DISTINCT l.id) cnt,GROUP_CONCAT(DISTINCT f.twitter_handle ORDER BY f.name SEPARATOR ' + ') fansub_handles, GROUP_CONCAT(DISTINCT f.type SEPARATOR '|') fansub_type, e.number, IF(et.title IS NOT NULL, et.title, IF(e.number IS NULL,e.name,et.title)) title, v.show_episode_numbers, NOT EXISTS(SELECT l2.id FROM link l2 WHERE l2.id<=$last_tweeted_anime_id AND l2.version_id=l.version_id AND l2.lost=0) new_series
FROM link l
LEFT JOIN version v ON l.version_id=v.id
LEFT JOIN rel_version_fansub vf ON v.id=vf.version_id
LEFT JOIN fansub f ON vf.fansub_id=f.id
LEFT JOIN series s ON v.series_id=s.id
LEFT JOIN episode_title et ON l.episode_id=et.episode_id AND et.version_id=l.version_id
LEFT JOIN episode e ON l.episode_id=e.id
LEFT JOIN season se ON se.id=e.season_id
WHERE l.id>$last_tweeted_anime_id AND l.lost=0 AND l.episode_id IS NOT NULL GROUP BY l.version_id ORDER BY MAX(l.id) ASC") or die(mysqli_error($db_connection));
while ($row = mysqli_fetch_assoc($result)){
	$type = 'subtitulat';
	if ($row['fansub_type']=='fandub') {
		$type = 'doblat';
	}
	if ($row['new_series']==1) {
		$random = array_rand($new_anime_tweets, 1);
		try{
			publish_tweet(get_shortened_tweet(sprintf(str_replace('%TYPE%', $type, $new_anime_tweets[$random]), $row['name'], $row['fansub_handles']))."\nhttps://anime.fansubs.cat/".($row['type']=='series' ? 'series' : 'films')."/".$row['slug'].(exists_more_than_one_version($row['series_id']) ? "?v=".$row['version_id'] : ""));
			file_put_contents('last_tweeted_anime_id.txt', $row['id']);
		} catch(Exception $e) {
			break;
		}
	} else if ($row['cnt']>1){ //Multiple episodes
		$random = array_rand($new_episodes_tweets, 1);
		try{
			publish_tweet(get_shortened_tweet(sprintf(str_replace('%TYPE%', $type, $new_episodes_tweets[$random]), $row['name'], $row['cnt'], $row['fansub_handles']))."\nhttps://anime.fansubs.cat/".($row['type']=='series' ? 'series' : 'films')."/".$row['slug'].(exists_more_than_one_version($row['series_id']) ? "?v=".$row['version_id'] : ""));
			file_put_contents('last_tweeted_anime_id.txt', $row['id']);
		} catch(Exception $e) {
			break;
		}
	} else { //Single episode
		if ($row['show_episode_numbers']==1) {
			if (!empty($row['title']) && empty($row['number'])) {
				$random = array_rand($new_episode_no_number_tweets, 1);
				try{
					publish_tweet(get_shortened_tweet(sprintf(str_replace('%TYPE%', $type, $new_episode_no_number_tweets[$random]), $row['name'], $row['title'], $row['fansub_handles']))."\nhttps://anime.fansubs.cat/".($row['type']=='series' ? 'series' : 'films')."/".$row['slug'].(exists_more_than_one_version($row['series_id']) ? "?v=".$row['version_id'] : ""));
					file_put_contents('last_tweeted_anime_id.txt', $row['id']);
				} catch(Exception $e) {
					break;
				}
			} else if (!empty($row['title'])) { //and has a number (normal case)
				$random = array_rand($new_episode_number_tweets, 1);
				try{
					publish_tweet(get_shortened_tweet(sprintf(str_replace('%TYPE%', $type, $new_episode_number_tweets[$random]), $row['name'], $row['title'], $row['fansub_handles'], str_replace('.',',',floatval($row['number']))))."\nhttps://anime.fansubs.cat/".($row['type']=='series' ? 'series' : 'films')."/".$row['slug'].(exists_more_than_one_version($row['series_id']) ? "?v=".$row['version_id'] : ""));
					file_put_contents('last_tweeted_anime_id.txt', $row['id']);
				} catch(Exception $e) {
					break;
				}
			} else {
				$random = array_rand($new_episode_number_no_name_tweets, 1);
				try{
					publish_tweet(get_shortened_tweet(sprintf(str_replace('%TYPE%', $type, $new_episode_number_no_name_tweets[$random]), $row['name'], '', $row['fansub_handles'], str_replace('.',',',floatval($row['number']))))."\nhttps://anime.fansubs.cat/".($row['type']=='series' ? 'series' : 'films')."/".$row['slug'].(exists_more_than_one_version($row['series_id']) ? "?v=".$row['version_id'] : ""));
					file_put_contents('last_tweeted_anime_id.txt', $row['id']);
				} catch(Exception $e) {
					break;
				}
			}
		} else {
			$random = array_rand($new_episode_no_number_tweets, 1);
			try{
				publish_tweet(get_shortened_tweet(sprintf(str_replace('%TYPE%', $type, $new_episode_no_number_tweets[$random]), $row['name'], $row['title'], $row['fansub_handles']))."\nhttps://anime.fansubs.cat/".($row['type']=='series' ? 'series' : 'films')."/".$row['slug'].(exists_more_than_one_version($row['series_id']) ? "?v=".$row['version_id'] : ""));
				file_put_contents('last_tweeted_anime_id.txt', $row['id']);
			} catch(Exception $e) {
				break;
			}
		}
	}
}

mysqli_free_result($result);

mysqli_close($db_connection);
?>
