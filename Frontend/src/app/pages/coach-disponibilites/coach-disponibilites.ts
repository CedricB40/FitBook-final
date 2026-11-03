import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensCoach } from '../../components/espace/liens';
import { SemaineCreneaux } from '../../components/semaine-creneaux/semaine-creneaux';
import { Creneau, NouveauCreneau } from '../../models/creneau';
import { CoachService } from '../../services/coach.service';

@Component({
  selector: 'app-coach-disponibilites',
  imports: [Espace, SemaineCreneaux],
  templateUrl: './coach-disponibilites.html',
  styleUrl: './coach-disponibilites.css'
})
export class CoachDisponibilites implements OnInit {
  private readonly coachService = inject(CoachService);

  protected readonly liens = liensCoach('disponibilites');

  protected readonly creneaux = signal<Creneau[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);

  ngOnInit(): void {
    this.charger();
  }

  protected ajouter(creneau: NouveauCreneau): void {
    this.erreur.set(null);
    this.succes.set(null);

    this.coachService
      .creerDisponibilite({
        jourSemaine: creneau.jourSemaine,
        heureDebut: creneau.debut,
        heureFin: creneau.fin
      })
      .subscribe({
        next: () => {
          this.succes.set('Disponibilité ajoutée.');
          this.charger();
        },
        error: (erreur: HttpErrorResponse) => this.echec(erreur)
      });
  }

  protected supprimer(id: number): void {
    this.erreur.set(null);
    this.succes.set(null);

    this.coachService.supprimerDisponibilite(id).subscribe({
      next: () => {
        this.succes.set('Disponibilité supprimée.');
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => this.echec(erreur)
    });
  }

  private charger(): void {
    this.coachService.disponibilites().subscribe({
      next: (disponibilites) => {
        this.creneaux.set(
          disponibilites.map((d) => ({ id: d.id, jourSemaine: d.jourSemaine, debut: d.heureDebut, fin: d.heureFin }))
        );
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger tes disponibilités. As-tu enregistré ton profil de coach ?');
      }
    });
  }

  private echec(erreur: HttpErrorResponse): void {
    const details = erreur.error?.erreurs;

    this.erreur.set(
      details && typeof details === 'object'
        ? Object.values(details).join(' ')
        : (erreur.error?.erreur ?? "L'opération a échoué. Réessaie plus tard.")
    );
  }
}
