import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';

import { BoutonFavori } from '../../components/bouton-favori/bouton-favori';
import { SeanceLigne } from '../../components/seance-ligne/seance-ligne';
import { CoachDetail } from '../../models/consultation';
import { ConsultationService } from '../../services/consultation.service';

@Component({
  selector: 'app-coach',
  imports: [RouterLink, SeanceLigne, BoutonFavori],
  templateUrl: './coach.html'
})
export class Coach implements OnInit {
  private readonly consultation = inject(ConsultationService);
  private readonly route = inject(ActivatedRoute);

  protected readonly coach = signal<CoachDetail | null>(null);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);

  protected readonly specialites = computed(() =>
    (this.coach()?.specialite ?? '')
      .split(/\s+et\s+|,|\/|·/)
      .map((specialite) => specialite.trim())
      .filter((specialite) => specialite !== '')
  );

  protected readonly lieux = computed(() => {
    const lieux = new Map<number, { id: number; nom: string; ville: string }>();

    for (const seance of this.coach()?.seances ?? []) {
      lieux.set(seance.salle.id, seance.salle);
    }

    return [...lieux.values()];
  });

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
