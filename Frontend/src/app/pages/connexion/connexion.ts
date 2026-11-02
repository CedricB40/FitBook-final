import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-connexion',
  imports: [ReactiveFormsModule],
  templateUrl: './connexion.html',
  styleUrl: './connexion.css'
})
export class Connexion {
  private readonly fb = inject(FormBuilder);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  protected readonly formulaire = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    motDePasse: ['', [Validators.required]]
  });

  protected readonly envoi = signal(false);
  protected readonly erreurGenerale = signal<string | null>(null);

  protected erreur(champ: string): string | null {
    const controle = this.formulaire.get(champ);

    if (!controle || !controle.invalid || !(controle.touched || controle.dirty)) {
      return null;
    }

    const erreurs = controle.errors ?? {};

    if (erreurs['required']) {
      return 'Ce champ est obligatoire.';
    }
    if (erreurs['email']) {
      return 'Adresse e-mail invalide.';
    }

    return 'Valeur invalide.';
  }

  protected envoyer(): void {
    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      return;
    }

    this.envoi.set(true);
    this.erreurGenerale.set(null);

    this.authService.connecter(this.formulaire.getRawValue()).subscribe({
      next: () => {
        this.envoi.set(false);
        this.router.navigateByUrl('/activites');
      },
      error: (erreur: HttpErrorResponse) => {
        this.envoi.set(false);

        if (erreur.status === 401) {
          this.erreurGenerale.set('E-mail ou mot de passe incorrect.');
        } else if (erreur.status === 0) {
          this.erreurGenerale.set('Le serveur est injoignable. Réessaie plus tard.');
        } else {
          this.erreurGenerale.set('Une erreur est survenue. Réessaie plus tard.');
        }
      }
    });
  }
}
