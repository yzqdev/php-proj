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
 * mkdir — 创建目录（支持多级与一次多个）
 *
 * 用法：
 *   thunder mkdir <dir>... [-p]
 */
#[AsCommand(name: 'mkdir', description: '创建目录（支持一次创建多个）')]
final class MkdirCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('dirs', InputArgument::IS_ARRAY, '要创建的目录（可多个）')
            ->addOption('parents', 'p', InputOption::VALUE_NONE, '递归创建父目录');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dirs = (array) $input->getArgument('dirs');
        if ($dirs === []) {
            $io->error('请至少指定一个目录');

            return Command::INVALID;
        }

        $recursive = (bool) $input->getOption('parents');
        $created = 0;
        foreach ($dirs as $dir) {
            $path = $this->resolvePath((string) $dir);
            if (is_dir($path)) {
                $io->writeln(sprintf('<fg=yellow>已存在，跳过:</> %s', $path));
                continue;
            }
            if (@mkdir($path, 0777, $recursive)) {
                ++$created;
                $io->writeln(sprintf('<fg=green>已创建:</> %s', $path));
            } else {
                // 失败常见原因：父目录不存在且未加 -p
                $io->error('创建失败（父目录缺失时可加 -p）: ' . $path);

                return Command::FAILURE;
            }
        }

        $io->success(sprintf('完成，共创建 %d 个目录', $created));

        return Command::SUCCESS;
    }
}
