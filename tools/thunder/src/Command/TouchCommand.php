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
 * touch — 创建空文件 / 更新修改时间
 *
 * 用法：
 *   thunder touch <file>...
 */
#[AsCommand(name: 'touch', description: '创建空文件或更新文件修改时间')]
final class TouchCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->addArgument('files', InputArgument::IS_ARRAY, '文件路径（可多个）');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $files = (array) $input->getArgument('files');
        if ($files === []) {
            $io->error('请至少指定一个文件');

            return Command::INVALID;
        }

        foreach ($files as $file) {
            $path = $this->resolvePath((string) $file);
            if (is_file($path)) {
                // 已存在：仅刷新 mtime
                @touch($path);
                $io->writeln(sprintf('<fg=yellow>已更新时间:</> %s', $path));
                continue;
            }
            // 新建：父目录必须已存在（保持与 POSIX touch 一致的行为）
            if (@touch($path)) {
                $io->writeln(sprintf('<fg=green>已创建:</> %s', $path));
            } else {
                $io->error('创建失败（检查父目录是否存在）: ' . $path);

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
