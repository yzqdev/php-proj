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
 * mv — 移动/重命名文件或目录
 *
 * 用法：
 *   thunder mv <src>... <dstDir|dstName> [-f|--force]
 */
#[AsCommand(name: 'mv', description: '移动或重命名文件/目录（跨盘自动回退为复制+删除）')]
final class MvCommand extends BaseCommand
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
            $io->error('用法: thunder mv <src>... <dst>（至少一个源和一个目标）');

            return Command::INVALID;
        }

        $dst = $this->resolvePath((string) array_pop($sources));
        $overwrite = (bool) $input->getOption('force');
        $dstIsDir = is_dir($dst);
        if (count($sources) > 1 && !$dstIsDir) {
            $io->error('多个源时目标必须是已存在的目录: ' . $dst);

            return Command::FAILURE;
        }

        $moved = 0;
        foreach ($sources as $src) {
            $srcPath = $this->resolvePath((string) $src);
            if (!file_exists($srcPath) && !is_link($srcPath)) {
                $io->error('源不存在: ' . $srcPath);

                return Command::FAILURE;
            }

            $target = $dstIsDir ? $dst . '/' . basename($srcPath) : $dst;
            if (file_exists($target) && !$overwrite) {
                $io->error('目标已存在（加 -f 覆盖）: ' . $target);

                return Command::FAILURE;
            }

            if (!Filesystem::move($srcPath, $target, $overwrite)) {
                $io->error('移动失败: ' . $srcPath . ' → ' . $target);

                return Command::FAILURE;
            }
            ++$moved;
            $io->writeln(sprintf('<fg=green>已移动:</> %s → %s', $srcPath, $target));
        }

        $io->success(sprintf('完成，共移动 %d 项', $moved));

        return Command::SUCCESS;
    }
}
