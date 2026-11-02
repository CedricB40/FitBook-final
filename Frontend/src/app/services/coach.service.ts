import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import {
  CoursChoix,
  DonneesSeance,
  ProfilCoach,
  ReservationCoach,
  SeancePlanning
} from '../models/espace-coach';
import { StatistiquesCoach } from '../models/statistiques';

@Injectable({ providedIn: 'root' })
export class CoachService {
  private readonly http = inject(HttpClient);

  statistiques(): Observable<StatistiquesCoach> {
    return this.http.get<StatistiquesCoach>(`${API_URL}/coach/statistiques`);
  }

  profil(): Observable<ProfilCoach> {
    return this.http.get<ProfilCoach>(`${API_URL}/coach/profil`);
  }

  planning(): Observable<SeancePlanning[]> {
    return this.http.get<SeancePlanning[]>(`${API_URL}/coach/planning`);
  }

  cours(): Observable<CoursChoix[]> {
    return this.http.get<CoursChoix[]>(`${API_URL}/coach/cours`);
  }

  reservations(): Observable<ReservationCoach[]> {
    return this.http.get<ReservationCoach[]>(`${API_URL}/coach/reservations`);
  }

  creerSeance(donnees: DonneesSeance): Observable<unknown> {
    return this.http.post(`${API_URL}/coach/seances`, donnees);
  }

  modifierSeance(id: number, donnees: DonneesSeance): Observable<unknown> {
    return this.http.put(`${API_URL}/coach/seances/${id}`, donnees);
  }

  supprimerSeance(id: number): Observable<unknown> {
    return this.http.delete(`${API_URL}/coach/seances/${id}`);
  }
}
