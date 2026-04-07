<?php

namespace App\Command;

use App\Service\ReservationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reservations:expire',
    description: 'Expire les réservations échues et restaure le stock des livres',
)]
class ReservationsExpireCommand extends Command
{
    public function __construct(
        private ReservationService $reservationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Expire les réservations échues et restaure le stock des livres')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->reservationService->processExpiredReservations();

        $io->success(sprintf('%d réservations expirées traitées. Stock restauré.', $count));

        return Command::SUCCESS;
    }
}
