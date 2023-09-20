<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Service\SecretService;
use App\Service\TwitchService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

        $repo = $this->entityManager->getRepository(Leaderboard::class);

        $xdd = new Leaderboard();
        $xdd->setName($request->headers->get('UserName'));
        $xdd->setCount($request->headers->get('UserCount'));

        $this->entityManager->persist($xdd);
        $this->entityManager->flush();

        return new Response();
    }
}
