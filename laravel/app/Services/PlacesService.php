<?php

namespace App\Services;

use App\Place;
use App\PlaceLink;
use App\Category;
use App\PlaceCategory;
use DB;
use XmlParser;

class PlacesService
{
    protected $mid = '1x-5deLW-Xs6Q0YRPqfzUZU3FXdo';
    protected $iconVisited = '#icon-61';
    protected $iconNotVisited = '#icon-22';

    protected $titleMap = [];
    protected $categoryMap = [];

    public function import()
    {
        DB::table('categories')->delete();

        $sKmlSource = file_get_contents('https://www.google.com/maps/d/kml?forcekml=1&mid=' . $this->mid . '&nc=' . time());
        $sFilename = tempnam(sys_get_temp_dir(), 'laravel_');
        file_put_contents($sFilename, $sKmlSource);

        $oXml = XmlParser::load($sFilename);
        $data = $oXml->parse([
            'placemarks' => ['uses' => 'Document.Folder.Placemark[name,description,styleUrl,Point.coordinates,ExtendedData.Data.value]']
        ]);

        $idx = 0;
        $cnt = count($data['placemarks']);
        print "Importing $cnt placemarks...\n";


        foreach ($data['placemarks'] as $item) {
            $idx++;
            if ($idx % 20 == 0) {
                print "$idx/$cnt done\r";
            }

            $oRecord = Place::where('title', $item['name'])->first();
            if (!$oRecord) {
                $oRecord = new Place();
            }

            $coordinates = trim($item['Point']['coordinates']);
            $coordinates = explode(',', $coordinates);
            $oRecord->lon = $coordinates[0];
            $oRecord->lat = $coordinates[1];

            $urls = [];
            if (!empty($item['ExtendedData']['Data']['value'])) {
                $urls = explode(' ', $item['ExtendedData']['Data']['value']);
            }

            $oRecord->title = $item['name'];
            $oRecord->is_visited = (bool)(strstr($item['styleUrl'], $this->iconVisited));

            if (! strstr($item['styleUrl'], $this->iconVisited) && ! strstr($item['styleUrl'], $this->iconNotVisited)) {
                print "\nUncategorized object {$item['name']} at {$oRecord->lat}, {$oRecord->lon}\n";
                continue;
            }

            $description = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $item['description']);
            $description = strip_tags($description);
            $description = str_replace("\xc2\xa0", '', $description); // remove non-breaking spaces %C2%A0
            preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $description, $matches);
            foreach ($matches[0] as $match) {
                $description = str_replace($match, '', $description);
                $urls[] = $match;
            }

            // get categories out of description
            $categoryIds = [];
            if (preg_match('!>(.+?)$!ui', $description, $matches)) {
                $categoryNames = explode(',', $matches[1]);

                foreach ($categoryNames as $categoryName) {
                    $categoryIds[] = $this->resolveCategory($categoryName);
                }
                $description = str_replace($matches[0], '', $description);
            }


            $oRecord->description = trim($description);

            $oRecord->save();

            // save links
            PlaceLink::where('place_id', $oRecord->id)->delete();

            foreach ($urls as $url) {
                $oRecordLink = new PlaceLink();
                $oRecordLink->place_id = $oRecord->id;
                $oRecordLink->url = $url;

                if (stristr($url, 'googleusercontent')) {
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

            foreach ($categoryIds as $categoryId) {
                $oRecordPlaceCategory = new PlaceCategory();
                $oRecordPlaceCategory->place_id = $oRecord->id;
                $oRecordPlaceCategory->category_id = $categoryId;
                $oRecordPlaceCategory->save();
            }
        }

        $this->calculateCategoryOccurencies();
        print "\n\nAll done\n";
    }

    public function getTitle($url)
    {
        $filenameMap = 'url-title-map.txt';

        if (empty($this->titleMap) && file_exists($filenameMap)) {
            $mapSerialized = file_get_contents($filenameMap);
            if (!empty($mapSerialized)) {
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
            preg_match("/\<title\>(.*)\<\/title\>/i", $str, $matches); // ignore case
            $title = html_entity_decode($matches[1]);
            $this->titleMap[$url] = $title;

            file_put_contents($filenameMap, serialize($this->titleMap));

            return $title;
        }
    }

    public function resolveCategory($name)
    {
        $name = trim($name);
        if (!isset($this->categoryMap[$name])) {
            $category = new Category();
            $category->title = $name;
            $category->save();

            $this->categoryMap[$name] = $category->id;
        }

        return $this->categoryMap[$name];
    }

    public function calculateCategoryOccurencies()
    {
        $result = DB::table('places_categories')->selectRaw('category_id, COUNT(category_id) as cnt')->groupBy('category_id')->get();

        foreach ($result as $row) {
            Category::where('id', $row->category_id)->update(['num_occurencies' => $row->cnt]);
        }
    }
}
