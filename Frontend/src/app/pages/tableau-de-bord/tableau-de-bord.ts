import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';

import { StatistiquesCoach } from '../../models/statistiques';
import { CoachService } from '../../services/coach.service';

@Component({
  selector: 'app-tableau-de-bord',
  templateUrl: './tableau-de-bord.html'
})
export class TableauDeBord implements OnInit {
  private readonly coachService = inject(CoachService);

  protected readonly statistiques = signal<StatistiquesCoach | null>(null);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal<string | null>(null);

  ngOnInit(): void {
    this.coachService.statistiques().subscribe({
      next: (statistiques) => {
        this.statistiques.set(statistiques);
        this.chargement.set(false);
      },
      error: (erreur: HttpErrorResponse) => {
        this.erreur.set(
          erreur.status === 404
            ? 'Aucun profil coach n\'est enregistré pour ton compte.'
            : 'Impossible de charger tes statistiques. Réessaie plus tard.'
        );
        this.chargement.set(false);
      }
    });
  }
}
