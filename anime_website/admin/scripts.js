function populateMalData(data, staff) {
	if ($("#form-name-with-autocomplete").val()=='') {
		$("#form-name-with-autocomplete").val(data.title);
	}
	if ($("#form-slug").val()=='') {
		$("#form-slug").val(string_to_slug(data.title));
	}
	if ($("#form-alternate_names").val()=='') {
		if (data.title && data.title_english) {
			$("#form-alternate_names").val(data.title+', '+data.title_english);
		} else if (data.title) {
			$("#form-alternate_names").val(data.title);
		} else if (data.title_english) {
			$("#form-alternate_names").val(data.title_english);
		}
	}
	if ($("#form-type").val()=='') {
		$("#form-type").val(data.episodes>1 ? 'series' : 'movie');
	}
	if ($("#form-air_date").val()=='') {
		$("#form-air_date").val(data.aired.from.substr(0, 10));
	}
	if (data.rating=='G - All Ages') {
		$("#form-rating").val('TP');
	} else if (data.rating=='PG - Children') {
		$("#form-rating").val('+7');
	} else if (data.rating=='PG-13 - Teens 13 or older') {
		$("#form-rating").val('+13');
	} else if (data.rating=='R - 17+ (violence & profanity)') {
		$("#form-rating").val('+16');
	} else if (data.rating=='R+ - Mild Nudity') {
		$("#form-rating").val('+18');
	} else if (data.rating=='Rx - Hentai') {
		$("#form-rating").val('XXX');
	} else {
		$("#form-rating").val('');
	}
	if ($("#form-synopsis").val()=='') {
		$("#form-synopsis").val(data.synopsis);
	}
	if ($("#form-duration").val()=='') {
		$("#form-duration").val(data.duration ? data.duration.replace('per ep','per capítol').replace('hr','h') : data.duration);
	}
	if ($("#form-image").val()=='' || $("#form-image").val().startsWith("https://cdn.myanimelist.net")) {
		var url = data.image_url ? data.image_url.replace(".jpg","l.jpg") : data.image_url;
		$("#form-image").val(url);
		$('#form-image-preview').prop('src', url);
		$('#form-image-preview-link').prop('href', url);
	}
	if ($("#form-episodes").val()=='' || $("#form-episodes").val()=='0') {
		$("#form-episodes").val(data.episodes);
	}

	if (data.episodes==1) {
		//Movie, populate first episode
		if ($('#form-episode-list-num-1').val()=='') {
			$('#form-episode-list-num-1').val("1");
			$('#form-episode-list-name-1').val($("#form-name-with-autocomplete").val());
			$('#form-episode-list-date-1').val(data.aired.from.substr(0, 10));
		}
	}

	var authors = staff.staff.filter(function(value, index, array) {
		return value.positions.includes("Original Creator");
	});

	var textAuthors = "";
	for (var i = 0; i < authors.length; i++) {
		var authorName;
		if (authors[i].name.includes(', ')) {
			authorName=authors[i].name.split(', ')[1]+" "+authors[i].name.split(', ')[0];
		} else {
			authorName=authors[i].name;
		}

		if (textAuthors!='') {
			textAuthors+=', ';
		}
		textAuthors+=authorName;
	}

	$("#form-author").val(textAuthors);

	var directors = staff.staff.filter(function(value, index, array) {
		return value.positions.includes("Director");
	});

	var textDirectors = "";
	for (var i = 0; i < directors.length; i++) {
		var directorName;
		if (directors[i].name.includes(', ')) {
			directorName=directors[i].name.split(', ')[1]+" "+directors[i].name.split(', ')[0];
		} else {
			directorName=directors[i].name;
		}

		if (textDirectors!='') {
			textDirectors+=', ';
		}
		textDirectors+=directorName;
	}

	$("#form-director").val(textDirectors);

	var textStudios = "";
	for (var i = 0; i < data.studios.length; i++) {
		if (textStudios!='') {
			textStudios+=', ';
		}
		textStudios+=data.studios[i].name;
	}

	$("#form-studio").val(textStudios);

	$("[name='genres[]']").each(function() {
		$(this).prop('checked', false);
	});
	
	for (var i = 0; i < data.genres.length; i++) {
		$("[data-myanimelist-id='"+data.genres[i].mal_id+"']").prop('checked', true);
	}
}

