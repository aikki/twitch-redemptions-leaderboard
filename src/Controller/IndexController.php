<?php

namespace App\Controller;

use App\Service\SecretService;
use App\Service\TwitchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly TwitchService $twitch,
        private readonly SecretService $secretService,
    )
    {
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        return $this->redirect('https://www.twitch.tv/aikki');

        // return $this->render('index/index.html.twig', [
        //     'authUrl' => $this->twitch->getAuthUrl($this->generateUrl('app_config')),
        // ]);
    }

    #[Route('/config', name: 'app_config')]
    public function config(Request $request): Response
    {
        $session = $request->getSession();
        if ($request->query->has('code')) {
            $data = $this->twitch->getUserAccessToken($request->query->get('code'));
            $session->set('token', $data->access_token);
            $session->set('r_token', $data->refresh_token);
            return $this->redirectToRoute('app_config');
        }

        $bearer = $session->get('token');

        $userData = $this->twitch->getUserData($bearer);
        $session->set('bid', $userData->id);

        if ($userData->id === "31098555")
            $rewards = $this->twitch->getSampleCustomRewards();
        else
            $rewards = $this->twitch->getCustomRewards($bearer, $userData->id);

        $choices = [];
        foreach ($rewards as $reward) {
            $choices[$reward->title] = $reward->id;
        }

        $form = $this->createFormBuilder()
            ->add('reward', ChoiceType::class, [
                'choices' => $choices,
                'label' => 'Wybierz nagrodę',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Wygeneruj'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $id = $form->get('reward')->getData();
            $session->set('rid', $id);
            return $this->redirect($this->twitch->getAuthUrl($this->generateUrl('app_generate')));
        }

        return $this->render('index/config.html.twig', [
            'userData' => $userData,
            'form' => $form,
        ]);

    }

    #[Route('/generate', name: 'app_generate')]
    public function generate(Request $request): Response
    {
        if ($request->query->has('code')) {
            $code = $request->query->get('code');
            $this->addFlash('code', $code);
            return $this->redirectToRoute('app_generate');
        }

        $session = $request->getSession();
        /**
         * @var FlashBag $flashBag
         */
        $flashBag = $session->getBag('flashes');
        $code = $flashBag->get('code');
        if (empty($code)) {
            return $this->redirectToRoute('app_config');
        } else {
            $code = $code[0];
        }

        $data = json_encode([
            'r' => time(),
            'code' => $code,
            'bid' => $session->get('bid'),
            'rid' => $session->get('rid'),
        ]);

        return $this->render('index/generate.html.twig', [
            'token' => $this->secretService->encrypt($data),
        ]);
    }
}
