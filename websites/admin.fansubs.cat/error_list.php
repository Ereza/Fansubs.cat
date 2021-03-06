<?php
$header_title="Errors de reproducció - Anàlisi";
$page="analytics";
include("header.inc.php");
include("common.inc.php");

function get_error_type($type) {
	switch ($type) {
		case 'mega-unknown':
			return 'MEGA: Error desconegut';
		case 'mega-unavailable':
			return '<span class="text-danger">MEGA: Contingut eliminat</span>';
		case 'mega-quota-exceeded':
			return '<span class="text-info">MEGA: Límit superat</span>';
		case 'mega-player-failed':
			return 'MEGA: Error de reproducció';
		case 'mega-incompatible-browser':
			return 'MEGA: Navegador no compatible';
		case 'mega-connection-error':
			return 'MEGA: Error de connexió';
		case 'mega-load-failed':
			return 'MEGA: Error de càrrega';
		case 'direct-load-failed': //No longer exists, kept for old logs
			return 'Vídeo: Error de càrrega';
		case 'direct-player-failed':
			return 'Vídeo: Error de reproducció';
		case 'page-too-old':
			return 'Vídeo: Pàgina massa antiga';
		case 'unknown':
			return 'Error desconegut';
		default:
			return $type;
	}
}

function get_time($time) {
	return gmdate("H:i:s", $time);
}

if (!empty($_SESSION['username']) && !empty($_SESSION['admin_level']) && $_SESSION['admin_level']>=1) {
?>
		<div class="container d-flex justify-content-center p-4">
			<div class="card w-100">
				<article class="card-body">
					<h4 class="card-title text-center mb-4 mt-1">Errors de reproducció</h4>
					<hr>
					<p class="text-center">Aquests són els darrers 100 errors de reproducció que han tingut els usuaris.</p>
					<div class="text-center pb-3">
						<a href="error_list.php" class="btn btn-primary"><span class="fa fa-redo pr-2"></span>Refresca</a>
					</div>
					<table class="table table-hover table-striped">
						<thead class="thead-dark">
							<tr>
								<th scope="col" style="width: 35%;">Anime i capítol</th>
								<th scope="col" style="width: 25%;" class="text-center">Error</th>
								<th scope="col" style="width: 5%;" class="text-center">Segon</th>
								<th scope="col" style="width: 10%;" class="text-center">Usuari</th>
								<th scope="col" style="width: 20%;" class="text-center">Data</th>
								<th scope="col" style="width: 5%;" class="text-center">Accions</th>
							</tr>
						</thead>
						<tbody>
<?php
	if (!empty($_SESSION['fansub_id']) && is_numeric($_SESSION['fansub_id'])) {
		$where = ' WHERE vf.fansub_id='.$_SESSION['fansub_id'];
	} else {
		$where = '';
	}
	$result = query("SELECT s.name series, IF(et.title IS NOT NULL,IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(ve.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),IF(ve.show_episode_numbers=1,CONCAT('Capítol ',TRIM(e.number)+0,': '),''),et.title),e.name),IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(ve.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),'Capítol ',TRIM(e.number)+0),IF(l.episode_id IS NULL,CONCAT('Extra: ', l.extra_name), '(Capítol sense nom)'))) episode_name, re.date, re.ip, re.user_agent, re.type, re.text, l.id link_id, re.play_time, ve.id version_id FROM reported_error re LEFT JOIN link l ON re.link_id=l.id LEFT JOIN version ve ON l.version_id=ve.id LEFT JOIN rel_version_fansub vf ON vf.version_id=ve.id LEFT JOIN series s ON ve.series_id=s.id LEFT JOIN episode e ON l.episode_id=e.id LEFT JOIN season se ON e.season_id=se.id LEFT JOIN episode_title et ON l.version_id=et.version_id AND l.episode_id=et.episode_id$where GROUP BY re.id ORDER BY date DESC LIMIT 100");
	if (mysqli_num_rows($result)==0) {
?>
							<tr>
								<td colspan="6" class="text-center">- No hi ha cap error -</td>
							</tr>
<?php
	}
	while ($row = mysqli_fetch_assoc($result)) {
?>
							<tr>
								<th scope="row" class="align-middle"><strong><?php echo $row['series']; ?></strong><br /><small><?php echo $row['episode_name']; ?></small></th>
								<td class="align-middle text-center"><span style="cursor: help;" title="<?php echo htmlentities($row['text']); ?>"><?php echo get_error_type($row['type']); ?></span></td>
								<td class="align-middle text-center"><?php echo get_time($row['play_time']); ?></td>
								<td class="align-middle text-center"><?php echo get_anonymized_username($row['ip'], $row['user_agent']); ?></td>
								<td class="align-middle text-center"><strong><?php echo $row['date']; ?></strong></td>
								<td class="align-middle text-center text-nowrap">
<?php
		if (!empty($row['link_id'])) {
?>
<a href="version_edit.php?id=<?php echo $row['version_id']; ?>" title="Edita la versió" class="fa fa-edit p-1"></a> 
<?php
			$resultli = query("SELECT * FROM link_instance WHERE link_id=${row['link_id']}");
			$count=0;
			while ($link_instance = mysqli_fetch_assoc($resultli)) {
				if ($count==0) {
					echo "<br>";
				}
				echo '	<a href="'.$link_instance['url'].'" target="_blank" title="Obre l\'enllaç" class="fa fa-external-link-alt p-1 text-success"></a>';
				$count++;
			}
			mysqli_free_result($resultli);
		}
?></td>
							</tr>
<?php
	}
	mysqli_free_result($result);
?>
						</tbody>
					</table>
				</article>
			</div>
		</div>
<?php
} else {
	header("Location: login.php");
}

include("footer.inc.php");
?>
