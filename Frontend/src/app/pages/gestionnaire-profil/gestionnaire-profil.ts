import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { Espace } from '../../components/espace/espace';
import { liensGestionnaire } from '../../components/espace/liens-gestionnaire';
import { ProfilService } from '../../services/profil.service';

@Component({
  selector: 'app-gestionnaire-profil',
  imports: [ReactiveFormsModule, Espace],
  templateUrl: './gestionnaire-profil.html',
  styleUrl: './gestionnaire-profil.css'
})
export class GestionnaireProfil implements OnInit {
  private readonly profilService = inject(ProfilService);
  private readonly fb = inject(FormBuilder);

  protected readonly liens = liensGestionnaire('profil');

  protected readonly chargement = signal(true);
  protected readonly envoi = signal(false);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);

  protected readonly formulaire = this.fb.nonNullable.group({
    prenom: ['', [Validators.required]],
    nom: ['', [Validators.required]],
    email: ['', [Validators.required, Validators.email]]
  });

  ngOnInit(): void {
    this.profilService.lire().subscribe({
      next: (profil) => {
        this.formulaire.setValue({
          prenom: profil.prenom ?? '',
          nom: profil.nom ?? '',
          email: profil.email ?? ''
        });
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger ton profil.');
      }
    });
  }

  protected enregistrer(): void {
    this.erreur.set(null);
    this.succes.set(null);

    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      this.erreur.set('Le prénom, le nom et une adresse e-mail valide sont obligatoires.');
      return;
    }

    this.envoi.set(true);

    this.profilService.enregistrer(this.formulaire.getRawValue()).subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set('Profil mis à jour.');
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
