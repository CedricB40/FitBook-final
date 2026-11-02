import { LienEspace } from './espace';

export const liensGestionnaire = (actif: string): LienEspace[] => [
  { libelle: 'Tableau de bord', route: '/gestionnaire', actif: actif === 'tableau' },
  { libelle: 'Ma salle', route: '/gestionnaire/salle', actif: actif === 'salle' },
  { libelle: 'Coachs de ma salle', route: '/gestionnaire/coachs', actif: actif === 'coachs' },
  { libelle: 'Réservations', route: '/gestionnaire/reservations', actif: actif === 'reservations' },
  { libelle: 'Avis reçus', route: '/gestionnaire/avis', actif: actif === 'avis' },
  { libelle: 'Mon profil', route: '/gestionnaire/profil', actif: actif === 'profil' }
];
