<?php
$header_title="Darreres visualitzacions d'anime - Anàlisi";
$page="analytics";
include("header.inc.php");
require_once("common.inc.php");

if (!empty($_SESSION['username']) && !empty($_SESSION['admin_level']) && $_SESSION['admin_level']>=1) {
	if (!empty($_SESSION['fansub_id'])) {
		$resultf = query("SELECT * FROM fansub WHERE id=".escape($_SESSION['fansub_id']));
		$fansub = mysqli_fetch_assoc($resultf);
		mysqli_free_result($resultf);
	} else if ($_SESSION['admin_level']>=3 && !empty($_GET['fansub_id'])) {
		$resultf = query("SELECT * FROM fansub WHERE id=".escape($_GET['fansub_id']));
		$fansub = mysqli_fetch_assoc($resultf);
		mysqli_free_result($resultf);
	}

	$limit = 25;
	if (!empty($_GET['limit']) && is_numeric($_GET['limit'])){
		$limit = escape($_GET['limit']);
	}
?>
		<div class="container justify-content-center p-4">
			<ul class="nav nav-tabs" id="stats_tabs" role="tablist">
<?php
	if (!empty($fansub)) {
?>
				<li class="nav-item">
					<a class="nav-link active" id="fansub-tab" data-toggle="tab" href="#fansub" role="tab" aria-controls="fansub" aria-selected="true">Visualitzacions <?php echo get_fansub_preposition_name($fansub['name']); ?></a>
				</li>
<?php
	}
?>
				<li class="nav-item">
					<a class="nav-link<?php echo empty($fansub) ? ' active' : ''; ?>" id="totals-tab" data-toggle="tab" href="#totals" role="tab" aria-controls="totals" aria-selected="false">Visualitzacions globals</a>
				</li>
			</ul>
			<div class="tab-content" id="stats_tabs_content" style="border: 1px solid #dee2e6; border-top: none;">
				<div class="tab-pane fade<?php echo empty($fansub) ? ' show active' : ''; ?>" id="totals" role="tabpanel" aria-labelledby="totals-tab">
					<div class="container d-flex justify-content-center p-4">
						<div class="card w-100">
							<article class="card-body">
								<h4 class="card-title text-center mb-4 mt-1">Visualitzacions d'anime en curs</h4>
								<hr>
								<div class="text-center pb-3">
									<a href="views.php" class="btn btn-primary"><span class="fa fa-redo pr-2"></span>Refresca</a>
								</div>
								<div class="row">
									<table class="table table-hover table-striped">
										<thead class="thead-dark">
											<tr>
												<th scope="col" style="width: 20%;">Anime</th>
												<th scope="col" style="width: 45%;">Capítol</th>
												<th scope="col" class="text-center" style="width: 10%;">Usuari</th>
												<th scope="col" class="text-center" style="width: 20%;">Progrés</th>
												<th scope="col" style="width: 5%; text-align: center;"><span class="far fa-eye"></span></th>
												<th scope="col" style="width: 5%; text-align: center;"><span class="far fa-thumbs-up"></span></th>
											</tr>
										</thead>
										<tbody>
<?php
$result = query("SELECT IFNULL(s.name, '(enllaç esborrat)') series_name,IF(et.title IS NOT NULL,IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(v.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),IF(v.show_episode_numbers=1,CONCAT('Capítol ',TRIM(e.number)+0,': '),''),et.title),e.name),IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(v.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),'Capítol ',TRIM(e.number)+0),IF(l.episode_id IS NULL,CONCAT('Extra: ', l.extra_name), '(Capítol sense nom)'))) episode_name, ps.method, (IF(ps.method='size', IF(ps.total_bytes>0, ps.bytes_read/ps.total_bytes, 0), ps.time_spent/ps.total_time))*100 progress, UNIX_TIMESTAMP(ps.last_update) last_update, ps.ip, ps.user_agent, ps.user_agent_read, ps.view_counted FROM play_session ps LEFT JOIN link l ON ps.link_id=l.id LEFT JOIN version v ON l.version_id=v.id LEFT JOIN series s ON v.series_id=s.id LEFT JOIN episode e ON l.episode_id=e.id LEFT JOIN season se ON e.season_id=se.id LEFT JOIN episode_title et ON l.version_id=et.version_id AND l.episode_id=et.episode_id WHERE ps.archived=0 AND ps.player_closed=0 AND UNIX_TIMESTAMP(ps.last_update)>=".(date('U')-120)." ORDER BY ps.created DESC");
while ($row = mysqli_fetch_assoc($result)) {
?>
											<tr>
												<td scope="col"><?php echo $row['series_name']; ?></td>
												<td scope="col"><?php echo $row['episode_name']; ?></td>
												<td scope="col" class="text-center"><?php echo get_anonymized_username($row['ip'], $row['user_agent']); ?></td>
												<td class="text-center"><div class="progress"><div class="progress-bar progress-bar-striped <?php echo $row['last_update']<date('U')-120 ? "bg-info" : "progress-bar-animated"; ?>" role="progressbar" style="width: <?php echo min(100,$row['progress']); ?>%;" aria-valuenow="<?php echo min(100,$row['progress']); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo min(100,round($row['progress'],1)); ?>%</div></div></td>
												<td class="text-center"><div <?php echo get_browser_icon_by_type($row['user_agent'], $row['user_agent_read']); ?>></div></td>
												<td class="text-center"><div<?php echo $row['view_counted']==1 ? ' class="fa fa-thumbs-up" style="color: green;" title="Comptada com a visualització"' : ' class="fa fa-thumbs-down" style="color: red;" title="De moment no compta com a visualització"'; ?>></div></td>
											</tr>
<?php
}
mysqli_free_result($result);
?>
										</tbody>
									</table>
								</div>
								<p class="text-center text-muted small">Aquesta llista només inclou de manera fiable les visualitzacions de navegadors, la resta poden fluctuar i s'incorporaran més tard a les estadístiques. El progrés és sols orientatiu.</p>
							</article>
						</div>
					</div>
					<div class="container d-flex justify-content-center p-4">
						<div class="card w-100">
							<article class="card-body">
								<h4 class="card-title text-center mb-4 mt-1">Darreres <?php echo $limit; ?> visualitzacions d'anime</h4>
								<hr>
								<div class="row">
									<table class="table table-hover table-striped">
										<thead class="thead-dark">
											<tr>
												<th scope="col">Anime</th>
												<th scope="col">Capítol</th>
												<th scope="col" class="text-center" style="width: 10%;">Usuari</th>
												<th scope="col" style="width: 5%; text-align: center;"><span class="far fa-eye"></span></th>
												<th scope="col" class="text-center" style="width: 20%;">Data</th>
											</tr>
										</thead>
										<tbody>
<?php
$result = query("SELECT IFNULL(s.name, '(enllaç esborrat)') series_name,IF(et.title IS NOT NULL,IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(v.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),IF(v.show_episode_numbers=1,CONCAT('Capítol ',TRIM(e.number)+0,': '),''),et.title),e.name),IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(v.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),'Capítol ',TRIM(e.number)+0),IF(l.episode_id IS NULL,CONCAT('Extra: ', l.extra_name), '(Capítol sense nom)'))) episode_name, vl.date, vl.ip, vl.user_agent, vl.user_agent_read FROM view_log vl LEFT JOIN link l ON vl.link_id=l.id LEFT JOIN version v ON l.version_id=v.id LEFT JOIN series s ON v.series_id=s.id LEFT JOIN episode e ON l.episode_id=e.id LEFT JOIN season se ON e.season_id=se.id LEFT JOIN episode_title et ON l.version_id=et.version_id AND l.episode_id=et.episode_id ORDER BY vl.date DESC LIMIT $limit");
while ($row = mysqli_fetch_assoc($result)) {
?>
											<tr>
												<td scope="col"><?php echo $row['series_name']; ?></td>
												<td scope="col"><?php echo $row['episode_name']; ?></td>
												<td scope="col" class="text-center"><?php echo get_anonymized_username($row['ip'], $row['user_agent']); ?></td>
												<td class="text-center"><div <?php echo get_browser_icon_by_type($row['user_agent'], $row['user_agent_read']); ?>></div></td>
												<td class="text-center" class="text-center"><?php echo $row['date']; ?></td>
											</tr>
<?php
}
mysqli_free_result($result);
?>
										</tbody>
									</table>
								</div>
							</article>
						</div>
					</div>
				</div>
<?php
	if (!empty($fansub)) {
?>
				<div class="tab-pane fade show active" id="fansub" role="tabpanel" aria-labelledby="fansub-tab">
					<div class="container d-flex justify-content-center p-4">
						<div class="card w-100">
							<article class="card-body">
								<h4 class="card-title text-center mb-4 mt-1">Visualitzacions d'anime en curs</h4>
								<hr>
								<div class="text-center pb-3">
									<a href="views.php" class="btn btn-primary"><span class="fa fa-redo pr-2"></span>Refresca</a>
								</div>
								<div class="row">
									<table class="table table-hover table-striped">
										<thead class="thead-dark">
											<tr>
												<th scope="col" style="width: 20%;">Anime</th>
												<th scope="col" style="width: 45%;">Capítol</th>
												<th scope="col" class="text-center" style="width: 10%;">Usuari</th>
												<th scope="col" class="text-center" style="width: 20%;">Progrés</th>
												<th scope="col" style="width: 5%; text-align: center;"><span class="far fa-eye"></span></th>
												<th scope="col" style="width: 5%; text-align: center;"><span class="far fa-thumbs-up"></span></th>
											</tr>
										</thead>
										<tbody>
<?php
$result = query("SELECT IFNULL(s.name, '(enllaç esborrat)') series_name,IF(et.title IS NOT NULL,IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(v.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),IF(v.show_episode_numbers=1,CONCAT('Capítol ',TRIM(e.number)+0,': '),''),et.title),e.name),IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(v.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),'Capítol ',TRIM(e.number)+0),IF(l.episode_id IS NULL,CONCAT('Extra: ', l.extra_name), '(Capítol sense nom)'))) episode_name, ps.method, (IF(ps.method='size', IF(ps.total_bytes>0, ps.bytes_read/ps.total_bytes, 0), ps.time_spent/ps.total_time))*100 progress, UNIX_TIMESTAMP(ps.last_update) last_update, ps.ip, ps.user_agent, ps.user_agent_read, ps.view_counted FROM play_session ps LEFT JOIN link l ON ps.link_id=l.id LEFT JOIN version v ON l.version_id=v.id LEFT JOIN series s ON v.series_id=s.id LEFT JOIN episode e ON l.episode_id=e.id LEFT JOIN season se ON e.season_id=se.id LEFT JOIN episode_title et ON l.version_id=et.version_id AND l.episode_id=et.episode_id WHERE v.id IN (SELECT version_id FROM rel_version_fansub WHERE fansub_id=".$fansub['id'].") AND ps.archived=0 AND ps.player_closed=0 AND UNIX_TIMESTAMP(ps.last_update)>=".(date('U')-120)." ORDER BY ps.created DESC");
while ($row = mysqli_fetch_assoc($result)) {
?>
											<tr>
												<td scope="col"><?php echo $row['series_name']; ?></td>
												<td scope="col"><?php echo $row['episode_name']; ?></td>
												<td scope="col" class="text-center"><?php echo get_anonymized_username($row['ip'], $row['user_agent']); ?></td>
												<td class="text-center"><div class="progress"><div class="progress-bar progress-bar-striped <?php echo $row['last_update']<date('U')-120 ? "bg-info" : "progress-bar-animated"; ?>" role="progressbar" style="width: <?php echo min(100,$row['progress']); ?>%;" aria-valuenow="<?php echo min(100,$row['progress']); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo min(100,round($row['progress'],1)); ?>%</div></div></td>
												<td class="text-center"><div <?php echo get_browser_icon_by_type($row['user_agent'], $row['user_agent_read']); ?>></div></td>
												<td class="text-center"><div<?php echo $row['view_counted']==1 ? ' class="fa fa-thumbs-up" style="color: green;" title="Comptada com a visualització"' : ' class="fa fa-thumbs-down" style="color: red;" title="De moment no compta com a visualització"'; ?>></div></td>
											</tr>
<?php
}
mysqli_free_result($result);
?>
										</tbody>
									</table>
								</div>
								<p class="text-center text-muted small">Aquesta llista només inclou de manera fiable les visualitzacions de navegadors, la resta poden fluctuar i s'incorporaran més tard a les estadístiques. El progrés és sols orientatiu.</p>
							</article>
						</div>
					</div>
					<div class="container d-flex justify-content-center p-4">
						<div class="card w-100">
							<article class="card-body">
								<h4 class="card-title text-center mb-4 mt-1">Darreres <?php echo $limit; ?> visualitzacions d'anime</h4>
								<hr>
								<div class="row">
									<table class="table table-hover table-striped">
										<thead class="thead-dark">
											<tr>
												<th scope="col">Anime</th>
												<th scope="col">Capítol</th>
												<th scope="col" class="text-center" style="width: 10%;">Usuari</th>
												<th scope="col" style="width: 5%; text-align: center;"><span class="far fa-eye"></span></th>
												<th scope="col" class="text-center" style="width: 20%;">Data</th>
											</tr>
										</thead>
										<tbody>
<?php
$result = query("SELECT IFNULL(s.name, '(enllaç esborrat)') series_name,IF(et.title IS NOT NULL,IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(v.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),IF(v.show_episode_numbers=1,CONCAT('Capítol ',TRIM(e.number)+0,': '),''),et.title),e.name),IF(e.number IS NOT NULL,CONCAT(IFNULL(IF(se.name IS NULL,NULL,CONCAT(se.name,' - ')),IF(v.show_seasons=1 AND (SELECT COUNT(*) FROM season se2 WHERE se2.series_id=s.id)>1,CONCAT('Temporada ', se.number, ' - '),'')),'Capítol ',TRIM(e.number)+0),IF(l.episode_id IS NULL,CONCAT('Extra: ', l.extra_name), '(Capítol sense nom)'))) episode_name, vl.date, vl.ip, vl.user_agent, vl.user_agent_read FROM view_log vl LEFT JOIN link l ON vl.link_id=l.id LEFT JOIN version v ON l.version_id=v.id LEFT JOIN series s ON v.series_id=s.id LEFT JOIN episode e ON l.episode_id=e.id LEFT JOIN season se ON e.season_id=se.id LEFT JOIN episode_title et ON l.version_id=et.version_id AND l.episode_id=et.episode_id WHERE v.id IN (SELECT version_id FROM rel_version_fansub WHERE fansub_id=".$fansub['id'].") ORDER BY vl.date DESC LIMIT $limit");
while ($row = mysqli_fetch_assoc($result)) {
?>
											<tr>
												<td scope="col"><?php echo $row['series_name']; ?></td>
												<td scope="col"><?php echo $row['episode_name']; ?></td>
												<td scope="col" class="text-center"><?php echo get_anonymized_username($row['ip'], $row['user_agent']); ?></td>
												<td class="text-center"><div <?php echo get_browser_icon_by_type($row['user_agent'], $row['user_agent_read']); ?>></div></td>
												<td class="text-center"><?php echo $row['date']; ?></td>
											</tr>
<?php
}
mysqli_free_result($result);
?>
										</tbody>
									</table>
								</div>
							</article>
						</div>
					</div>
				</div>
<?php
	}
?>
			</div>
		</div>
<?php
} else {
	header("Location: login.php");
}

include("footer.inc.php");
?>
