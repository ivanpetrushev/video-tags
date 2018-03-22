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

    public function removeTag(Request $request)
    {
        $iTagId = $request->input('tag_id');

        FileTag::find($iTagId)->delete();

        return response()->json(['success' => true]);
    }

    public function stopTag(Request $request)
    {
        $iTagId = $request->input('tag_id');
        $iDuration = $request->input('duration');

        $oRecord = FileTag::find($iTagId);
        if ($oRecord) {
            $oRecord->duration = $iDuration;
            $oRecord->save();
        }

        return response()->json(['success' => true]);
    }

    public function saveTag(Request $request)
    {
        $id = $request->input('tag.id');
        $oRecord = FileTag::get()->find($id);
        if (! $oRecord) {
            return response()->json(['success' => false, 'error' => 'Record not found']);
        }

        $aUpdateData = $request->input('tag');
        unset($aUpdateData['id']);
        unset($aUpdateData['created_at']);
        unset($aUpdateData['modified_at']);

        foreach ($aUpdateData as $key => $value) {
            $oRecord->$key = $value;
        }

        try {
            $oRecord->save();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'error' => 'Database error']);
        }

        // refresh fields
        $oRecord = FileTag::get()->find($id);

        return response()->json(['success' => true, 'data' => $oRecord]);
    }
}