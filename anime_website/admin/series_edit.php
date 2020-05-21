<?php
$header_title="Sèries";
$page="series";
include("header.inc.php");

if (!empty($_SESSION['username']) && !empty($_SESSION['admin_level']) && $_SESSION['admin_level']>=2) {
	if (!empty($_POST['action'])) {
		$data=array();
		if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
			$data['id']=escape($_POST['id']);
		} else if ($_POST['action']=='edit') {
			crash("Dades invàlides: manca id");
		}
		if (!empty($_POST['name'])) {
			$data['name']=escape($_POST['name']);
		} else {
			crash("Dades invàlides: manca name");
		}
		if (!empty($_POST['slug'])) {
			$data['slug']=escape($_POST['slug']);
		} else {
			crash("Dades invàlides: manca slug");
		}
		if (!empty($_POST['myanimelist_id']) && is_numeric($_POST['myanimelist_id'])) {
			$data['myanimelist_id']=escape($_POST['myanimelist_id']);
		} else {
			$data['myanimelist_id']="NULL";
		}
		if (!empty($_POST['tadaima_id']) && is_numeric($_POST['tadaima_id'])) {
			$data['tadaima_id']=escape($_POST['tadaima_id']);
		} else {
			$data['tadaima_id']="NULL";
		}
		if (!empty($_POST['score']) && is_numeric($_POST['score'])) {
			$data['score']=escape($_POST['score']);
		} else {
			$data['score']="NULL";
		}
		if (!empty($_POST['alternate_names'])) {
			$data['alternate_names']="'".escape($_POST['alternate_names'])."'";
		} else {
			$data['alternate_names']="NULL";
		}
		if (!empty($_POST['type'])) {
			$data['type']=escape($_POST['type']);
		} else {
			crash("Dades invàlides: manca type");
		}
		if (!empty($_POST['air_date'])) {
			$data['air_date']="'".date('Y-m-d H:i:s', strtotime($_POST['air_date']))."'";
		} else {
			$data['air_date']="NULL";
		}
		if (!empty($_POST['author'])) {
			$data['author']="'".escape($_POST['author'])."'";
		} else {
			$data['author']="NULL";
		}
		if (!empty($_POST['director'])) {
			$data['director']="'".escape($_POST['director'])."'";
		} else {
			$data['director']="NULL";
		}
		if (!empty($_POST['studio'])) {
			$data['studio']="'".escape($_POST['studio'])."'";
		} else {
			$data['studio']="NULL";
		}
		if (!empty($_POST['rating'])) {
			$data['rating']="'".escape($_POST['rating'])."'";
		} else {
			$data['rating']="NULL";
		}
		if (!empty($_POST['synopsis'])) {
			$data['synopsis']=escape($_POST['synopsis']);
		} else {
			crash("Dades invàlides: manca synopsis");
		}
		if (!empty($_POST['duration'])) {
			$data['duration']="'".escape($_POST['duration'])."'";
		} else {
			$data['duration']="NULL";
		}
		if (!empty($_POST['image'])) {
			$data['image']=escape($_POST['image']);
		} else {
			crash("Dades invàlides: manca image");
		}
		if (!empty($_POST['show_seasons'])){
			$data['show_seasons']=1;
		} else {
			$data['show_seasons']=0;
		}
		if (!empty($_POST['show_expanded_seasons'])){
			$data['show_expanded_seasons']=1;
		} else {
			$data['show_expanded_seasons']=0;
		}
		if (!empty($_POST['show_episode_numbers'])){
			$data['show_episode_numbers']=1;
		} else {
			$data['show_episode_numbers']=0;
		}
		if (!empty($_POST['order_type'])){
			$data['order_type']=escape($_POST['order_type']);
		} else {
			$data['order_type']=0;
		}

		$genres=array();
		if (!empty($_POST['genres'])) {
			foreach ($_POST['genres'] as $genre) {
				if (is_numeric($genre)) {
					array_push($genres, escape($genre));
				}
				else{
					crash("Dades invàlides: genre no numèric");
				}
			}
		}

		$seasons=array();
		$i=1;
		$total_eps=0;
		while (!empty($_POST['form-season-list-id-'.$i])) {
			$season = array();
			if (is_numeric($_POST['form-season-list-id-'.$i])) {
				$season['id']=escape($_POST['form-season-list-id-'.$i]);
			} else {
				crash("Dades invàlides: id de temporada no numèric");
			}
			if (!empty($_POST['form-season-list-number-'.$i]) && is_numeric($_POST['form-season-list-number-'.$i])) {
				$season['number']=escape($_POST['form-season-list-number-'.$i]);
			} else {
				crash("Dades invàlides: número de temporada buit o no numèric");
			}
			if (!empty($_POST['form-season-list-name-'.$i])) {
				$season['name']="'".escape($_POST['form-season-list-name-'.$i])."'";
			} else {
				$season['name']="NULL";
			}
			if (!empty($_POST['form-season-list-episodes-'.$i]) && is_numeric($_POST['form-season-list-episodes-'.$i])) {
				$season['episodes']=escape($_POST['form-season-list-episodes-'.$i]);
				$total_eps+=$_POST['form-season-list-episodes-'.$i];
			} else {
				crash("Dades invàlides: número de capítols buit o no numèric");
			}
			if (!empty($_POST['form-season-list-myanimelist_id-'.$i]) && is_numeric($_POST['form-season-list-myanimelist_id-'.$i])) {
				$season['myanimelist_id']=escape($_POST['form-season-list-myanimelist_id-'.$i]);
			} else {
				$season['myanimelist_id']="NULL";
			}
			array_push($seasons, $season);
			$i++;
		}

		if (!empty($_POST['is_open'])){
			$data['episodes']=-1;
		} else {
			$data['episodes']=$total_eps;
		}

		$episodes=array();
		$i=1;
		while (!empty($_POST['form-episode-list-id-'.$i])) {
			$episode = array();
			if (is_numeric($_POST['form-episode-list-id-'.$i])) {
				$episode['id']=escape($_POST['form-episode-list-id-'.$i]);
			} else {
				crash("Dades invàlides: id de capítol no numèric");
			}
			if (!empty($_POST['form-episode-list-season-'.$i]) && is_numeric($_POST['form-episode-list-season-'.$i])) {
				$episode['season']=escape($_POST['form-episode-list-season-'.$i]);
			} else {
				$episode['season']="NULL";
			}
			if (!empty($_POST['form-episode-list-num-'.$i]) && is_numeric($_POST['form-episode-list-num-'.$i])) {
				$episode['number']=escape($_POST['form-episode-list-num-'.$i]);
			} else {
				$episode['number']="NULL";
			}
			if (!empty($_POST['form-episode-list-name-'.$i])) {
				$episode['name']="'".escape($_POST['form-episode-list-name-'.$i])."'";
			} else {
				$episode['name']="NULL";
			}
			if (!empty($_POST['form-episode-list-duration-'.$i])) {
				$episode['duration']=escape($_POST['form-episode-list-duration-'.$i]);
			} else {
				$episode['duration']="NULL";
			}
			array_push($episodes, $episode);
			$i++;
		}
		
		if ($_POST['action']=='edit') {
			log_action("update-series", "S'ha actualitzat la sèrie amb nom '".$data['name']."' (id. de sèrie: ".$data['id'].")");
			query("UPDATE series SET slug='".$data['slug']."',name='".$data['name']."',alternate_names=".$data['alternate_names'].",score=".$data['score'].",type='".$data['type']."',air_date=".$data['air_date'].",author=".$data['author'].",director=".$data['director'].",studio=".$data['studio'].",rating=".$data['rating'].",episodes=".$data['episodes'].",synopsis='".$data['synopsis']."',duration=".$data['duration'].",image='".$data['image']."',myanimelist_id=".$data['myanimelist_id'].",tadaima_id=".$data['tadaima_id'].",show_seasons=".$data['show_seasons'].",show_expanded_seasons=".$data['show_expanded_seasons'].",show_episode_numbers=".$data['show_episode_numbers'].",order_type=".$data['order_type'].",updated=CURRENT_TIMESTAMP,updated_by='".escape($_SESSION['username'])."' WHERE id=".$data['id']);
			query("DELETE FROM rel_series_genre WHERE series_id=".$data['id']);
			foreach ($genres as $genre) {
				query("INSERT INTO rel_series_genre (series_id,genre_id) VALUES (".$data['id'].",".$genre.")");
			}
			$ids=array();
			foreach ($seasons as $season) {
				if ($season['id']!=-1) {
					array_push($ids,$season['id']);
				}
			}
			query("DELETE FROM season WHERE series_id=".$data['id']." AND id NOT IN (".(count($ids)>0 ? implode(',',$ids) : "-1").")");
			foreach ($seasons as $season) {
				if ($season['id']==-1) {
					query("INSERT INTO season (series_id,number,name,episodes,myanimelist_id) VALUES (".$data['id'].",".$season['number'].",".$season['name'].",".$season['episodes'].",".$season['myanimelist_id'].")");
				} else {
					query("UPDATE season SET number=".$season['number'].",name=".$season['name'].",episodes=".$season['episodes'].",myanimelist_id=".$season['myanimelist_id']." WHERE id=".$season['id']);
				}
			}
			$ids=array();
			foreach ($episodes as $episode) {
				if ($episode['id']!=-1) {
					array_push($ids,$episode['id']);
				}
			}
			//Links and episode_titles will be removed too because their FK is set to cascade
			query("DELETE FROM episode WHERE series_id=".$data['id']." AND id NOT IN (".(count($ids)>0 ? implode(',',$ids) : "-1").")");
			foreach ($episodes as $episode) {
				if ($episode['id']==-1) {
					query("INSERT INTO episode (series_id,season_id,number,name,duration) VALUES (".$data['id'].",(SELECT id FROM season WHERE number=".$episode['season']." AND series_id=".$data['id']."),".$episode['number'].",".$episode['name'].",".$episode['duration'].")");
				} else {
					query("UPDATE episode SET season_id=(SELECT id FROM season WHERE number=".$episode['season']." AND series_id=".$data['id']."),number=".$episode['number'].",name=".$episode['name'].",duration=".$episode['duration']." WHERE id=".$episode['id']);
				}
			}

			$_SESSION['message']="S'han desat les dades correctament.";
		}
		else {
			log_action("create-series", "S'ha creat una sèrie amb nom '".$data['name']."'");
			query("INSERT INTO series (slug,name,alternate_names,type,air_date,author,director,studio,rating,episodes,synopsis,duration,image,myanimelist_id,tadaima_id,score,show_seasons,show_expanded_seasons,show_episode_numbers,order_type,created,created_by,updated,updated_by) VALUES ('".$data['slug']."','".$data['name']."',".$data['alternate_names'].",'".$data['type']."',".$data['air_date'].",".$data['author'].",".$data['director'].",".$data['studio'].",".$data['rating'].",".$data['episodes'].",'".$data['synopsis']."',".$data['duration'].",'".$data['image']."',".$data['myanimelist_id'].",".$data['tadaima_id'].",".$data['score'].",".$data['show_seasons'].",".$data['show_expanded_seasons'].",".$data['show_episode_numbers'].",".$data['order_type'].",CURRENT_TIMESTAMP,'".escape($_SESSION['username'])."',CURRENT_TIMESTAMP,'".escape($_SESSION['username'])."')");
			$inserted_id=mysqli_insert_id($db_connection);
			foreach ($genres as $genre) {
				query("INSERT INTO rel_series_genre (series_id,genre_id) VALUES (".$inserted_id.",".$genre.")");
			}
			foreach ($seasons as $season) {
				query("INSERT INTO season (series_id,number,name,episodes,myanimelist_id) VALUES (".$inserted_id.",".$season['number'].",".$season['name'].",".$season['episodes'].",".$season['myanimelist_id'].")");
			}
			foreach ($episodes as $episode) {
				query("INSERT INTO episode (series_id,season_id,number,name,duration) VALUES (".$inserted_id.",(SELECT id FROM season WHERE number=".$episode['season']." AND series_id=".$inserted_id."),".$episode['number'].",".$episode['name'].",".$episode['duration'].")");
			}

			$_SESSION['message']="S'han desat les dades correctament.<br /><a class=\"btn btn-primary mt-2\" href=\"version_edit.php?series_id=$inserted_id\"><span class=\"fa fa-plus pr-2\"></span>Crea'n una versió</a>";
		}

		header("Location: series_list.php");
		die();
	}

	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		$result = query("SELECT s.* FROM series s WHERE id=".escape($_GET['id']));
		$row = mysqli_fetch_assoc($result) or crash('Series not found');
		mysqli_free_result($result);

		$resultg = query("SELECT sg.* FROM rel_series_genre sg WHERE sg.series_id=".escape($_GET['id']));
		$genres = array();
		while ($rowg = mysqli_fetch_assoc($resultg)) {
			array_push($genres, $rowg['genre_id']);
		}
		mysqli_free_result($resultg);

		$resultss = query("SELECT ss.* FROM season ss WHERE ss.series_id=".escape($_GET['id'])." ORDER BY ss.number ASC");
		$seasons = array();
		while ($rowss = mysqli_fetch_assoc($resultss)) {
			array_push($seasons, $rowss);
		}
		mysqli_free_result($resultss);

		$resulte = query("SELECT e.*,ss.number season FROM episode e LEFT JOIN season ss ON e.season_id=ss.id WHERE e.series_id=".escape($_GET['id'])." ORDER BY ss.number IS NULL ASC, ss.number ASC, e.number IS NULL ASC, e.number ASC, e.name ASC");
		$episodes = array();
		while ($rowe = mysqli_fetch_assoc($resulte)) {
			array_push($episodes, $rowe);
		}
		mysqli_free_result($resulte);
	} else {
		$row = array();
		$genres = array();
		$seasons = array();
		$episodes = array();
		$row['show_seasons']=1;
		$row['show_expanded_seasons']=1;
		$row['show_episode_numbers']=1;
		$row['order_type']=0;
	}
