import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { API_URL } from '../config/api';
import { Activite } from '../models/activite';

@Injectable({ providedIn: 'root' })
export class ActiviteService {
  private readonly http = inject(HttpClient);

  lister(): Observable<Activite[]> {
    return this.http.get<Activite[]>(`${API_URL}/activites`);
  }
}
