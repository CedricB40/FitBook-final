import { LienEspace } from './espace';

export const liensCoach = (actif: string): LienEspace[] => [
  { libelle: 'Tableau de bord', route: '/tableau-de-bord', actif: actif === 'tableau' },
  { libelle: 'Mes séances', route: '/coach/seances', actif: actif === 'seances' },
  { libelle: 'Disponibilités', route: '/coach/disponibilites', actif: actif === 'disponibilites' },
  { libelle: 'Mes sportifs', route: '/coach/sportifs', actif: actif === 'sportifs' },
  { libelle: 'Avis reçus', route: '/coach/avis', actif: actif === 'avis' },
  { libelle: 'Mon profil', route: '/coach/profil', actif: actif === 'profil' }
];
