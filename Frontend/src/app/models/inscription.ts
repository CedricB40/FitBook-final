export interface DonneesInscription {
  nom: string;
  prenom: string;
  email: string;
  motDePasse: string;
  role: string;
}

export interface ReponseInscription {
  id: number;
  email: string;
  role: string;
  emailConfirme: boolean;
}
