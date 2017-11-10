<?php

namespace App\Services;

use App\Place;
use App\PlaceLink;
use XmlParser;

class PlacesService {
    protected $mid = '1x-5deLW-Xs6Q0YRPqfzUZU3FXdo';
    protected $iconVisited = '#icon-61';
    protected $iconNotVisited = '#icon-22';

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

            $description = strip_tags($item['description']);

            $oRecord->description = $description;

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
                $oRecordLink->title = 'image';
                $oRecordLink->save();
            }
        }
        print_r($data);
    }
}