?>
		<div class="container d-flex justify-content-center p-4">
			<div class="card w-100">
				<article class="card-body">
					<h4 class="card-title text-center mb-4 mt-1"><?php echo !empty($row['id']) ? "Edita la sèrie" : "Afegeix una sèrie"; ?></h4>
					<hr>
					<form method="post" action="series_edit.php" onsubmit="return checkNumberOfEpisodes()">
						<div class="row align-items-end">
							<div class="col-sm-3">
								<div class="form-group">
									<label for="form-myanimelist_id">Identificador de MyAnimeList</label>
									<input class="form-control" name="myanimelist_id" id="form-myanimelist_id" type="number" value="<?php echo $row['myanimelist_id']; ?>">
								</div>
							</div>
							<div class="col-sm form-group">
								<button type="button" id="import-from-mal" class="btn btn-primary">
									<span id="import-from-mal-loading" class="d-none spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
									<span id="import-from-mal-not-loading" class="fa fa-th-list pr-2"></span>Importa la fitxa de MyAnimeList
								</button>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label for="form-tadaima_id">Identificador de fil a Tadaima.cat</label>
									<input class="form-control" name="tadaima_id" id="form-tadaima_id" type="number" value="<?php echo $row['tadaima_id']; ?>">
								</div>
							</div>
						</div>
						<div id="import-from-mal-done" class="col-sm form-group alert alert-warning d-none">
							<span class="fa fa-exclamation-triangle pr-2"></span>S'ha importat la fitxa de MyAnimeList. Revisa que les dades siguin correctes i tradueix-ne la sinopsi i el nom, si s'escau.
						</div>
						<hr />
						<div class="row">
							<div class="col-sm">
								<div class="form-group">
									<label for="form-name-with-autocomplete" class="mandatory">Nom</label>
									<input class="form-control" name="name" id="form-name-with-autocomplete" required maxlength="200" value="<?php echo htmlspecialchars(html_entity_decode($row['name'])); ?>">
									<input type="hidden" name="id" id="id" value="<?php echo $row['id']; ?>">
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<label for="form-slug" class="mandatory">Identificador <small class="text-muted">(autogenerat, no cal editar-lo)</small></label>
									<input class="form-control" name="slug" id="form-slug" required maxlength="200" value="<?php echo htmlspecialchars($row['slug']); ?>">
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<label for="form-air_status">Estat</label>
									<div id="form-air_status" class="row pl-3 pr-3">
										<div class="form-check form-check-inline">
											<input class="form-check-input" type="checkbox" name="is_open" id="form-is_open" value="1"<?php echo $row['episodes']==-1? " checked" : ""; ?>>
											<label class="form-check-label" for="form-is_open">En emissió (sèrie oberta)</label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-8">
								<div class="form-group">
									<label for="form-alternate_names">Altres noms</label>
									<input class="form-control" name="alternate_names" id="form-alternate_names" maxlength="200" value="<?php echo htmlspecialchars(html_entity_decode($row['alternate_names'])); ?>">
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label for="form-score">Puntuació a MyAnimeList</label>
									<input class="form-control" name="score" id="form-score" type="number" value="<?php echo $row['score']; ?>" step=".01">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm">
								<div class="form-group">
									<label for="form-type" class="mandatory">Tipus</label>
									<select class="form-control" name="type" id="form-type" required>
										<option value="">- Selecciona un tipus -</option>
										<option value="movie"<?php echo $row['type']=='movie' ? " selected" : ""; ?>>Film</option>
										<option value="series"<?php echo $row['type']=='series' ? " selected" : ""; ?>>Sèrie</option>
									</select>
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<label for="form-air_date">Data d'estrena</label>
									<input class="form-control" name="air_date" type="date" id="form-air_date" maxlength="200" value="<?php echo !empty($row['air_date']) ? date('Y-m-d', strtotime($row['air_date'])) : ""; ?>">
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<label for="form-author">Autor</label>
									<input class="form-control" name="author" id="form-author" maxlength="200" value="<?php echo htmlspecialchars($row['author']); ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm">
								<div class="form-group">
									<label for="form-director">Director</label>
									<input class="form-control" name="director" id="form-director" maxlength="200" value="<?php echo htmlspecialchars($row['director']); ?>">
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<label for="form-studio">Estudi</label>
									<input class="form-control" name="studio" id="form-studio" maxlength="200" value="<?php echo htmlspecialchars($row['studio']); ?>">
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<label for="form-rating">Valoració per edats</label>
									<select class="form-control" name="rating" id="form-rating">
										<option value="">- Sense valoració -</option>
										<option value="TP"<?php echo $row['rating']=='TP' ? " selected" : ""; ?>>Tots els públics</option>
										<option value="+7"<?php echo $row['rating']=='+7' ? " selected" : ""; ?>>Majors de 7 anys</option>
										<option value="+13"<?php echo $row['rating']=='+13' ? " selected" : ""; ?>>Majors de 13 anys</option>
										<option value="+16"<?php echo $row['rating']=='+16' ? " selected" : ""; ?>>Majors de 16 anys</option>
										<option value="+18"<?php echo $row['rating']=='+18' ? " selected" : ""; ?>>Majors de 18 anys</option>
										<option value="XXX"<?php echo $row['rating']=='XXX' ? " selected" : ""; ?>>Majors de 18 anys (hentai)</option>
									</select>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="form-synopsis" class="mandatory">Sinopsi</label>
							<textarea class="form-control" name="synopsis" id="form-synopsis" required style="height: 150px;"><?php echo htmlspecialchars(str_replace('&#039;',"'",html_entity_decode($row['synopsis']))); ?></textarea>
						</div>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label for="form-duration">Durada</label>
									<input class="form-control" name="duration" id="form-duration" maxlength="200" value="<?php echo htmlspecialchars($row['duration']); ?>">
								</div>
							</div>
							<div class="col-sm-7">
								<div class="form-group">
									<label for="form-image" class="mandatory">URL de la imatge de portada</label>
									<input class="form-control" name="image" type="url" id="form-image" required maxlength="200" value="<?php echo htmlspecialchars($row['image']); ?>" oninput="$('#form-image-preview').prop('src',$(this).val());$('#form-image-preview-link').prop('href',$(this).val());">
								</div>
							</div>
							<div class="col-sm-1">
								<div class="form-group">
									<a id="form-image-preview-link" href="<?php echo htmlspecialchars($row['image']); ?>" target="_blank">
										<img id="form-image-preview" style="width: 64px; height: 90px; object-fit: cover; background-color: black; display:inline-block;" src="<?php echo htmlspecialchars($row['image']); ?>" alt="">
									</a>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="form-genres">Gèneres</label>
							<div id="form-genres" class="row pl-3 pr-3">
