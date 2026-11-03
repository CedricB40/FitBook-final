export interface Profil {
  nom: string;
  prenom: string;
  email: string;
  role?: string;
}

export interface DonneesProfil {
  nom: string;
  prenom: string;
  email: string;
}

export interface ReponseProfil {
  profil: Profil;
  token: string | null;
}
