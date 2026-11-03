<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Entity\Utilisateur;
use App\Service\CourrielService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationController extends AbstractController
{
    #[Route(path: '/api/sportif/reservations', name: 'api_sportif_reservations_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $entityManager): JsonResponse
    {
        $reservations = $entityManager->createQueryBuilder()
            ->select('r', 'se', 'c', 's', 'a', 'co', 'u')
            ->from(Reservation::class, 'r')
            ->join('r.seance', 'se')
            ->join('se.cours', 'c')
            ->join('c.salle', 's')
            ->join('c.activite', 'a')
            ->join('se.coach', 'co')
            ->join('co.utilisateur', 'u')
            ->andWhere('r.sportif = :sportif')
            ->setParameter('sportif', $this->getUser())
            ->orderBy('se.dateHeureDebut', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->json(array_map(
            fn (Reservation $reservation) => $this->formater($reservation),
            $reservations
        ));
    }

    #[Route(path: '/api/sportif/reservations', name: 'api_sportif_reservations_creer', methods: ['POST'])]
    public function creer(Request $request, EntityManagerInterface $entityManager, CourrielService $courrielService): JsonResponse
    {
        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $idSeance = $donnees['seance'] ?? null;

        if (!is_int($idSeance) || $idSeance < 1) {
            return $this->json(
                ['erreurs' => ['seance' => 'Identifiant de séance invalide.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        /** @var Utilisateur $sportif */
        $sportif = $this->getUser();

        $reservationCreee = null;

        $reponse = $entityManager->wrapInTransaction(function () use ($entityManager, $idSeance, $sportif, &$reservationCreee): JsonResponse {
            $seance = $entityManager->find(Seance::class, $idSeance, LockMode::PESSIMISTIC_WRITE);

            if (!$seance) {
                return $this->json(['erreur' => 'Séance introuvable.'], Response::HTTP_NOT_FOUND);
            }

            if ($seance->getDateHeureDebut() <= new \DateTimeImmutable()) {
                return $this->json(['erreur' => 'Cette séance a déjà commencé.'], Response::HTTP_CONFLICT);
            }

            $existante = $entityManager->getRepository(Reservation::class)->findOneBy([
                'sportif' => $sportif,
                'seance' => $seance,
            ]);

            if ($existante && $existante->getStatut() === Reservation::STATUT_CONFIRMEE) {
                return $this->json(['erreur' => 'Tu as déjà réservé cette séance.'], Response::HTTP_CONFLICT);
            }

            $reservees = (int) $entityManager->createQuery(
                'SELECT COUNT(r.id) FROM App\Entity\Reservation r WHERE r.seance = :seance AND r.statut = :statut'
            )
                ->setParameter('seance', $seance)
                ->setParameter('statut', Reservation::STATUT_CONFIRMEE)
                ->getSingleScalarResult();

            if ($reservees >= $seance->getCours()->getCapacite()) {
                return $this->json(['erreur' => 'Cette séance est complète.'], Response::HTTP_CONFLICT);
            }

            $reservation = $existante ?? (new Reservation())->setSportif($sportif)->setSeance($seance);
            $reservation->setStatut(Reservation::STATUT_CONFIRMEE);
            $reservation->setDateReservation(new \DateTimeImmutable());

            $entityManager->persist($reservation);
            $entityManager->flush();

            $reservationCreee = $reservation;

            return $this->json($this->formater($reservation), Response::HTTP_CREATED);
        });

        if ($reservationCreee instanceof Reservation) {
            $courrielService->envoyerReservation($reservationCreee, true);
        }

        return $reponse;
    }

    #[Route(path: '/api/sportif/reservations/{id}', name: 'api_sportif_reservations_annuler', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function annuler(int $id, EntityManagerInterface $entityManager, CourrielService $courrielService): JsonResponse
    {
        $reservation = $entityManager->find(Reservation::class, $id);

        if (!$reservation) {
            return $this->json(['erreur' => 'Réservation introuvable.'], Response::HTTP_NOT_FOUND);
        }

        /** @var Utilisateur $sportif */
        $sportif = $this->getUser();

        if ($reservation->getSportif()?->getId() !== $sportif->getId()) {
            return $this->json(['erreur' => 'Cette réservation ne t\'appartient pas.'], Response::HTTP_FORBIDDEN);
        }

        if ($reservation->getStatut() === Reservation::STATUT_ANNULEE) {
            return $this->json(['erreur' => 'Cette réservation est déjà annulée.'], Response::HTTP_CONFLICT);
        }

        if ($reservation->getSeance()->getDateHeureDebut() <= new \DateTimeImmutable()) {
            return $this->json(['erreur' => 'Cette séance a déjà commencé.'], Response::HTTP_CONFLICT);
        }

        $reservation->setStatut(Reservation::STATUT_ANNULEE);
        $entityManager->flush();

        $courrielService->envoyerReservation($reservation, false);

        return $this->json($this->formater($reservation));
    }

    private function formater(Reservation $reservation): array
    {
        $seance = $reservation->getSeance();
        $cours = $seance?->getCours();
        $coach = $seance?->getCoach();

        return [
            'id' => $reservation->getId(),
            'statut' => $reservation->getStatut(),
            'dateReservation' => $reservation->getDateReservation()?->format('Y-m-d\TH:i:s'),
            'seance' => [
                'id' => $seance?->getId(),
                'debut' => $seance?->getDateHeureDebut()?->format('Y-m-d\TH:i:s'),
                'fin' => $seance?->getDateHeureFin()?->format('Y-m-d\TH:i:s'),
                'cours' => [
                    'id' => $cours?->getId(),
                    'titre' => $cours?->getTitre(),
                    'activite' => $cours?->getActivite()?->getNom(),
                ],
                'salle' => [
                    'id' => $cours?->getSalle()?->getId(),
                    'nom' => $cours?->getSalle()?->getNom(),
                    'ville' => $cours?->getSalle()?->getVille(),
                ],
                'coach' => [
                    'id' => $coach?->getId(),
                    'nom' => $coach?->getUtilisateur()?->getNom(),
                    'prenom' => $coach?->getUtilisateur()?->getPrenom(),
                ],
            ],
        ];
    }
}
