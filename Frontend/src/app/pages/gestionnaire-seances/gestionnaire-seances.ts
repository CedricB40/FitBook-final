import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';

import { Espace } from '../../components/espace/espace';
import { liensGestionnaire } from '../../components/espace/liens-gestionnaire';
import { Activite } from '../../models/activite';
import { CoachChoix, CoursSalle, SeanceSalle } from '../../models/espace-gestionnaire';
import { ActiviteService } from '../../services/activite.service';
import { GestionnaireService } from '../../services/gestionnaire.service';

@Component({
  selector: 'app-gestionnaire-seances',
  imports: [DatePipe, ReactiveFormsModule, Espace],
  templateUrl: './gestionnaire-seances.html',
  styleUrl: './gestionnaire-seances.css'
})
export class GestionnaireSeances implements OnInit {
  private readonly gestionnaireService = inject(GestionnaireService);
  private readonly activiteService = inject(ActiviteService);
  private readonly route = inject(ActivatedRoute);
  private readonly fb = inject(FormBuilder);

  protected readonly liens = liensGestionnaire('seances');

  protected readonly seances = signal<SeanceSalle[]>([]);
  protected readonly cours = signal<CoursSalle[]>([]);
  protected readonly coachs = signal<CoachChoix[]>([]);
  protected readonly activites = signal<Activite[]>([]);
  protected readonly chargement = signal(true);
  protected readonly formulaire = signal<'seance' | 'cours' | null>(null);
  protected readonly aSupprimer = signal<number | null>(null);
  protected readonly coursASupprimer = signal<number | null>(null);
  protected readonly seanceEnEdition = signal<number | null>(null);
  protected readonly coursEnEdition = signal<number | null>(null);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);
  protected readonly envoi = signal(false);

  protected readonly formSeance = this.fb.nonNullable.group({
    coursId: [0, [Validators.min(1)]],
    coachId: [0, [Validators.min(1)]],
    date: ['', [Validators.required]],
    debut: ['', [Validators.required]],
    fin: ['', [Validators.required]]
  });

  protected readonly formCours = this.fb.nonNullable.group({
    titre: ['', [Validators.required, Validators.maxLength(100)]],
    activiteId: [0, [Validators.min(1)]],
    duree: [60, [Validators.required, Validators.min(1)]],
    capacite: [10, [Validators.required, Validators.min(1)]],
    description: ['']
  });

  ngOnInit(): void {
    this.activiteService.lister().subscribe({ next: (activites) => this.activites.set(activites) });
    this.gestionnaireService.tousLesCoachs().subscribe({ next: (coachs) => this.coachs.set(coachs) });
    this.chargerCours();
    this.charger();

    if (this.route.snapshot.queryParamMap.get('nouvelle')) {
      this.ouvrir('seance');
    }
  }

  protected ouvrir(type: 'seance' | 'cours'): void {
    this.erreur.set(null);
    this.succes.set(null);
    this.seanceEnEdition.set(null);
    this.coursEnEdition.set(null);

    if (type === 'seance') {
      this.formSeance.reset({ coursId: 0, coachId: 0, date: '', debut: '', fin: '' });
    } else {
      this.formCours.reset({ titre: '', activiteId: 0, duree: 60, capacite: 10, description: '' });
    }

    this.formulaire.set(type);
  }

  protected modifierSeance(seance: SeanceSalle): void {
    this.erreur.set(null);
    this.succes.set(null);
    this.coursEnEdition.set(null);
    this.seanceEnEdition.set(seance.id);
    this.formSeance.reset({
      coursId: seance.cours.id,
      coachId: seance.coachId,
      date: seance.debut.slice(0, 10),
      debut: seance.debut.slice(11, 16),
      fin: seance.fin.slice(11, 16)
    });
    this.formulaire.set('seance');
  }

  protected modifierCours(unCours: CoursSalle): void {
    this.erreur.set(null);
    this.succes.set(null);
    this.seanceEnEdition.set(null);
    this.coursEnEdition.set(unCours.id);
    this.formCours.reset({
      titre: unCours.titre,
      activiteId: unCours.activiteId,
      duree: unCours.duree,
      capacite: unCours.capacite,
      description: unCours.description ?? ''
    });
    this.formulaire.set('cours');
  }

  protected fermer(): void {
    this.formulaire.set(null);
    this.seanceEnEdition.set(null);
    this.coursEnEdition.set(null);
  }

  protected enregistrerSeance(): void {
    this.erreur.set(null);
    this.succes.set(null);

    if (this.formSeance.invalid) {
      this.formSeance.markAllAsTouched();
      this.erreur.set('Choisis un cours, un coach, une date et des heures de début et de fin.');
      return;
    }

    const { coursId, coachId, date, debut, fin } = this.formSeance.getRawValue();

    if (fin <= debut) {
      this.erreur.set("L'heure de fin doit être après l'heure de début.");
      return;
    }

    const donnees = {
      coursId: Number(coursId),
      coachId: Number(coachId),
      dateHeureDebut: `${date} ${debut}:00`,
      dateHeureFin: `${date} ${fin}:00`
    };

    const id = this.seanceEnEdition();
    const requete = id === null
      ? this.gestionnaireService.creerSeance(donnees)
      : this.gestionnaireService.modifierSeance(id, donnees);

    this.envoi.set(true);

    requete.subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set(id === null ? 'Séance créée.' : 'Séance modifiée.');
        this.fermer();
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => this.echec(erreur)
    });
  }

  protected enregistrerCours(): void {
    this.erreur.set(null);
    this.succes.set(null);

    if (this.formCours.invalid) {
      this.formCours.markAllAsTouched();
      this.erreur.set("Indique un titre, une activité, une durée et une capacité valides.");
      return;
    }

    const valeurs = this.formCours.getRawValue();

    const donnees = {
      titre: valeurs.titre.trim(),
      description: valeurs.description.trim() === '' ? null : valeurs.description.trim(),
      duree: Number(valeurs.duree),
      capacite: Number(valeurs.capacite),
      activiteId: Number(valeurs.activiteId)
    };

    const id = this.coursEnEdition();
    const requete = id === null
      ? this.gestionnaireService.creerCours(donnees)
      : this.gestionnaireService.modifierCours(id, donnees);

    this.envoi.set(true);

    requete.subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set(id === null ? 'Cours créé.' : 'Cours modifié.');
        this.fermer();
        this.chargerCours();
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => this.echec(erreur)
    });
  }

  protected demanderSuppression(id: number): void {
    this.aSupprimer.set(id);
  }

  protected annulerSuppression(): void {
    this.aSupprimer.set(null);
  }

  protected supprimer(id: number): void {
    this.gestionnaireService.supprimerSeance(id).subscribe({
      next: () => {
        this.aSupprimer.set(null);
        this.succes.set('Séance supprimée.');
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => {
        this.aSupprimer.set(null);
        this.echec(erreur);
      }
    });
  }

  protected demanderSuppressionCours(id: number): void {
    this.coursASupprimer.set(id);
  }

  protected annulerSuppressionCours(): void {
    this.coursASupprimer.set(null);
  }

  protected supprimerCours(id: number): void {
    this.erreur.set(null);
    this.succes.set(null);

    this.gestionnaireService.supprimerCours(id).subscribe({
      next: () => {
        this.coursASupprimer.set(null);
        this.succes.set('Cours supprimé.');
        this.chargerCours();
      },
      error: (erreur: HttpErrorResponse) => {
        this.coursASupprimer.set(null);
        this.echec(erreur);
      }
    });
  }

  private charger(): void {
    this.gestionnaireService.planning().subscribe({
      next: (planning) => {
        this.seances.set(planning);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger les séances. As-tu enregistré ta salle ?');
      }
    });
  }

  private chargerCours(): void {
    this.gestionnaireService.cours().subscribe({ next: (cours) => this.cours.set(cours) });
  }

  private echec(erreur: HttpErrorResponse): void {
    this.envoi.set(false);
    const details = erreur.error?.erreurs;

    this.erreur.set(
      details && typeof details === 'object'
        ? Object.values(details).join(' ')
        : (erreur.error?.erreur ?? "L'opération a échoué. Réessaie plus tard.")
    );
  }
}
