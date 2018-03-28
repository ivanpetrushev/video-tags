<?php

namespace App\Http\Controllers;

use App\Services\ScanService;
use Illuminate\Http\Request;
use App\Directory;
use App\File;
use DB;
use Illuminate\Support\Facades\Log;

class DirectoryController extends Controller
{
    /**
     * @var ScanService
     */
    protected $scanService;

    public function construct(ScanService $scanService)
    {
        $this->scanService = $scanService;

        parent::__construct();
    }

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

        // test if this directory is reachable
        $sDefaultPath = '/var/www/html/docroot/public/data/';
        $sTestPath = $sDefaultPath . $oRecord->path;
        if (!is_dir($sTestPath)) {
            return response()->json(['success' => false, 'error' => $oRecord->path . ' is not a directory. Check mount points in docker-compose.yml']);
        }

        $bExists = Directory::where('path', $oRecord->path)->count();
        if ($bExists) {
            return response()->json(['success' => false, 'error' => 'Directory is already added']);
        }

        try {
            $oRecord->save();
            $this->scanService->scanDir($oRecord->path);
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

    public function tree()
    {
        $data = [];
        $dirs = Directory::all();
        foreach ($dirs as $dir) {
            $files = File::where('directory_id', $dir->id)->get();
            if (empty($files)) {
                $data[] = ['text' => $dir->path, 'expanded' => true];
            } else {
                $arr = [];
                foreach ($files as $file) {
                    $arr[$file->path] = $file->toArray();
                }
                $tree = $this->explodeTree($arr, '/');
                $children = $this->treeToChildren($tree);
                $data[] = ['text' => $dir->path, 'expanded' => true, 'children' => $children];
            }
        }

        return response()->json(['success' => true, 'children' => $data]);
    }

    protected function treeToChildren($tree, $indent = 0)
    {
        $data = [];

        foreach ($tree as $dir => $contents) {
            if (!isset($contents['id'])) {
                $data[] = ['text' => $dir, 'leaf' => false, 'expanded' => true, 'children' => $this->treeToChildren($contents)];
            } else {
                $oFile = File::find($contents['id']);
                $oDirectory = $oFile->directory;

                $data[] = [
                    'text' => $dir,
                    'leaf' => true,
                    'id' => $contents['id'],
                    'duration' => $contents['duration'],
                    'duration_hi' => gmdate('i:s', $contents['duration']),
                    'path' => $contents['path'],
                    'fullpath' => $oDirectory['path'] . $oFile['path'],
                    'filesize' => $oFile->filesize
                ];
            }
        }
        return $data;
    }

    protected function explodeTree($array, $delimiter = '_', $baseval = false)
    {
        if (!is_array($array)) return false;
        $splitRE = '/' . preg_quote($delimiter, '/') . '/';
        $returnArr = array();
        foreach ($array as $key => $val) {
            // Get parent parts and the current leaf
            $parts = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
            $leafPart = array_pop($parts);

            // Build parent structure
            // Might be slow for really deep and large structures
            $parentArr = &$returnArr;
            foreach ($parts as $part) {
                if (!isset($parentArr[$part])) {
                    $parentArr[$part] = array();
                } elseif (!is_array($parentArr[$part])) {
                    if ($baseval) {
                        $parentArr[$part] = array('__base_val' => $parentArr[$part]);
                    } else {
                        $parentArr[$part] = array();
                    }
                }
                $parentArr = &$parentArr[$part];
            }

            // Add the final part to the structure
            if (empty($parentArr[$leafPart])) {
                $parentArr[$leafPart] = $val;
            } elseif ($baseval && is_array($parentArr[$leafPart])) {
                $parentArr[$leafPart]['__base_val'] = $val;
            }
        }
        return $returnArr;
    }
}