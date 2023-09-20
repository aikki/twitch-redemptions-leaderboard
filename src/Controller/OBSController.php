<?php

namespace App\Controller;

use App\Entity\Streamer;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class OBSController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }
    #[Route('/leaderboard/{viewKey}', name: 'app_leaderboard')]
    public function leaderboard(Request $request, string $viewKey): Response
    {
        return $this->render('obs/leaderboard.html.twig', [
            'viewKey' => $viewKey,
        ]);
    }

    #[Route('/leaderboard/load/{viewKey}', name: 'app_leaderboard_load')]
    public function getLeaderboard(Request $request, string $viewKey): Response
    {
        $response = new Response();

        $streamer = $this->entityManager->getRepository(Streamer::class)->findOneBy(['viewKey' => $viewKey]);

        if (!$streamer instanceof Streamer) {
            throw new BadRequestHttpException('Bad view key');
        }

        $leaderboard = $streamer->getLeaderboards();

        usort($leaderboard, function($a, $b) {
            return $a->getCount() < $b->getCount();
        });

        return $this->render('obs/leaderboard_load.html.twig', [
            'leaderboard' => $leaderboard,
        ], $response);
    }
}
