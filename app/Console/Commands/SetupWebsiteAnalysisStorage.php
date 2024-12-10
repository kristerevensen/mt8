<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetupWebsiteAnalysisStorage extends Command
{
    protected $signature = 'website-analysis:setup-storage';
    protected $description = 'Setup storage directories for website analysis';

    public function handle()
    {
        $disk = Storage::disk('website_analysis');

        // Create base directories
        $directories = [
            'screenshots',
            'html',
        ];

        foreach ($directories as $dir) {
            if (!$disk->exists($dir)) {
                $disk->makeDirectory($dir);
                $this->info("Created directory: {$dir}");
            }
        }

        // Create .gitignore to prevent committing analysis files
        $gitignore = "*\n!.gitignore\n";
        $disk->put('.gitignore', $gitignore);

        // Create symbolic link if it doesn't exist
        $this->call('storage:link');

        $this->info('Website analysis storage setup completed!');
    }
}
