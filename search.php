<?php

class TorrentSearchAnidex {
    private $qurl = "https://anidex.info/rss/?q=";

    private $unit_map = array(
        "KB" => 1024,
        "MB" => 1048576,
        "GB" => 1073741824,
    );

    public function __construct() {
    }

    private function format_size($size, $unit) {
        $multiplier = $this->unit_map[$unit];
        $size = floatval($size) * $multiplier;

        return $size;
    }

    private function format_datetime($datetime) {
        $timestamp = strtotime($datetime);
        $converted_datetime = date("Y-m-d H:i", $timestamp);
        return $converted_datetime;
    }


    public function prepare($curl, $query) {
        $url = $this->qurl . urlencode($query);
        curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
    }


    public function parse($plugin, $response) {
        $regexp = "<item>.*".
                "<category>(?P<category>.*)</category>.*".
                "<title>(?P<title>.*)</title>.*".
                "<link>(?P<download>https://anidex.info/dl/(?P<id>\d*))</link>.*".  // download (torrent file)
                "<description><!\[CDATA\[.*\| Size: (?P<size>[\d.]+) (?P<unit>[KMG]B) \|.*</description>.*".
                "<pubDate>(?P<date>.*)</pubDate>.*". // date
                "<guid>.*</guid>.*".
                "</item>.*";

        $count = 0;
        if (preg_match_all("|$regexp|siU", $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches  as $match) {
                $title = $match["title"];
                $download = $match["download"];
                $size = $this->format_size($match["size"], $match["unit"]);
                $datetime = $this->format_datetime($match["date"]);
                $page = "https:\/\/anidex.info/?page=torrent&id=%s" . $match["id"];
                $hash = $count;
                $seeds = 0; // FIXME
                $leechs = 0; // FIXME
                $category = $match["category"];


                $plugin->addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category);
                $count++;
            }
        }

        return $count;
    }
}
?>
