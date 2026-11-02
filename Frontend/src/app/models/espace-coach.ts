export interface SeancePlanning {
  id: number;
  debut: string;
  fin: string;
  cours: { id: number; titre: string; capacite: number; activite: string };
  salle: { id: number; nom: string; ville: string };
  reservees: number;
  placesRestantes: number;
}

export interface CoursChoix {
  id: number;
  titre: string;
  duree: number;
  capacite: number;
  salle: { id: number; nom: string; ville: string };
  activite: string;
}

export interface ReservationCoach {
  id: number;
  dateReservation: string;
  sportif: { nom: string; prenom: string; email: string };
  seance: { id: number; debut: string; fin: string; cours: string; salle: string };
}

export interface ProfilCoach {
  id: number;
  specialite: string;
  biographie: string | null;
  photo: string | null;
}

export interface DonneesSeance {
  coursId: number;
  dateHeureDebut: string;
  dateHeureFin: string;
}

export interface DonneesProfilCoach {
  specialite: string;
  biographie: string | null;
  photo: string | null;
}
