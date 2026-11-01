<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Seance;
use App\Repository\CoachRepository;
use App\Repository\CoursRepository;
use App\Repository\SeanceRepository;
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

        $donnees = $this->preparer($request, $validator, $coursRepository);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $seance = new Seance();
        $seance->setCoach($coach);
        $seance->setCours($donnees['cours']);
        $seance->setDateHeureDebut($donnees['debut']);
        $seance->setDateHeureFin($donnees['fin']);

        $entityManager->persist($seance);
        $entityManager->flush();

        return $this->json($this->formater($seance), Response::HTTP_CREATED);
    }

    #[Route(path: '/api/coach/seances/{id}', name: 'api_coach_seances_modifier', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function modifier(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        SeanceRepository $seanceRepository,
        CoachRepository $coachRepository,
        CoursRepository $coursRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $seance = $this->chargerSeance($id, $seanceRepository, $coachRepository);

        if ($seance instanceof JsonResponse) {
            return $seance;
        }

        $donnees = $this->preparer($request, $validator, $coursRepository);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $seance->setCours($donnees['cours']);
        $seance->setDateHeureDebut($donnees['debut']);
        $seance->setDateHeureFin($donnees['fin']);

        $entityManager->flush();

        return $this->json($this->formater($seance));
    }

    #[Route(path: '/api/coach/seances/{id}', name: 'api_coach_seances_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimer(
        int $id,
        SeanceRepository $seanceRepository,
        CoachRepository $coachRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $seance = $this->chargerSeance($id, $seanceRepository, $coachRepository);

        if ($seance instanceof JsonResponse) {
            return $seance;
        }

        $entityManager->remove($seance);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function chargerSeance(int $id, SeanceRepository $seanceRepository, CoachRepository $coachRepository): Seance|JsonResponse
    {
        $seance = $seanceRepository->find($id);

        if (!$seance) {
            return $this->json(['erreur' => 'Séance introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $coach = $coachRepository->findOneBy(['utilisateur' => $this->getUser()]);

        if (!$coach || $seance->getCoach()->getId() !== $coach->getId()) {
            return $this->json(['erreur' => 'Cette séance ne vous appartient pas.'], Response::HTTP_FORBIDDEN);
        }

        return $seance;
    }

    private function preparer(Request $request, ValidatorInterface $validator, CoursRepository $coursRepository): array|JsonResponse
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

        $debut = \DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $donnees['dateHeureDebut']);
        $fin = \DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $donnees['dateHeureFin']);

        if ($fin <= $debut) {
            return $this->json(
                ['erreurs' => ['dateHeureFin' => 'La fin doit être après le début.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $cours = $coursRepository->find($donnees['coursId']);

        if (!$cours instanceof Cours) {
            return $this->json(
                ['erreurs' => ['coursId' => 'Ce cours n\'existe pas.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return ['cours' => $cours, 'debut' => $debut, 'fin' => $fin];
    }

    private function formater(Seance $seance): array
    {
        return [
            'id' => $seance->getId(),
            'coursId' => $seance->getCours()->getId(),
            'coachId' => $seance->getCoach()->getId(),
            'dateHeureDebut' => $seance->getDateHeureDebut()->format(self::FORMAT_DATE),
            'dateHeureFin' => $seance->getDateHeureFin()->format(self::FORMAT_DATE),
        ];
    }
}
