<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\File;
use App\FileTag;

class FileController extends Controller {

    public function tags(Request $request)
    {
        $iFileId = $request->input('file_id');

        $all = FileTag::where('file_id', $iFileId)->get();
        $data = [];
        foreach ($all as $item) {
            $item['tag_name'] = $item->tag->name;
            $item['start_time_is'] = gmdate('i:s', $item['start_time']);
            $item['duration_is'] = gmdate('i:s', $item['duration']);
            $data[] = $item;
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}