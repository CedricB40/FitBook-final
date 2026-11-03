<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Entity\Utilisateur;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SalleController extends AbstractController
{
    #[Route(path: '/api/gestionnaire/salle', name: 'api_gestionnaire_salle_voir', methods: ['GET'])]
    public function voir(SalleRepository $salleRepository): JsonResponse
    {
        $salle = $salleRepository->findOneBy(['gestionnaire' => $this->getUser()]);

        if (!$salle) {
            return $this->json(['erreur' => 'Aucune salle enregistrée pour ce gestionnaire.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->formater($salle));
    }

    #[Route(path: '/api/gestionnaire/salle', name: 'api_gestionnaire_salle_creer', methods: ['POST'])]
    public function creer(
        Request $request,
        ValidatorInterface $validator,
        SalleRepository $salleRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        if ($salleRepository->findOneBy(['gestionnaire' => $utilisateur])) {
            return $this->json(['erreur' => 'Ce gestionnaire a déjà une salle.'], Response::HTTP_CONFLICT);
        }

        $donnees = $this->lireDonnees($request, $validator);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $salle = new Salle();
        $salle->setGestionnaire($utilisateur);
        $this->remplir($salle, $donnees);

        $entityManager->persist($salle);
        $entityManager->flush();

        return $this->json($this->formater($salle), Response::HTTP_CREATED);
    }

    #[Route(path: '/api/gestionnaire/salle', name: 'api_gestionnaire_salle_modifier', methods: ['PUT'])]
    public function modifier(
        Request $request,
        ValidatorInterface $validator,
        SalleRepository $salleRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $salle = $salleRepository->findOneBy(['gestionnaire' => $this->getUser()]);

        if (!$salle) {
            return $this->json(['erreur' => 'Aucune salle enregistrée pour ce gestionnaire.'], Response::HTTP_NOT_FOUND);
        }

        $donnees = $this->lireDonnees($request, $validator);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $this->remplir($salle, $donnees);

        $entityManager->flush();

        return $this->json($this->formater($salle));
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
                    new Assert\Length(max: 100),
                ]),
                'adresse' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ]),
                'ville' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Length(max: 50),
                ]),
                'description' => new Assert\Optional([
                    new Assert\Type(type: ['string', 'null']),
                ]),
                'telephone' => new Assert\Optional([
                    new Assert\Type(type: ['string', 'null']),
                    new Assert\Length(max: 255),
                ]),
                'photo' => new Assert\Optional([
                    new Assert\Type(type: ['string', 'null']),
                    new Assert\Length(max: 255),
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

    private function remplir(Salle $salle, array $donnees): void
    {
        $salle->setNom($donnees['nom']);
        $salle->setAdresse($donnees['adresse']);
        $salle->setVille($donnees['ville']);
        $salle->setDescription($donnees['description'] ?? null);
        $salle->setTelephone($donnees['telephone'] ?? null);
        $salle->setPhoto($donnees['photo'] ?? null);
    }

    private function formater(Salle $salle): array
    {
        return [
            'id' => $salle->getId(),
            'nom' => $salle->getNom(),
            'adresse' => $salle->getAdresse(),
            'ville' => $salle->getVille(),
            'description' => $salle->getDescription(),
            'telephone' => $salle->getTelephone(),
            'photo' => $salle->getPhoto(),
        ];
    }
}