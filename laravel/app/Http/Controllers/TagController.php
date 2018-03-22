<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tag;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{

    public function index(Request $request)
    {
        $query = $request->input('query');

        if ($query) {
            $data = Tag::where('name', 'like', '%'.$query.'%')->get();
        } else {
            $data = Tag::all();
        }
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

}