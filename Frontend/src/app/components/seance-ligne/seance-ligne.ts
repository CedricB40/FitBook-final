import { DatePipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, input, linkedSignal, signal } from '@angular/core';
import { RouterLink } from '@angular/router';

import { Seance } from '../../models/consultation';
import { AuthService } from '../../services/auth.service';
import { ReservationService } from '../../services/reservation.service';
import { ReservationStore } from '../../services/reservation.store';

@Component({
  selector: 'app-seance-ligne',
  imports: [DatePipe, RouterLink],
  templateUrl: './seance-ligne.html',
  styleUrl: './seance-ligne.css'
})
export class SeanceLigne {
  readonly seance = input.required<Seance>();
  readonly masquer = input<'salle' | 'coach' | null>(null);

  protected readonly auth = inject(AuthService);
  protected readonly store = inject(ReservationStore);
  private readonly reservationService = inject(ReservationService);

  protected readonly places = linkedSignal(() => this.seance().placesRestantes);
  protected readonly enCours = signal(false);
  protected readonly erreur = signal<string | null>(null);

  protected reserver(): void {
    this.enCours.set(true);
    this.erreur.set(null);

    this.reservationService.reserver(this.seance().id).subscribe({
      next: () => {
        this.enCours.set(false);
        this.store.ajouter(this.seance().id);
        this.places.update((places) => Math.max(0, places - 1));
      },
      error: (erreur: HttpErrorResponse) => {
        this.enCours.set(false);
        this.erreur.set(
          erreur.status === 401
            ? 'Ta session a expiré. Reconnecte-toi pour réserver.'
            : (erreur.error?.erreur ?? 'La réservation a échoué. Réessaie plus tard.')
        );
      }
    });
  }
}
