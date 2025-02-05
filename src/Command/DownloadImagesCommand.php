<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadImagesCommand extends Command
{
    protected static $defaultName = 'app:download-images';
    protected static $defaultDescription = 'Downloads sample images for properties';

    private array $images = [
        'industrial_land' => [
            'https://images.unsplash.com/photo-1519619091416-f5d7e5200702',
            'https://images.unsplash.com/photo-1513828583688-c52646db42da',
            'https://images.unsplash.com/photo-1500382017468-9049fed747ef'
        ],
        'industrial_building' => [
            'https://images.unsplash.com/photo-1565636291760-dd5d39c6811c',
            'https://images.unsplash.com/photo-1581092446327-9b52bd1570c2',
            'https://images.unsplash.com/photo-1581092160562-40aa08e78837'
        ],
        'logistics_center' => [
            'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d',
            'https://images.unsplash.com/photo-1586528116493-ce30b36871c4',
            'https://images.unsplash.com/photo-1586528116550-27c5c6cd55c3'
        ],
        'warehouse' => [
            'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d',
            'https://images.unsplash.com/photo-1586528116493-ce30b36871c4',
            'https://images.unsplash.com/photo-1586528116550-27c5c6cd55c3'
        ],
        'production_facility' => [
            'https://images.unsplash.com/photo-1565636291760-dd5d39c6811c',
            'https://images.unsplash.com/photo-1581092446327-9b52bd1570c2',
            'https://images.unsplash.com/photo-1581092160562-40aa08e78837'
        ]
    ];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $assetsPath = __DIR__ . '/../../assets/images/properties/';
        $publicPath = __DIR__ . '/../../public/uploads/properties/';

        // Create directories if they don't exist
        if (!file_exists($assetsPath)) {
            mkdir($assetsPath, 0777, true);
        }
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        foreach ($this->images as $type => $urls) {
            $io->section(sprintf('Downloading images for %s', $type));

            foreach ($urls as $index => $url) {
                $filename = sprintf('%s-%d.jpg', str_replace('_', '-', $type), $index + 1);
                $filepath = $assetsPath . $filename;

                $io->text(sprintf('Downloading %s...', $filename));

                // Download image
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $image = curl_exec($ch);
                curl_close($ch);

                if ($image) {
                    // Save to assets
                    file_put_contents($filepath, $image);
                    
                    // Copy to public
                    copy($filepath, $publicPath . $filename);

                    $io->text('Done!');
                } else {
                    $io->error(sprintf('Failed to download %s', $filename));
                }
            }
        }

        $io->success('All images have been downloaded and copied!');

        return Command::SUCCESS;
    }
} 