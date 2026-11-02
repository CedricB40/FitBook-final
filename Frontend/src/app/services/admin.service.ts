import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import {
  ActiviteAdmin,
  AvisAdmin,
  DonneesActivite,
  DonneesUtilisateurAdmin,
  ReservationAdmin,
  StatistiquesAdmin,
  UtilisateurAdmin
} from '../models/espace-admin';

@Injectable({ providedIn: 'root' })
export class AdminService {
  private readonly http = inject(HttpClient);

  statistiques(): Observable<StatistiquesAdmin> {
    return this.http.get<StatistiquesAdmin>(`${API_URL}/admin/statistiques`);
  }

  utilisateurs(): Observable<UtilisateurAdmin[]> {
    return this.http.get<UtilisateurAdmin[]>(`${API_URL}/admin/utilisateurs`);
  }

  modifierUtilisateur(id: number, donnees: DonneesUtilisateurAdmin): Observable<UtilisateurAdmin> {
    return this.http.put<UtilisateurAdmin>(`${API_URL}/admin/utilisateurs/${id}`, donnees);
  }

  supprimerUtilisateur(id: number): Observable<unknown> {
    return this.http.delete(`${API_URL}/admin/utilisateurs/${id}`);
  }

  avis(): Observable<AvisAdmin[]> {
    return this.http.get<AvisAdmin[]>(`${API_URL}/admin/avis`);
  }

  supprimerAvis(id: number): Observable<unknown> {
    return this.http.delete(`${API_URL}/admin/avis/${id}`);
  }

  activites(): Observable<ActiviteAdmin[]> {
    return this.http.get<ActiviteAdmin[]>(`${API_URL}/admin/activites`);
  }

  creerActivite(donnees: DonneesActivite): Observable<ActiviteAdmin> {
    return this.http.post<ActiviteAdmin>(`${API_URL}/admin/activites`, donnees);
  }

  modifierActivite(id: number, donnees: DonneesActivite): Observable<ActiviteAdmin> {
    return this.http.put<ActiviteAdmin>(`${API_URL}/admin/activites/${id}`, donnees);
  }

  supprimerActivite(id: number): Observable<unknown> {
    return this.http.delete(`${API_URL}/admin/activites/${id}`);
  }

  reservations(): Observable<ReservationAdmin[]> {
    return this.http.get<ReservationAdmin[]>(`${API_URL}/admin/reservations`);
  }

  modifierReservation(id: number, statut: string): Observable<unknown> {
    return this.http.put(`${API_URL}/admin/reservations/${id}`, { statut });
  }
}
