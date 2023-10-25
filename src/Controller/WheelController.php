<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Entity\Streamer;
use App\Entity\Wheel\Entry;
use App\Entity\Wheel\Wheel;
use App\Service\SecretService;
use App\Service\TwitchService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class WheelController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }
    #[Route('/wheel/register', name: 'app_wheel_register')]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent());

        if (!property_exists($data, 'channels') || !is_array($data->channels) || empty($data->channels)) {
            throw new BadRequestHttpException('Property `channels` is mandatory');
        }

        $wheel = new Wheel();
        $wheel->setCode(bin2hex(random_bytes(10)));
        $this->entityManager->persist($wheel);

        foreach ($data->channels as $channel) {
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
        return new Response($code);
    }
}
