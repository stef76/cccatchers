<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
function docleanup($data)
{
    global $INSTALLER09, $queries,$mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    //$last_date = mysqli_fetch_assoc(sql_query("SELECT date_diff FROM diffusions ORDER BY date_diff DESC LIMIT 1")) or sqlerr(__FILE__, __LINE__);
//$now = date("Y-m-d");

	//if (strtotime($last_date) < strtotime($now)) {
	$url="https://api.betaseries.com/shows/list?v=2.4&key=d8a25885862b&format=json"; 
$options=array(
      CURLOPT_URL            => $url, // Url cible (l'url la page que vous voulez télécharger)
      CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
      CURLOPT_HEADER         => false // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
);
$CURL=curl_init();
curl_setopt_array($CURL,$options);
$content=curl_exec($CURL);
$data = json_decode($content, true);

foreach ($data as $key => $value) {
	$i = 0;
	while ($i < sizeof($value)) {
	
			$id_serie = $value[$i][id];
			$id_tvdb = $value[$i][thetvdb_id];
			$nb_saison = $value[$i][seasons];
			$nb_episode = $value[$i][episodes];
			$titre = $value[$i][title];
			$url_serie="http://api.betaseries.com/shows/display?v=2.4&key=d8a25885862b&format=json&id=".$id_serie;
			$url_acteurs = "http://api.betaseries.com/shows/characters?v=2.4&key=d8a25885862b&format=json&id=".$id_serie;
			$url_lastep = "http://api.betaseries.com/episodes/latest?v=2.4&key=d8a25885862b&format=json&id=".$id_serie;
			$url_similaire = "http://api.betaseries.com/shows/similars?v=2.4&key=d8a25885862b&format=json&id=".$id_serie;
			//$url_picture = "http://api.betaseries.com/shows/pictures?v=2.4&key=d8a25885862b&format=json&id=".$id_serie;
			
			$options2=array(
				CURLOPT_URL            => $url_serie, // Url cible (l'url la page que vous voulez télécharger)
				CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
				CURLOPT_HEADER         => false // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
			);
			$CURL2=curl_init();
			curl_setopt_array($CURL2,$options2);
			$content2=curl_exec($CURL2);
			$serie = json_decode($content2, true);
			$statut = $serie[show][status];
			$followers = $serie[show][followers];
			if ($followers >2000) {
			if ($statut == "Continuing") {
			$url_planning = "http://api.betaseries.com/shows/episodes?v=2.4&key=d8a25885862b&format=json&id=".$id_serie;
			$options3=array(
				CURLOPT_URL            => $url_planning, // Url cible (l'url la page que vous voulez télécharger)
				CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
				CURLOPT_HEADER         => false // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
			);
			$CURL3=curl_init();
			curl_setopt_array($CURL3,$options3);
			$content3=curl_exec($CURL3);
			$result = json_decode($content3, true);
			$planning = $result[episodes];
			for ($j=0;$j<sizeof($planning);$j++) {
			$saison = $planning[$j][season];
			//if ($saison == $nb_saison) {
			$episode = $planning[$j][code];
			$date_diff= $planning[$j]['date'];
			$titre_ep = $planning[$j][title];
			sql_query("INSERT INTO diffusions (date_diff, episode, titre, titre_ep, id_serie) VALUES (".sqlesc($date_diff).", ".sqlesc($episode).", ".sqlesc($titre).",".sqlesc($titre_ep).",".sqlesc($id_tvdb).")");
			//}
			}
			}
			$res = sql_query("SELECT COUNT(id_serie) FROM serie WHERE id_serie= ".sqlesc($id_tvdb)) or sqlerr(__FILE__, __LINE__);
			$row = mysqli_fetch_row($res);
			$count = $row[0];
		
			if ($count == 0) {
			
			$options3=array(
				CURLOPT_URL            => $url_acteurs, // Url cible (l'url la page que vous voulez télécharger)
				CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
				CURLOPT_HEADER         => false // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
			);
			$CURL3=curl_init();
			curl_setopt_array($CURL3,$options3);
			$content3=curl_exec($CURL3);
			$acteurs = json_decode($content3, true);
			$count = sizeof($acteurs[characters]);
			if ($count >=3) {
			$acteur = $acteurs[characters][0][actor];
			for ($j=1; $j < 3;$j++) {
			$acteur .= ", ".$acteurs[characters][$j][actor];
			}
			}
			else
			{
			$acteur = $acteurs[characters][0][actor];
			for ($j=1; $j < sizeof($acteurs[characters]);$j++) {
			$acteur .= ", ".$acteurs[characters][$j][actor];
			}
			}
			
			$options4=array(
				CURLOPT_URL            => $url_lastep, // Url cible (l'url la page que vous voulez télécharger)
				CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
				CURLOPT_HEADER         => false // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
			);
			$CURL4=curl_init();
			curl_setopt_array($CURL4,$options4);
			$content4=curl_exec($CURL4);
			$last_ep = json_decode($content4, true);
			$last_episode = $last_ep[episode][code];
			$date_lastep = $last_ep[episode]['date'];
			
			$options5=array(
				CURLOPT_URL            => $url_similaire, // Url cible (l'url la page que vous voulez télécharger)
				CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
				CURLOPT_HEADER         => false // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
			);
			$CURL5=curl_init();
			curl_setopt_array($CURL5,$options5);
			$content5=curl_exec($CURL5);
			$similaires = json_decode($content5, true);
			$similaire = $similaires[similars][0][show_title];
			for ($j=1; $j < sizeof($similaires[similars]);$j++) {
			$similaire .= ", ".$similaires[similars][$j][show_title];
			}
			$descr = $serie[show][description];
			$annee = $serie[show][creation];
			$genres = $serie[show][genres];
			$genre = $genres[0];
			for ($j=1; $j < sizeof($genres);$j++) {
			$genre .= ", ".$genres[$j];
			}
			$duree = $serie[show][length];
			$chaine = $serie[show][network];
			$statut = $serie[show][status];
			$statut = "En cours";
			$langue = $serie[show][language];
			if ($langue == "en")
			$langue = "Anglais";
			if ($langue == "fr")
			$langue = "Français";
			$note = $serie[show][notes][mean];
			$note = round($note,0);
			$picture = "http://thetvdb.com/banners/_cache/graphical/".$id_tvdb."-g.jpg";
			$picture_name= poster($picture);
			
			sql_query("INSERT INTO serie VALUES (".sqlesc($id_tvdb).", ".sqlesc($titre).", ".sqlesc($descr).",".sqlesc($picture_name).", ".sqlesc($annee).", ".sqlesc($genre).", ".sqlesc($chaine).", ".sqlesc($statut).", ".sqlesc($langue).", ".sqlesc($note).", ".sqlesc($acteur).", ".sqlesc($last_episode).", ".sqlesc($date_lastep).", ".sqlesc($nb_saison).", ".sqlesc($nb_episode).", ".sqlesc($similaire).", ".sqlesc($duree).")");
			}
			}
					$i++;
	}
}
//}
    if ($queries > 0) write_log("Stats clean-------------------- Stats cleanup Complete using $queries queries --------------------");
    if (false !== mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS["___mysqli_ston"]) . " items updated";
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}

function poster($image)
	{
	$filename = substr(strrchr($image, "/"), 1);
	$img = "info_images/serie/".$filename;
	//$img2 = "info_images/allocine/covers/".$type."/small_".$filename;
			$image_data = file_get_contents($image);
			file_put_contents($img, $image_data);
			//resize_poster($img,$img2);
			//unlink($img);
	return $filename;
	}
function cleanup_log($data)
{
    $text = sqlesc($data['clean_title']);
    $added = TIME_NOW;
    $ip = sqlesc($_SERVER['REMOTE_ADDR']);
    $desc = sqlesc($data['clean_desc']);
    sql_query("INSERT INTO cleanup_log (clog_event, clog_time, clog_ip, clog_desc) VALUES ($text, $added, $ip, {$desc})") or sqlerr(__FILE__, __LINE__);
}
?>
