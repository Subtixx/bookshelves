<?php

namespace App\Console\Commands\Bookshelves;

use Artisan;
use App\Utils\FileTools;
use Illuminate\Console\Command;

class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookshelves:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear temporary files from Bookshelves';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->alert('Bookshelves: clear');
        $debug = new FileTools(storage_path('app/public/debug'));
        $cache = new FileTools(storage_path('app/public/cache'));
        $temp = new FileTools(storage_path('app/public/temp'));
        $glide = new FileTools(storage_path('app/public/glide'));

        $debug->clearDir();
        $cache->clearDir();
        $temp->clearDir();
        $glide->clearDir();

        Artisan::call('cache:clear', [], $this->getOutput());
        Artisan::call('route:clear', [], $this->getOutput());
        Artisan::call('config:clear', [], $this->getOutput());
        Artisan::call('view:clear', [], $this->getOutput());
        Artisan::call('optimize:clear', [], $this->getOutput());
        Artisan::call('webreader:clear', [], $this->getOutput());

        $this->newLine();

        return 0;
    }
}