<?php
	$resultg = query("SELECT g.* FROM genre g ORDER BY g.name");
	while ($rowg = mysqli_fetch_assoc($resultg)) {
?>
								<div class="form-check form-check-inline col-sm-2">
									<input class="form-check-input" type="checkbox" name="genres[]" id="form-genre-<?php echo $rowg['id']; ?>" data-myanimelist-id="<?php echo $rowg['myanimelist_id']; ?>" value="<?php echo $rowg['id']; ?>"<?php echo in_array($rowg['id'],$genres)? "checked" : ""; ?>>
									<label class="form-check-label" for="form-genre-<?php echo $rowg['id']; ?>"><?php echo htmlspecialchars($rowg['name']); ?></label>
								</div>
<?php
	}
	mysqli_free_result($resultg);
?>
							</div>
						</div>
						<div class="form-group">
							<label for="form-season-list">Temporades</label>
							<div class="container" id="form-season-list">
								<div class="row">
									<div class="w-100 column">
										<table class="table table-bordered table-hover table-sm" id="season-list-table" data-count="<?php echo max(count($seasons),1); ?>">
											<thead>
												<tr>
													<th style="width: 10%;" class="mandatory">Núm.</th>
													<th>Nom <small class="text-muted">(només es mostra si n'hi ha més d'una i la casella de dalt està marcada)</small></th>
													<th class="mandatory" style="width: 15%;">Capítols</th>
													<th style="width: 15%;">Id. MyAnimeList</th>
													<th class="text-center" style="width: 5%;">Acció</th>
												</tr>
											</thead>
											<tbody>
