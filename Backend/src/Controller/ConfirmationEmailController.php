<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\CourrielService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class ConfirmationEmailController extends AbstractController
{
    #[Route(path: '/api/confirmation-email', name: 'api_confirmation_email', methods: ['GET'])]
    public function confirmer(
        Request $request,
        EntityManagerInterface $entityManager,
        VerifyEmailHelperInterface $verifyEmailHelper,
        #[Autowire(env: 'FRONTEND_URL')] string $urlFrontend
    ): RedirectResponse {
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find((int) $request->query->get('id'));

        if (!$utilisateur) {
            return $this->redirect($urlFrontend.'/connexion?confirme=0');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                (string) $utilisateur->getId(),
                (string) $utilisateur->getEmail()
            );
        } catch (VerifyEmailExceptionInterface) {
            return $this->redirect($urlFrontend.'/connexion?confirme=0');
        }

        $utilisateur->setEmailConfirme(true);
        $entityManager->flush();

        return $this->redirect($urlFrontend.'/connexion?confirme=1');
    }

    #[Route(path: '/api/renvoyer-confirmation', name: 'api_renvoyer_confirmation', methods: ['POST'])]
    public function renvoyer(
        Request $request,
        EntityManagerInterface $entityManager,
        CourrielService $courrielService
    ): JsonResponse {
        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], 400);
        }

        $email = is_string($donnees['email'] ?? null) ? mb_strtolower(trim($donnees['email'])) : '';

        $utilisateur = $email === ''
            ? null
            : $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if ($utilisateur && !$utilisateur->isEmailConfirme()) {
            $courrielService->envoyerConfirmation($utilisateur);
        }

        // Même réponse dans tous les cas, pour ne pas révéler quelles adresses existent.
        return $this->json(['message' => "Si cette adresse existe et n'est pas encore confirmée, un e-mail vient d'être envoyé."]);
    }
}
