export interface Creneau {
  id: number;
  jourSemaine: number;
  debut: string;
  fin: string;
}

export interface NouveauCreneau {
  jourSemaine: number;
  debut: string;
  fin: string;
}

export const JOURS_SEMAINE: { numero: number; libelle: string }[] = [
  { numero: 1, libelle: 'Lundi' },
  { numero: 2, libelle: 'Mardi' },
  { numero: 3, libelle: 'Mercredi' },
  { numero: 4, libelle: 'Jeudi' },
  { numero: 5, libelle: 'Vendredi' },
  { numero: 6, libelle: 'Samedi' },
  { numero: 7, libelle: 'Dimanche' }
];
