export type CibleAvis = 'salle' | 'coach' | 'cours';

export interface Avis {
  id: number;
  auteur: string;
  note: number;
  commentaire: string | null;
  date: string;
  mien: boolean;
}

export interface ResumeAvis {
  moyenne: number | null;
  total: number;
  repartition: Record<string, number>;
  avis: Avis[];
}

export interface DonneesAvis {
  cible: CibleAvis;
  id: number;
  note: number;
  commentaire?: string;
}
