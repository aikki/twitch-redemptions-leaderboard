<?php

namespace App\Controller;

use App\Entity\Streamer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class BridgeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    )
    {}

    #[Route('/bridge/{name}/leaderboards.json', name: 'app_bridge_leaderboards')]
    public function index(string $name, Request $request): Response
    {
        $streamer = $this->entityManager->getRepository(Streamer::class)->findOneBy(['name' => $name]);

        if (null === $streamer) {
            throw new NotFoundHttpException();
        }

        return JsonResponse::fromJsonString($this->serializer->serialize([
            'first' => $streamer->getLeaderboards(),
            'fishing' => $streamer->getLurkbaitEntries(),
        ], 'json', ['groups' => 'bridge']));
    }
}
