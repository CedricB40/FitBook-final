import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { Activite } from '../../models/activite';
import { ResultatRecherche } from '../../models/recherche';
import { ActiviteService } from '../../services/activite.service';
import { ConsultationService } from '../../services/consultation.service';
import { fondPhoto } from '../../utils/photo';

@Component({
  selector: 'app-accueil',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './accueil.html',
  styleUrl: './accueil.css'
})
export class Accueil implements OnInit {
  protected readonly fondPhoto = fondPhoto;

  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly consultation = inject(ConsultationService);
  private readonly activiteService = inject(ActiviteService);

  protected readonly formulaire = this.fb.nonNullable.group({
    q: [''],
    ville: ['']
  });

  protected readonly activites = signal<Activite[]>([]);
  protected readonly salles = signal<ResultatRecherche[]>([]);
  protected readonly coachs = signal<ResultatRecherche[]>([]);

  ngOnInit(): void {
    this.activiteService.lister().subscribe({
      next: (activites) => this.activites.set(activites)
    });

    this.consultation.rechercher({ types: ['salle'], limite: 4 }).subscribe({
      next: (reponse) => this.salles.set(reponse.resultats)
    });

    this.consultation.rechercher({ types: ['coach'], limite: 4 }).subscribe({
      next: (reponse) => this.coachs.set(reponse.resultats)
    });
  }

  protected chercher(): void {
    const { q, ville } = this.formulaire.getRawValue();

    this.router.navigate(['/resultats'], {
      queryParams: { q: q.trim() || null, ville: ville.trim() || null }
    });
  }
}
