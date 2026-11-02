import { HttpClient } from '@angular/common/http';
import { Injectable, effect, inject, signal } from '@angular/core';

import { API_URL } from '../config/api';
import { Favoris, TypeFavori } from '../models/favoris';
import { AuthService } from './auth.service';

@Injectable({ providedIn: 'root' })
export class FavorisStore {
  private readonly http = inject(HttpClient);
  private readonly auth = inject(AuthService);

  readonly favoris = signal<Favoris>({ salles: [], coachs: [], cours: [] });

  constructor() {
    effect(() => {
      if (this.auth.estSportif()) {
        this.charger();
      } else {
        this.favoris.set({ salles: [], coachs: [], cours: [] });
      }
    });
  }

  estFavori(type: TypeFavori, id: number): boolean {
    return this.liste(type).some((favori) => favori.id === id);
  }

  basculer(type: TypeFavori, id: number): void {
    const requete = this.estFavori(type, id)
      ? this.http.delete(`${API_URL}/sportif/favoris/${type}/${id}`)
      : this.http.put(`${API_URL}/sportif/favoris/${type}/${id}`, {});

    requete.subscribe({ next: () => this.charger() });
  }

  charger(): void {
    this.http.get<Favoris>(`${API_URL}/sportif/favoris`).subscribe({
      next: (favoris) => this.favoris.set(favoris)
    });
  }

  private liste(type: TypeFavori) {
    const favoris = this.favoris();

    return type === 'salle' ? favoris.salles : type === 'coach' ? favoris.coachs : favoris.cours;
  }
}
