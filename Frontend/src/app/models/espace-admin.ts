export interface StatistiquesAdmin {
  utilisateurs: number;
  avis: number;
  reservations: number;
}

export interface UtilisateurAdmin {
  id: number;
  nom: string;
  prenom: string;
  email: string;
  role: string;
  dateInscription: string | null;
}

export interface DonneesUtilisateurAdmin {
  nom: string;
  prenom: string;
  email: string;
  role: string;
}

export interface AvisAdmin {
  id: number;
  cible: string;
  nomCible: string;
  auteur: string;
  email: string;
  note: number;
  commentaire: string | null;
  date: string | null;
}

export const ROLES_ADMIN: { valeur: string; libelle: string }[] = [
  { valeur: 'ROLE_SPORTIF', libelle: 'Sportif' },
  { valeur: 'ROLE_COACH', libelle: 'Coach' },
  { valeur: 'ROLE_GESTIONNAIRE', libelle: 'Gestionnaire' },
  { valeur: 'ROLE_ADMIN', libelle: 'Administrateur' }
];

export const libelleRole = (role: string): string =>
  ROLES_ADMIN.find((unRole) => unRole.valeur === role)?.libelle ?? role;
