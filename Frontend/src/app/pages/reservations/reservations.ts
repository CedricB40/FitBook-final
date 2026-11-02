import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';

import { Reservation } from '../../models/reservation';
import { ReservationService } from '../../services/reservation.service';

@Component({
  selector: 'app-reservations',
  imports: [DatePipe, RouterLink],
  templateUrl: './reservations.html'
})
export class Reservations implements OnInit {
  private readonly reservationService = inject(ReservationService);

  protected readonly reservations = signal<Reservation[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);
  protected readonly enCours = signal<number | null>(null);
  protected readonly message = signal<{ texte: string; erreur: boolean } | null>(null);

  protected readonly aVenir = computed(() =>
    this.reservations()
      .filter((r) => r.statut === 'CONFIRMEE' && new Date(r.seance.debut) > new Date())
      .sort((a, b) => a.seance.debut.localeCompare(b.seance.debut))
  );

  protected readonly historique = computed(() =>
    this.reservations().filter((r) => !this.aVenir().includes(r))
  );

  ngOnInit(): void {
    this.reservationService.lister().subscribe({
      next: (reservations) => {
        this.reservations.set(reservations);
        this.chargement.set(false);
      },
      error: () => {
        this.erreur.set(true);
        this.chargement.set(false);
      }
    });
  }

  protected annuler(reservation: Reservation): void {
    this.enCours.set(reservation.id);
    this.message.set(null);

    this.reservationService.annuler(reservation.id).subscribe({
      next: (misAJour) => {
        this.enCours.set(null);
        this.reservations.update((liste) => liste.map((r) => (r.id === misAJour.id ? misAJour : r)));
        this.message.set({ texte: 'Ta réservation a bien été annulée.', erreur: false });
      },
      error: (erreur: HttpErrorResponse) => {
        this.enCours.set(null);
        this.message.set({
          texte: erreur.error?.erreur ?? "L'annulation a échoué. Réessaie plus tard.",
          erreur: true
        });
      }
    });
  }

  protected libelleStatut(statut: string): string {
    return statut === 'ANNULEE' ? 'Annulée' : 'Confirmée';
  }
}
