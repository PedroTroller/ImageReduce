<?php

declare(strict_types=1);

namespace PedroTroller\ImageResize\Command;

use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class Reduce extends Command
{
    /**
     * @var string[]
     */
    private $units = [
        'B',
        'KB',
        'MB',
        'GB',
        'TB',
    ];

    protected function configure(): void
    {
        $this
            ->setName('reduce')
            ->setDescription('Try to reduce images.')
            ->addArgument(
                'fileOrFolder',
                InputArgument::REQUIRED,
                'An image path or a folder containing images. Images will be overwritten by an optimized version which should be smaller.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $files = [];

        if (is_file($input->getArgument('fileOrFolder'))) {
            $files = [$input->getArgument('fileOrFolder')];
        } else {
            $finder = Finder::create()->in($input->getArgument('fileOrFolder'))->files();

            foreach ($finder as $file) {
                $files[] = $file->getPathname();
            }
        }

        foreach ($files as $file) {
            $originalSize = filesize($file);

            do {
                $beforeOptimization = filesize($file);
                OptimizerChainFactory::create()->optimize($file);
                $afterOptimization = filesize($file);
            } while ($afterOptimization < $beforeOptimization);

            $compressedSize = filesize($file);

            if ($originalSize === $compressedSize) {
                $output
                    ->writeln(
                        sprintf(
                            '<comment>File %s not optimized.</comment>',
                            $file
                        )
                    )
                ;
            }

            if ($originalSize > $compressedSize) {
                $output
                    ->writeln(
                        sprintf(
                            '<info>File %s optimized from %s to %s.</info>',
                            $file,
                            $this->format((float) $originalSize),
                            $this->format((float) $compressedSize)
                        )
                    )
                ;
            }

            if ($originalSize < $compressedSize) {
                $output
                    ->writeln(
                        sprintf(
                            '<error>File %s degraded from %s to %s.</error>',
                            $file,
                            $this->format((float) $originalSize),
                            $this->format((float) $compressedSize)
                        )
                    )
                ;
            }
        }
    }

    private function format(float $size, string $unit = 'B'): string
    {
        $currentIndex = array_search($unit, $this->units);

        if ($size < 1024 || false === $currentIndex || false === array_key_exists((int) $currentIndex + 1, $this->units)) {
            return sprintf('%s %s', number_format($size, 2), $unit);
        }

        return $this->format($size / 1024, $this->units[(int) $currentIndex + 1]);
    }
}
