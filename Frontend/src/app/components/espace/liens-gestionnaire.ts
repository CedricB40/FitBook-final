import { LienEspace } from './espace';

export const liensGestionnaire = (actif: string): LienEspace[] => [
  { libelle: 'Tableau de bord', route: '/gestionnaire', actif: actif === 'tableau' }
];
