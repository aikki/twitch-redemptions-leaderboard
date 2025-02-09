<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Entity\Lurkbait\Entry;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class LurkbaitController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }
    #[Route('/lurkbait/sync/{key}', name: 'app_lurkbait_sync', methods: ['POST', 'OPTIONS'])]
    public function sync(string $key, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $streamer = $this->entityManager->getRepository(Streamer::class)->findOneBy(['key' => $key]);

            if (null === $streamer) {
                throw new NotFoundHttpException();
            }

            $data = json_decode($request->getContent(), true);

            foreach ($data as $name => $item) {
                $entry = new Entry($streamer, $name, $item['displayName'], $item['gold'], $item['totalCasts']);
                $this->entityManager->persist($entry);
            }
            $this->entityManager->flush();

            $this->entityManager->createQueryBuilder()
                ->delete(Entry::class, 'le')
                ->where('le.streamer = :streamer')
                ->andWhere('le.active = :active')
                ->setParameter('streamer', $streamer)
                ->setParameter('active', true)
            ->getQuery()
            ->execute();

            $this->entityManager->createQueryBuilder()
                ->update(Entry::class, 'le')
                ->set('le.active', true)
                ->where('le.streamer = :streamer')
                ->setParameter('streamer', $streamer)
            ->getQuery()
            ->execute();
        }

        return new Response(headers: [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

}