function populateMalEpisodes(episodes) {
	var i = parseInt($('#episode-list-table').attr('data-count'));
	for (var id=1;id<i+1;id++) {
		$("#form-episode-list-row-"+id).remove();
	}
	$('#episode-list-table').attr('data-count', 0);

	for (var i=0;i<episodes.episodes.length;i++) {
		addRow();
		$("#form-episode-list-num-"+(i+1)).val(episodes.episodes[i].episode_id);
		$("#form-episode-list-name-"+(i+1)).val(episodes.episodes[i].title);
		if (episodes.episodes[i].aired) {
			$("#form-episode-list-date-"+(i+1)).val(episodes.episodes[i].aired.substr(0,10));
		} else {
			$("#form-episode-list-date-"+(i+1)).val(episodes.episodes[i].aired);
		}
	}
}

//Taken from: https://gist.github.com/codeguy/6684588
function string_to_slug(str) {
	str = str.replace(/^\s+|\s+$/g, ''); // trim
	str = str.toLowerCase();

	// remove accents, swap ñ for n, etc
	var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;'";
	var to   = "aaaaeeeeiiiioooouuuunc-------";
	for (var i=0, l=from.length ; i<l ; i++) {
		str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
	}

	str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
		.replace(/\s+/g, '-') // collapse whitespace and replace by -
		.replace(/-+/g, '-'); // collapse dashes

	return str;
}

function addRow() {
	var i = parseInt($('#episode-list-table').attr('data-count'))+1;
	$('#episode-list-table').append('<tr id="form-episode-list-row-'+i+'"><td><input id="form-episode-list-num-'+i+'" name="form-episode-list-num-'+i+'" type="number" class="form-control" value="" placeholder="(Esp.)"/><input id="form-episode-list-id-'+i+'" name="form-episode-list-id-'+i+'" type="hidden" value="-1"/></td><td><input id="form-episode-list-name-'+i+'" name="form-episode-list-name-'+i+'" type="text" class="form-control" value="" placeholder="(Sense títol)"/></td><td><input id="form-episode-list-date-'+i+'" name="form-episode-list-date-'+i+'" type="date" class="form-control" value=""/></td><td class="text-center align-middle"><button id="form-episode-list-delete-'+i+'" onclick="deleteRow('+i+');" type="button" class="btn fa fa-trash p-1 text-danger"></button></td></tr>');
	$('#episode-list-table').attr('data-count', i);
}

function deleteRow(id) {
	var i = parseInt($('#episode-list-table').attr('data-count'));
	if(i==1) {
		alert('La sèrie ha de tenir un capítol, com a mínim!');
	}
	else {
		$("#form-episode-list-row-"+id).remove();
		for (var j=id+1;j<i+1;j++) {
			$("#form-episode-list-row-"+j).attr('id','form-episode-list-row-'+(j-1));
			$("#form-episode-list-id-"+j).attr('name','form-episode-list-id-'+(j-1));
			$("#form-episode-list-id-"+j).attr('id','form-episode-list-id-'+(j-1));
			$("#form-episode-list-num-"+j).attr('name','form-episode-list-num-'+(j-1));
			$("#form-episode-list-num-"+j).attr('id','form-episode-list-num-'+(j-1));
			$("#form-episode-list-name-"+j).attr('name','form-episode-list-name-'+(j-1));
			$("#form-episode-list-name-"+j).attr('id','form-episode-list-name-'+(j-1));
			$("#form-episode-list-date-"+j).attr('name','form-episode-list-date-'+(j-1));
			$("#form-episode-list-date-"+j).attr('id','form-episode-list-date-'+(j-1));
			$("#form-episode-list-delete-"+j).attr('onclick','deleteRow('+(j-1)+');');
			$("#form-episode-list-delete-"+j).attr('id','form-episode-list-delete-'+(j-1));
		}
		$('#episode-list-table').attr('data-count', i-1);
	}
}

