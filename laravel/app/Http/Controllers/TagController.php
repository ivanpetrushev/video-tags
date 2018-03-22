<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tag;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{

    public function index(Request $request)
    {
        $data = Tag::all();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

}