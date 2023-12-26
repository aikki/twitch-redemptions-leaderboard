<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Entity\PlaylistCounter;
use App\Entity\Streamer;
use App\Entity\Wheel\Entry;
use App\Entity\Wheel\Ignored;
use App\Entity\Wheel\Wheel;
use App\Repository\PlaylistCounterRepository;
use App\Repository\Wheel\EntryRepository;
use App\Repository\Wheel\WheelRepository;
use App\Service\SecretService;
use App\Service\TwitchService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Util\Json;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class CounterController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlaylistCounterRepository $playlistCounterRepository,
    )
    {
    }

    #[Route('/playlist/count', name: 'app_playlist_count')]
    public function count(Request $request): Response
    {
        $data = $this->validateHeaders($request->headers);

        $counter = $this->playlistCounterRepository->findOneBy(['userId' => $data['userId'], 'playlistId' => $data['playlistId']]);
        if (!($counter instanceof PlaylistCounter))
        {
            $counter = new PlaylistCounter();
            $counter->setUserId($data['userId']);
            $counter->setCounter(0);
            $counter->setPlaylistId($data['playlistId']);
        }
        $counter->setUsername($data['username']);
        $counter->setCounter($counter->getCounter() + 1);
        $this->entityManager->persist($counter);
        $this->entityManager->flush();

        return new Response($counter->getCounter());
    }

    #[Route('/playlist/subtract', name: 'subtract_playlist_count')]
    public function subtract(Request $request): Response
    {
        $data = $this->validateHeaders($request->headers);

        $counter = $this->playlistCounterRepository->findOneBy(['userId' => $data['userId'], 'playlistId' => $data['playlistId']]);
        if (!($counter instanceof PlaylistCounter))
        {
            $counter = new PlaylistCounter();
            $counter->setUserId($data['userId']);
            $counter->setCounter(0);
            $counter->setPlaylistId($data['playlistId']);
        } else {
            $counter->setCounter($counter->getCounter() - 1);
        }
        $counter->setUsername($data['username']);
        $this->entityManager->persist($counter);
        $this->entityManager->flush();

        return new Response($counter->getCounter());
    }

    private function validateHeaders(HeaderBag $headers): array
    {
        if (
            !$headers->has('UserName') ||
            !$headers->has('UserId') ||
            !$headers->has('PlaylistId')
        ) {
            throw new BadRequestHttpException('Bad headers');
        }

        return [
            'username' => $headers->get('UserName'),
            'userId' => $headers->get('UserId'),
            'playlistId' => $headers->get('PlaylistId'),
        ];
    }
}
