<?php

namespace App\Controller;

use App\Service\SecretService;
use App\Service\TwitchService;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class OBSController extends AbstractController
{
    public function __construct(
        private readonly TwitchService $twitch,
        private readonly SecretService $secretService,
    )
    {
    }
    #[Route('/login/{token}', name: 'app_login')]
    public function login(Request $request, string $token): Response
    {
        $response = $this->redirectToRoute('app_leaderboard');

        $tokenData = json_decode($this->secretService->decrypt($token));
        if (!$request->cookies->has('r') || (string) $tokenData->r !== $request->cookies->get('r')) {
            $twitchAuthCode = $tokenData->code;
            $data = $this->twitch->getUserAccessToken($twitchAuthCode);
            $this->assignCookiesFromData($data, $response, $tokenData->bid, $tokenData->rid, $tokenData->r);
        } else {
            $this->handleTokens($request, $response);
        }

        return $response;
    }
    #[Route('/leaderboard', name: 'app_leaderboard')]
    public function leaderboard(Request $request): Response
    {
        return $this->render('obs/leaderboard.html.twig');
    }

    #[Route('/leaderboard/load', name: 'app_leaderboard_load')]
    public function getLeaderboard(Request $request): Response
    {
        $response = new Response();
        $bearer = $this->handleTokens($request, $response);

        if ($request->cookies->get('bid') === "31098555")
            $redemptions = $this->twitch->getSampleCustomRewardRedemptions($bearer, $request->cookies->get('bid'), $request->cookies->get('rid'));
        else
            $redemptions = $this->twitch->getCustomRewardRedemptions($bearer, $request->cookies->get('bid'), $request->cookies->get('rid'));


        $leaderboard = [];
        foreach ($redemptions as $redemption)
        {
            if (!isset($leaderboard[$redemption->user_name])) {
                $leaderboard[$redemption->user_name] = [
                    'name' => $redemption->user_name,
                    'count' => 0,
                ];
            }
            $leaderboard[$redemption->user_name]['count']++;
        }
        $leaderboard = array_values($leaderboard);

        usort($leaderboard, function($a, $b) {
            return $a['count'] < $b['count'];
        });

        return $this->render('obs/leaderboard_load.html.twig', [
            'leaderboard' => $leaderboard,
        ], $response);
    }

    private function handleTokens(Request $request, Response $response): string
    {
        try {
            $this->twitch->getOauth()->isValidAccessToken($this->secretService->decrypt($request->cookies->get('t')));
            return $this->secretService->decrypt($request->cookies->get('t'));
        } catch (ClientException $exception) {
            $data = $this->twitch->refreshToken($this->secretService->decrypt($request->cookies->get('rt')));
            $this->assignCookiesFromData($data, $response, $request->cookies->get('bid'), $request->cookies->get('rid'), $request->cookies->get('r'));
            return $data->access_token;
        }
    }

    private function assignCookiesFromData(\stdClass $data, Response $response, string $bid, string $rid, string $r): void
    {
        $expire = time() + (365 * 24 * 60 * 60);
        $response->headers->setCookie(new Cookie('t', $this->secretService->encrypt($data->access_token), $expire));
        $response->headers->setCookie(new Cookie('rt', $this->secretService->encrypt($data->refresh_token), $expire));
        $response->headers->setCookie(new Cookie('bid', $bid, $expire));
        $response->headers->setCookie(new Cookie('rid', $rid, $expire));
        $response->headers->setCookie(new Cookie('r', $r, $expire));
    }
}