<?php
	for ($i=0;$i<count($seasons);$i++) {
?>
												<tr id="form-season-list-row-<?php echo $i+1; ?>">
													<td>
														<input id="form-season-list-number-<?php echo $i+1; ?>" name="form-season-list-number-<?php echo $i+1; ?>" type="number" class="form-control" value="<?php echo $seasons[$i]['number']; ?>" required/>
														<input id="form-season-list-id-<?php echo $i+1; ?>" name="form-season-list-id-<?php echo $i+1; ?>" type="hidden" value="<?php echo $seasons[$i]['id']; ?>"/>
													</td>
													<td>
														<input id="form-season-list-name-<?php echo $i+1; ?>" name="form-season-list-name-<?php echo $i+1; ?>" type="text" class="form-control" value="<?php echo htmlspecialchars($seasons[$i]['name']); ?>" placeholder="(Sense nom)"/>
													</td>
													<td>
														<input id="form-season-list-episodes-<?php echo $i+1; ?>" name="form-season-list-episodes-<?php echo $i+1; ?>" type="number" class="form-control" value="<?php echo $seasons[$i]['episodes']; ?>" required/>
													</td>
													<td>
														<input id="form-season-list-myanimelist_id-<?php echo $i+1; ?>" name="form-season-list-myanimelist_id-<?php echo $i+1; ?>" type="number" class="form-control" value="<?php echo $seasons[$i]['myanimelist_id']; ?>"/>
													</td>
													<td class="text-center align-middle">
														<button id="form-season-list-delete-<?php echo $i+1; ?>" onclick="deleteSeasonRow(<?php echo $i+1; ?>);" type="button" class="btn fa fa-trash p-1 text-danger"></button>
													</td>
												</tr>
<?php
	}
	if (count($seasons)==0) {
?>
												<tr id="form-season-list-row-1">
													<td>
														<input id="form-season-list-number-1" name="form-season-list-number-1" type="number" class="form-control" value="1" required/>
														<input id="form-season-list-id-1" name="form-season-list-id-1" type="hidden" value="-1"/>
													</td>
													<td>
														<input id="form-season-list-name-1" name="form-season-list-name-1" type="text" class="form-control" value="" placeholder="(Sense nom)"/>
													</td>
													<td>
														<input id="form-season-list-episodes-1" name="form-season-list-episodes-1" type="number" class="form-control" value="" required/>
													</td>
													<td>
														<input id="form-season-list-myanimelist_id-1" name="form-season-list-myanimelist_id-1" type="number" class="form-control" value=""/>
													</td>
													<td class="text-center align-middle">
														<button id="form-season-list-delete-1" onclick="deleteSeasonRow(1);" type="button" class="btn fa fa-trash p-1 text-danger"></button>
													</td>
												</tr>
<?php
	}
