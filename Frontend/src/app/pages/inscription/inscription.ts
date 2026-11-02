import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-inscription',
  imports: [ReactiveFormsModule],
  templateUrl: './inscription.html',
  styleUrl: './inscription.css'
})
export class Inscription {
  private readonly fb = inject(FormBuilder);
  private readonly authService = inject(AuthService);

  protected readonly roles = [
    { valeur: 'ROLE_SPORTIF', libelle: 'Sportif' },
    { valeur: 'ROLE_COACH', libelle: 'Coach' },
    { valeur: 'ROLE_GESTIONNAIRE', libelle: 'Gestionnaire de salle' }
  ];

  protected readonly formulaire = this.fb.nonNullable.group({
    nom: ['', [Validators.required, Validators.maxLength(50)]],
    prenom: ['', [Validators.required, Validators.maxLength(50)]],
    email: ['', [Validators.required, Validators.email, Validators.maxLength(255)]],
    motDePasse: ['', [Validators.required, Validators.minLength(8), Validators.maxLength(72)]],
    role: ['ROLE_SPORTIF', [Validators.required]]
  });

  protected readonly envoi = signal(false);
  protected readonly succes = signal(false);
  protected readonly erreurGenerale = signal<string | null>(null);

  protected erreur(champ: string): string | null {
    const controle = this.formulaire.get(champ);

    if (!controle || !controle.invalid || !(controle.touched || controle.dirty)) {
      return null;
    }

    const erreurs = controle.errors ?? {};

    if (erreurs['serveur']) {
      return erreurs['serveur'];
    }
    if (erreurs['required']) {
      return 'Ce champ est obligatoire.';
    }
    if (erreurs['email']) {
      return 'Adresse e-mail invalide.';
    }
    if (erreurs['minlength']) {
      return `Minimum ${erreurs['minlength'].requiredLength} caractères.`;
    }
    if (erreurs['maxlength']) {
      return `Maximum ${erreurs['maxlength'].requiredLength} caractères.`;
    }

    return 'Valeur invalide.';
  }

  protected envoyer(): void {
    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      return;
    }

    this.envoi.set(true);
    this.succes.set(false);
    this.erreurGenerale.set(null);

    this.authService.inscrire(this.formulaire.getRawValue()).subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set(true);
        this.formulaire.reset();
      },
      error: (erreur: HttpErrorResponse) => {
        this.envoi.set(false);

        const erreursServeur = erreur.error?.erreurs;

        if ((erreur.status === 409 || erreur.status === 422) && erreursServeur) {
          for (const [champ, message] of Object.entries(erreursServeur)) {
            const controle = this.formulaire.get(champ);
            controle?.setErrors({ serveur: String(message) });
            controle?.markAsTouched();
          }
        } else if (erreur.status === 0) {
          this.erreurGenerale.set('Le serveur est injoignable. Réessaie plus tard.');
        } else {
          this.erreurGenerale.set('Une erreur est survenue. Réessaie plus tard.');
        }
      }
    });
  }
}
