import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensAdmin } from '../../components/espace/liens-admin';
import { ReservationAdmin } from '../../models/espace-admin';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-reservations',
  imports: [DatePipe, Espace],
  templateUrl: './admin-reservations.html',
  styleUrl: './admin-reservations.css'
})
export class AdminReservations implements OnInit {
  private readonly adminService = inject(AdminService);

  protected readonly liens = liensAdmin('reservations');

  protected readonly reservations = signal<ReservationAdmin[]>([]);
  protected readonly chargement = signal(true);
  protected readonly aModifier = signal<number | null>(null);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);

  ngOnInit(): void {
    this.charger();
  }

  protected demander(id: number): void {
    this.aModifier.set(id);
  }

  protected annuler(): void {
    this.aModifier.set(null);
  }

  protected changerStatut(reservation: ReservationAdmin): void {
    const nouveau = reservation.statut === 'CONFIRMEE' ? 'ANNULEE' : 'CONFIRMEE';

    this.erreur.set(null);
    this.succes.set(null);

    this.adminService.modifierReservation(reservation.id, nouveau).subscribe({
      next: () => {
        this.aModifier.set(null);
        this.succes.set(nouveau === 'ANNULEE' ? 'Réservation annulée.' : 'Réservation rétablie.');
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => {
        this.aModifier.set(null);
        const details = erreur.error?.erreurs;

        this.erreur.set(
          details && typeof details === 'object'
            ? Object.values(details).join(' ')
            : (erreur.error?.erreur ?? "La modification a échoué. Réessaie plus tard.")
        );
      }
    });
  }

  private charger(): void {
    this.adminService.reservations().subscribe({
      next: (reservations) => {
        this.reservations.set(reservations);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger les réservations.');
      }
    });
  }
}
