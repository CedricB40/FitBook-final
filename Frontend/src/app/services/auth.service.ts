import { HttpClient } from '@angular/common/http';
import { Injectable, computed, inject, signal } from '@angular/core';
import { Observable, tap } from 'rxjs';

import { API_URL } from '../config/api';
import { IdentifiantsConnexion, ProfilJwt, ReponseConnexion } from '../models/connexion';
import { DonneesInscription, ReponseInscription } from '../models/inscription';

const CLE_TOKEN = 'fitbook_token';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);

  private readonly token = signal<string | null>(this.lireToken());

  readonly utilisateur = computed(() => this.decoder(this.token()));
  readonly estConnecte = computed(() => this.utilisateur() !== null);
  readonly estSportif = computed(() => this.utilisateur()?.roles?.includes('ROLE_SPORTIF') ?? false);

  jeton(): string | null {
    return this.token();
  }

  inscrire(donnees: DonneesInscription): Observable<ReponseInscription> {
    return this.http.post<ReponseInscription>(`${API_URL}/inscription`, donnees);
  }

  connecter(identifiants: IdentifiantsConnexion): Observable<ReponseConnexion> {
    return this.http
      .post<ReponseConnexion>(`${API_URL}/login_check`, identifiants)
      .pipe(tap((reponse) => this.enregistrerToken(reponse.token)));
  }

  deconnecter(): void {
    this.enregistrerToken(null);
  }

  private enregistrerToken(token: string | null): void {
    try {
      if (token) {
        localStorage.setItem(CLE_TOKEN, token);
      } else {
        localStorage.removeItem(CLE_TOKEN);
      }
    } catch {
      // stockage indisponible : le token reste seulement en mémoire
    }
    this.token.set(token);
  }

  private lireToken(): string | null {
    try {
      return localStorage.getItem(CLE_TOKEN);
    } catch {
      return null;
    }
  }

  private decoder(token: string | null): ProfilJwt | null {
    if (!token) {
      return null;
    }

    try {
      const charge = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
      const profil = JSON.parse(atob(charge)) as ProfilJwt;

      if (profil.exp && profil.exp * 1000 < Date.now()) {
        return null;
      }

      return profil;
    } catch {
      return null;
    }
  }
}