?>
											</tbody>
										</table>
									</div>
									<button onclick="addSeasonRow();" type="button" class="btn btn-success btn-sm"><span class="fa fa-plus pr-2"></span>Afegeix una temporada</button>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="form-episode-list">Capítols</label>
							<div id="import-from-mal-episodes-done" class="col-sm form-group alert alert-warning d-none">
								<span class="fa fa-exclamation-triangle pr-2"></span>S'han importat els capítols de MyAnimeList. Revisa'n tots els camps.
							</div>
							<div class="container" id="form-episode-list">
								<div class="row">
									<div class="w-100 column">
										<table class="table table-bordered table-hover table-sm" id="episode-list-table" data-count="<?php echo max(count($episodes),1); ?>">
											<thead>
												<tr>
													<th style="width: 10%;">Temp.</th>
													<th style="width: 10%;">Núm.</th>
													<th>Títol <small class="text-muted">(informatiu, només es mostra públicament en el cas dels especials)</small></th>
													<th style="width: 15%;" class="mandatory">Durada (min)</th>
													<th class="text-center" style="width: 5%;">Acció</th>
												</tr>
											</thead>
											<tbody>
<?php
	for ($i=0;$i<count($episodes);$i++) {
?>
												<tr id="form-episode-list-row-<?php echo $i+1; ?>">
													<td>
														<input id="form-episode-list-season-<?php echo $i+1; ?>" name="form-episode-list-season-<?php echo $i+1; ?>" type="number" class="form-control" value="<?php echo $episodes[$i]['season']; ?>" placeholder="(Altres)"/>
													</td>
													<td>
														<input id="form-episode-list-num-<?php echo $i+1; ?>" name="form-episode-list-num-<?php echo $i+1; ?>" type="number" class="form-control" value="<?php echo $episodes[$i]['number']; ?>" placeholder="(Esp.)"/>
														<input id="form-episode-list-id-<?php echo $i+1; ?>" name="form-episode-list-id-<?php echo $i+1; ?>" type="hidden" value="<?php echo $episodes[$i]['id']; ?>"/>
													</td>
													<td>
														<input id="form-episode-list-name-<?php echo $i+1; ?>" name="form-episode-list-name-<?php echo $i+1; ?>" type="text" class="form-control" value="<?php echo htmlspecialchars($episodes[$i]['name']); ?>" placeholder="(Sense títol)"/>
													</td>
													<td>
														<input id="form-episode-list-duration-<?php echo $i+1; ?>" name="form-episode-list-duration-<?php echo $i+1; ?>" type="number" class="form-control" value="<?php echo $episodes[$i]['duration']; ?>" required/>
													</td>
													<td class="text-center align-middle">
														<button id="form-episode-list-delete-<?php echo $i+1; ?>" onclick="deleteRow(<?php echo $i+1; ?>);" type="button" class="btn fa fa-trash p-1 text-danger"></button>
													</td>
												</tr>
<?php
	}
	if (count($episodes)==0) {
?>
												<tr id="form-episode-list-row-1">
													<td>
														<input id="form-episode-list-season-1" name="form-episode-list-season-1" type="number" class="form-control" value="1" placeholder="(Altres)"/>
													</td>
													<td>
														<input id="form-episode-list-num-1" name="form-episode-list-num-1" type="number" class="form-control" value="1" placeholder="(Esp.)"/>
														<input id="form-episode-list-id-1" name="form-episode-list-id-1" type="hidden" value="-1"/>
													</td>
													<td>
														<input id="form-episode-list-name-1" name="form-episode-list-name-1" type="text" class="form-control" value="" placeholder="(Sense títol)"/>
													</td>
													<td>
														<input id="form-episode-list-duration-1" name="form-episode-list-duration-1" type="number" class="form-control" value="" required/>
													</td>
													<td class="text-center align-middle">
														<button id="form-episode-list-delete-1" onclick="deleteRow(1);" type="button" class="btn fa fa-trash p-1 text-danger"></button>
													</td>
												</tr>
<?php
	}
