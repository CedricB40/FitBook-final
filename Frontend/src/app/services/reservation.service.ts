import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import { Reservation } from '../models/reservation';

@Injectable({ providedIn: 'root' })
export class ReservationService {
  private readonly http = inject(HttpClient);

  lister(): Observable<Reservation[]> {
    return this.http.get<Reservation[]>(`${API_URL}/sportif/reservations`);
  }

  reserver(idSeance: number): Observable<Reservation> {
    return this.http.post<Reservation>(`${API_URL}/sportif/reservations`, { seance: idSeance });
  }

  annuler(idReservation: number): Observable<Reservation> {
    return this.http.delete<Reservation>(`${API_URL}/sportif/reservations/${idReservation}`);
  }
}
