import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';

import { Espace } from '../../components/espace/espace';
import { liensCoach } from '../../components/espace/liens';
import { SeancePlanning } from '../../models/espace-coach';
import { StatistiquesCoach } from '../../models/statistiques';
import { CoachService } from '../../services/coach.service';

interface JourSemaine {
  date: Date;
  cle: string;
  seances: SeancePlanning[];
  places: number;
}

@Component({
  selector: 'app-tableau-de-bord',
  imports: [DatePipe, RouterLink, Espace],
  templateUrl: './tableau-de-bord.html',
  styleUrl: './tableau-de-bord.css'
})
export class TableauDeBord implements OnInit {
  private readonly coachService = inject(CoachService);

  protected readonly liens = liensCoach('tableau');

  protected readonly statistiques = signal<StatistiquesCoach | null>(null);
  protected readonly planning = signal<SeancePlanning[]>([]);
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

  protected readonly prochaines = computed(() =>
    this.planning()
      .filter((seance) => new Date(seance.debut) > new Date())
      .slice(0, 6)
  );

  ngOnInit(): void {
    this.coachService.statistiques().subscribe({
      next: (statistiques) => this.statistiques.set(statistiques),
      error: (erreur: HttpErrorResponse) => this.signalerErreur(erreur)
    });

    this.coachService.planning().subscribe({
      next: (planning) => {
        this.planning.set(planning);
        this.chargement.set(false);
      },
      error: (erreur: HttpErrorResponse) => {
        this.signalerErreur(erreur);
        this.chargement.set(false);
      }
    });
  }

  protected changerSemaine(decalage: number): void {
    this.decalage.update((valeur) => valeur + decalage);
  }

  private signalerErreur(erreur: HttpErrorResponse): void {
    this.erreur.set(
      erreur.status === 404 || erreur.status === 409
        ? "Aucun profil coach n'est enregistré pour ton compte."
        : 'Impossible de charger ton tableau de bord. Réessaie plus tard.'
    );
  }

  private cle(date: Date): string {
    const mois = String(date.getMonth() + 1).padStart(2, '0');
    const jour = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${mois}-${jour}`;
  }
}
