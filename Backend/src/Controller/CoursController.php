<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Repository\ActiviteRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CoursController extends AbstractController
{
    #[Route(path: '/api/gestionnaire/cours', name: 'api_gestionnaire_cours_creer', methods: ['POST'])]
    public function creer(
        Request $request,
        ValidatorInterface $validator,
        SalleRepository $salleRepository,
        ActiviteRepository $activiteRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $salle = $salleRepository->findOneBy(['gestionnaire' => $this->getUser()]);

        if (!$salle) {
            return $this->json(['erreur' => 'Vous devez d\'abord enregistrer votre salle.'], Response::HTTP_CONFLICT);
        }

        $donnees = $this->lireDonnees($request, $validator);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $activite = $activiteRepository->find($donnees['activiteId']);

        if (!$activite) {
            return $this->json(
                ['erreurs' => ['activiteId' => 'Cette activité n\'existe pas.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $cours = new Cours();
        $cours->setSalle($salle);
        $cours->setActivite($activite);
        $cours->setTitre($donnees['titre']);
        $cours->setDescription($donnees['description'] ?? null);
        $cours->setDuree($donnees['duree']);
        $cours->setCapacite($donnees['capacite']);

        $entityManager->persist($cours);
        $entityManager->flush();

        return $this->json([
            'id' => $cours->getId(),
            'titre' => $cours->getTitre(),
            'description' => $cours->getDescription(),
            'duree' => $cours->getDuree(),
            'capacite' => $cours->getCapacite(),
            'salleId' => $salle->getId(),
            'activiteId' => $activite->getId(),
        ], Response::HTTP_CREATED);
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
                'titre' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Length(max: 100),
                ]),
                'description' => new Assert\Optional([
                    new Assert\Type(type: ['string', 'null']),
                ]),
                'duree' => new Assert\Sequentially([
                    new Assert\Type('int'),
                    new Assert\Positive(),
                ]),
                'capacite' => new Assert\Sequentially([
                    new Assert\Type('int'),
                    new Assert\Positive(),
                ]),
                'activiteId' => new Assert\Sequentially([
                    new Assert\Type('int'),
                    new Assert\Positive(),
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
}
