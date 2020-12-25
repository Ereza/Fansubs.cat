				</div>
			</div>

			<div id="sidebar" class="aside">
<?php
if (strcmp(date('Y-m-d'),'2020-12-01')>=0 && strcmp(date('Y-m-d'),'2020-12-25')<=0){
?>
				<div class="section promo">
					<a href="https://www.fansubs.cat/nadal/"><img src="/style/images/advent_promo.png" alt="Calendari d'advent dels fansubs en català" /></a>
				</div>
<?php
}
?>
				<div class="section promo">
					<a href="https://anime.fansubs.cat/"><img src="/style/images/anime_promo.png" alt="Mira tot l'anime dels fansubs en català!" /></a>
				</div>
				<div class="section promo">
					<a href="https://manga.fansubs.cat/"><img src="/style/images/manga_promo.png" alt="Llegeix tot el manga dels fansubs en català!" /></a>
				</div>
<?php
if (!isset($fansub_id) || $fansub_id==NULL){
?>
				<div class="section">
					<h2>Cerca notícies</h2>
					<ul>
						<li>
							<form id="search_form">
								<input id="search_query" required type="text" value="<?php echo (isset($query) ? $query : ''); ?>" placeholder="Introdueix la cerca..." />
								<input id="search_button" type="image" src="/style/images/search.png" title="Cerca" alt="Cerca" />
							</form>
						</li>
					</ul>
				</div>
<?php
}
?>
				<div class="section">
					<h2>Fansubs actius</h2>
					<ul>
<?php
$result = mysqli_query($db_connection, "SELECT DISTINCT f.* FROM fansubs f LEFT JOIN news n ON f.id=n.fansub_id WHERE f.is_visible=1 AND f.is_historical=0 AND n.date>='".date('Y-m-d H:i:s', time()-3600*24*180)."' ORDER BY name ASC") or crash(mysqli_error($db_connection));

while ($row = mysqli_fetch_assoc($result)){
?>
						<li>
							<img src="/images/fansubs/favicons/<?php echo $row['favicon_image']; ?>" alt="" height="14" width="14" />
							<a href="<?php echo $row['url']; ?>"><?php echo $row['name']; ?></a>
						</li>
<?php
}
?>
					</ul>
				</div>

<?php
mysqli_free_result($result);
$result = mysqli_query($db_connection, "SELECT * FROM fansubs WHERE id NOT IN (SELECT DISTINCT f.id FROM fansubs f LEFT JOIN news n ON f.id=n.fansub_id WHERE f.is_visible=1 AND f.is_historical=0 AND n.date>='".date('Y-m-d H:i:s', time()-3600*24*180)."') AND id IN (SELECT DISTINCT f.id FROM fansubs f LEFT JOIN news n ON f.id=n.fansub_id WHERE f.is_visible=1 AND f.is_historical=0 AND n.date>='".date('Y-m-d H:i:s', time()-3600*24*365*2)."') AND is_visible=1 AND is_historical=0 ORDER BY name ASC") or crash(mysqli_error($db_connection));

if (mysqli_num_rows($result)>0) {
?>
				<div class="section">
					<h2>Fansubs inactius</h2>
					<ul>
<?php

	while ($row = mysqli_fetch_assoc($result)){
?>
						<li>
							<img src="/images/fansubs/favicons/<?php echo $row['favicon_image']; ?>" alt="" height="14" width="14" />
							<a href="<?php echo $row['url']; ?>"><?php echo $row['name']; ?></a>
						</li>
<?php
	}
	mysqli_free_result($result);
?>
					</ul>
				</div>
<?php
}
?>

				<div class="section">
					<h2>Fansubs històrics</h2>
					<ul>
<?php
$result = mysqli_query($db_connection, "SELECT * FROM fansubs WHERE is_visible=1 AND (is_historical=1 OR id NOT IN (SELECT DISTINCT f.id FROM fansubs f LEFT JOIN news n ON f.id=n.fansub_id WHERE f.is_visible=1 AND f.is_historical=0 AND n.date>='".date('Y-m-d H:i:s', time()-3600*24*365*2)."')) ORDER BY name ASC") or crash(mysqli_error($db_connection));

while ($row = mysqli_fetch_assoc($result)){
?>
						<li>
							<img src="/images/fansubs/favicons/<?php echo $row['favicon_image']; ?>" alt="" height="14" width="14" />
							<a<?php echo $row['archive_url']!=NULL ? ' class="archive-org-link" title="Versió històrica a Archive.org"' : ''; ?> href="<?php echo $row['archive_url']!=NULL ? $row['archive_url'] : $row['url']; ?>"><?php echo $row['name']; ?></a>
						</li>
<?php
}
mysqli_free_result($result);
?>
					</ul>
				</div>

<?php
if ($header_current_page=='main' && (!isset($fansub_id) || $fansub_id==NULL)){
?>
				<div class="section">
					<h2>Arxiu</h2>
					<ul>
						<li>
							<img src="/style/images/icon_archive.png" alt="" height="14" width="14" />
							<a href="/arxiu">Mostra tot l'historial</a>
						</li>
					</ul>
				</div>
<?php
}
else{
?>
				<div class="section">
					<h2>Totes les notícies</h2>
					<ul>
						<li>
							<img src="/style/images/icon_home.png" alt="" height="14" width="14" />
							<a href="/">Torna a l'inici</a>
						</li>
					</ul>
				</div>
<?php
}
?>
				<div class="section">
					<h2>Fansubs.cat</h2>
					<ul>
						<li>
							<img src="/style/images/icon_contact.png" alt="" height="14" width="14" />
							<a href="/envia-noticies-contacta">Envia notícies / Contacta</a>
						</li>
						<li>
							<img src="/style/images/icon_stats.png" alt="" height="14" width="14" />
							<a href="/estadistiques">Estadístiques</a>
						</li>
						<li>
							<img src="/style/images/icon_status.png" alt="" height="14" width="14" />
							<a href="/estat-del-sistema">Estat del sistema</a>
						</li>
					</ul>
				</div>
			</div>

			<div id="footer">
				<p>
					Fansubs.cat sols recopila notícies dels webs esmentats. Tots els drets dels textos, imatges i obres esmentades pertanyen a llurs propietaris.<br />
					Codi, disseny i contingut propi © 2015-<?php echo date('Y'); ?> Fansubs.cat. <a href="https://github.com/fansubscat/Fansubs.cat" target="_blank">És codi obert</a>. <a href="/envia-noticies-contacta">Contacta'ns</a>. <a href="https://twitter.com/fansubscat" target="_blank">Segueix-nos al Twitter</a>.
				</p>
			</div>
		</div>
	</body>
</html>
<?php
ob_flush();
mysqli_close($db_connection);
?>
