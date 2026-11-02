import { LienEspace } from './espace';

export const liensGestionnaire = (actif: string): LienEspace[] => [
  { libelle: 'Tableau de bord', route: '/gestionnaire', actif: actif === 'tableau' },
  { libelle: 'Ma salle', route: '/gestionnaire/salle', actif: actif === 'salle' },
  { libelle: 'Réservations', route: '/gestionnaire/reservations', actif: actif === 'reservations' }
];
