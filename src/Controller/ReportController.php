<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\OrderManager;
use App\Service\Report\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReportController extends AbstractController
{
    public function __construct(
        private readonly OrderManager $orders,
        private readonly ReportService $reports,
    ) {
    }

    #[Route(
        path: '/reports/{format}',
        name: 'app_report',
        requirements: ['format' => 'csv|xlsx|pdf'],
        methods: ['GET'],
    )]
    #[IsGranted('ROLE_USER')]
    public function download(string $format): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->reports->generate($format, $this->orders->listVisibleTo($user));
    }
}
