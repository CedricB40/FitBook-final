export interface SeanceSalle {
  id: number;
  debut: string;
  fin: string;
  cours: { id: number; titre: string; capacite: number; activite: string };
  coachId: number;
  coach: string;
  reservees: number;
  placesRestantes: number;
}

export interface ReservationSalle {
  id: number;
  statut: string;
  dateReservation: string;
  sportif: string;
  seance: { id: number; debut: string; cours: string; coach: string };
}

export interface CoachSalle {
  id: number;
  nom: string;
  specialite: string;
  photo: string | null;
  seances: number;
}

export interface SalleGestion {
  id: number;
  nom: string;
  adresse: string;
  ville: string;
  description: string | null;
  telephone: string | null;
  photo: string | null;
}

export interface DonneesSalle {
  nom: string;
  adresse: string;
  ville: string;
  description: string | null;
  telephone: string | null;
  photo: string | null;
}

export interface CoursSalle {
  id: number;
  titre: string;
  description: string | null;
  duree: number;
  capacite: number;
  activiteId: number;
  activite: string;
}

export interface CoachChoix {
  id: number;
  nom: string;
  prenom: string;
  specialite: string;
}

export interface DonneesCours {
  titre: string;
  description: string | null;
  duree: number;
  capacite: number;
  activiteId: number;
}

export interface DonneesSeanceSalle {
  coursId: number;
  coachId: number;
  dateHeureDebut: string;
  dateHeureFin: string;
}
