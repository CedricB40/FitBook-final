<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UtilisateurController extends AbstractController
{
    #[Route(path: '/api/admin/utilisateurs/{id}', name: 'api_admin_utilisateurs_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimer(
        int $id,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            return $this->json(['erreur' => 'Utilisateur introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($utilisateur->getUserIdentifier() === $this->getUser()->getUserIdentifier()) {
            return $this->json(['erreur' => 'Vous ne pouvez pas supprimer votre propre compte.'], Response::HTTP_CONFLICT);
        }

        try {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->json(
                ['erreur' => 'Cet utilisateur est lié à une salle, un profil de coach ou des réservations.'],
                Response::HTTP_CONFLICT
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
