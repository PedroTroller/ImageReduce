<?php

declare(strict_types=1);

namespace PedroTroller\ImageResize\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Spatie\ImageOptimizer\OptimizerChainFactory;

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

    protected function configure()
    {
        $this
            ->setName('reduce')
            ->setDescription('Try to reduce images.')
            ->addArgument(
                'globToImage',
                InputArgument::REQUIRED,
                'A pattern to find images. Images will be overwritten by an optimized version which should be smaller.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (glob($input->getArgument('globToImage')) as $file) {
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
                            $this->format(floatval($originalSize)),
                            $this->format(floatval($compressedSize))
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
                            $this->format(floatval($originalSize)),
                            $this->format(floatval($compressedSize))
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
