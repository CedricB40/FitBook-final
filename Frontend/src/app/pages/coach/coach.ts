import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';

import { CoachDetail } from '../../models/consultation';
import { ConsultationService } from '../../services/consultation.service';

@Component({
  selector: 'app-coach',
  imports: [DatePipe, RouterLink],
  templateUrl: './coach.html'
})
export class Coach implements OnInit {
  private readonly consultation = inject(ConsultationService);
  private readonly route = inject(ActivatedRoute);

  protected readonly coach = signal<CoachDetail | null>(null);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));

    this.consultation.coach(id).subscribe({
      next: (coach) => {
        this.coach.set(coach);
        this.chargement.set(false);
      },
      error: () => {
        this.erreur.set(true);
        this.chargement.set(false);
      }
    });
  }
}
