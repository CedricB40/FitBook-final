import { DatePipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensGestionnaire } from '../../components/espace/liens-gestionnaire';
import { ReservationSalle } from '../../models/espace-gestionnaire';
import { GestionnaireService } from '../../services/gestionnaire.service';

@Component({
  selector: 'app-gestionnaire-reservations',
  imports: [DatePipe, Espace],
  templateUrl: './gestionnaire-reservations.html',
  styleUrl: './gestionnaire-reservations.css'
})
export class GestionnaireReservations implements OnInit {
  private readonly gestionnaireService = inject(GestionnaireService);

  protected readonly liens = liensGestionnaire('reservations');

  protected readonly reservations = signal<ReservationSalle[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal<string | null>(null);
  protected readonly filtre = signal<'avenir' | 'passees'>('avenir');

  protected readonly affichees = computed(() => {
    const maintenant = new Date();

    return this.reservations().filter((reservation) => {
      const futur = new Date(reservation.seance.debut) > maintenant;

      return this.filtre() === 'avenir' ? futur : !futur;
    });
  });

  ngOnInit(): void {
    this.gestionnaireService.reservations().subscribe({
      next: (reservations) => {
        this.reservations.set(reservations);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger les réservations. As-tu enregistré ta salle ?');
      }
    });
  }

  protected choisir(filtre: 'avenir' | 'passees'): void {
    this.filtre.set(filtre);
  }
}
