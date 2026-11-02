import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import { FiltresRecherche, ReponseRecherche } from '../models/recherche';
import {
  Coach,
  CoachDetail,
  FiltresSeances,
  Salle,
  SalleDetail,
  Seance
} from '../models/consultation';

@Injectable({ providedIn: 'root' })
export class ConsultationService {
  private readonly http = inject(HttpClient);

  salles(terme: string): Observable<Salle[]> {
    return this.http.get<Salle[]>(`${API_URL}/salles`, { params: this.params({ q: terme }) });
  }

  salle(id: number): Observable<SalleDetail> {
    return this.http.get<SalleDetail>(`${API_URL}/salles/${id}`);
  }

  coachs(terme: string): Observable<Coach[]> {
    return this.http.get<Coach[]>(`${API_URL}/coachs`, { params: this.params({ q: terme }) });
  }

  coach(id: number): Observable<CoachDetail> {
    return this.http.get<CoachDetail>(`${API_URL}/coachs/${id}`);
  }

  seances(filtres: FiltresSeances): Observable<Seance[]> {
    return this.http.get<Seance[]>(`${API_URL}/seances`, {
      params: this.params({ q: filtres.q, activite: filtres.activite })
    });
  }

  rechercher(filtres: FiltresRecherche): Observable<ReponseRecherche> {
    return this.http.get<ReponseRecherche>(`${API_URL}/recherche`, {
      params: this.params({
        types: filtres.types?.join(','),
        q: filtres.q,
        ville: filtres.ville,
        activite: filtres.activite,
        page: filtres.page,
        limite: filtres.limite
      })
    });
  }

  private params(valeurs: Record<string, string | number | null | undefined>): HttpParams {
    let params = new HttpParams();

    for (const [cle, valeur] of Object.entries(valeurs)) {
      if (valeur !== null && valeur !== undefined && valeur !== '') {
        params = params.set(cle, String(valeur));
      }
    }

    return params;
  }
}
