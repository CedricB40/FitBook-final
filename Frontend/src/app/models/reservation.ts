export interface Reservation {
  id: number;
  statut: string;
  dateReservation: string;
  seance: {
    id: number;
    debut: string;
    fin: string;
    cours: { id: number; titre: string; activite: string };
    salle: { id: number; nom: string; ville: string };
    coach: { id: number; nom: string; prenom: string };
  };
}
