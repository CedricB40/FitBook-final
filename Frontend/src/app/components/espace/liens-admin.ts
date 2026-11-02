import { LienEspace } from './espace';

export const liensAdmin = (actif: string): LienEspace[] => [
  { libelle: 'Tableau de bord', route: '/admin', actif: actif === 'tableau' },
  { libelle: 'Utilisateurs', route: '/admin/utilisateurs', actif: actif === 'utilisateurs' }
];