function addVersionRow(episode_id) {
	var i = parseInt($('#links-list-table-'+episode_id).attr('data-count'))+1;
	$('#links-list-table-'+episode_id).append('<tr id="form-links-list-'+episode_id+'-row-'+i+'"><td><input id="form-links-list-'+episode_id+'-link-'+i+'" name="form-links-list-'+episode_id+'-link-'+i+'" type="url" class="form-control" value="" maxlength="200" placeholder="(Sense enllaç)"/><input id="form-links-list-'+episode_id+'-id-'+i+'" name="form-links-list-'+episode_id+'-id-'+i+'" type="hidden" value="-1"/></td><td><input id="form-links-list-'+episode_id+'-resolution-'+i+'" name="form-links-list-'+episode_id+'-resolution-'+i+'" type="text" class="form-control" list="resolution-options" value="" maxlength="200" placeholder="- Tria -"/></td><td><input id="form-links-list-'+episode_id+'-comments-'+i+'" name="form-links-list-'+episode_id+'-comments-'+i+'" type="text" class="form-control" value="" maxlength="200"/></td><td class="text-center align-middle"><button id="form-links-list-'+episode_id+'-delete-'+i+'" onclick="deleteVersionRow('+episode_id+','+i+');" type="button" class="btn fa fa-trash p-1 text-danger"></button></td></tr>');
	$('#links-list-table-'+episode_id).attr('data-count', i);
}

function addVersionExtraRow() {
	var i = parseInt($('#extras-list-table').attr('data-count'))+1;
	$('#extras-list-table').append('<tr id="form-extras-list-row-'+i+'"><td><input id="form-extras-list-name-'+i+'" name="form-extras-list-name-'+i+'" type="text" class="form-control" value="" maxlength="200" required placeholder="- Introdueix un nom -"/><input id="form-extras-list-id-'+i+'" name="form-extras-list-id-'+i+'" type="hidden" value="-1"/></td><td><input id="form-extras-list-link-'+i+'" name="form-extras-list-link-'+i+'" type="url" class="form-control" value="" maxlength="200" required placeholder="- Introdueix un enllaç -"/></td><td><input id="form-extras-list-resolution-'+i+'" name="form-extras-list-resolution-'+i+'" type="text" class="form-control" list="resolution-options" value="" maxlength="200" placeholder="- Tria -"/></td><td><input id="form-extras-list-comments-'+i+'" name="form-extras-list-comments-'+i+'" type="text" class="form-control" value="" maxlength="200"/></td><td class="text-center align-middle"><button id="form-extras-list-delete-'+i+'" onclick="deleteVersionExtraRow('+i+');" type="button" class="btn fa fa-trash p-1 text-danger"></button></td></tr>');
	$('#extras-list-table').attr('data-count', i);
	$('#extras-list-table-empty').addClass('d-none');
}

function addVersionFolderRow() {
	var i = parseInt($('#folders-list-table').attr('data-count'))+1;

	var html = $('#form-folders-list-account_id-XXX').prop('outerHTML').replace(/XXX/g, i).replace(' d-none">','" required>');

	$('#folders-list-table').append('<tr id="form-folders-list-row-'+i+'"><td>'+html+'<input id="form-folders-list-id-'+i+'" name="form-folders-list-id-'+i+'" type="hidden" value="-1"/></td><td><input id="form-folders-list-folder-'+i+'" name="form-folders-list-folder-'+i+'" class="form-control" value="" maxlength="200" required/></td><td class="text-center align-middle"><input id="form-folders-list-active-'+i+'" name="form-folders-list-active-'+i+'" type="checkbox" value="1"/></td><td class="text-center align-middle"><button id="form-folders-list-delete-'+i+'" onclick="deleteVersionFolderRow('+i+');" type="button" class="btn fa fa-trash p-1 text-danger"></button></td></tr>');
	$('#folders-list-table').attr('data-count', i);
	$('#folders-list-table-empty').addClass('d-none');
}

