<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Cours;
use App\Entity\Salle;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FavorisController extends AbstractController
{
    #[Route(path: '/api/sportif/favoris', name: 'api_sportif_favoris_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        return $this->json([
            'salles' => array_map(
                fn (Salle $salle) => $this->formaterSalle($entityManager, $salle),
                $utilisateur->getSallesFavorites()->toArray()
            ),
            'coachs' => array_map(
                fn (Coach $coach) => $this->formaterCoach($coach),
                $utilisateur->getCoachsFavoris()->toArray()
            ),
            'cours' => array_map(
                fn (Cours $cours) => $this->formaterCours($cours),
                $utilisateur->getCoursFavoris()->toArray()
            ),
        ]);
    }

    #[Route(path: '/api/sportif/favoris/{type}/{id}', name: 'api_sportif_favoris_ajouter', requirements: ['type' => 'salle|coach|cours', 'id' => '\d+'], methods: ['PUT'])]
    public function ajouter(string $type, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        $element = $entityManager->find($this->classe($type), $id);

        if (!$element) {
            return $this->json(['erreur' => 'Élément introuvable.'], Response::HTTP_NOT_FOUND);
        }

        match ($type) {
            'salle' => $utilisateur->ajouterSalleFavorite($element),
            'coach' => $utilisateur->ajouterCoachFavori($element),
            'cours' => $utilisateur->ajouterCoursFavori($element),
        };

        $entityManager->flush();

        return $this->json(['favori' => true]);
    }

    #[Route(path: '/api/sportif/favoris/{type}/{id}', name: 'api_sportif_favoris_retirer', requirements: ['type' => 'salle|coach|cours', 'id' => '\d+'], methods: ['DELETE'])]
    public function retirer(string $type, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        $element = $entityManager->find($this->classe($type), $id);

        if (!$element) {
            return $this->json(['erreur' => 'Élément introuvable.'], Response::HTTP_NOT_FOUND);
        }

        match ($type) {
            'salle' => $utilisateur->retirerSalleFavorite($element),
            'coach' => $utilisateur->retirerCoachFavori($element),
            'cours' => $utilisateur->retirerCoursFavori($element),
        };

        $entityManager->flush();

        return $this->json(['favori' => false]);
    }

    private function classe(string $type): string
    {
        return match ($type) {
            'salle' => Salle::class,
            'coach' => Coach::class,
            'cours' => Cours::class,
        };
    }

    private function formaterSalle(EntityManagerInterface $entityManager, Salle $salle): array
    {
        $activites = [];

        foreach ($entityManager->getRepository(Cours::class)->findBy(['salle' => $salle]) as $cours) {
            $activites[$cours->getActivite()?->getNom()] = true;
        }

        $noms = array_keys($activites);
        sort($noms);

        return [
            'type' => 'salle',
            'id' => $salle->getId(),
            'nom' => $salle->getNom(),
            'ville' => $salle->getVille(),
            'activites' => $noms,
            'photo' => $salle->getPhoto(),
        ];
    }

    private function formaterCoach(Coach $coach): array
    {
        return [
            'type' => 'coach',
            'id' => $coach->getId(),
            'nom' => trim($coach->getUtilisateur()?->getPrenom() . ' ' . $coach->getUtilisateur()?->getNom()),
            'ville' => null,
            'activites' => $coach->getSpecialite() ? [$coach->getSpecialite()] : [],
            'photo' => $coach->getPhoto(),
        ];
    }

    private function formaterCours(Cours $cours): array
    {
        return [
            'type' => 'cours',
            'id' => $cours->getId(),
            'nom' => $cours->getTitre(),
            'ville' => $cours->getSalle()?->getVille(),
            'activites' => [$cours->getActivite()?->getNom()],
            'photo' => $cours->getSalle()?->getPhoto(),
            'salleId' => $cours->getSalle()?->getId(),
        ];
    }
}
