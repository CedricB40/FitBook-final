import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { Espace } from '../../components/espace/espace';
import { liensGestionnaire } from '../../components/espace/liens-gestionnaire';
import { GestionnaireService } from '../../services/gestionnaire.service';

@Component({
  selector: 'app-gestionnaire-salle',
  imports: [ReactiveFormsModule, Espace],
  templateUrl: './gestionnaire-salle.html',
  styleUrl: './gestionnaire-salle.css'
})
export class GestionnaireSalle implements OnInit {
  private readonly gestionnaireService = inject(GestionnaireService);
  private readonly fb = inject(FormBuilder);

  protected readonly liens = liensGestionnaire('salle');

  protected readonly existe = signal(false);
  protected readonly chargement = signal(true);
  protected readonly envoi = signal(false);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);

  protected readonly formulaire = this.fb.nonNullable.group({
    nom: ['', [Validators.required, Validators.maxLength(100)]],
    adresse: ['', [Validators.required, Validators.maxLength(255)]],
    ville: ['', [Validators.required, Validators.maxLength(50)]],
    description: [''],
    telephone: ['', [Validators.maxLength(255)]],
    photo: ['', [Validators.maxLength(255)]]
  });

  ngOnInit(): void {
    this.gestionnaireService.salle().subscribe({
      next: (salle) => {
        this.existe.set(true);
        this.formulaire.setValue({
          nom: salle.nom ?? '',
          adresse: salle.adresse ?? '',
          ville: salle.ville ?? '',
          description: salle.description ?? '',
          telephone: salle.telephone ?? '',
          photo: salle.photo ?? ''
        });
        this.chargement.set(false);
      },
      error: (erreur: HttpErrorResponse) => {
        this.chargement.set(false);

        if (erreur.status !== 404) {
          this.erreur.set('Impossible de charger ta salle.');
        }
      }
    });
  }

  protected enregistrer(): void {
    this.erreur.set(null);
    this.succes.set(null);

    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      this.erreur.set('Le nom, l\'adresse et la ville sont obligatoires.');
      return;
    }

    const valeurs = this.formulaire.getRawValue();
    const donnees = {
      nom: valeurs.nom.trim(),
      adresse: valeurs.adresse.trim(),
      ville: valeurs.ville.trim(),
      description: valeurs.description.trim() === '' ? null : valeurs.description.trim(),
      telephone: valeurs.telephone.trim() === '' ? null : valeurs.telephone.trim(),
      photo: valeurs.photo.trim() === '' ? null : valeurs.photo.trim()
    };

    const requete = this.existe()
      ? this.gestionnaireService.modifierSalle(donnees)
      : this.gestionnaireService.creerSalle(donnees);

    this.envoi.set(true);

    requete.subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set(this.existe() ? 'Salle mise à jour.' : 'Salle créée.');
        this.existe.set(true);
      },
      error: (erreur: HttpErrorResponse) => {
        this.envoi.set(false);
        const details = erreur.error?.erreurs;

        this.erreur.set(
          details && typeof details === 'object'
            ? Object.values(details).join(' ')
            : (erreur.error?.erreur ?? "L'enregistrement a échoué. Réessaie plus tard.")
        );
      }
    });
  }
}
