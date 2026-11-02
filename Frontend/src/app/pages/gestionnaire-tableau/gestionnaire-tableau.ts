import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensGestionnaire } from '../../components/espace/liens-gestionnaire';
import { CoachSalle, ReservationSalle, SeanceSalle } from '../../models/espace-gestionnaire';
import { GestionnaireService } from '../../services/gestionnaire.service';

interface JourSemaine {
  date: Date;
  cle: string;
  seances: SeanceSalle[];
  places: number;
}

@Component({
  selector: 'app-gestionnaire-tableau',
  imports: [DatePipe, Espace],
  templateUrl: './gestionnaire-tableau.html',
  styleUrl: './gestionnaire-tableau.css'
})
export class GestionnaireTableau implements OnInit {
  private readonly gestionnaireService = inject(GestionnaireService);

  protected readonly liens = liensGestionnaire('tableau');

  protected readonly planning = signal<SeanceSalle[]>([]);
  protected readonly reservations = signal<ReservationSalle[]>([]);
  protected readonly coachs = signal<CoachSalle[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal<string | null>(null);
  protected readonly decalage = signal(0);

  protected readonly debutSemaine = computed(() => {
    const aujourdhui = new Date();
    const jour = (aujourdhui.getDay() + 6) % 7;

    return new Date(
      aujourdhui.getFullYear(),
      aujourdhui.getMonth(),
      aujourdhui.getDate() - jour + this.decalage() * 7
    );
  });

  protected readonly jours = computed<JourSemaine[]>(() => {
    const lundi = this.debutSemaine();

    return Array.from({ length: 7 }, (_, i) => {
      const date = new Date(lundi.getFullYear(), lundi.getMonth(), lundi.getDate() + i);
      const cle = this.cle(date);
      const seances = this.planning().filter((seance) => seance.debut.slice(0, 10) === cle);

      return {
        date,
        cle,
        seances,
        places: seances.reduce((total, seance) => total + seance.placesRestantes, 0)
      };
    });
  });

  protected readonly seancesSemaine = computed(() =>
    this.jours().reduce((total, jour) => total + jour.seances.length, 0)
  );

  protected readonly seancesAVenir = computed(() =>
    this.planning().filter((seance) => new Date(seance.debut) > new Date())
  );

  protected readonly reservationsAVenir = computed(() =>
    this.reservations()
      .filter((reservation) => new Date(reservation.seance.debut) > new Date())
      .reverse()
  );

  protected readonly tauxRemplissage = computed(() => {
    const seances = this.seancesAVenir();
    const capacite = seances.reduce((total, seance) => total + seance.cours.capacite, 0);
    const reservees = seances.reduce((total, seance) => total + seance.reservees, 0);

    return capacite === 0 ? 0 : Math.round((reservees / capacite) * 1000) / 10;
  });

  ngOnInit(): void {
    this.gestionnaireService.planning().subscribe({
      next: (planning) => {
        this.planning.set(planning);
        this.chargement.set(false);
      },
      error: (erreur: HttpErrorResponse) => {
        this.chargement.set(false);
        this.erreur.set(
          erreur.status === 409
            ? "Tu n'as pas encore enregistré ta salle."
            : 'Impossible de charger ton tableau de bord. Réessaie plus tard.'
        );
      }
    });

    this.gestionnaireService.reservations().subscribe({
      next: (reservations) => this.reservations.set(reservations),
      error: () => undefined
    });

    this.gestionnaireService.coachs().subscribe({
      next: (coachs) => this.coachs.set(coachs),
      error: () => undefined
    });
  }

  protected changerSemaine(decalage: number): void {
    this.decalage.update((valeur) => valeur + decalage);
  }

  private cle(date: Date): string {
    const mois = String(date.getMonth() + 1).padStart(2, '0');
    const jour = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${mois}-${jour}`;
  }
}