function deleteVersionRow(episode_id, id) {
	var i = parseInt($('#links-list-table-'+episode_id).attr('data-count'));
	if(i==1) {
		$("#form-links-list-"+episode_id+"-id-1").val("-1");
		$("#form-links-list-"+episode_id+"-link-1").val("");
		$("#form-links-list-"+episode_id+"-resolution-1").val("");
		$("#form-links-list-"+episode_id+"-comments-1").val("");
	}
	else {
		$("#form-links-list-"+episode_id+"-row-"+id).remove();
		for (var j=id+1;j<i+1;j++) {
			$("#form-links-list-"+episode_id+"-row-"+j).attr('id','form-links-list-'+episode_id+'-row-'+(j-1));
			$("#form-links-list-"+episode_id+"-id-"+j).attr('name','form-links-list-'+episode_id+'-id-'+(j-1));
			$("#form-links-list-"+episode_id+"-id-"+j).attr('id','form-links-list-'+episode_id+'-id-'+(j-1));
			$("#form-links-list-"+episode_id+"-link-"+j).attr('name','form-links-list-'+episode_id+'-link-'+(j-1));
			$("#form-links-list-"+episode_id+"-link-"+j).attr('id','form-links-list-'+episode_id+'-link-'+(j-1));
			$("#form-links-list-"+episode_id+"-resolution-"+j).attr('name','form-links-list-'+episode_id+'-resolution-'+(j-1));
			$("#form-links-list-"+episode_id+"-resolution-"+j).attr('id','form-links-list-'+episode_id+'-resolution-'+(j-1));
			$("#form-links-list-"+episode_id+"-comments-"+j).attr('name','form-links-list-'+episode_id+'-comments-'+(j-1));
			$("#form-links-list-"+episode_id+"-comments-"+j).attr('id','form-links-list-'+episode_id+'-comments-'+(j-1));
			$("#form-links-list-"+episode_id+"-delete-"+j).attr('onclick','deleteVersionRow('+episode_id+','+(j-1)+');');
			$("#form-links-list-"+episode_id+"-delete-"+j).attr('id','form-links-list-'+episode_id+'-delete-'+(j-1));
		}
		$('#links-list-table-'+episode_id).attr('data-count', i-1);
	}
}

function deleteVersionExtraRow(id) {
	var i = parseInt($('#extras-list-table').attr('data-count'));
	$("#form-extras-list-row-"+id).remove();
	for (var j=id+1;j<i+1;j++) {
		$("#form-extras-list-row-"+j).attr('id','form-extras-list-row-'+(j-1));
		$("#form-extras-list-id-"+j).attr('name','form-extras-list-id-'+(j-1));
		$("#form-extras-list-id-"+j).attr('id','form-extras-list-id-'+(j-1));
		$("#form-extras-list-name-"+j).attr('name','form-extras-list-name-'+(j-1));
		$("#form-extras-list-name-"+j).attr('id','form-extras-list-name-'+(j-1));
		$("#form-extras-list-link-"+j).attr('name','form-extras-list-link-'+(j-1));
		$("#form-extras-list-link-"+j).attr('id','form-extras-list-link-'+(j-1));
		$("#form-extras-list-resolution-"+j).attr('name','form-extras-list-resolution-'+(j-1));
		$("#form-extras-list-resolution-"+j).attr('id','form-extras-list-resolution-'+(j-1));
		$("#form-extras-list-comments-"+j).attr('name','form-extras-list-comments-'+(j-1));
		$("#form-extras-list-comments-"+j).attr('id','form-extras-list-comments-'+(j-1));
		$("#form-extras-list-delete-"+j).attr('onclick','deleteVersionRow('+(j-1)+');');
		$("#form-extras-list-delete-"+j).attr('id','form-extras-list-delete-'+(j-1));
	}
	$('#extras-list-table').attr('data-count', i-1);

	if (i-1==0) {
		$('#extras-list-table-empty').removeClass('d-none');
	}
}

function deleteVersionFolderRow(id) {
	var i = parseInt($('#folders-list-table').attr('data-count'));
	$("#form-folders-list-row-"+id).remove();
	for (var j=id+1;j<i+1;j++) {
		$("#form-folders-list-row-"+j).attr('id','form-folders-list-row-'+(j-1));
		$("#form-folders-list-id-"+j).attr('name','form-folders-list-id-'+(j-1));
		$("#form-folders-list-id-"+j).attr('id','form-folders-list-id-'+(j-1));
		$("#form-folders-list-account_id-"+j).attr('name','form-folders-account_id-link-'+(j-1));
		$("#form-folders-list-account_id-"+j).attr('id','form-folders-account_id-link-'+(j-1));
		$("#form-folders-list-folder-"+j).attr('name','form-folders-list-folder-'+(j-1));
		$("#form-folders-list-folder-"+j).attr('id','form-folders-list-folder-'+(j-1));
		$("#form-folders-list-delete-"+j).attr('onclick','deleteVersionFolderRow('+(j-1)+');');
		$("#form-folders-list-delete-"+j).attr('id','form-folders-list-delete-'+(j-1));
	}
	$('#folders-list-table').attr('data-count', i-1);

	if (i-1==0) {
		$('#folders-list-table-empty').removeClass('d-none');
	}
}

