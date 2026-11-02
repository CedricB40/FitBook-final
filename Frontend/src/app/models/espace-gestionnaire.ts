export interface SeanceSalle {
  id: number;
  debut: string;
  fin: string;
  cours: { id: number; titre: string; capacite: number; activite: string };
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
