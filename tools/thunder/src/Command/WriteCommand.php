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
 * write — 把内容写入文件（新建或覆盖/追加）
 *
 * 用法：
 *   thunder write <file> "内容" [-a]   内容含空格时记得加引号
 */
#[AsCommand(name: 'write', description: '把文本内容写入文件（默认覆盖，-a 追加）')]
final class WriteCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, '目标文件路径')
            ->addArgument('content', InputArgument::OPTIONAL, '要写入的内容（缺省为空串）', '')
            ->addOption('append', 'a', InputOption::VALUE_NONE, '追加到文件末尾而非覆盖');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $this->resolvePath((string) $input->getArgument('file'));
        $content = (string) $input->getArgument('content');
        $append = (bool) $input->getOption('append');

        if (is_dir($path)) {
            $io->error('目标是目录，无法写入: ' . $path);

            return Command::FAILURE;
        }

        $flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;
        $ok = @file_put_contents($path, $content, $flags);
        if ($ok === false) {
            $io->error('写入失败（检查父目录与权限）: ' . $path);

            return Command::FAILURE;
        }

        $action = $append ? '已追加' : '已写入';
        $io->success(sprintf('%s %s（%s）', $action, $path, $this->formatBytes($ok)));

        return Command::SUCCESS;
    }
}
