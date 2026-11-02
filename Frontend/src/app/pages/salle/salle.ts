import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';

import { SalleDetail } from '../../models/consultation';
import { ConsultationService } from '../../services/consultation.service';

@Component({
  selector: 'app-salle',
  imports: [RouterLink],
  templateUrl: './salle.html'
})
export class Salle implements OnInit {
  private readonly consultation = inject(ConsultationService);
  private readonly route = inject(ActivatedRoute);

  protected readonly salle = signal<SalleDetail | null>(null);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));

    this.consultation.salle(id).subscribe({
      next: (salle) => {
        this.salle.set(salle);
        this.chargement.set(false);
      },
      error: () => {
        this.erreur.set(true);
        this.chargement.set(false);
      }
    });
  }
}
