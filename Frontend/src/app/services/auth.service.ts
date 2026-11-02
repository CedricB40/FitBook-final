import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import { DonneesInscription, ReponseInscription } from '../models/inscription';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);

  inscrire(donnees: DonneesInscription): Observable<ReponseInscription> {
    return this.http.post<ReponseInscription>(`${API_URL}/inscription`, donnees);
  }
}