function fetchMalEpisodes(page) {
	var xmlhttp = new XMLHttpRequest();
	var url = "https://api.jikan.moe/v3/anime/"+$("#form-myanimelist_id").val()+"/episodes/"+page;

	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var malDataEpisodesPage = JSON.parse(this.responseText);
			if (page==1) {
				malDataEpisodes=malDataEpisodesPage;
			} else {
				malDataEpisodes.episodes=malDataEpisodes.episodes.concat(malDataEpisodesPage.episodes);
			}
			if (page<malDataEpisodesPage.episodes_last_page) {
				setTimeout(function() {
					fetchMalEpisodes(page+1);
				}, 4000);
			} else{
				if (malDataEpisodes.episodes.length>0) {
					$("#import-from-mal-episodes-done").removeClass("d-none");
					populateMalEpisodes(malDataEpisodes);
				}
				else{
					alert("Aquesta sèrie no té capítols donats d'alta a MyAnimeList. Introdueix-los a mà.");
				}
				$("#import-from-mal-episodes-loading").addClass("d-none");
				$("#import-from-mal-episodes-not-loading").removeClass("d-none");
				setTimeout(function() {
					$("#import-from-mal").prop('disabled', false);
					$("#import-from-mal-episodes").prop('disabled', false);
				}, 4000);
			}
		} else if (this.readyState == 4) {
			alert("S'ha produït un error en obtenir dades de MyAnimeList, torna-ho a provar més tard.");
			$("#import-from-mal-episodes-loading").addClass("d-none");
			$("#import-from-mal-episodes-not-loading").removeClass("d-none");
			setTimeout(function() {
				$("#import-from-mal").prop('disabled', false);
				$("#import-from-mal-episodes").prop('disabled', false);
			}, 4000);
		}
	};
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
}

function checkNumberOfEpisodes() {
	if ($('#form-episodes').val()!=''){
		var episodeCount = parseInt($('#episode-list-table').attr('data-count'));
		var seriesCount = parseInt($('#form-episodes').val());
		var normalEpisodeCount = 0;

		for (var i=1;i<=episodeCount;i++){
			if ($('#form-episode-list-num-'+i).val()!=''){
				normalEpisodeCount++;
			}
		}
		if (normalEpisodeCount!=seriesCount){
			alert('El nombre de capítols numerats de la llista ha de coincidir amb el nombre de capítols indicat a la fitxa.');
			return false;
		}
	}
	var episodeCount2 = parseInt($('#episode-list-table').attr('data-count'));
	for (var i=1;i<=episodeCount2;i++){
		if ($('#form-episode-list-num-'+i).val()=='' && $('#form-episode-list-name-'+i).val()==''){
			alert('Hi ha capítols sense número ni nom. Els capítols normals han de tenir com a mínim número, i els capítols especials han de tenir com a mínim nom.');
			return false;
		}
	}

	return true;
}

var validLinks=0;
var invalidLinks=0;
var failedLinks=0;
var unknownLinks=0;
var linkVerifyRetries=0;

