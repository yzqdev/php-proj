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
use Yzqde\Thunder\Filesystem;

/**
 * cp — 复制文件/目录（递归）
 *
 * 用法：
 *   thunder cp <src>... <dstDir|dstFile> [-f|--force]
 */
#[AsCommand(name: 'cp', description: '复制文件或目录（目录递归复制）')]
final class CpCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('sources', InputArgument::IS_ARRAY, '源路径（可多个，最后一个为目标）')
            ->addOption('force', 'f', InputOption::VALUE_NONE, '目标已存在时覆盖');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sources = (array) $input->getArgument('sources');
        if (count($sources) < 2) {
            $io->error('用法: thunder cp <src>... <dst>（至少一个源和一个目标）');

            return Command::INVALID;
        }

        $dst = $this->resolvePath((string) array_pop($sources));
        $overwrite = (bool) $input->getOption('force');
        // 多个源时目标必须是已存在的目录（与 cp 语义一致）
        $dstIsDir = is_dir($dst);
        if (count($sources) > 1 && !$dstIsDir) {
            $io->error('多个源时目标必须是已存在的目录: ' . $dst);

            return Command::FAILURE;
        }

        $copied = 0;
        foreach ($sources as $src) {
            $srcPath = $this->resolvePath((string) $src);
            if (!file_exists($srcPath) && !is_link($srcPath)) {
                $io->error('源不存在: ' . $srcPath);

                return Command::FAILURE;
            }

            // 目标路径：目标为目录时落到其内部（保留原名），否则直接用
            $target = $dstIsDir ? $dst . '/' . basename($srcPath) : $dst;

            if (is_file($target) && !$overwrite) {
                $io->error('目标已存在（加 -f 覆盖）: ' . $target);

                return Command::FAILURE;
            }

            if (!Filesystem::copy($srcPath, $target, $overwrite)) {
                $io->error('复制失败: ' . $srcPath . ' → ' . $target);

                return Command::FAILURE;
            }
            ++$copied;
            $io->writeln(sprintf('<fg=green>已复制:</> %s → %s', $srcPath, $target));
        }

        $io->success(sprintf('完成，共复制 %d 项', $copied));

        return Command::SUCCESS;
    }
}
