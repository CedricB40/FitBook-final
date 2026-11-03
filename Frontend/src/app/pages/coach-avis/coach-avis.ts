import { DatePipe, DecimalPipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensCoach } from '../../components/espace/liens';
import { ResumeAvis } from '../../models/avis';
import { AvisService } from '../../services/avis.service';
import { CoachService } from '../../services/coach.service';

@Component({
  selector: 'app-coach-avis',
  imports: [DatePipe, DecimalPipe, Espace],
  templateUrl: './coach-avis.html',
  styleUrl: './coach-avis.css'
})
export class CoachAvis implements OnInit {
  private readonly coachService = inject(CoachService);
  private readonly avisService = inject(AvisService);

  protected readonly liens = liensCoach('avis');
  protected readonly niveaux = [5, 4, 3, 2, 1];
  protected readonly etoiles = [1, 2, 3, 4, 5];

  protected readonly resume = signal<ResumeAvis | null>(null);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal<string | null>(null);

  protected readonly etoilesMoyenne = computed(() => Math.round(this.resume()?.moyenne ?? 0));

  ngOnInit(): void {
    this.coachService.profil().subscribe({
      next: (profil) => {
        this.avisService.lister('coach', profil.id).subscribe({
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
    this.erreur.set('Impossible de charger tes avis. As-tu enregistré ton profil de coach ?');
  }
}
