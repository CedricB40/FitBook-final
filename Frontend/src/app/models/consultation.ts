export interface Salle {
  id: number;
  nom: string;
  adresse: string;
  ville: string;
  description: string | null;
  telephone: string | null;
  photo: string | null;
}

export interface Cours {
  id: number;
  titre: string;
  description: string | null;
  duree: number;
  capacite: number;
  salle: { id: number; nom: string; ville: string };
  activite: { id: number; nom: string };
}

export interface Seance {
  id: number;
  debut: string;
  fin: string;
  cours: { id: number; titre: string; capacite: number; activite: string };
  salle: { id: number; nom: string; ville: string };
  coach: { id: number; nom: string; prenom: string };
}

export interface Coach {
  id: number;
  nom: string;
  prenom: string;
  specialite: string;
  biographie: string | null;
  photo: string | null;
}

export interface SalleDetail extends Salle {
  cours: Cours[];
}

export interface CoachDetail extends Coach {
  seances: Seance[];
}

export interface FiltresSeances {
  q?: string;
  activite?: number | null;
}
