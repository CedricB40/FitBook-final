import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, tap } from 'rxjs';

import { API_URL } from '../config/api';
import { DonneesProfil, Profil, ReponseProfil } from '../models/profil';
import { AuthService } from './auth.service';

@Injectable({ providedIn: 'root' })
export class ProfilService {
  private readonly http = inject(HttpClient);
  private readonly auth = inject(AuthService);

  lire(): Observable<Profil> {
    return this.http.get<Profil>(`${API_URL}/profil`);
  }

  enregistrer(donnees: DonneesProfil): Observable<ReponseProfil> {
    return this.http.put<ReponseProfil>(`${API_URL}/profil`, donnees).pipe(
      tap((reponse) => {
        if (reponse.token) {
          this.auth.mettreAJourToken(reponse.token);
        }
      })
    );
  }
}
