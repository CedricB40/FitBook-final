<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Repository\CoachRepository;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SeanceController extends AbstractController
{
    private const FORMAT_DATE = 'Y-m-d H:i:s';

    #[Route(path: '/api/coach/seances', name: 'api_coach_seances_creer', methods: ['POST'])]
    public function creer(
        Request $request,
        ValidatorInterface $validator,
        CoachRepository $coachRepository,
        CoursRepository $coursRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $coach = $coachRepository->findOneBy(['utilisateur' => $this->getUser()]);

        if (!$coach) {
            return $this->json(['erreur' => 'Vous devez d\'abord enregistrer votre profil de coach.'], Response::HTTP_CONFLICT);
        }

        $donnees = $this->lireDonnees($request, $validator);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $debut = \DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $donnees['dateHeureDebut']);
        $fin = \DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $donnees['dateHeureFin']);

        if ($fin <= $debut) {
            return $this->json(
                ['erreurs' => ['dateHeureFin' => 'La fin doit être après le début.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $cours = $coursRepository->find($donnees['coursId']);

        if (!$cours) {
            return $this->json(
                ['erreurs' => ['coursId' => 'Ce cours n\'existe pas.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $seance = new Seance();
        $seance->setCoach($coach);
        $seance->setCours($cours);
        $seance->setDateHeureDebut($debut);
        $seance->setDateHeureFin($fin);

        $entityManager->persist($seance);
        $entityManager->flush();

        return $this->json([
            'id' => $seance->getId(),
            'coursId' => $cours->getId(),
            'coachId' => $coach->getId(),
            'dateHeureDebut' => $seance->getDateHeureDebut()->format(self::FORMAT_DATE),
            'dateHeureFin' => $seance->getDateHeureFin()->format(self::FORMAT_DATE),
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
                'coursId' => new Assert\Sequentially([
                    new Assert\Type('int'),
                    new Assert\Positive(),
                ]),
                'dateHeureDebut' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\DateTime(format: self::FORMAT_DATE),
                ]),
                'dateHeureFin' => new Assert\Sequentially([
                    new Assert\Type('string'),
                    new Assert\DateTime(format: self::FORMAT_DATE),
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
