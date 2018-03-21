<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Directory;
use DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Scalar\MagicConst\Dir;

class DirectoryController extends Controller
{
    /**
     * @var PlacesService
     */
//    protected $places;

//    public function construct(PlacesService $places)
//    {
//        $this->places = $places;
//
//        parent::__construct();
//    }

    public function index($request)
    {
        $data = Directory::all();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $oRecord = new Directory();
        $oRecord->path = $request->input('directory.path');

        try {
            $oRecord->save();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'error' => 'Database error']);
        }

        return response()->json(['success' => true, 'data' => $oRecord]);
    }

    public function update(Request $request, $id)
    {
        $oRecord = Directory::get()->find($id);
        if (! $oRecord) {
            return response()->json(['success' => false, 'error' => 'Record not found']);
        }

        $aUpdateData = $request->input('directory');
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
        $oRecord = Directory::get()->find($id);

        return response()->json(['success' => true, 'data' => $oRecord]);
    }

    public function destroy(Request $request, $id)
    {
        $oRecord = Directory::get()->find($id);
        if (! $oRecord) {
            return response()->json(['success' => false, 'error' => 'Record not found']);
        }

        $oRecord->delete();
        return response()->json(['success' => true, 'data' => $oRecord]);
    }
}