<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Entity\Streamer;
use App\Service\SecretService;
use App\Service\TwitchService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class StreamerbotController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }
    #[Route('/streamerbot/redeem', name: 'app_redeem')]
    public function redeem(Request $request): Response
    {
        $this->logger->info('received', $request->headers->all());

        $data = $this->validateHeaders($request->headers);

        $repo = $this->entityManager->getRepository(Leaderboard::class);

        $leaderboard = $repo->findOneBy($data);
        if (!($leaderboard instanceof Leaderboard))
        {
            $leaderboard = new Leaderboard();
            $leaderboard->setName($data['name']);
            $leaderboard->setCount(0);
            $leaderboard->setStreamer($data['streamer']);
        }
        $leaderboard->setCount($leaderboard->getCount() + 1);
        $this->entityManager->persist($leaderboard);
        $this->entityManager->flush();

        return new Response();
    }

    #[Route('/leaderboard/{viewKey}', name: 'app_streamerbot_leaderboard')]
    public function leaderboard(Request $request): Response
    {

    }

    private function validateHeaders(HeaderBag $headers): array
    {
        if (
            !$headers->has('UserName') ||
            !$headers->has('StreamerKey')
        ) {
            throw new BadRequestHttpException('Bad headers');
        }

        $streamer = $this->entityManager->getRepository(Streamer::class)->findOneBy(['key' => $headers->get('StreamerKey')]);

        if ($streamer instanceof Streamer) {
            return [
                'streamer' => $streamer,
                'name' => $headers->get('UserName'),
            ];
        }
        throw new BadRequestHttpException('Bad streamer');
    }
}
