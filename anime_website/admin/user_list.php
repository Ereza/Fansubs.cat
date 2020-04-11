<?php
$header_title="Usuaris";
$page="user";
include("header.inc.php");

if (!empty($_SESSION['username']) && !empty($_SESSION['admin_level']) && $_SESSION['admin_level']>=3) {
	if (!empty($_GET['delete_id'])) {
		log_action("delete", "user", $_GET['delete_id']);
		query("DELETE FROM user WHERE username='".escape($_GET['delete_id'])."'");
		$_SESSION['message']="S'ha suprimit correctament.";
	}
?>
		<div class="container d-flex justify-content-center p-4">
			<div class="card w-100">
				<article class="card-body">
					<h4 class="card-title text-center mb-4 mt-1">Llista d'usuaris</h4>
					<hr>

<?php
	if (!empty($_SESSION['message'])) {
?>
					<p class="alert alert-success text-center"><?php echo $_SESSION['message']; ?></p>
<?php
		$_SESSION['message']=NULL;
	}
?>

					<table class="table table-hover table-striped">
						<thead class="thead-dark">
							<tr>
								<th scope="col">Usuari</th>
								<th class="text-center" scope="col">Nivell d'administrador</th>
								<th class="text-center" scope="col">Fansub associat</th>
								<th class="text-center" scope="col">Accions</th>
							</tr>
						</thead>
						<tbody>
<?php
	$result = query("SELECT u.*, f.name fansub_name FROM user u LEFT JOIN fansub f ON u.fansub_id=f.id ORDER BY u.username ASC");
	while ($row = mysqli_fetch_assoc($result)) {
?>
							<tr>
								<th scope="row" class="align-middle"><?php echo $row['username']; ?></th>
								<td class="align-middle text-center"><?php echo $row['admin_level']; ?></td>
								<td class="align-middle text-center"><?php echo $row['fansub_name']!=NULL ? $row['fansub_name'] : '-'; ?></td>
								<td class="align-middle text-center"><a href="user_edit.php?id=<?php echo $row['username']; ?>" title="Modifica" class="fa fa-edit p-1"></a> <a href="user_list.php?delete_id=<?php echo $row['username']; ?>" title="Suprimeix" onclick="return confirm(<?php echo htmlspecialchars(json_encode("Segur que vols suprimir l'usuari '".$row['username']."'? L'acció no es podrà desfer.")); ?>)" onauxclick="return false;" class="fa fa-trash p-1 text-danger"></a></td>
							</tr>
<?php
	}
	mysqli_free_result($result);
?>
						</tbody>
					</table>
					<div class="text-center">
						<a href="user_edit.php" class="btn btn-primary"><span class="fa fa-plus pr-2"></span>Afegeix un usuari</a>
					</div>
				</article>
			</div>
		</div>
<?php
} else {
	header("Location: login.php");
}

include("footer.inc.php");
?>
