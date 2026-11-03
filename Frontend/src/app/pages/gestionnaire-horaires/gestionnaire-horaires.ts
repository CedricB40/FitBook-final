import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensGestionnaire } from '../../components/espace/liens-gestionnaire';
import { SemaineCreneaux } from '../../components/semaine-creneaux/semaine-creneaux';
import { Creneau, NouveauCreneau } from '../../models/creneau';
import { GestionnaireService } from '../../services/gestionnaire.service';

@Component({
  selector: 'app-gestionnaire-horaires',
  imports: [Espace, SemaineCreneaux],
  templateUrl: './gestionnaire-horaires.html',
  styleUrl: './gestionnaire-horaires.css'
})
export class GestionnaireHoraires implements OnInit {
  private readonly gestionnaireService = inject(GestionnaireService);

  protected readonly liens = liensGestionnaire('horaires');

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

    this.gestionnaireService
      .creerHoraire({
        jourSemaine: creneau.jourSemaine,
        heureOuverture: creneau.debut,
        heureFermeture: creneau.fin
      })
      .subscribe({
        next: () => {
          this.succes.set('Horaire ajouté.');
          this.charger();
        },
        error: (erreur: HttpErrorResponse) => this.echec(erreur)
      });
  }

  protected supprimer(id: number): void {
    this.erreur.set(null);
    this.succes.set(null);

    this.gestionnaireService.supprimerHoraire(id).subscribe({
      next: () => {
        this.succes.set('Horaire supprimé.');
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => this.echec(erreur)
    });
  }

  private charger(): void {
    this.gestionnaireService.horaires().subscribe({
      next: (horaires) => {
        this.creneaux.set(
          horaires.map((h) => ({ id: h.id, jourSemaine: h.jourSemaine, debut: h.heureOuverture, fin: h.heureFermeture }))
        );
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger les horaires. As-tu enregistré ta salle ?');
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
