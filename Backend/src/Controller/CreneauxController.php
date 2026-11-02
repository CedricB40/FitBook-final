<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Disponibilite;
use App\Entity\Horaire;
use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Gère les créneaux hebdomadaires : disponibilités des coachs et horaires d'ouverture des salles.
 */
class CreneauxController extends AbstractController
{
    private const MOTIF_HEURE = '/^([01]\d|2[0-3]):[0-5]\d$/';

    #[Route(path: '/api/coach/disponibilites', name: 'api_coach_disponibilites_liste', methods: ['GET'])]
    public function disponibilites(EntityManagerInterface $entityManager): JsonResponse
    {
        $coach = $this->coach($entityManager);

        if (!$coach) {
            return $this->profilCoachManquant();
        }

        $disponibilites = $entityManager->getRepository(Disponibilite::class)->findBy(
            ['coach' => $coach],
            ['jourSemaine' => 'ASC', 'heureDebut' => 'ASC']
        );

        return $this->json(array_map(
            fn (Disponibilite $disponibilite) => $this->formaterDisponibilite($disponibilite),
            $disponibilites
        ));
    }

    #[Route(path: '/api/coach/disponibilites', name: 'api_coach_disponibilites_creer', methods: ['POST'])]
    public function creerDisponibilite(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $coach = $this->coach($entityManager);

        if (!$coach) {
            return $this->profilCoachManquant();
        }

        $creneau = $this->lireCreneau($request, $validator, 'heureDebut', 'heureFin');

        if ($creneau instanceof JsonResponse) {
            return $creneau;
        }

        $disponibilite = new Disponibilite();
        $disponibilite->setCoach($coach);
        $disponibilite->setJourSemaine($creneau['jour']);
        $disponibilite->setHeureDebut($creneau['debut']);
        $disponibilite->setHeureFin($creneau['fin']);

        $entityManager->persist($disponibilite);
        $entityManager->flush();

        return $this->json($this->formaterDisponibilite($disponibilite), Response::HTTP_CREATED);
    }

    #[Route(path: '/api/coach/disponibilites/{id}', name: 'api_coach_disponibilites_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimerDisponibilite(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $coach = $this->coach($entityManager);

        if (!$coach) {
            return $this->profilCoachManquant();
        }

        $disponibilite = $entityManager->getRepository(Disponibilite::class)->find($id);

        if (!$disponibilite) {
            return $this->json(['erreur' => 'Disponibilité introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($disponibilite->getCoach()?->getId() !== $coach->getId()) {
            return $this->json(['erreur' => "Cette disponibilité ne t'appartient pas."], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($disponibilite);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/gestionnaire/horaires', name: 'api_gestionnaire_horaires_liste', methods: ['GET'])]
    public function horaires(EntityManagerInterface $entityManager): JsonResponse
    {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $horaires = $entityManager->getRepository(Horaire::class)->findBy(
            ['salle' => $salle],
            ['jourSemaine' => 'ASC', 'heureOuverture' => 'ASC']
        );

        return $this->json(array_map(fn (Horaire $horaire) => $this->formaterHoraire($horaire), $horaires));
    }

    #[Route(path: '/api/gestionnaire/horaires', name: 'api_gestionnaire_horaires_creer', methods: ['POST'])]
    public function creerHoraire(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $creneau = $this->lireCreneau($request, $validator, 'heureOuverture', 'heureFermeture');

        if ($creneau instanceof JsonResponse) {
            return $creneau;
        }

        $horaire = new Horaire();
        $horaire->setSalle($salle);
        $horaire->setJourSemaine($creneau['jour']);
        $horaire->setHeureOuverture($creneau['debut']);
        $horaire->setHeureFermeture($creneau['fin']);

        $entityManager->persist($horaire);
        $entityManager->flush();

        return $this->json($this->formaterHoraire($horaire), Response::HTTP_CREATED);
    }

    #[Route(path: '/api/gestionnaire/horaires/{id}', name: 'api_gestionnaire_horaires_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimerHoraire(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $horaire = $entityManager->getRepository(Horaire::class)->find($id);

        if (!$horaire) {
            return $this->json(['erreur' => 'Horaire introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($horaire->getSalle()?->getId() !== $salle->getId()) {
            return $this->json(['erreur' => "Cet horaire n'appartient pas à ta salle."], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($horaire);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Valide le corps d'une requête (jour de la semaine + heure de début + heure de fin).
     * Les deux clés d'heures changent selon qu'il s'agit d'une disponibilité ou d'un horaire.
     */
    private function lireCreneau(
        Request $request,
        ValidatorInterface $validator,
        string $cleDebut,
        string $cleFin
    ): array|JsonResponse {
        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $contrainteHeure = fn (string $libelle) => new Assert\Sequentially([
            new Assert\Type(type: 'string', message: "L'heure $libelle est invalide."),
            new Assert\Regex(pattern: self::MOTIF_HEURE, message: "L'heure $libelle doit être au format HH:MM."),
        ]);

        $contraintes = new Assert\Collection(
            fields: [
                'jourSemaine' => new Assert\Sequentially([
                    new Assert\Type(type: 'int', message: 'Choisis un jour de la semaine.'),
                    new Assert\Range(min: 1, max: 7, notInRangeMessage: 'Le jour doit être compris entre 1 (lundi) et 7 (dimanche).'),
                ]),
                $cleDebut => $contrainteHeure('de début'),
                $cleFin => $contrainteHeure('de fin'),
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

        if ($donnees[$cleFin] <= $donnees[$cleDebut]) {
            return $this->json(
                ['erreurs' => [$cleFin => "L'heure de fin doit être après l'heure de début."]],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return [
            'jour' => $donnees['jourSemaine'],
            'debut' => \DateTimeImmutable::createFromFormat('H:i', $donnees[$cleDebut]),
            'fin' => \DateTimeImmutable::createFromFormat('H:i', $donnees[$cleFin]),
        ];
    }

    private function coach(EntityManagerInterface $entityManager): ?Coach
    {
        return $entityManager->getRepository(Coach::class)->findOneBy(['utilisateur' => $this->getUser()]);
    }

    private function salle(EntityManagerInterface $entityManager): ?Salle
    {
        return $entityManager->getRepository(Salle::class)->findOneBy(['gestionnaire' => $this->getUser()]);
    }

    private function profilCoachManquant(): JsonResponse
    {
        return $this->json(['erreur' => "Tu dois d'abord enregistrer ton profil de coach."], Response::HTTP_CONFLICT);
    }

    private function salleManquante(): JsonResponse
    {
        return $this->json(['erreur' => "Tu dois d'abord enregistrer ta salle."], Response::HTTP_CONFLICT);
    }

    private function formaterDisponibilite(Disponibilite $disponibilite): array
    {
        return [
            'id' => $disponibilite->getId(),
            'jourSemaine' => $disponibilite->getJourSemaine(),
            'heureDebut' => $disponibilite->getHeureDebut()?->format('H:i'),
            'heureFin' => $disponibilite->getHeureFin()?->format('H:i'),
        ];
    }

    private function formaterHoraire(Horaire $horaire): array
    {
        return [
            'id' => $horaire->getId(),
            'jourSemaine' => $horaire->getJourSemaine(),
            'heureOuverture' => $horaire->getHeureOuverture()?->format('H:i'),
            'heureFermeture' => $horaire->getHeureFermeture()?->format('H:i'),
        ];
    }
}
