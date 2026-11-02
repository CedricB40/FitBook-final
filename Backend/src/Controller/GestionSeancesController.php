<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Cours;
use App\Entity\Salle;
use App\Entity\Seance;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GestionSeancesController extends AbstractController
{
    private const FORMAT_DATE = 'Y-m-d H:i:s';

    #[Route(path: '/api/gestionnaire/cours', name: 'api_gestionnaire_cours_liste', methods: ['GET'])]
    public function cours(EntityManagerInterface $entityManager): JsonResponse
    {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $cours = $entityManager->getRepository(Cours::class)->findBy(['salle' => $salle], ['titre' => 'ASC']);

        return $this->json(array_map(
            fn (Cours $unCours) => [
                'id' => $unCours->getId(),
                'titre' => $unCours->getTitre(),
                'description' => $unCours->getDescription(),
                'duree' => $unCours->getDuree(),
                'capacite' => $unCours->getCapacite(),
                'activiteId' => $unCours->getActivite()?->getId(),
                'activite' => $unCours->getActivite()?->getNom(),
            ],
            $cours
        ));
    }

    #[Route(path: '/api/gestionnaire/cours/{id}', name: 'api_gestionnaire_cours_modifier', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function modifierCours(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        ActiviteRepository $activiteRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $cours = $this->chargerCours($id, $entityManager);

        if ($cours instanceof JsonResponse) {
            return $cours;
        }

        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $contraintes = new Assert\Collection(
            fields: [
                'titre' => new Assert\Sequentially([
                    new Assert\Type(type: 'string', message: 'Le titre est invalide.'),
                    new Assert\NotBlank(message: 'Le titre est obligatoire.'),
                    new Assert\Length(max: 100, maxMessage: 'Le titre est trop long (100 caractères maximum).'),
                ]),
                'description' => new Assert\Optional([
                    new Assert\Type(type: ['string', 'null'], message: 'La description est invalide.'),
                ]),
                'duree' => new Assert\Sequentially([
                    new Assert\Type(type: 'int', message: 'La durée doit être un nombre entier.'),
                    new Assert\Positive(message: 'La durée doit être positive.'),
                ]),
                'capacite' => new Assert\Sequentially([
                    new Assert\Type(type: 'int', message: 'La capacité doit être un nombre entier.'),
                    new Assert\Positive(message: 'La capacité doit être positive.'),
                ]),
                'activiteId' => new Assert\Sequentially([
                    new Assert\Type(type: 'int', message: 'Choisis une activité.'),
                    new Assert\Positive(message: 'Choisis une activité.'),
                ]),
            ]
        );

        $violations = $validator->validate($donnees, $contraintes);

        if (count($violations) > 0) {
            return $this->erreursValidation($violations);
        }

        $activite = $activiteRepository->find($donnees['activiteId']);

        if (!$activite) {
            return $this->json(
                ['erreurs' => ['activiteId' => "Cette activité n'existe pas."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $cours->setTitre($donnees['titre']);
        $cours->setDescription($donnees['description'] ?? null);
        $cours->setDuree($donnees['duree']);
        $cours->setCapacite($donnees['capacite']);
        $cours->setActivite($activite);

        $entityManager->flush();

        return $this->json(['id' => $cours->getId()]);
    }

    #[Route(path: '/api/gestionnaire/cours/{id}', name: 'api_gestionnaire_cours_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimerCours(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $cours = $this->chargerCours($id, $entityManager);

        if ($cours instanceof JsonResponse) {
            return $cours;
        }

        $nombreSeances = $entityManager->getRepository(Seance::class)->count(['cours' => $cours]);

        if ($nombreSeances > 0) {
            return $this->json(
                ['erreur' => 'Ce cours a encore des séances. Supprime-les avant de supprimer le cours.'],
                Response::HTTP_CONFLICT
            );
        }

        $entityManager->remove($cours);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/gestionnaire/seances', name: 'api_gestionnaire_seances_creer', methods: ['POST'])]
    public function creer(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $donnees = $this->preparerSeance($request, $validator, $salle, $entityManager);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $seance = new Seance();
        $seance->setCours($donnees['cours']);
        $seance->setCoach($donnees['coach']);
        $seance->setDateHeureDebut($donnees['debut']);
        $seance->setDateHeureFin($donnees['fin']);

        $entityManager->persist($seance);
        $entityManager->flush();

        return $this->json(['id' => $seance->getId()], Response::HTTP_CREATED);
    }

    #[Route(path: '/api/gestionnaire/seances/{id}', name: 'api_gestionnaire_seances_modifier', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function modifierSeance(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $seance = $this->chargerSeance($id, $salle, $entityManager);

        if ($seance instanceof JsonResponse) {
            return $seance;
        }

        $donnees = $this->preparerSeance($request, $validator, $salle, $entityManager);

        if ($donnees instanceof JsonResponse) {
            return $donnees;
        }

        $seance->setCours($donnees['cours']);
        $seance->setCoach($donnees['coach']);
        $seance->setDateHeureDebut($donnees['debut']);
        $seance->setDateHeureFin($donnees['fin']);

        $entityManager->flush();

        return $this->json(['id' => $seance->getId()]);
    }

    #[Route(path: '/api/gestionnaire/seances/{id}', name: 'api_gestionnaire_seances_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimer(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $seance = $this->chargerSeance($id, $salle, $entityManager);

        if ($seance instanceof JsonResponse) {
            return $seance;
        }

        $entityManager->remove($seance);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function preparerSeance(
        Request $request,
        ValidatorInterface $validator,
        Salle $salle,
        EntityManagerInterface $entityManager
    ): array|JsonResponse {
        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $contraintes = new Assert\Collection(
            fields: [
                'coursId' => new Assert\Sequentially([
                    new Assert\Type(type: 'int', message: 'Choisis un cours.'),
                    new Assert\Positive(message: 'Choisis un cours.'),
                ]),
                'coachId' => new Assert\Sequentially([
                    new Assert\Type(type: 'int', message: 'Choisis un coach.'),
                    new Assert\Positive(message: 'Choisis un coach.'),
                ]),
                'dateHeureDebut' => new Assert\Sequentially([
                    new Assert\Type(type: 'string', message: 'Le début est invalide.'),
                    new Assert\DateTime(format: self::FORMAT_DATE, message: 'Le début est invalide.'),
                ]),
                'dateHeureFin' => new Assert\Sequentially([
                    new Assert\Type(type: 'string', message: 'La fin est invalide.'),
                    new Assert\DateTime(format: self::FORMAT_DATE, message: 'La fin est invalide.'),
                ]),
            ]
        );

        $violations = $validator->validate($donnees, $contraintes);

        if (count($violations) > 0) {
            return $this->erreursValidation($violations);
        }

        $debut = \DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $donnees['dateHeureDebut']);
        $fin = \DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $donnees['dateHeureFin']);

        if ($fin <= $debut) {
            return $this->json(
                ['erreurs' => ['dateHeureFin' => 'La fin doit être après le début.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $cours = $entityManager->getRepository(Cours::class)->find($donnees['coursId']);

        if (!$cours instanceof Cours || $cours->getSalle()?->getId() !== $salle->getId()) {
            return $this->json(
                ['erreurs' => ['coursId' => "Ce cours n'appartient pas à ta salle."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $coach = $entityManager->getRepository(Coach::class)->find($donnees['coachId']);

        if (!$coach instanceof Coach) {
            return $this->json(
                ['erreurs' => ['coachId' => "Ce coach n'existe pas."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return ['cours' => $cours, 'coach' => $coach, 'debut' => $debut, 'fin' => $fin];
    }

    private function chargerSeance(int $id, Salle $salle, EntityManagerInterface $entityManager): Seance|JsonResponse
    {
        $seance = $entityManager->getRepository(Seance::class)->find($id);

        if (!$seance) {
            return $this->json(['erreur' => 'Séance introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($seance->getCours()?->getSalle()?->getId() !== $salle->getId()) {
            return $this->json(['erreur' => 'Cette séance ne se déroule pas dans ta salle.'], Response::HTTP_FORBIDDEN);
        }

        return $seance;
    }

    private function chargerCours(int $id, EntityManagerInterface $entityManager): Cours|JsonResponse
    {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $cours = $entityManager->getRepository(Cours::class)->find($id);

        if (!$cours) {
            return $this->json(['erreur' => 'Cours introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($cours->getSalle()?->getId() !== $salle->getId()) {
            return $this->json(['erreur' => "Ce cours n'appartient pas à ta salle."], Response::HTTP_FORBIDDEN);
        }

        return $cours;
    }

    private function erreursValidation(iterable $violations): JsonResponse
    {
        $erreurs = [];

        foreach ($violations as $violation) {
            $erreurs[trim($violation->getPropertyPath(), '[]')] = $violation->getMessage();
        }

        return $this->json(['erreurs' => $erreurs], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function salle(EntityManagerInterface $entityManager): ?Salle
    {
        return $entityManager->getRepository(Salle::class)->findOneBy(['gestionnaire' => $this->getUser()]);
    }

    private function salleManquante(): JsonResponse
    {
        return $this->json(
            ['erreur' => "Tu dois d'abord enregistrer ta salle."],
            Response::HTTP_CONFLICT
        );
    }
}
