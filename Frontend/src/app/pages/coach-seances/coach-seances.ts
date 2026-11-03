import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';

import { Espace } from '../../components/espace/espace';
import { liensCoach } from '../../components/espace/liens';
import { CoursChoix, SeancePlanning } from '../../models/espace-coach';
import { CoachService } from '../../services/coach.service';

@Component({
  selector: 'app-coach-seances',
  imports: [DatePipe, ReactiveFormsModule, Espace],
  templateUrl: './coach-seances.html',
  styleUrl: './coach-seances.css'
})
export class CoachSeances implements OnInit {
  private readonly coachService = inject(CoachService);
  private readonly route = inject(ActivatedRoute);
  private readonly fb = inject(FormBuilder);

  protected readonly liens = liensCoach('seances');

  protected readonly seances = signal<SeancePlanning[]>([]);
  protected readonly cours = signal<CoursChoix[]>([]);
  protected readonly chargement = signal(true);
  protected readonly formulaireOuvert = signal(false);
  protected readonly enEdition = signal<number | null>(null);
  protected readonly aSupprimer = signal<number | null>(null);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);
  protected readonly envoi = signal(false);

  protected readonly formulaire = this.fb.nonNullable.group({
    coursId: [0, [Validators.min(1)]],
    date: ['', [Validators.required]],
    debut: ['', [Validators.required]],
    fin: ['', [Validators.required]]
  });

  ngOnInit(): void {
    this.coachService.cours().subscribe({
      next: (cours) => this.cours.set(cours),
      error: () => this.erreur.set('Impossible de charger la liste des cours.')
    });

    this.charger(() => {
      const parametres = this.route.snapshot.queryParamMap;

      if (parametres.get('nouvelle')) {
        this.ouvrirCreation();
      }

      const idModification = Number(parametres.get('modifier'));
      const seance = this.seances().find((s) => s.id === idModification);

      if (seance) {
        this.ouvrirModification(seance);
      }
    });
  }

  protected ouvrirCreation(): void {
    this.enEdition.set(null);
    this.formulaire.reset({ coursId: 0, date: '', debut: '', fin: '' });
    this.erreur.set(null);
    this.succes.set(null);
    this.formulaireOuvert.set(true);
  }

  protected ouvrirModification(seance: SeancePlanning): void {
    this.enEdition.set(seance.id);
    this.formulaire.reset({
      coursId: seance.cours.id,
      date: seance.debut.slice(0, 10),
      debut: seance.debut.slice(11, 16),
      fin: seance.fin.slice(11, 16)
    });
    this.erreur.set(null);
    this.succes.set(null);
    this.formulaireOuvert.set(true);
  }

  protected fermerFormulaire(): void {
    this.formulaireOuvert.set(false);
    this.enEdition.set(null);
  }

  protected enregistrer(): void {
    this.erreur.set(null);
    this.succes.set(null);

    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      this.erreur.set('Choisis un cours, une date et des heures de début et de fin.');
      return;
    }

    const { coursId, date, debut, fin } = this.formulaire.getRawValue();

    if (fin <= debut) {
      this.erreur.set("L'heure de fin doit être après l'heure de début.");
      return;
    }

    const donnees = {
      coursId: Number(coursId),
      dateHeureDebut: `${date} ${debut}:00`,
      dateHeureFin: `${date} ${fin}:00`
    };

    const id = this.enEdition();
    const requete = id === null
      ? this.coachService.creerSeance(donnees)
      : this.coachService.modifierSeance(id, donnees);

    this.envoi.set(true);

    requete.subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set(id === null ? 'Séance créée.' : 'Séance modifiée.');
        this.fermerFormulaire();
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => {
        this.envoi.set(false);
        this.erreur.set(this.messageErreur(erreur));
      }
    });
  }

  protected demanderSuppression(id: number): void {
    this.aSupprimer.set(id);
  }

  protected annulerSuppression(): void {
    this.aSupprimer.set(null);
  }

  protected supprimer(id: number): void {
    this.coachService.supprimerSeance(id).subscribe({
      next: () => {
        this.aSupprimer.set(null);
        this.succes.set('Séance supprimée.');
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => {
        this.aSupprimer.set(null);
        this.erreur.set(this.messageErreur(erreur));
      }
    });
  }

  private charger(apres?: () => void): void {
    this.coachService.planning().subscribe({
      next: (planning) => {
        this.seances.set(planning);
        this.chargement.set(false);
        apres?.();
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set("Impossible de charger tes séances. As-tu enregistré ton profil de coach ?");
      }
    });
  }

  private messageErreur(erreur: HttpErrorResponse): string {
    const details = erreur.error?.erreurs;

    if (details && typeof details === 'object') {
      return Object.values(details).flat().join(' ');
    }

    return erreur.error?.erreur ?? "L'opération a échoué. Réessaie plus tard.";
  }
}
