import { DatePipe, DecimalPipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensGestionnaire } from '../../components/espace/liens-gestionnaire';
import { ResumeAvis } from '../../models/avis';
import { AvisService } from '../../services/avis.service';
import { GestionnaireService } from '../../services/gestionnaire.service';

@Component({
  selector: 'app-gestionnaire-avis',
  imports: [DatePipe, DecimalPipe, Espace],
  templateUrl: './gestionnaire-avis.html',
  styleUrl: './gestionnaire-avis.css'
})
export class GestionnaireAvis implements OnInit {
  private readonly gestionnaireService = inject(GestionnaireService);
  private readonly avisService = inject(AvisService);

  protected readonly liens = liensGestionnaire('avis');
  protected readonly niveaux = [5, 4, 3, 2, 1];
  protected readonly etoiles = [1, 2, 3, 4, 5];

  protected readonly resume = signal<ResumeAvis | null>(null);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal<string | null>(null);

  protected readonly etoilesMoyenne = computed(() => Math.round(this.resume()?.moyenne ?? 0));

  ngOnInit(): void {
    this.gestionnaireService.salle().subscribe({
      next: (salle) => {
        this.avisService.lister('salle', salle.id).subscribe({
          next: (resume) => {
            this.resume.set(resume);
            this.chargement.set(false);
          },
          error: () => this.echec()
        });
      },
      error: () => this.echec()
    });
  }

  protected pourcentage(niveau: number): number {
    const resume = this.resume();

    if (!resume || resume.total === 0) {
      return 0;
    }

    return ((resume.repartition[niveau] ?? 0) / resume.total) * 100;
  }

  private echec(): void {
    this.chargement.set(false);
    this.erreur.set('Impossible de charger tes avis. As-tu enregistré ta salle ?');
  }
}
