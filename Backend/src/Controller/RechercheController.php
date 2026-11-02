<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Cours;
use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RechercheController extends AbstractController
{
    private const TYPES = ['salle', 'coach', 'cours'];
    private const LIMITE_PAR_DEFAUT = 6;
    private const LIMITE_MAXIMALE = 24;

    #[Route(path: '/api/recherche', name: 'api_recherche', methods: ['GET'])]
    public function rechercher(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $types = $this->types($request);
        $terme = $this->texte($request, 'q');
        $ville = $this->texte($request, 'ville');
        $activite = $this->entier($request, 'activite');
        $page = max(1, $this->entier($request, 'page') ?? 1);
        $limite = min(self::LIMITE_MAXIMALE, max(1, $this->entier($request, 'limite') ?? self::LIMITE_PAR_DEFAUT));

        $resultats = [];

        if (in_array('salle', $types, true)) {
            array_push($resultats, ...$this->salles($entityManager, $terme, $ville, $activite));
        }

        if (in_array('coach', $types, true)) {
            array_push($resultats, ...$this->coachs($entityManager, $terme, $ville, $activite));
        }

        if (in_array('cours', $types, true)) {
            array_push($resultats, ...$this->cours($entityManager, $terme, $ville, $activite));
        }

        usort($resultats, fn (array $a, array $b) => strcasecmp($a['nom'], $b['nom']));

        $total = count($resultats);

        return $this->json([
            'total' => $total,
            'page' => $page,
            'limite' => $limite,
            'pages' => max(1, (int) ceil($total / $limite)),
            'resultats' => array_slice($resultats, ($page - 1) * $limite, $limite),
        ]);
    }

    private function salles(EntityManagerInterface $entityManager, ?string $terme, ?string $ville, ?int $activite): array
    {
        $requete = $entityManager->createQueryBuilder()
            ->select('s')
            ->from(Salle::class, 's')
            ->orderBy('s.nom', 'ASC');

        if ($terme !== null) {
            $requete
                ->andWhere('s.nom LIKE :terme OR s.description LIKE :terme')
                ->setParameter('terme', '%' . $terme . '%');
        }

        if ($ville !== null) {
            $requete
                ->andWhere('s.ville LIKE :ville OR s.adresse LIKE :ville')
                ->setParameter('ville', '%' . $ville . '%');
        }

        if ($activite !== null) {
            $requete
                ->andWhere('EXISTS (SELECT c1.id FROM App\Entity\Cours c1 WHERE c1.salle = s AND c1.activite = :activite)')
                ->setParameter('activite', $activite);
        }

        $salles = $requete->getQuery()->getResult();
        $activitesParSalle = $this->activitesParSalle($entityManager, $salles);

        return array_map(
            fn (Salle $salle) => [
                'type' => 'salle',
                'id' => $salle->getId(),
                'nom' => $salle->getNom(),
                'ville' => $salle->getVille(),
                'activites' => $activitesParSalle[$salle->getId()] ?? [],
                'photo' => $salle->getPhoto(),
            ],
            $salles
        );
    }

    private function coachs(EntityManagerInterface $entityManager, ?string $terme, ?string $ville, ?int $activite): array
    {
        $requete = $entityManager->createQueryBuilder()
            ->select('c', 'u')
            ->from(Coach::class, 'c')
            ->join('c.utilisateur', 'u')
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC');

        if ($terme !== null) {
            $requete
                ->andWhere('u.nom LIKE :terme OR u.prenom LIKE :terme OR c.specialite LIKE :terme')
                ->setParameter('terme', '%' . $terme . '%');
        }

        if ($ville !== null) {
            $requete
                ->andWhere('EXISTS (SELECT se1.id FROM App\Entity\Seance se1 JOIN se1.cours c1 JOIN c1.salle s1 WHERE se1.coach = c AND (s1.ville LIKE :ville OR s1.adresse LIKE :ville))')
                ->setParameter('ville', '%' . $ville . '%');
        }

        if ($activite !== null) {
            $requete
                ->andWhere('EXISTS (SELECT se2.id FROM App\Entity\Seance se2 JOIN se2.cours c2 WHERE se2.coach = c AND c2.activite = :activite)')
                ->setParameter('activite', $activite);
        }

        return array_map(
            fn (Coach $coach) => [
                'type' => 'coach',
                'id' => $coach->getId(),
                'nom' => trim($coach->getUtilisateur()?->getPrenom() . ' ' . $coach->getUtilisateur()?->getNom()),
                'ville' => null,
                'activites' => $coach->getSpecialite() ? [$coach->getSpecialite()] : [],
                'photo' => $coach->getPhoto(),
            ],
            $requete->getQuery()->getResult()
        );
    }

    private function cours(EntityManagerInterface $entityManager, ?string $terme, ?string $ville, ?int $activite): array
    {
        $requete = $entityManager->createQueryBuilder()
            ->select('c', 's', 'a')
            ->from(Cours::class, 'c')
            ->join('c.salle', 's')
            ->join('c.activite', 'a')
            ->orderBy('c.titre', 'ASC');

        if ($terme !== null) {
            $requete
                ->andWhere('c.titre LIKE :terme OR c.description LIKE :terme')
                ->setParameter('terme', '%' . $terme . '%');
        }

        if ($ville !== null) {
            $requete
                ->andWhere('s.ville LIKE :ville OR s.adresse LIKE :ville')
                ->setParameter('ville', '%' . $ville . '%');
        }

        if ($activite !== null) {
            $requete
                ->andWhere('a.id = :activite')
                ->setParameter('activite', $activite);
        }

        return array_map(
            fn (Cours $cours) => [
                'type' => 'cours',
                'id' => $cours->getId(),
                'nom' => $cours->getTitre(),
                'ville' => $cours->getSalle()?->getVille(),
                'activites' => [$cours->getActivite()?->getNom()],
                'photo' => $cours->getSalle()?->getPhoto(),
                'salleId' => $cours->getSalle()?->getId(),
            ],
            $requete->getQuery()->getResult()
        );
    }

    private function activitesParSalle(EntityManagerInterface $entityManager, array $salles): array
    {
        if ($salles === []) {
            return [];
        }

        $lignes = $entityManager->createQuery(
            'SELECT DISTINCT IDENTITY(c.salle) AS salle, a.nom AS nom FROM App\Entity\Cours c JOIN c.activite a WHERE c.salle IN (:salles) ORDER BY a.nom ASC'
        )
            ->setParameter('salles', $salles)
            ->getResult();

        $activites = [];

        foreach ($lignes as $ligne) {
            $activites[(int) $ligne['salle']][] = $ligne['nom'];
        }

        return $activites;
    }

    private function types(Request $request): array
    {
        $demandes = explode(',', (string) $request->query->get('types', ''));
        $types = array_values(array_intersect($demandes, self::TYPES));

        return $types === [] ? self::TYPES : $types;
    }

    private function texte(Request $request, string $cle): ?string
    {
        $valeur = trim((string) $request->query->get($cle, ''));

        return $valeur === '' ? null : mb_substr($valeur, 0, 100);
    }

    private function entier(Request $request, string $cle): ?int
    {
        $valeur = $request->query->get($cle);

        return ($valeur !== null && ctype_digit((string) $valeur)) ? (int) $valeur : null;
    }
}
