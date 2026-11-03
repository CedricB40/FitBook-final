import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import { Avis, CibleAvis, DonneesAvis, ResumeAvis } from '../models/avis';

@Injectable({ providedIn: 'root' })
export class AvisService {
  private readonly http = inject(HttpClient);

  lister(cible: CibleAvis, id: number): Observable<ResumeAvis> {
    return this.http.get<ResumeAvis>(`${API_URL}/avis/${cible}/${id}`);
  }

  publier(donnees: DonneesAvis): Observable<Avis> {
    return this.http.post<Avis>(`${API_URL}/sportif/avis`, donnees);
  }

  supprimer(id: number): Observable<void> {
    return this.http.delete<void>(`${API_URL}/sportif/avis/${id}`);
  }
}
