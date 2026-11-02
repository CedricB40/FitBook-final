import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import {
  CoachSalle,
  DonneesSalle,
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
