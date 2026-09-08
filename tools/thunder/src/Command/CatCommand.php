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
 * cat — 查看文件内容
 *
 * 用法：
 *   thunder cat <file> [-n] [-b N]
 */
#[AsCommand(name: 'cat', description: '查看文件内容')]
final class CatCommand extends BaseCommand
{
    /** 默认最多读取的行数（防止误刷屏） */
    private const MAX_LINES = 2000;

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, '文件路径')
            // 快捷方式不能用 -n：与全局 --no-interaction 冲突
            ->addOption('lines', null, InputOption::VALUE_REQUIRED, '只显示前 N 行', (string) self::MAX_LINES);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $this->resolvePath((string) $input->getArgument('file'));

        if (!is_file($path)) {
            $io->error('文件不存在: ' . $path);

            return Command::FAILURE;
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            $io->error('文件不可读: ' . $path);

            return Command::FAILURE;
        }

        // 二进制检测：含 NUL 字节或有效字符比例过低时拒绝输出
        if (str_contains($content, "\0")) {
            $io->warning('检测到二进制文件，已跳过内容输出（大小: ' . $this->formatBytes(strlen($content)) . '）');

            return Command::SUCCESS;
        }

        $limit = max(1, (int) $input->getOption('lines'));
        $lines = preg_split("/\r\n|\n|\r/", $content);
        $total = count($lines);

        foreach (array_slice($lines, 0, $limit) as $i => $line) {
            $output->writeln(str_pad((string) ($i + 1), 5, ' ', STR_PAD_LEFT) . '  ' . $line);
        }

        if ($total > $limit) {
            $io->newLine();
            $io->writeln(sprintf('<fg=yellow>… 共 %d 行，仅显示前 %d 行（用 -n 调整）</>', $total, $limit));
        }

        return Command::SUCCESS;
    }
}
