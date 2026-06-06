<?php

require __DIR__.'/../vendor/autoload.php';

// Search vendor/filament for #[Url]
$dir = new RecursiveDirectoryIterator(__DIR__.'/../vendor/filament');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (strpos($content, 'Livewire\Attributes\Url') !== false) {
            echo $file->getPathname()."\n";
        }
    }
}
