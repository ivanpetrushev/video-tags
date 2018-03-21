<?php

namespace App\Console\Commands;

use App\File;
use Illuminate\Console\Command;
use App\Services\PlacesService;

class ImportPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:places';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import list of places from Google Maps KML';

    /**
     * @var PlacesService
     */
    protected $places;

    public function __construct(PlacesService $places)
    {
        $this->places = $places;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->places->import();
    }
}