function verifyLinks(i) {
	if (i==links.length){
		$('#link-verifier-button').prop('disabled', false);
		$('#link-verifier-loading').addClass('d-none');
		$('#link-verifier-progress')[0].innerHTML="Procés completat";
		return;
	}

	if (i==0){
		validLinks=0;
		invalidLinks=0;
		failedLinks=0;
		unknownLinks=0;
		linkVerifyRetries=0;
		updateVerifyLinksResult(0);
		$('#link-verifier-progress').removeClass('d-none');
		$('#link-verifier-wrong-links-list').addClass('d-none');
		$('#link-verifier-failed-links-list').addClass('d-none');
		$('#link-verifier-results').removeClass('d-none');
		$('#link-verifier-button').prop('disabled', true);
		$('#link-verifier-loading').removeClass('d-none');
	}
	
	var matchesMega = links[i].link.match(/https:\/\/mega\.nz\/(?:#!|embed#!|file\/|embed\/)?([a-zA-Z0-9]{0,8})[!#]([a-zA-Z0-9_-]+)/);
	var matchesGoogleDrive = links[i].link.match(/https:\/\/drive\.google\.com\/(?:file\/d\/|open\?id=)?([^\/]*)(?:preview|view)?/);
	if (matchesMega && matchesMega.length>1 && matchesMega[1]!=''){
		//MEGA link
		$.post("https://eu.api.mega.co.nz/cs", "[{\"a\":\"g\", \"g\":1, \"ssl\":0, \"p\":\""+matchesMega[1]+"\"}]", function(data, status){
			if (data=="-9") {
				//invalid
				$('#link-verifier-wrong-links-list').append('<div class="row w-100"><p class="col-sm-4 font-weight-bold">'+links[i].text+'</p><p class="col-sm-8">'+links[i].link+'</p></div>');
				invalidLinks++;
				updateVerifyLinksResult(i+1);
				linkVerifyRetries=0;
				verifyLinks(i+1);
			} else if (status=='success') {
				//valid
				validLinks++;
				updateVerifyLinksResult(i+1);
				linkVerifyRetries=0;
				verifyLinks(i+1);
			} else {
				if (linkVerifyRetries<5){
					linkVerifyRetries++;
					setTimeout(function(){
						verifyLinks(i);
					}, 5000);
				} else {
					$('#link-verifier-failed-links-list').append('<div class="row w-100"><p class="col-sm-4 font-weight-bold">'+links[i].text+'</p><p class="col-sm-8">'+links[i].link+'</p></div>');
					failedLinks++;
					linkVerifyRetries=0;
					verifyLinks(i+1);
				}
			}
		}).fail(function() {
			if (linkVerifyRetries<5){
				linkVerifyRetries++;
				setTimeout(function(){
					verifyLinks(i);
				}, 5000);
			} else {
				$('#link-verifier-failed-links-list').append('<div class="row w-100"><p class="col-sm-4 font-weight-bold">'+links[i].text+'</p><p class="col-sm-8">'+links[i].link+'</p></div>');
				failedLinks++;
				linkVerifyRetries=0;
				verifyLinks(i+1);
			}
		});
	} else if (matchesGoogleDrive && matchesGoogleDrive.length>1 && matchesGoogleDrive[1]!=''){
		//Google Drive link
		$.post("check_googledrive_link.php?link="+encodeURIComponent(matchesGoogleDrive[1]), function(data, status){
			if (data=='OK') {
				//valid
				validLinks++;
				updateVerifyLinksResult(i+1);
				linkVerifyRetries=0;
				verifyLinks(i+1);
			} else if (data=='KO') {
				//invalid
				$('#link-verifier-wrong-links-list').append('<div class="row w-100"><p class="col-sm-4 font-weight-bold">'+links[i].text+'</p><p class="col-sm-8">'+links[i].link+'</p></div>');
				invalidLinks++;
				updateVerifyLinksResult(i+1);
				linkVerifyRetries=0;
				verifyLinks(i+1);
			} else {
				if (linkVerifyRetries<5){
					linkVerifyRetries++;
					verifyLinks(i);
				} else {
					$('#link-verifier-failed-links-list').append('<div class="row w-100"><p class="col-sm-4 font-weight-bold">'+links[i].text+'</p><p class="col-sm-8">'+links[i].link+'</p></div>');
					failedLinks++;
					linkVerifyRetries=0;
					verifyLinks(i+1);
				}
			}
		}).fail(function() {
			if (linkVerifyRetries<5){
				linkVerifyRetries++;
				verifyLinks(i);
			} else {
				$('#link-verifier-failed-links-list').append('<div class="row w-100"><p class="col-sm-4 font-weight-bold">'+links[i].text+'</p><p class="col-sm-8">'+links[i].link+'</p></div>');
				failedLinks++;
				linkVerifyRetries=0;
				verifyLinks(i+1);
			}
		});
	} else {
		unknownLinks++;
		updateVerifyLinksResult(i+1);
		verifyLinks(i+1);
	}
}

function updateVerifyLinksResult(i) {
	$('#link-verifier-progress')[0].innerHTML=i+"/"+links.length;
	$('#link-verifier-good-links')[0].innerHTML=validLinks;
	$('#link-verifier-unknown-links')[0].innerHTML=unknownLinks;
	$('#link-verifier-failed-links')[0].innerHTML=failedLinks;
	$('#link-verifier-wrong-links')[0].innerHTML=invalidLinks;
	if (invalidLinks>0) {
		$('#link-verifier-wrong-links-list').removeClass('d-none');
	}
	if (failedLinks>0) {
		$('#link-verifier-failed-links-list').removeClass('d-none');
	}
}

var malData;
var malDataStaff;
var malDataEpisodes;

$(document).ready(function() {
	$("#form-name-with-autocomplete").on('input', function() {
		$("#form-slug").val(string_to_slug($("#form-name-with-autocomplete").val()));
	});

	$("#import-from-mal").click(function() {
		if ($("#form-myanimelist_id").val()=='') {
			var result = prompt("Introdueix l'URL de l'anime a MyAnimeList per a importar-ne la fitxa.");
			if (!result) {
				return;
			} else if (result.match(/https?:\/\/.*myanimelist.net\/anime\/(\d+)\//i)) {
				$("#form-myanimelist_id").val(result.match(/https?:\/\/.*myanimelist.net\/anime\/(\d*)\//i)[1]);
			} else {
				alert("L'URL no és vàlida.");
				return;
			}
		}
		if (($("#form-name-with-autocomplete").val()!='' || $("#form-synopsis").val()!='') && !confirm("ATENCIÓ! La fitxa ja conté dades. Si continues, se sobreescriuran les dades d'autor, director, estudi, valoració per edats i gèneres, i també s'ompliran els camps que siguin buits.\nL'acció no es podrà desfer un cop hagis desat els canvis. Vols continuar?")) {
			return;
		}
		$("#import-from-mal").prop('disabled', true);
		$("#import-from-mal-episodes").prop('disabled', true);
		$("#import-from-mal-loading").removeClass("d-none");
		$("#import-from-mal-not-loading").addClass("d-none");
		var xmlhttp = new XMLHttpRequest();
		var url = "https://api.jikan.moe/v3/anime/"+$("#form-myanimelist_id").val();

		xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				malData = JSON.parse(this.responseText);

				setTimeout(function() {
					var xmlhttp2 = new XMLHttpRequest();
					var url2 = "https://api.jikan.moe/v3/anime/"+$("#form-myanimelist_id").val()+"/characters_staff";

					xmlhttp2.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							malDataStaff = JSON.parse(this.responseText);
							populateMalData(malData,malDataStaff);
							$("#import-from-mal-loading").addClass("d-none");
							$("#import-from-mal-not-loading").removeClass("d-none");
							setTimeout(function() {
								$("#import-from-mal").prop('disabled', false);
								$("#import-from-mal-episodes").prop('disabled', false);
							}, 4000);
							$("#import-from-mal-done").removeClass("d-none");
						} else if (this.readyState == 4) {
							$("#import-from-mal-loading").addClass("d-none");
							$("#import-from-mal-not-loading").removeClass("d-none");
							alert("S'ha produït un error en obtenir dades de MyAnimeList, torna-ho a provar més tard.");
							setTimeout(function() {
								$("#import-from-mal").prop('disabled', false);
								$("#import-from-mal-episodes").prop('disabled', false);
							}, 4000);
						}
					};
					xmlhttp2.open("GET", url2, true);
					xmlhttp2.send();
				}, 4000);
			} else if (this.readyState == 4) {
				alert("S'ha produït un error en obtenir dades de MyAnimeList, torna-ho a provar més tard.");
				$("#import-from-mal-loading").addClass("d-none");
				$("#import-from-mal-not-loading").removeClass("d-none");
				setTimeout(function() {
					$("#import-from-mal").prop('disabled', false);
					$("#import-from-mal-episodes").prop('disabled', false);
				}, 4000);
			}
		};
		xmlhttp.open("GET", url, true);
		xmlhttp.send();
	});

	$("#import-from-mal-episodes").click(function() {
		if ($("#form-myanimelist_id").val()=='') {
			var result = prompt("Introdueix l'URL de l'anime a MyAnimeList per a importar-ne la fitxa.");
			if (!result) {
				return;
			} else if (result.match(/https?:\/\/.*myanimelist.net\/anime\/(\d+)\//i)) {
				$("#form-myanimelist_id").val(result.match(/https?:\/\/.*myanimelist.net\/anime\/(\d*)\//i)[1]);
			} else {
				alert("L'URL no és vàlida.");
				return;
			}
		}
		if ((parseInt($('#episode-list-table').attr('data-count'))>1 || $('#form-episode-list-name-1').val()!='') && !confirm("ATENCIÓ! Ja hi ha dades de capítols. Si continues, se suprimiran tots i es tornaran a crear. Totes les versions que continguin aquests capítols i tots els enllaços d'aquests capítols desapareixeran i no es podrà desfer l'acció un cop hagis desat els canvis. Vols continuar?")) {
			return;
		}

		$("#import-from-mal").prop('disabled', true);
		$("#import-from-mal-episodes").prop('disabled', true);
		$("#import-from-mal-episodes-loading").removeClass("d-none");
		$("#import-from-mal-episodes-not-loading").addClass("d-none");

		fetchMalEpisodes(1);
	});

	$("#generate-episodes").click(function() {
		if ($("#form-episodes").val()=='') {
			alert('Per a poder-los generar, cal que introdueix el nombre de capítols.');
			return;
		}
		if ((parseInt($('#episode-list-table').attr('data-count'))>1 || $('#form-episode-list-name-1').val()!='') && !confirm("ATENCIÓ! Ja hi ha dades de capítols. Si continues, se suprimiran tots i es tornaran a crear. Totes les versions que continguin aquests capítols i tots els enllaços d'aquests capítols desapareixeran i no es podrà desfer l'acció un cop hagis desat els canvis. Vols continuar?")) {
			return;
		}
		var i = parseInt($('#episode-list-table').attr('data-count'));
		for (var id=1;id<i+1;id++) {
			$("#form-episode-list-row-"+id).remove();
		}
		$('#episode-list-table').attr('data-count', 0);

		for (var i=0;i<$("#form-episodes").val();i++) {
			addRow();
			$("#form-episode-list-num-"+(i+1)).val(i+1);
			$("#form-episode-list-name-"+(i+1)).val('');
			$("#form-episode-list-date-"+(i+1)).val('');
		}
	});

	$("#import-from-mega").click(function() {
		var count = parseInt($('#folders-list-table').attr('data-count'));
		if (count==0){
			alert('Per a poder importar, abans has de configurar una carpeta!');
			return;
		}

		$("#import-from-mega-loading").removeClass("d-none");
		$("#import-from-mega-not-loading").addClass("d-none");
		$("#import-from-mega").prop('disabled', true);

		var account_ids = [];
		var folders = [];
		for (var i=1;i<=count;i++){
			account_ids.push(encodeURIComponent($('#form-folders-list-account_id-'+i).val()));
			folders.push(encodeURIComponent($('#form-folders-list-folder-'+i).val()));
		}

		var xmlhttp = new XMLHttpRequest();
		var url = "fetch_mega_files.php?series_id="+$('[name="series_id"]').val()+"&account_ids[]="+account_ids.join("&account_ids[]=")+"&folders[]="+folders.join("&folders[]=");

		xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				var data = JSON.parse(this.responseText);
				if (data.status=='ko') {
					alert("S'ha produït un error:\n"+data.error);
				} else {
					for (var i = 0; i < data.results.length; i++) {
						$("[id^=form-links-list-"+data.results[i].id+"-link-]").val(data.results[i].link);
					}
				}
				$("#import-from-mega-loading").addClass("d-none");
				$("#import-from-mega-not-loading").removeClass("d-none");
				$("#import-from-mega").prop('disabled', false);
			} else if (this.readyState == 4) {
				alert("S'ha produït un error. Torna-ho a provar.");
				$("#import-from-mega-loading").addClass("d-none");
				$("#import-from-mega-not-loading").removeClass("d-none");
				$("#import-from-mega").prop('disabled', false);
			}
		};
		xmlhttp.open("GET", url, true);
		xmlhttp.send();
	});
});