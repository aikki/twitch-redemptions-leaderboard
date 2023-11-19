<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Entity\Streamer;
use App\Entity\Wheel\Entry;
use App\Entity\Wheel\Ignored;
use App\Entity\Wheel\Wheel;
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

class WheelController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WheelRepository $wheelRepository,
        private readonly EntryRepository $entryRepository,
    )
    {
    }
    #[Route('/wheel/register', name: 'app_wheel_register')]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent());

        if (empty($data)
            || !property_exists($data, 'channels') || !is_array($data->channels) || empty($data->channels)
            || !property_exists($data, 'broadcasterId') ||  empty($data->broadcasterId)
        ) {
            throw new BadRequestHttpException('Properties `broadcasterId` and `channels` are mandatory');
        }

        $wheel = new Wheel();
        $wheel->setCode(bin2hex(random_bytes(10)));
        $wheel->setBroadcasterId(intval($data->broadcasterId));
        $this->entityManager->persist($wheel);

        $ignored =
            array_map(function($e) {
                return $e->getChannel();
            },
                $this->entityManager->getRepository(Ignored::class)->findBy(['broadcasterId' => $data->broadcasterId])
            );

        foreach ($data->channels as $channel) {
            if (in_array($channel, $ignored)) continue;
            $entry = new Entry();
            $entry->setName($channel);
            $wheel->addEntry($entry);
            $this->entityManager->persist($entry);
        }

        $this->entityManager->flush();

        return new JsonResponse(['source' => $this->generateUrl('app_wheel_source', ['code' => $wheel->getCode()], UrlGeneratorInterface::ABSOLUTE_URL)]);
    }

    #[Route('/wheel/source/{code}', name: 'app_wheel_source')]
    public function source(string $code, Request $request): Response
    {
        $wheel = $this->wheelRepository->findOneBy(['code' => $code]);
        if (!$wheel instanceof Wheel) {
            throw new NotFoundHttpException();
        }

        return $this->render('wheel/wheel.html.twig', [
            'code' => $code,
            'channels' => $wheel->getEntries(),
        ]);
    }

    #[Route('/wheel/source/{code}/spin', name: 'app_wheel_spin_get', methods: ['GET'])]
    public function spinGET(string $code, Request $request): Response
    {
        $wheel = $this->wheelRepository->findOneBy(['code' => $code]);
        if (!$wheel instanceof Wheel) {
            throw new NotFoundHttpException();
        }
        if (!$wheel->isSpin()) {
            throw new LockedHttpException();
        }
        return new Response();
    }

    #[Route('/wheel/source/{code}/spin', name: 'app_wheel_spin_post', methods: ['POST'])]
    public function spinPOST(string $code, Request $request): Response
    {
        $wheel = $this->wheelRepository->findOneBy(['code' => $code]);
        if (!$wheel instanceof Wheel) {
            throw new NotFoundHttpException();
        }

        $wheel->setSpin(true);
        $this->entityManager->persist($wheel);
        $this->entityManager->flush();

        return new Response();
    }

    #[Route('/wheel/source/{code}/winner', name: 'app_wheel_winner_post', methods: ['POST'])]
    public function winner(string $code, Request $request): Response
    {
        $wheel = $this->wheelRepository->findOneBy(['code' => $code]);
        if (!$wheel instanceof Wheel) {
            throw new NotFoundHttpException();
        }
        if (!$wheel->isSpin()) {
            throw new LockedHttpException();
        }
        $data = json_decode($request->getContent());

        if (empty($data) || !property_exists($data, 'winner') || empty($data->winner)) {
            throw new BadRequestHttpException('Property `winner` is mandatory');
        }

        $winner = $this->entryRepository->findOneBy([
            'wheel' => $wheel,
            'name' => $data->winner,
        ]);

        if (!$winner instanceof Entry) {
            throw new NotFoundHttpException();
        }

        $wheel->setWinner($winner);
        $this->entityManager->persist($wheel);
        $this->entityManager->flush();

        return new Response();
    }

    #[Route('/wheel/source/{code}/winner', name: 'app_wheel_winner_get', methods: ['GET'])]
    public function winnerGET(string $code, Request $request): Response
    {
        $wheel = $this->wheelRepository->findOneBy(['code' => $code]);
        if (!$wheel instanceof Wheel) {
            throw new NotFoundHttpException();
        }
        if (!$wheel->isSpin() || !$wheel->getWinner() instanceof Entry) {
            throw new LockedHttpException();
        }

        return new Response($wheel->getWinner()->getName());
    }

    #[Route('/wheel/ignore', name: 'app_wheel_ignore', methods: ['POST'])]
    public function ignore(Request $request): Response
    {
        $data = json_decode($request->getContent());

        if (empty($data)
            || !property_exists($data, 'broadcasterId') || empty($data->broadcasterId)
            || !property_exists($data, 'channel') || empty($data->channel)
        ) {
            throw new BadRequestHttpException('Properties `broadcasterId` and `channel` are mandatory');
        }

        $channel = explode(' ', trim($data->channel))[0];
        $this->entityManager->persist(new Ignored(intval($data->broadcasterId), $channel));
        $this->entityManager->flush();

        return new Response();
    }
}
