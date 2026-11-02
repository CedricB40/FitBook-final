export type TypeResultat = 'salle' | 'coach' | 'cours';

export interface ResultatRecherche {
  type: TypeResultat;
  id: number;
  nom: string;
  ville: string | null;
  activites: string[];
  photo: string | null;
  salleId?: number;
}

export interface ReponseRecherche {
  total: number;
  page: number;
  limite: number;
  pages: number;
  resultats: ResultatRecherche[];
}

export interface FiltresRecherche {
  types?: TypeResultat[];
  q?: string;
  ville?: string;
  activite?: number | null;
  page?: number;
  limite?: number;
}
