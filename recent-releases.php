<?php

include("include/defines.php");

$recentReleasesArray = array();

if(!isset($_REQUEST["type"]) || !isset($_REQUEST["page"])){
    die("param required");
}

$type = $_REQUEST["type"];
$page = $_REQUEST["page"];

if(!is_numeric($page) || !is_numeric($type)){
    die("error numeric");
}

$type = (int) $type;
$page = (int) $page;

if($type < 1){
    $type = 1;
}

if($type > 3){
    $type = 3;
}

if($page < 1){
    $page = 1;
}

$subOrDub = "";

if($type == 1 || $type == 3){
    $subOrDub = "SUB";
}

if($type == 2){
    $subOrDub = "DUB";
}

$gUrl = $gUrl . "type=" . $type . "&page=" . $page;

$options = array(
    'http' => array(
        'method' => 'GET',
        'header' => $user_agent
    )
);

$context = stream_context_create($options);

$html = file_get_contents($gUrl, false, $context);

$pattern = '/<ul[^>]*class="items"[^>]*>(.*?)<\/ul>/si';

preg_match($pattern, $html, $matches);

if (isset($matches[1])) {

    $pattern = '/<li[^>]*>(.*?)<\/li>/si';
    preg_match_all($pattern, $matches[1], $li_matches);

    foreach ($li_matches[1] as $li) {

        $animeId = "";
        $episodeId = "";
        $animeImg = "";
        $episodeNum = "";
        $animeTitle = "";
        
        $pattern_a = '/<a[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/si';
        preg_match($pattern_a, $li, $a_matches);

        if (isset($a_matches[1])) {
            $episodeId = substr(trim($a_matches[1]), 1);
        }

        $pattern_img = '/<img[^>]*src="([^"]*)"[^>]*>/si';
        preg_match($pattern_img, $li, $img_matches);

        if (isset($img_matches[1])) {
            $animeImg = $img_matches[1];
        }

        $pattern_p1 = '/<p[^>]*class="name"[^>]*>(.*?)<\/p>/si';
        preg_match($pattern_p1, $li, $p1_matches);

        if (isset($p1_matches[1])) {
            $animeTitle = strip_tags($p1_matches[1]);
        }

        $pattern_p2 = '/<p[^>]*class="episode"[^>]*>(.*?)<\/p>/si';
        preg_match($pattern_p2, $li, $p2_matches);

        if (isset($p2_matches[1])) {
            $episodeNum = trim(str_replace("episode", "", strtolower($p2_matches[1])));
        }

        $replace_text = "-episode-" . $episodeNum;
        $animeId = trim(str_replace($replace_text, "", $episodeId));

        $episodeUrl = $gogoWeb . "/" . $episodeId;

        $episodeArray = array(
            "animeId" => $animeId,
            "episodeId" => $episodeId,
            "animeTitle" => $animeTitle,
            "episodeNum" => $episodeNum,
            "subOrDub" => $subOrDub,
            "animeImg" => $animeImg,
            "episodeUrl" => $episodeUrl
        );

        array_push($recentReleasesArray, $episodeArray);
    }
}


$data = json_encode($recentReleasesArray);

header("Content-Type: application/json; charset=UTF-8");
echo $data;
?>