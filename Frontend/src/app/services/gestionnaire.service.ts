import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import { CoachSalle, ReservationSalle, SeanceSalle } from '../models/espace-gestionnaire';

@Injectable({ providedIn: 'root' })
export class GestionnaireService {
  private readonly http = inject(HttpClient);

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
