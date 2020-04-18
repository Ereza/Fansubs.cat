<?php
$header_title="Fansubs";
$page="fansub";
include("header.inc.php");

if (!empty($_SESSION['username']) && !empty($_SESSION['admin_level']) && $_SESSION['admin_level']>=3) {
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
		if (!empty($_POST['url'])) {
			$data['url']="'".escape($_POST['url'])."'";
		} else {
			$data['url']="NULL";
		}
		if (!empty($_POST['status']) && $_POST['status']==1) {
			$data['status']=1;
		} else {
			$data['status']=0;
		}
		
		if ($_POST['action']=='edit') {
			log_action("update-fansub", "S'ha actualitzat el fansub amb nom '".$data['name']."' (id. de fansub: ".$data['id'].")");
			query("UPDATE fansub SET name='".$data['name']."',url=".$data['url'].",status=".$data['status'].",updated=CURRENT_TIMESTAMP,updated_by='".escape($_SESSION['username'])."' WHERE id=".$data['id']);
		}
		else {
			log_action("create-fansub", "S'ha creat un fansub amb nom '".$data['name']."'");
			query("INSERT INTO fansub (name,url,status,created,created_by,updated,updated_by) VALUES ('".$data['name']."',".$data['url'].",".$data['status'].",CURRENT_TIMESTAMP,'".escape($_SESSION['username'])."',CURRENT_TIMESTAMP,'".escape($_SESSION['username'])."')");
		}

		$_SESSION['message']="S'han desat les dades correctament.";

		header("Location: fansub_list.php");
		die();
	}

	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		$result = query("SELECT f.* FROM fansub f WHERE id=".escape($_GET['id']));
		$row = mysqli_fetch_assoc($result) or crash('Fansub not found');
		mysqli_free_result($result);
	} else {
		$row = array();
	}
?>
		<div class="container d-flex justify-content-center p-4">
			<div class="card w-100">
			<article class="card-body">
				<h4 class="card-title text-center mb-4 mt-1"><?php echo !empty($row['id']) ? "Edita el fansub" : "Afegeix un fansub"; ?></h4>
				<hr>
				<form method="post" action="fansub_edit.php">
					<div class="form-group">
						<label for="form-name">Nom</label>
						<input class="form-control" name="name" id="form-name" required maxlength="200" value="<?php echo htmlspecialchars($row['name']); ?>">
						<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
					</div>
					<div class="form-group">
						<label for="form-url">URL</label>
						<input class="form-control" type="url" name="url" id="form-url" maxlength="200" value="<?php echo htmlspecialchars($row['url']); ?>">
					</div>
					<div class="form-group">
						<label for="form-status">Estat</label>
						<div id="form-status" class="row pl-3 pr-3">
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="checkbox" name="status" id="form-active" value="1"<?php echo $row['status']==1? " checked" : ""; ?>>
								<label class="form-check-label" for="form-active">Actiu</label>
							</div>
						</div>
					</div>
					<div class="form-group text-center pt-2">
						<button type="submit" name="action" value="<?php echo !empty($row['id']) ? "edit" : "add"; ?>" class="btn btn-primary font-weight-bold"><span class="fa fa-check pr-2"></span><?php echo !empty($row['id']) ? "Desa els canvis" : "Afegeix el fansub"; ?></button>
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