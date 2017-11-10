<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Place;
use App\PlaceLink;
use DB;
use App\Services\PlacesService;

class PlacesController extends Controller
{
    /**
     * @var PlacesService
     */
    protected $places;

    public function construct(PlacesService $places)
    {
        $this->places = $places;

        parent::__construct();
    }

    public function geojson(Request $request)
    {
        $south = $request->input('south'); // lat
        $north = $request->input('north'); // lat
        $east = $request->input('east'); // lon
        $west = $request->input('west'); // lon

        $data = Place::where([
            ['lat', '>=', $south],
            ['lat', '<=', $north],
            ['lon', '>=', $west],
            ['lon', '<=', $east]
        ])->get();

        $geoJSON = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        foreach ($data as $item) {
            $images = [];
            $blogs = [];
            $links = [];

            foreach ($item->links as $link) {
                if ($link->is_blog) {
                    $blogs[] = $link;
                } elseif ($link->title == 'image') {
                    $images[] = $link;
                } else {
                    $links[] = $link;
                }
            }

            $geoJSON['features'][] = [
                'id' => $item->id,
                'type' => 'Feature',
                'properties' => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'is_visited' => $item->is_visited,
                    'description' => $item->description,
                    'images' => $images,
                    'blogs' => $blogs,
                    'links' => $links,
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$item->lon, $item->lat]
                ]
            ];
        }

        echo json_encode($geoJSON);

    }
}