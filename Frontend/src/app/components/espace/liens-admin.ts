import { LienEspace } from './espace';

export const liensAdmin = (actif: string): LienEspace[] => [
  { libelle: 'Tableau de bord', route: '/admin', actif: actif === 'tableau' },
  { libelle: 'Utilisateurs', route: '/admin/utilisateurs', actif: actif === 'utilisateurs' },
  { libelle: 'Avis', route: '/admin/avis', actif: actif === 'avis' },
  { libelle: 'Catalogue', route: '/admin/catalogue', actif: actif === 'catalogue' },
  { libelle: 'Réservations', route: '/admin/reservations', actif: actif === 'reservations' }
];
