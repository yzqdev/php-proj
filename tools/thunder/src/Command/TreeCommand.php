<?php

declare(strict_types=1);

namespace Yzqde\Thunder\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * tree — 树形展示目录结构
 *
 * 用法：
 *   thunder tree [path] [-d N] [-a]
 */
#[AsCommand(name: 'tree', description: '以树形展示目录结构')]
final class TreeCommand extends BaseCommand
{
    private int $dirCount = 0;
    private int $fileCount = 0;

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, '目标目录', '.')
            ->addOption('depth', 'd', InputOption::VALUE_REQUIRED, '最大递归深度', '3')
            ->addOption('all', 'a', InputOption::VALUE_NONE, '显示隐藏文件（. 开头）');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $this->resolvePath((string) $input->getArgument('path'));

        if (!is_dir($path)) {
            $io->error('目录不存在: ' . $path);

            return Command::FAILURE;
        }

        $depth = max(1, (int) $input->getOption('depth'));

        $this->dirCount = 0;
        $this->fileCount = 0;
        $io->writeln('<fg=cyan;options=bold>' . $path . '</>');
        $this->render($io, $path, '', $depth, (bool) $input->getOption('all'));
        $io->newLine();
        $io->writeln(sprintf('%d 个目录, %d 个文件', $this->dirCount, $this->fileCount));

        return Command::SUCCESS;
    }

    /**
     * 递归输出树形结构
     *
     * @param string $prefix 缩进前缀（父级连线，如 "│   " / "    "）
     */
    private function render(SymfonyStyle $io, string $dir, string $prefix, int $depth, bool $showAll): void
    {
        if ($depth <= 0) {
            return;
        }
        $entries = $this->listEntries($dir, $showAll);
        $last = count($entries) - 1;

        foreach ($entries as $i => $item) {
            $isLast = $i === $last;
            $connector = $isLast ? '└── ' : '├── ';
            if ($item->isDir()) {
                ++$this->dirCount;
                $io->writeln($prefix . $connector . '<fg=cyan;options=bold>' . $item->getFilename() . '/</>');
                $this->render(
                    $io,
                    $item->getPathname(),
                    $prefix . ($isLast ? '    ' : '│   '),
                    $depth - 1,
                    $showAll
                );
            } else {
                ++$this->fileCount;
                $io->writeln($prefix . $connector . $item->getFilename());
            }
        }
    }
}
