<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateRobotsCommand extends Command
{
    protected $signature = 'app:generate-robots';

    protected $description = 'Generate public/robots.txt from APP_URL';

    public function handle(): void
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /login',
            'Disallow: /register',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ]);

        file_put_contents(public_path('robots.txt'), $content);

        $this->info('robots.txt generated: '.url('/sitemap.xml'));
    }
}
