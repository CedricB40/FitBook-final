<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Repository\ActiviteRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActiviteController extends AbstractController
{
    #[Route(path: '/api/admin/activites', name: 'api_admin_activites_liste', methods: ['GET'])]
    public function liste(ActiviteRepository $activiteRepository): JsonResponse
    {
        $activites = array_map(
            fn (Activite $activite) => $this->formater($activite),
            $activiteRepository->findBy([], ['nom' => 'ASC'])
        );

        return $this->json($activites);
    }

    #[Route(path: '/api/activites', name: 'api_activites_liste_publique', methods: ['GET'])]
    public function listePublique(ActiviteRepository $activiteRepository): JsonResponse
    {
        return $this->liste($activiteRepository);
    }

    #[Route(path: '/api/admin/activites', name: 'api_admin_activites_creer', methods: ['POST'])]
    public function creer(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $donnees = $this->lireDonnees($request, $validator);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $activite = new Activite();
        $activite->setNom($donnees['nom']);
        $activite->setDescription($donnees['description']);

        $entityManager->persist($activite);
        $entityManager->flush();

        return $this->json($this->formater($activite), Response::HTTP_CREATED);
    }

    #[Route(path: '/api/admin/activites/{id}', name: 'api_admin_activites_modifier', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function modifier(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        ActiviteRepository $activiteRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
            return $this->json(['erreur' => 'Activité introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $donnees = $this->lireDonnees($request, $validator);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $activite->setNom($donnees['nom']);
        $activite->setDescription($donnees['description']);

        $entityManager->flush();

        return $this->json($this->formater($activite));
    }

    #[Route(path: '/api/admin/activites/{id}', name: 'api_admin_activites_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimer(
        int $id,
        ActiviteRepository $activiteRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
            return $this->json(['erreur' => 'Activité introuvable.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($activite);
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return $this->json(
                ['erreur' => 'Cette activité est utilisée par des cours. Supprime-les avant de la supprimer.'],
                Response::HTTP_CONFLICT
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function lireDonnees(Request $request, ValidatorInterface $validator): array|JsonResponse
    {
        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $contraintes = new Assert\Collection(
            fields: [
                'nom' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ]),
                'description' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                ]),
            ]
        );

        $violations = $validator->validate($donnees, $contraintes);

        if (count($violations) > 0) {
            $erreurs = [];
            foreach ($violations as $violation) {
                $erreurs[trim($violation->getPropertyPath(), '[]')] = $violation->getMessage();
            }

            return $this->json(['erreurs' => $erreurs], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $donnees;
    }

    private function formater(Activite $activite): array
    {
        return [
            'id' => $activite->getId(),
            'nom' => $activite->getNom(),
            'description' => $activite->getDescription(),
        ];
    }
}