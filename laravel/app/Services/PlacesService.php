<?php

namespace App\Services;

use App\Place;
use App\PlaceLink;
use XmlParser;

class PlacesService {
    protected $mid = '1x-5deLW-Xs6Q0YRPqfzUZU3FXdo';
    protected $iconVisited = '#icon-61';
    protected $iconNotVisited = '#icon-22';

    protected $titleMap = [];

    public function import()
    {
        $sKmlSource = file_get_contents('https://www.google.com/maps/d/kml?forcekml=1&mid=' . $this->mid . '&nc=' . time());
        $sFilename = tempnam(sys_get_temp_dir(), 'laravel_');
        file_put_contents($sFilename, $sKmlSource);

        $oXml = XmlParser::load($sFilename);
        $data = $oXml->parse([
            'placemarks' => ['uses' => 'Document.Folder.Placemark[name,description,styleUrl,Point.coordinates,ExtendedData.Data.value]']
        ]);


        foreach ($data['placemarks'] as $item) {
            $oRecord = Place::where('title', $item['name'])->first();
            if (! $oRecord) {
                $oRecord = new Place();
            }

            $urls = [];
            if (!empty($item['ExtendedData']['Data']['value'])) {
                $urls = explode(' ', $item['ExtendedData']['Data']['value']);
            }

            $oRecord->title = $item['name'];
            $oRecord->is_visited = (bool) (strstr($item['styleUrl'], $this->iconVisited));
            $oRecord->category_id = null;

            $description = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $item['description']);
            $description = strip_tags($description);
            preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $description, $matches);
            foreach ($matches[0] as $match) {
                $description = str_replace($match, '', $description);
                $urls[] = $match;
            }

            $oRecord->description = trim($description);

            $coordinates = trim($item['Point']['coordinates']);
            $coordinates = explode(',', $coordinates);
            $oRecord->lon = $coordinates[0];
            $oRecord->lat = $coordinates[1];

            $oRecord->save();

            // save links
            PlaceLink::where('place_id', $oRecord->id)->delete();

            foreach ($urls as $url) {
                $oRecordLink = new PlaceLink();
                $oRecordLink->place_id = $oRecord->id;
                $oRecordLink->url = $url;

                if (stristr($url,'googleusercontent')) {
                    $oRecordLink->title = 'image';
                } elseif (stristr($url, 'blog.ivanatora.info')) {
                    $oRecordLink->title = $this->getTitle($url);
                } else {
                    $oRecordLink->title = $url;
                }

                if (stristr($url, 'blog.ivanatora.info')) {
                    $oRecordLink->is_blog = 1;
                }

                $oRecordLink->save();
            }
        }
        print_r($data);
    }

    public function getTitle($url)
    {
        $filenameMap = 'url-title-map.txt';

        if (empty($this->titleMap) && file_exists($filenameMap)) {
            $mapSerialized = file_get_contents($filenameMap);
            if (! empty($mapSerialized)) {
                $this->titleMap = unserialize($mapSerialized);
            }
        }

        if (isset($this->titleMap[$url])) {
            return $this->titleMap[$url];
        }
        $str = file_get_contents($url);

        print "GET TITLE: $url\n";

        if (strlen($str) > 0) {
            $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
            preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title); // ignore case
            $this->titleMap[$url] = $title[1];

            file_put_contents($filenameMap, serialize($this->titleMap));

            return $title[1];
        }
    }
}