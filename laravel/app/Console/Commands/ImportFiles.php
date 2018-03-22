<?php

namespace App\Console\Commands;

use App\File;
use Illuminate\Console\Command;
use App\Services\ScanService;

class ImportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import new files';

    /**
     * @var ScanService
     */
    protected $scanService;

    public function __construct(ScanService $scanService)
    {
        $this->scanService = $scanService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->scanService->scanAll();
    }
}