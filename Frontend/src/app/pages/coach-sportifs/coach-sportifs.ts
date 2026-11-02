import { DatePipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensCoach } from '../../components/espace/liens';
import { ReservationCoach } from '../../models/espace-coach';
import { CoachService } from '../../services/coach.service';

@Component({
  selector: 'app-coach-sportifs',
  imports: [DatePipe, Espace],
  templateUrl: './coach-sportifs.html',
  styleUrl: './coach-sportifs.css'
})
export class CoachSportifs implements OnInit {
  private readonly coachService = inject(CoachService);

  protected readonly liens = liensCoach('sportifs');

  protected readonly reservations = signal<ReservationCoach[]>([]);
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
    this.coachService.reservations().subscribe({
      next: (reservations) => {
        this.reservations.set(reservations);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger tes sportifs. As-tu enregistré ton profil de coach ?');
      }
    });
  }

  protected choisir(filtre: 'avenir' | 'passees'): void {
    this.filtre.set(filtre);
  }
}
