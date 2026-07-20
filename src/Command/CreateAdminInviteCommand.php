<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\AdminInviteService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:admin:create-invite',
    description: 'Создаёт инвайт для регистрации администратора',
)]
class CreateAdminInviteCommand extends Command
{
    public function __construct(
        private readonly AdminInviteService $adminInviteService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email будущего администратора');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');

        $invite = $this->adminInviteService->createInvite($email);
        $url = $this->urlGenerator->generate(
            'admin_invite_register',
            ['token' => $invite->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $io->success(sprintf('Инвайт для %s создан.', $email));
        $io->section('Ссылка для регистрации администратора');
        $io->writeln(sprintf('  <href=%s>%s</>', $url, $url));
        $io->newLine();
        $io->note('Ссылка действительна 2 дней. Отправьте её будущему администратору.');

        return Command::SUCCESS;
    }
}
