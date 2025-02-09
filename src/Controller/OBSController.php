<?php

namespace App\Controller;

use App\Entity\Streamer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $streamer = $this->entityManager->getRepository(Streamer::class)->findOneBy(['viewKey' => $viewKey]);

        if (!$streamer instanceof Streamer) {
            throw new BadRequestHttpException('Bad view key');
        }

        $counter = $request->query->get('counter', 0) % 2;

        if ($streamer->getLurkbaitEntries()->count() <= 0) {
            $counter = 0;
        }

        switch ($counter) {
            case 1:
                return $this->renderFishingLeaderboard($streamer);
            case 0:
            default:
                return $this->renderFirstLeaderboard($streamer);

        }
    }

    private function renderFishingLeaderboard(Streamer $streamer): Response
    {
        $leaderboard = $streamer->getLurkbaitEntries()->toArray();

        usort($leaderboard, function($a, $b) {
            return $a->getGold() < $b->getGold();
        });
        $leaderboard = array_splice($leaderboard, 0, 7, true);

        return $this->render('obs/leaderboard_load_fishing.html.twig', [
            'leaderboard' => $leaderboard,
            'title' => 'RANKING OKW "RYBAK":',
        ]);
    }

    private function renderFirstLeaderboard(Streamer $streamer): Response
    {
        $leaderboard = $streamer->getLeaderboards()->toArray();

        usort($leaderboard, function($a, $b) {
            return $a->getCount() < $b->getCount();
        });
        $leaderboard = array_splice($leaderboard, 0, 7, true);

        return $this->render('obs/leaderboard_load.html.twig', [
            'leaderboard' => $leaderboard,
            'title' => 'RANKING "PIERWSZY":',
        ]);
    }
}
