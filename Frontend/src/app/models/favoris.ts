import { ResultatRecherche, TypeResultat } from './recherche';

export interface Favoris {
  salles: ResultatRecherche[];
  coachs: ResultatRecherche[];
  cours: ResultatRecherche[];
}

export type TypeFavori = TypeResultat;
