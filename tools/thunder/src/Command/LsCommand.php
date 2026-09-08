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
 * ls — 列出目录内容
 *
 * 用法：
 *   thunder ls [path] [-a|--all] [-l|--long]
 */
#[AsCommand(name: 'ls', description: '列出目录内容')]
final class LsCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, '目标目录', '.')
            ->addOption('all', 'a', InputOption::VALUE_NONE, '显示隐藏文件（. 开头）')
            ->addOption('long', 'l', InputOption::VALUE_NONE, '显示详细信息（类型/大小/修改时间）');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $this->resolvePath((string) $input->getArgument('path'));

        if (!is_dir($path)) {
            $io->error('目录不存在: ' . $path);

            return Command::FAILURE;
        }

        $entries = $this->listEntries($path, (bool) $input->getOption('all'));
        if ($entries === []) {
            $io->writeln('(空目录)');

            return Command::SUCCESS;
        }

        if ($input->getOption('long')) {
            $rows = [];
            foreach ($entries as $item) {
                $rows[] = [
                    $item->isDir() ? '<fg=blue>dir</>' : 'file',
                    $item->isDir() ? '-' : $this->formatBytes($item->getSize()),
                    date('Y-m-d H:i', $item->getMTime()),
                    $this->paint($item),
                ];
            }
            $io->table(['类型', '大小', '修改时间', '名称'], $rows);
        } else {
            // 简洁模式：名称一行铺开，目录名加色加斜杠
            $names = array_map(fn($item) => $this->paint($item), $entries);
            $io->writeln(implode('  ', $names));
        }

        return Command::SUCCESS;
    }

    /** 目录蓝色加粗带斜杠，文件白色 */
    private function paint(\SplFileInfo $item): string
    {
        return $item->isDir()
            ? '<fg=cyan;options=bold>' . $item->getFilename() . '/</>'
            : $item->getFilename();
    }
}
