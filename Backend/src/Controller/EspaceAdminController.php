<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Reservation;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EspaceAdminController extends AbstractController
{
    private const FORMAT_ISO = 'Y-m-d\TH:i:s';
    private const ROLES = ['ROLE_SPORTIF', 'ROLE_COACH', 'ROLE_GESTIONNAIRE', 'ROLE_ADMIN'];

    #[Route(path: '/api/admin/statistiques', name: 'api_admin_statistiques', methods: ['GET'])]
    public function statistiques(EntityManagerInterface $entityManager): JsonResponse
    {
        return $this->json([
            'utilisateurs' => $entityManager->getRepository(Utilisateur::class)->count([]),
            'avis' => $entityManager->getRepository(Avis::class)->count([]),
            'reservations' => $entityManager->getRepository(Reservation::class)->count(
                ['statut' => Reservation::STATUT_CONFIRMEE]
            ),
        ]);
    }

    #[Route(path: '/api/admin/utilisateurs', name: 'api_admin_utilisateurs_liste', methods: ['GET'])]
    public function utilisateurs(EntityManagerInterface $entityManager): JsonResponse
    {
        $utilisateurs = $entityManager->getRepository(Utilisateur::class)->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);

        return $this->json(array_map(fn (Utilisateur $utilisateur) => $this->formaterUtilisateur($utilisateur), $utilisateurs));
    }

    #[Route(path: '/api/admin/utilisateurs/{id}', name: 'api_admin_utilisateurs_modifier', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function modifierUtilisateur(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);

        if (!$utilisateur) {
            return $this->json(['erreur' => 'Utilisateur introuvable.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $contraintes = new Assert\Collection(
            fields: [
                'nom' => new Assert\Sequentially([
                    new Assert\Type(type: 'string', message: 'Le nom est invalide.'),
                    new Assert\NotBlank(message: 'Le nom est obligatoire.'),
                    new Assert\Length(max: 100, maxMessage: 'Le nom est trop long.'),
                ]),
                'prenom' => new Assert\Sequentially([
                    new Assert\Type(type: 'string', message: 'Le prénom est invalide.'),
                    new Assert\NotBlank(message: 'Le prénom est obligatoire.'),
                    new Assert\Length(max: 100, maxMessage: 'Le prénom est trop long.'),
                ]),
                'email' => new Assert\Sequentially([
                    new Assert\Type(type: 'string', message: "L'adresse e-mail est invalide."),
                    new Assert\NotBlank(message: "L'adresse e-mail est obligatoire."),
                    new Assert\Email(message: "L'adresse e-mail est invalide."),
                ]),
                'role' => new Assert\Choice(choices: self::ROLES, message: 'Ce rôle est invalide.'),
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

        $existant = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $donnees['email']]);

        if ($existant && $existant->getId() !== $utilisateur->getId()) {
            return $this->json(
                ['erreurs' => ['email' => 'Cette adresse e-mail est déjà utilisée.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($utilisateur->getId() === $this->getUser()->getId() && $donnees['role'] !== $utilisateur->getRole()) {
            return $this->json(
                ['erreurs' => ['role' => 'Tu ne peux pas changer ton propre rôle.']],
                Response::HTTP_CONFLICT
            );
        }

        $utilisateur->setNom(trim($donnees['nom']));
        $utilisateur->setPrenom(trim($donnees['prenom']));
        $utilisateur->setEmail(trim($donnees['email']));
        $utilisateur->setRole($donnees['role']);

        $entityManager->flush();

        return $this->json($this->formaterUtilisateur($utilisateur));
    }

    #[Route(path: '/api/admin/reservations', name: 'api_admin_reservations_liste', methods: ['GET'])]
    public function reservations(EntityManagerInterface $entityManager): JsonResponse
    {
        $reservations = $entityManager->createQuery(
            'SELECT r, se, c, s, co, uc, us
             FROM App\Entity\Reservation r
             JOIN r.seance se
             JOIN se.cours c
             JOIN c.salle s
             JOIN se.coach co
             JOIN co.utilisateur uc
             JOIN r.sportif us
             ORDER BY se.dateHeureDebut DESC'
        )->getResult();

        return $this->json(array_map(
            fn (Reservation $reservation) => [
                'id' => $reservation->getId(),
                'statut' => $reservation->getStatut(),
                'dateReservation' => $reservation->getDateReservation()?->format(self::FORMAT_ISO),
                'sportif' => trim($reservation->getSportif()?->getPrenom().' '.$reservation->getSportif()?->getNom()),
                'seance' => [
                    'id' => $reservation->getSeance()?->getId(),
                    'debut' => $reservation->getSeance()?->getDateHeureDebut()?->format(self::FORMAT_ISO),
                    'cours' => $reservation->getSeance()?->getCours()?->getTitre(),
                    'salle' => $reservation->getSeance()?->getCours()?->getSalle()?->getNom(),
                    'coach' => trim(
                        $reservation->getSeance()?->getCoach()?->getUtilisateur()?->getPrenom().' '
                        .$reservation->getSeance()?->getCoach()?->getUtilisateur()?->getNom()
                    ),
                ],
            ],
            $reservations
        ));
    }

    #[Route(path: '/api/admin/reservations/{id}', name: 'api_admin_reservations_modifier', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function modifierReservation(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return $this->json(['erreur' => 'Réservation introuvable.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $statut = $donnees['statut'] ?? null;

        if (!in_array($statut, [Reservation::STATUT_CONFIRMEE, Reservation::STATUT_ANNULEE], true)) {
            return $this->json(['erreurs' => ['statut' => 'Ce statut est invalide.']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($statut === Reservation::STATUT_CONFIRMEE && $reservation->getStatut() !== Reservation::STATUT_CONFIRMEE) {
            $seance = $reservation->getSeance();
            $capacite = $seance?->getCours()?->getCapacite() ?? 0;
            $confirmees = $entityManager->getRepository(Reservation::class)->count(
                ['seance' => $seance, 'statut' => Reservation::STATUT_CONFIRMEE]
            );

            if ($confirmees >= $capacite) {
                return $this->json(['erreur' => 'Cette séance est complète.'], Response::HTTP_CONFLICT);
            }
        }

        $reservation->setStatut($statut);
        $entityManager->flush();

        return $this->json(['id' => $reservation->getId(), 'statut' => $reservation->getStatut()]);
    }

    private function formaterUtilisateur(Utilisateur $utilisateur): array
    {
        return [
            'id' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
            'role' => $utilisateur->getRole(),
            'dateInscription' => $utilisateur->getDateInscription()?->format('Y-m-d'),
        ];
    }
}
