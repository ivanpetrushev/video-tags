<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\File;
use App\FileTag;
use App\Tag;

class FileController extends Controller {

    public function export(Request $request, $id)
    {
        $file = File::find($id);
        $data = [
            'file' => $file,
            'tags' => []
        ];

        foreach ($file->tags as $idx => $item) {
            $data['file']->tags[$idx]['name'] = $item->tag->name;
        }

        $data = json_encode($data);

        header('Content-disposition: attachment; filename=video-' . $id . '.json');
        header('Content-type: application/json');
        print $data;
        exit();
    }

    public function import(Request $request)
    {
        $iMaxSize = 1000000;
        if (!isset($_FILES['json_file'])) {
            return response()->json([
                'success' => false,
                'error' => 'No file uploaded'
            ]);
        }

        if ($_FILES['json_file']['error']) {
            return response()->json([
                'success' => false,
                'error' => 'Upload error: ' . $_FILES['json_file']['error']
            ]);
        }

        if ($_FILES['json_file']['size'] > $iMaxSize) {
            return response()->json([
                'success' => false,
                'error' => 'File too big. Maximum allowed size: ' . $iMaxSize
            ]);
        }

        try {
            $json = json_decode(file_get_contents($_FILES['json_file']['tmp_name']), true);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'File is not a valid JSON file'
            ]);
        }

        // try to match file by its size
        $iUploadedSize = $json['file']['filesize'];
        $oFile = File::where('filesize', $iUploadedSize)->first();
        if (! $oFile) {
            return response()->json([
                'success' => false,
                'error' => 'Requested file cannot be found'
            ]);
        }

        // file is found, clear old tag associations

        FileTag::where('file_id', $oFile->id)->delete();

        $iSavedTags = 0;

        // assign new associations
        foreach ($json['file']['tags'] as $aImportTag) {
            $oTag = Tag::where('name', $aImportTag['name'])->first();
            if (! $oTag) {
                $oTag = new Tag();
                $oTag->name = $aImportTag['name'];
                $oTag->save();
            }

            $oFileTag = new FileTag();
            $oFileTag->file_id = $oFile->id;
            $oFileTag->tag_id = $oTag->id;
            $oFileTag->start_time = $aImportTag['start_time'];
            $oFileTag->duration = $aImportTag['duration'];
            $oFileTag->save();
            $iSavedTags++;
        }

        return response()->json([
            'success' => true,
            'saved_tags_cnt' => $iSavedTags,
            'file_id' => $oFile->id
        ]);
    }

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

    public function newTag(Request $request)
    {
        $iFileId = $request->input('file_id');
        $iTagId = $request->input('tag_id');
        $iStartTime = $request->input('start_time');

        $oTagRecord = Tag::find($iTagId);
        if (! $oTagRecord) {
            $oTagRecord = new Tag();
            $oTagRecord->name = $iTagId;
            $oTagRecord->save();
            $iTagId = $oTagRecord->id;
        }

        $oRecord = new FileTag();
        $oRecord->file_id = $iFileId;
        $oRecord->tag_id = $iTagId;
        $oRecord->start_time = $iStartTime;
        $oRecord->save();

        return response()->json(['success' => true, 'data' => $oRecord]);
    }

    public function copyTag(Request $request)
    {
        $id = $request->input('tag_id');
        $oRecord = FileTag::find($id);
        if ($oRecord) {
            $oNewRecord = new FileTag();
            $oNewRecord->file_id = $oRecord->file_id;
            $oNewRecord->tag_id = $oRecord->tag_id;
            $oNewRecord->duration = 0;
            $oNewRecord->start_time = $request->input('start_time');
            $oNewRecord->save();
        }

        return response()->json(['success' => true, 'data' => $oRecord]);
    }
}