<?php

declare(strict_types=1);

namespace PedroTroller\ImageResize\Command;

use Exception;
use PedroTroller\ImageResize\FileSizeFormatter;
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

    /**
     * @var array<string,string>
     */
    private $backups = [];

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

        foreach ($files as $originalFile) {
            $this->backup($originalFile);
            $originalSize = filesize($originalFile);

            do {
                $beforeOptimization = filesize($originalFile);
                OptimizerChainFactory::create()->optimize($originalFile);
                $afterOptimization = filesize($originalFile);
            } while ($afterOptimization < $beforeOptimization);

            $compressedSize = filesize($originalFile);

            if ($originalSize === $compressedSize) {
                $this->handleNoOptimization($output, $originalFile);

                continue;
            }

            if ($originalSize > $compressedSize) {
                $this->handleCompression($output, $originalFile, (float) $originalSize, (float) $compressedSize);

                continue;
            }

            if ($originalSize < $compressedSize) {
                $this->handleRegression($output, $originalFile, (float) $originalSize, (float) $compressedSize);

                continue;
            }
        }
    }

    private function handleCompression(
        OutputInterface $output,
        string $originalFile,
        float $originalSize,
        float $compressedSize
    ): void {
        $compression = ceil(($compressedSize * 100) / $originalSize);

        $output
            ->writeln(
                sprintf(
                    '<info>File %s optimized from %s to %s (-%d%%).</info>',
                    $originalFile,
                    $this->format($originalSize),
                    $this->format($compressedSize),
                    100 - $compression
                )
            )
        ;
    }

    private function handleRegression(
        OutputInterface $output,
        string $originalFile,
        float $originalSize,
        float $compressedSize
    ): void {
        $this->restore($originalFile);

        $compression = ceil(($compressedSize * 100) / $originalSize);

        if ($output->isVerbose()) {
            $output
                ->writeln(
                    sprintf(
                        '<error>File %s degraded from %s to %s (+%s%%).</error>',
                        $originalFile,
                        $this->format($originalSize),
                        $this->format($compressedSize),
                        -(100 - $compression)
                    )
                )
            ;
        }
    }

    private function handleNoOptimization(OutputInterface $output, string $originalFile): void
    {
        $this->restore($originalFile);

        if ($output->isVerbose()) {
            $output
                ->writeln(
                    sprintf(
                        '<comment>File %s not optimized.</comment>',
                        $originalFile
                    )
                )
            ;
        }
    }

    private function format(float $bytes): string
    {
        return (new FileSizeFormatter())->bytes($bytes);
    }

    private function backup(string $file): void
    {
        $originalFile = realpath($file);

        if (false === $originalFile) {
            throw new Exception(sprintf('File "%s" does not exists.', $file));
        }

        if (array_key_exists($originalFile, $this->backups)) {
            return;
        }

        $backupFile = tempnam(sys_get_temp_dir(), 'reduce_');

        if (false === $backupFile) {
            throw new Exception(
                sprintf(
                    'Enable to create a backup file. The "%s" directory does not seem to be accessible.',
                    sys_get_temp_dir()
                )
            );
        }

        $this->backups[$originalFile] = $backupFile;

        $originalStream = fopen($originalFile, 'r');

        if (false === $originalStream) {
            throw new Exception(
                sprintf(
                    'The "%s" file does not seem to be accessible.',
                    $originalFile
                )
            );
        }

        $backupStream = fopen($backupFile, 'w');

        if (false === $backupStream) {
            throw new Exception(
                sprintf(
                    'The "%s" file does not seem to be accessible.',
                    $backupFile
                )
            );
        }

        stream_copy_to_stream($originalStream, $backupStream);
    }

    private function restore(string $file): void
    {
        $originalFile = realpath($file);

        if (false === $originalFile) {
            throw new Exception(sprintf('File "%s" does not exists.', $file));
        }

        if (false === array_key_exists($originalFile, $this->backups)) {
            throw new Exception(sprintf('There is no backup file for "%s".', $file));
        }

        $backupFile = $this->backups[$originalFile];

        $backupStream = fopen($backupFile, 'r');

        if (false === $backupStream) {
            throw new Exception(
                sprintf(
                    'The "%s" file does not seem to be accessible.',
                    $backupFile
                )
            );
        }

        $originalStream = fopen($originalFile, 'w');

        if (false === $originalStream) {
            throw new Exception(
                sprintf(
                    'The "%s" file does not seem to be accessible.',
                    $originalFile
                )
            );
        }

        stream_copy_to_stream($backupStream, $originalStream);
    }
}
