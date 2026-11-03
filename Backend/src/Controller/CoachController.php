<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Utilisateur;
use App\Repository\CoachRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CoachController extends AbstractController
{
    #[Route(path: '/api/coach/profil', name: 'api_coach_profil_voir', methods: ['GET'])]
    public function voir(CoachRepository $coachRepository): JsonResponse
    {
        $coach = $coachRepository->findOneBy(['utilisateur' => $this->getUser()]);

        if (!$coach) {
            return $this->json(['erreur' => 'Aucun profil enregistré pour ce coach.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->formater($coach));
    }

    #[Route(path: '/api/coach/profil', name: 'api_coach_profil_creer', methods: ['POST'])]
    public function creer(
        Request $request,
        ValidatorInterface $validator,
        CoachRepository $coachRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        if ($coachRepository->findOneBy(['utilisateur' => $utilisateur])) {
            return $this->json(['erreur' => 'Ce coach a déjà un profil.'], Response::HTTP_CONFLICT);
        }

        $donnees = $this->lireDonnees($request, $validator);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $coach = new Coach();
        $coach->setUtilisateur($utilisateur);
        $this->remplir($coach, $donnees);

        $entityManager->persist($coach);
        $entityManager->flush();

        return $this->json($this->formater($coach), Response::HTTP_CREATED);
    }

    #[Route(path: '/api/coach/profil', name: 'api_coach_profil_modifier', methods: ['PUT'])]
    public function modifier(
        Request $request,
        ValidatorInterface $validator,
        CoachRepository $coachRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $coach = $coachRepository->findOneBy(['utilisateur' => $this->getUser()]);

        if (!$coach) {
            return $this->json(['erreur' => 'Aucun profil enregistré pour ce coach.'], Response::HTTP_NOT_FOUND);
        }

        $donnees = $this->lireDonnees($request, $validator);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $this->remplir($coach, $donnees);

        $entityManager->flush();

        return $this->json($this->formater($coach));
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
                'specialite' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ]),
                'biographie' => new Assert\Optional([
                    new Assert\Type(type: ['string', 'null']),
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

    private function remplir(Coach $coach, array $donnees): void
    {
        $coach->setSpecialite($donnees['specialite']);
        $coach->setBiographie($donnees['biographie'] ?? null);
        $coach->setPhoto($donnees['photo'] ?? null);
    }

    private function formater(Coach $coach): array
    {
        return [
            'id' => $coach->getId(),
            'specialite' => $coach->getSpecialite(),
            'biographie' => $coach->getBiographie(),
            'photo' => $coach->getPhoto(),
        ];
    }
}
