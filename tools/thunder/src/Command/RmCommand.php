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
 * rm — 删除文件/目录（递归），带危险路径守卫
 *
 * 用法：
 *   thunder rm <path>... [-r] [-f]
 */
#[AsCommand(name: 'rm', description: '删除文件或目录（目录需 -r，内置危险路径保护）')]
final class RmCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('paths', InputArgument::IS_ARRAY, '要删除的路径（可多个）')
            ->addOption('recursive', 'r', InputOption::VALUE_NONE, '递归删除目录及其内容')
            ->addOption('force', 'f', InputOption::VALUE_NONE, '跳过确认直接删除');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $paths = (array) $input->getArgument('paths');
        if ($paths === []) {
            $io->error('请至少指定一个要删除的路径');

            return Command::INVALID;
        }

        $recursive = (bool) $input->getOption('recursive');
        $force = (bool) $input->getOption('force');

        // 第一步：解析并校验全部目标，任何一项不合法则整体拒绝
        $targets = [];
        foreach ($paths as $path) {
            $abs = $this->resolvePath((string) $path);

            if (!file_exists($abs) && !is_link($abs)) {
                $io->error('路径不存在: ' . $abs);

                return Command::FAILURE;
            }
            if (is_dir($abs) && !is_link($abs) && !$recursive) {
                $io->error('是目录，需要 -r 递归删除: ' . $abs);

                return Command::FAILURE;
            }
            if (Filesystem::isDangerous($abs)) {
                $io->error('拒绝删除危险路径（根目录/当前目录及其祖先）: ' . $abs);

                return Command::FAILURE;
            }
            $targets[] = $abs;
        }

        // 第二步：非 -f 时列出目标并要求确认
        if (!$force) {
            $io->warning('将删除以下内容:');
            $io->listing($targets);
            if (!$io->confirm('确认删除？', false)) {
                $io->writeln('已取消');

                return Command::SUCCESS;
            }
        }

        $deleted = 0;
        foreach ($targets as $abs) {
            if (!Filesystem::remove($abs)) {
                $io->error('删除失败: ' . $abs);

                return Command::FAILURE;
            }
            ++$deleted;
            $io->writeln(sprintf('<fg=green>已删除:</> %s', $abs));
        }

        $io->success(sprintf('完成，共删除 %d 项', $deleted));

        return Command::SUCCESS;
    }
}
