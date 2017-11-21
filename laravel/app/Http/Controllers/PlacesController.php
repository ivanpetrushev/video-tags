<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Place;
use App\PlaceLink;
use App\Category;
use DB;
use App\Services\PlacesService;
use Illuminate\Support\Facades\Log;

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

        $where = [
            ['lat', '>=', $south],
            ['lat', '<=', $north],
            ['lon', '>=', $west],
            ['lon', '<=', $east]
        ];

        $filters = $request->input('filterCategories', json_encode([]));
        $filters = json_decode($filters, true);

        $whereHas = $whereDoesntHave = [];
        foreach ($filters as $name => $value) {
            if (preg_match('/only-visited/', $name, $matches)) {
                if ($value == 1) {
                    $where[] = ['is_visited', '=', 1];
                } elseif ($value == 2) {
                    $where[] = ['is_visited', '=', 0];
                }
            }
            if (preg_match('/category-(\d+)$/', $name, $matches)) {
                $catId = $matches[1];
                if ($value == 1) {
                    $whereHas[] = $catId;
                } elseif ($value == 2) {
                    $whereDoesntHave[] = $catId;
                }
            }
        }

        $query = Place::where($where);
        $query->with('categories', 'categories.category');
        $data = $query->get();

        $geoJSON = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        foreach ($data as $item) {
            $images = [];
            $blogs = [];
            $links = [];

            $categories = $item->categories;

            if (! empty($whereHas)) {
                $allowed = true;
                foreach ($whereHas as $catId) {
                    $categoryFound = false;
                    foreach ($categories as $category) {
                        if ($catId == $category->category_id) {
                            $categoryFound = true;
                        }
                    }
                    if (! $categoryFound) {
                        $allowed = false;
                        break;
                    }
                }
                if (! $allowed) {
                    continue;
                }
            }

            if (! empty($whereDoesntHave)) {
                $allowed = true;
                foreach ($whereDoesntHave as $catId) {
                    foreach ($categories as $category) {
                        if ($catId == $category->category_id) {
                            $allowed = false;
                        }
                    }
                }
                if (! $allowed) {
                    continue;
                }
            }

            foreach ($item->links as $link) {
                if ($link->is_blog) {
                    $blogs[] = $link;
                } elseif ($link->title == 'image') {
                    $images[] = $link;
                } else {
                    $links[] = $link;
                }
            }

            $categories = [];
            foreach ($item->categories as $category) {
                $categories[] = $category->category;
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
                    'categories' => $categories
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$item->lon, $item->lat]
                ]
            ];
        }

        echo json_encode($geoJSON);
    }

    public function categories()
    {
        $data = Category::orderBy('title', 'ASC')->get();

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }
}