?>
											</tbody>
										</table>
									</div>
									<button onclick="addRow(false);" type="button" class="btn btn-success btn-sm"><span class="fa fa-plus pr-2"></span>Afegeix un capítol</button>
									<button onclick="addRow(true);" type="button" class="btn btn-success btn-sm ml-2"><span class="fa fa-plus pr-2"></span>Afegeix un especial</button>
									<span style="flex-grow: 1;"></span>
									<button type="button" id="import-from-mal-episodes" class="btn btn-primary btn-sm">
										<span id="import-from-mal-episodes-loading" class="d-none spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
										<span id="import-from-mal-episodes-not-loading" class="fa fa-th-list pr-2"></span>
										Importa els capítols de MyAnimeList
									</button>
									<button type="button" id="generate-episodes" class="btn btn-primary btn-sm ml-2">
										<span class="fa fa-sort-numeric-down pr-2"></span>
										Genera els capítols automàticament
									</button>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="form-view-options">Opcions de visualització de la fitxa pública</label>
							<div id="form-view-options" class="row pl-3 pr-3">
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="checkbox" name="show_seasons" id="form-show_seasons" value="1"<?php echo $row['show_seasons']==1 ? " checked" : ""; ?>>
									<label class="form-check-label" for="form-show_seasons">Separa per temporades i mostra'n els noms <small class="text-muted">(si només n'hi ha una, no es mostrarà)</small></label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="checkbox" name="show_expanded_seasons" id="form-show_expanded_seasons" value="1"<?php echo $row['show_expanded_seasons']==1 ? " checked" : ""; ?>>
									<label class="form-check-label" for="form-show_expanded_seasons">Mostra les temporades desplegades per defecte <small class="text-muted">(si n'hi ha moltes o amb molts capítols, és recomanable desmarcar-ho)</small></label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="checkbox" name="show_episode_numbers" id="form-show_episode_numbers" value="1"<?php echo $row['show_episode_numbers']==1 ? " checked" : ""; ?>>
									<label class="form-check-label" for="form-show_episode_numbers">Mostra el número dels capítols normals <small class="text-muted">(afegeix "Capítol X: " davant del nom dels capítols no especials)</small></label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" name="order_type" id="form-order_type_standard" value="0"<?php echo $row['order_type']==0 ? " checked" : ""; ?>>
									<label class="form-check-label" for="form-order_type_standard">Aplica l'ordenació estàndard <small class="text-muted">(primer capítols normals per ordre numèric, després especials per ordre alfabètic estricte)</small></label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" name="order_type" id="form-order_type_alphabetic" value="1"<?php echo $row['order_type']==1 ? " checked" : ""; ?>>
									<label class="form-check-label" for="form-order_type_alphabetic">Aplica l'ordenació alfabètica estricta <small class="text-muted">(capítols i especials barrejats, ordre: 1, 10, 11, 12..., 2, 3...)</small></label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" name="order_type" id="form-order_type_natural" value="2"<?php echo $row['order_type']==2 ? " checked" : ""; ?>>
									<label class="form-check-label" for="form-order_type_natural">Aplica l'ordenació alfabètica natural <small class="text-muted">(capítols i especials barrejats, ordre: 1, 2, 3... 10, 11, 12...)</small></label>
								</div>
							</div>
						</div>
						<div class="form-group text-center pt-2">
							<button type="submit" name="action" value="<?php echo !empty($row['id']) ? "edit" : "add"; ?>" class="btn btn-primary font-weight-bold"><span class="fa fa-check pr-2"></span><?php echo !empty($row['id']) ? "Desa els canvis" : "Afegeix la sèrie"; ?></button>
						</div>
					</form>
				</article>
			</div>
		</div>
<?php
} else {
	header("Location: login.php");
}

include("footer.inc.php");
?>
