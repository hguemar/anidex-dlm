<?php

require_once('search.php');


Class FakePlugin {
    public function addResult($title, $download, $size, $datetime, $page,
                              $hash, $seeds, $leechs, $category) {

               echo("$title\n");
           }

};


$search = new TorrentSearchAnidex();
$response = file_get_contents('sample_search_fish.rss');
$plugin = new FakePlugin();
$search->parse($plugin, $response);

?>