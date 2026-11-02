import { Injectable, effect, inject, signal } from '@angular/core';

import { AuthService } from './auth.service';
import { ReservationService } from './reservation.service';

@Injectable({ providedIn: 'root' })
export class ReservationStore {
  private readonly auth = inject(AuthService);
  private readonly reservationService = inject(ReservationService);

  readonly reservees = signal<Set<number>>(new Set());

  constructor() {
    effect(() => {
      if (this.auth.estSportif()) {
        this.charger();
      } else {
        this.reservees.set(new Set());
      }
    });
  }

  ajouter(idSeance: number): void {
    this.reservees.update((ids) => new Set(ids).add(idSeance));
  }

  private charger(): void {
    this.reservationService.lister().subscribe({
      next: (reservations) =>
        this.reservees.set(
          new Set(reservations.filter((r) => r.statut === 'CONFIRMEE').map((r) => r.seance.id))
        )
    });
  }
}
