<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Cours;
use App\Entity\Reservation;
use App\Entity\Salle;
use App\Entity\Seance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConsultationController extends AbstractController
{
    #[Route(path: '/api/salles', name: 'api_salles_liste', methods: ['GET'])]
    public function salles(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $terme = $this->terme($request);

        $requete = $entityManager->createQueryBuilder()
            ->select('s')
            ->from(Salle::class, 's')
            ->orderBy('s.nom', 'ASC');

        if ($terme !== null) {
            $requete
                ->andWhere('s.nom LIKE :terme OR s.ville LIKE :terme OR s.adresse LIKE :terme')
                ->setParameter('terme', '%' . $terme . '%');
        }

        return $this->json(array_map(
            fn (Salle $salle) => $this->formaterSalle($salle),
            $requete->getQuery()->getResult()
        ));
    }

    #[Route(path: '/api/salles/{id}', name: 'api_salles_fiche', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function salle(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $salle = $entityManager->getRepository(Salle::class)->find($id);

        if (!$salle) {
            return $this->json(['erreur' => 'Salle introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $cours = $entityManager->getRepository(Cours::class)->findBy(['salle' => $salle], ['titre' => 'ASC']);

        return $this->json($this->formaterSalle($salle) + [
            'cours' => array_map(fn (Cours $unCours) => $this->formaterCours($unCours), $cours),
        ]);
    }

    #[Route(path: '/api/coachs', name: 'api_coachs_liste', methods: ['GET'])]
    public function coachs(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $terme = $this->terme($request);

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

        return $this->json(array_map(
            fn (Coach $coach) => $this->formaterCoach($coach),
            $requete->getQuery()->getResult()
        ));
    }

    #[Route(path: '/api/coachs/{id}', name: 'api_coachs_fiche', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function coach(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);

        if (!$coach) {
            return $this->json(['erreur' => 'Coach introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $seances = $this->requeteSeances($entityManager)
            ->andWhere('co.id = :coach')
            ->setParameter('coach', $coach->getId())
            ->getQuery()
            ->getResult();

        $reservees = $this->compterReservations($entityManager, $seances);

        return $this->json($this->formaterCoach($coach) + [
            'seances' => array_map(fn (Seance $seance) => $this->formaterSeance($seance, $reservees), $seances),
        ]);
    }

    #[Route(path: '/api/cours', name: 'api_cours_liste', methods: ['GET'])]
    public function cours(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $terme = $this->terme($request);
        $activite = $this->entier($request, 'activite');

        $requete = $entityManager->createQueryBuilder()
            ->select('c', 's', 'a')
            ->from(Cours::class, 'c')
            ->join('c.salle', 's')
            ->join('c.activite', 'a')
            ->orderBy('c.titre', 'ASC');

        if ($terme !== null) {
            $requete
                ->andWhere('c.titre LIKE :terme OR c.description LIKE :terme OR s.nom LIKE :terme OR s.ville LIKE :terme')
                ->setParameter('terme', '%' . $terme . '%');
        }

        if ($activite !== null) {
            $requete
                ->andWhere('a.id = :activite')
                ->setParameter('activite', $activite);
        }

        return $this->json(array_map(
            fn (Cours $unCours) => $this->formaterCours($unCours),
            $requete->getQuery()->getResult()
        ));
    }

    #[Route(path: '/api/seances', name: 'api_seances_liste', methods: ['GET'])]
    public function seances(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $terme = $this->terme($request);
        $activite = $this->entier($request, 'activite');
        $salle = $this->entier($request, 'salle');
        $coach = $this->entier($request, 'coach');

        $requete = $this->requeteSeances($entityManager);

        if ($terme !== null) {
            $requete
                ->andWhere('c.titre LIKE :terme OR s.nom LIKE :terme OR s.ville LIKE :terme OR u.nom LIKE :terme OR u.prenom LIKE :terme')
                ->setParameter('terme', '%' . $terme . '%');
        }

        if ($activite !== null) {
            $requete->andWhere('a.id = :activite')->setParameter('activite', $activite);
        }

        if ($salle !== null) {
            $requete->andWhere('s.id = :salle')->setParameter('salle', $salle);
        }

        if ($coach !== null) {
            $requete->andWhere('co.id = :coach')->setParameter('coach', $coach);
        }

        $resultat = $requete->getQuery()->getResult();
        $reservees = $this->compterReservations($entityManager, $resultat);

        return $this->json(array_map(
            fn (Seance $seance) => $this->formaterSeance($seance, $reservees),
            $resultat
        ));
    }

    private function requeteSeances(EntityManagerInterface $entityManager): \Doctrine\ORM\QueryBuilder
    {
        return $entityManager->createQueryBuilder()
            ->select('se', 'c', 's', 'a', 'co', 'u')
            ->from(Seance::class, 'se')
            ->join('se.cours', 'c')
            ->join('c.salle', 's')
            ->join('c.activite', 'a')
            ->join('se.coach', 'co')
            ->join('co.utilisateur', 'u')
            ->andWhere('se.dateHeureDebut >= :maintenant')
            ->setParameter('maintenant', new \DateTimeImmutable())
            ->orderBy('se.dateHeureDebut', 'ASC');
    }

    private function terme(Request $request): ?string
    {
        $terme = trim((string) $request->query->get('q', ''));

        return $terme === '' ? null : mb_substr($terme, 0, 100);
    }

    private function entier(Request $request, string $cle): ?int
    {
        $valeur = $request->query->get($cle);

        return ($valeur !== null && ctype_digit((string) $valeur)) ? (int) $valeur : null;
    }

    private function formaterSalle(Salle $salle): array
    {
        return [
            'id' => $salle->getId(),
            'nom' => $salle->getNom(),
            'adresse' => $salle->getAdresse(),
            'ville' => $salle->getVille(),
            'description' => $salle->getDescription(),
            'telephone' => $salle->getTelephone(),
            'photo' => $salle->getPhoto(),
        ];
    }

    private function formaterCoach(Coach $coach): array
    {
        return [
            'id' => $coach->getId(),
            'nom' => $coach->getUtilisateur()?->getNom(),
            'prenom' => $coach->getUtilisateur()?->getPrenom(),
            'specialite' => $coach->getSpecialite(),
            'biographie' => $coach->getBiographie(),
            'photo' => $coach->getPhoto(),
        ];
    }

    private function formaterCours(Cours $cours): array
    {
        return [
            'id' => $cours->getId(),
            'titre' => $cours->getTitre(),
            'description' => $cours->getDescription(),
            'duree' => $cours->getDuree(),
            'capacite' => $cours->getCapacite(),
            'salle' => [
                'id' => $cours->getSalle()?->getId(),
                'nom' => $cours->getSalle()?->getNom(),
                'ville' => $cours->getSalle()?->getVille(),
            ],
            'activite' => [
                'id' => $cours->getActivite()?->getId(),
                'nom' => $cours->getActivite()?->getNom(),
            ],
        ];
    }

    private function compterReservations(EntityManagerInterface $entityManager, array $seances): array
    {
        if ($seances === []) {
            return [];
        }

        $lignes = $entityManager->createQuery(
            'SELECT IDENTITY(r.seance) AS id, COUNT(r.id) AS total FROM App\Entity\Reservation r WHERE r.seance IN (:seances) AND r.statut = :statut GROUP BY r.seance'
        )
            ->setParameter('seances', $seances)
            ->setParameter('statut', Reservation::STATUT_CONFIRMEE)
            ->getResult();

        $comptes = [];

        foreach ($lignes as $ligne) {
            $comptes[(int) $ligne['id']] = (int) $ligne['total'];
        }

        return $comptes;
    }

    private function formaterSeance(Seance $seance, array $reservees = []): array
    {
        $cours = $seance->getCours();
        $coach = $seance->getCoach();

        return [
            'id' => $seance->getId(),
            'debut' => $seance->getDateHeureDebut()?->format('Y-m-d\TH:i:s'),
            'fin' => $seance->getDateHeureFin()?->format('Y-m-d\TH:i:s'),
            'placesRestantes' => max(0, ($cours?->getCapacite() ?? 0) - ($reservees[$seance->getId()] ?? 0)),
            'cours' => [
                'id' => $cours?->getId(),
                'titre' => $cours?->getTitre(),
                'capacite' => $cours?->getCapacite(),
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
        ];
    }
}
