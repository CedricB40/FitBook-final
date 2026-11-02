import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import { StatistiquesCoach } from '../models/statistiques';

@Injectable({ providedIn: 'root' })
export class CoachService {
  private readonly http = inject(HttpClient);

  statistiques(): Observable<StatistiquesCoach> {
    return this.http.get<StatistiquesCoach>(`${API_URL}/coach/statistiques`);
  }
}
