<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use App\Service\NotesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AvisController extends AbstractController
{
    private const CIBLES = [
        'salle' => Avis::CIBLE_SALLE,
        'coach' => Avis::CIBLE_COACH,
        'cours' => Avis::CIBLE_COURS,
    ];

    #[Route(path: '/api/avis/{cible}/{id}', name: 'api_avis_liste', requirements: ['cible' => 'salle|coach|cours', 'id' => '\d+'], methods: ['GET'])]
    public function liste(string $cible, int $id, NotesService $notesService): JsonResponse
    {
        $cibleAvis = self::CIBLES[$cible];
        $utilisateur = $this->getUser();

        return $this->json(
            $notesService->resume($cibleAvis, $id) + [
                'avis' => $notesService->avis($cibleAvis, $id, $utilisateur instanceof Utilisateur ? $utilisateur->getId() : null),
            ]
        );
    }

    #[Route(path: '/api/sportif/avis', name: 'api_sportif_avis_creer', methods: ['POST'])]
    public function creer(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        NotesService $notesService
    ): JsonResponse {
        try {
            $donnees = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['erreur' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $contraintes = new Assert\Collection(
            fields: [
                'cible' => new Assert\Choice(
                    choices: array_keys(self::CIBLES),
                    message: 'Cible invalide : choisis salle, coach ou cours.'
                ),
                'id' => new Assert\Sequentially([
                    new Assert\Type('int', message: "L'identifiant doit être un nombre entier."),
                    new Assert\Positive(message: "L'identifiant doit être positif."),
                ]),
                'note' => new Assert\Sequentially([
                    new Assert\Type('int', message: 'La note doit être un nombre entier.'),
                    new Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être comprise entre 1 et 5.'),
                ]),
                'commentaire' => new Assert\Optional([
                    new Assert\Type('string', message: 'Le commentaire doit être du texte.'),
                    new Assert\Length(max: 1000, maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.'),
                ]),
            ],
            extraFieldsMessage: "Ce champ n'est pas autorisé.",
            missingFieldsMessage: 'Ce champ est obligatoire.'
        );

        $violations = $validator->validate($donnees, $contraintes);

        if (count($violations) > 0) {
            $erreurs = [];
            foreach ($violations as $violation) {
                $erreurs[trim($violation->getPropertyPath(), '[]')] = $violation->getMessage();
            }

            return $this->json(['erreurs' => $erreurs], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var Utilisateur $sportif */
        $sportif = $this->getUser();
        $cibleAvis = self::CIBLES[$donnees['cible']];
        $reservation = $notesService->reservationEligible($sportif, $cibleAvis, $donnees['id']);

        if (!$reservation) {
            return $this->json(
                ['erreur' => 'Tu peux laisser un avis après avoir suivi une séance (un seul avis par séance).'],
                Response::HTTP_CONFLICT
            );
        }

        $commentaire = isset($donnees['commentaire']) ? trim($donnees['commentaire']) : '';

        $avis = new Avis();
        $avis->setReservation($reservation);
        $avis->setCible($cibleAvis);
        $avis->setNote($donnees['note']);
        $avis->setCommentaire($commentaire === '' ? null : $commentaire);

        $entityManager->persist($avis);
        $entityManager->flush();

        return $this->json($notesService->formater($avis, $sportif->getId()), Response::HTTP_CREATED);
    }

    #[Route(path: '/api/sportif/avis/{id}', name: 'api_sportif_avis_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimerMonAvis(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $avis = $entityManager->find(Avis::class, $id);

        if (!$avis) {
            return $this->json(['erreur' => 'Avis introuvable.'], Response::HTTP_NOT_FOUND);
        }

        /** @var Utilisateur $sportif */
        $sportif = $this->getUser();

        if ($avis->getReservation()?->getSportif()?->getId() !== $sportif->getId()) {
            return $this->json(['erreur' => "Cet avis ne t'appartient pas."], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($avis);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/admin/avis', name: 'api_admin_avis_liste', methods: ['GET'])]
    public function listeAdmin(EntityManagerInterface $entityManager): JsonResponse
    {
        $avis = $entityManager->getRepository(Avis::class)->findBy([], ['dateAvis' => 'DESC', 'id' => 'DESC']);

        return $this->json(array_map(fn (Avis $unAvis) => $this->formaterAdmin($unAvis), $avis));
    }

    #[Route(path: '/api/admin/avis/{id}', name: 'api_admin_avis_supprimer', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function supprimerAdmin(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $avis = $entityManager->find(Avis::class, $id);

        if (!$avis) {
            return $this->json(['erreur' => 'Avis introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($avis);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function formaterAdmin(Avis $avis): array
    {
        $reservation = $avis->getReservation();
        $cours = $reservation?->getSeance()?->getCours();
        $coach = $reservation?->getSeance()?->getCoach();
        $sportif = $reservation?->getSportif();

        $nomCible = match ($avis->getCible()) {
            Avis::CIBLE_SALLE => $cours?->getSalle()?->getNom(),
            Avis::CIBLE_COACH => trim($coach?->getUtilisateur()?->getPrenom() . ' ' . $coach?->getUtilisateur()?->getNom()),
            default => $cours?->getTitre(),
        };

        return [
            'id' => $avis->getId(),
            'cible' => $avis->getCible(),
            'nomCible' => $nomCible,
            'auteur' => trim($sportif?->getPrenom() . ' ' . $sportif?->getNom()),
            'email' => $sportif?->getEmail(),
            'note' => $avis->getNote(),
            'commentaire' => $avis->getCommentaire(),
            'date' => $avis->getDateAvis()?->format('Y-m-d'),
        ];
    }
}
