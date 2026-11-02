import { DecimalPipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';

import { AvisSection } from '../../components/avis-section/avis-section';
import { BoutonFavori } from '../../components/bouton-favori/bouton-favori';
import { CreneauxLecture } from '../../components/creneaux-lecture/creneaux-lecture';
import { SeanceLigne } from '../../components/seance-ligne/seance-ligne';
import { ResumeAvis } from '../../models/avis';
import { Seance, SalleDetail } from '../../models/consultation';
import { AvisService } from '../../services/avis.service';
import { ConsultationService } from '../../services/consultation.service';

@Component({
  selector: 'app-salle',
  imports: [DecimalPipe, RouterLink, SeanceLigne, BoutonFavori, AvisSection, CreneauxLecture],
  templateUrl: './salle.html'
})
export class Salle implements OnInit {
  private readonly consultation = inject(ConsultationService);
  private readonly route = inject(ActivatedRoute);
  private readonly avisService = inject(AvisService);

  protected readonly salle = signal<SalleDetail | null>(null);
  protected readonly seances = signal<Seance[]>([]);
  protected readonly resume = signal<ResumeAvis | null>(null);
  protected readonly horaires = computed(() =>
    (this.salle()?.horaires ?? []).map((h) => ({
      jourSemaine: h.jourSemaine,
      debut: h.heureOuverture,
      fin: h.heureFermeture
    }))
  );
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

    this.consultation.seances({ salle: id }).subscribe({
      next: (seances) => this.seances.set(seances)
    });

    this.chargerAvis();
  }

  protected chargerAvis(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));

    this.avisService.lister('salle', id).subscribe({
      next: (resume) => this.resume.set(resume)
    });
  }
}
