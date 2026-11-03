import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import {
  CoachChoix,
  CoachSalle,
  CoursSalle,
  DonneesCours,
  DonneesSalle,
  DonneesSeanceSalle,
  ReservationSalle,
  SalleGestion,
  SeanceSalle
} from '../models/espace-gestionnaire';

@Injectable({ providedIn: 'root' })
export class GestionnaireService {
  private readonly http = inject(HttpClient);

  salle(): Observable<SalleGestion> {
    return this.http.get<SalleGestion>(`${API_URL}/gestionnaire/salle`);
  }

  creerSalle(donnees: DonneesSalle): Observable<SalleGestion> {
    return this.http.post<SalleGestion>(`${API_URL}/gestionnaire/salle`, donnees);
  }

  modifierSalle(donnees: DonneesSalle): Observable<SalleGestion> {
    return this.http.put<SalleGestion>(`${API_URL}/gestionnaire/salle`, donnees);
  }

  horaires(): Observable<{ id: number; jourSemaine: number; heureOuverture: string; heureFermeture: string }[]> {
    return this.http.get<{ id: number; jourSemaine: number; heureOuverture: string; heureFermeture: string }[]>(
      `${API_URL}/gestionnaire/horaires`
    );
  }

  creerHoraire(donnees: { jourSemaine: number; heureOuverture: string; heureFermeture: string }): Observable<unknown> {
    return this.http.post(`${API_URL}/gestionnaire/horaires`, donnees);
  }

  supprimerHoraire(id: number): Observable<unknown> {
    return this.http.delete(`${API_URL}/gestionnaire/horaires/${id}`);
  }

  cours(): Observable<CoursSalle[]> {
    return this.http.get<CoursSalle[]>(`${API_URL}/gestionnaire/cours`);
  }

  creerCours(donnees: DonneesCours): Observable<unknown> {
    return this.http.post(`${API_URL}/gestionnaire/cours`, donnees);
  }

  modifierCours(id: number, donnees: DonneesCours): Observable<unknown> {
    return this.http.put(`${API_URL}/gestionnaire/cours/${id}`, donnees);
  }

  modifierSeance(id: number, donnees: DonneesSeanceSalle): Observable<unknown> {
    return this.http.put(`${API_URL}/gestionnaire/seances/${id}`, donnees);
  }

  supprimerCours(id: number): Observable<unknown> {
    return this.http.delete(`${API_URL}/gestionnaire/cours/${id}`);
  }

  tousLesCoachs(): Observable<CoachChoix[]> {
    return this.http.get<CoachChoix[]>(`${API_URL}/coachs`);
  }

  creerSeance(donnees: DonneesSeanceSalle): Observable<unknown> {
    return this.http.post(`${API_URL}/gestionnaire/seances`, donnees);
  }

  supprimerSeance(id: number): Observable<unknown> {
    return this.http.delete(`${API_URL}/gestionnaire/seances/${id}`);
  }

  planning(): Observable<SeanceSalle[]> {
    return this.http.get<SeanceSalle[]>(`${API_URL}/gestionnaire/planning`);
  }

  reservations(): Observable<ReservationSalle[]> {
    return this.http.get<ReservationSalle[]>(`${API_URL}/gestionnaire/reservations`);
  }

  coachs(): Observable<CoachSalle[]> {
    return this.http.get<CoachSalle[]>(`${API_URL}/gestionnaire/coachs`);
  }
}
