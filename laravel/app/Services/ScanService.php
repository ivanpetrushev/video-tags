<?php

namespace App\Services;

use App\File;
use App\Directory;
use DB;
use PhpParser\Node\Scalar\MagicConst\Dir;

class ScanService
{
    public $aAllowedExtensions = ['mov', 'mp4', 'avi', 'mpeg'];

    public $sBaseDir = '/var/www/html/docroot/public/data/';

    /*
     * recursively traverse down directory and add any movie files to DB
     */

    public function scanDir($dir)
    {
        print "Scanning $dir ...\n";
        $oDirectory = Directory::where('path', $dir)->first();

        $sRegex = '/^.+\.(' . join('|', $this->aAllowedExtensions) . ')/i';
        $rdi = new \RecursiveDirectoryIterator($this->sBaseDir . $dir);
        $rii = new \RecursiveIteratorIterator($rdi);
        $r = new \RegexIterator($rii, $sRegex, \RecursiveRegexIterator::GET_MATCH);

        $aFileList = array_keys(iterator_to_array($r));
        foreach ($aFileList as $sFilepath) {
            $info = pathinfo($sFilepath);
            $sRealpath = str_replace($this->sBaseDir . $dir, '', $sFilepath);

            $bExists = File::where([
                'path' => $sRealpath,
                'filename' => $info['basename']
            ])->count();

            if (!$bExists) {
                $id3 = new \getID3();
                $id3info = $id3->analyze($sFilepath);
                if (isset($id3info['error'])) {
                    print "Error reading $sFilepath: " . join("\n", $id3info['error']) . "\n";
                    continue;
                }

                $oRecord = new File();
                $oRecord->directory_id = $oDirectory->id;
                $oRecord->path = $sRealpath;
                $oRecord->filename = $info['basename'];
                $oRecord->duration = (int)$id3info['playtime_seconds'];
                $oRecord->filesize = filesize($sFilepath);
                $oRecord->save();
                print "Added $sFilepath\n";
            }
        }
    }

    public function scanAll()
    {
        $oDirectories = Directory::all();
        foreach ($oDirectories as $oDirectory) {
            $this->scanDir($oDirectory->path);
        }
    }
}
