import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

import { API_URL } from '../../config/api';
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
  private readonly route = inject(ActivatedRoute);
  private readonly http = inject(HttpClient);

  protected readonly formulaire = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    motDePasse: ['', [Validators.required]]
  });

  protected readonly envoi = signal(false);
  protected readonly confirmation = signal<'ok' | 'echec' | null>(this.lireConfirmation());
  protected readonly erreurGenerale = signal<string | null>(null);
  protected readonly nonConfirme = signal(false);
  protected readonly renvoiEnvoye = signal(false);

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
    this.nonConfirme.set(false);
    this.renvoiEnvoye.set(false);

    this.authService.connecter(this.formulaire.getRawValue()).subscribe({
      next: () => {
        this.envoi.set(false);
        this.router.navigateByUrl('/');
      },
      error: (erreur: HttpErrorResponse) => {
        this.envoi.set(false);

        const message = String(erreur.error?.message ?? '');

        if (erreur.status === 401 && message.startsWith('Confirme ton adresse')) {
          this.erreurGenerale.set(message);
          this.nonConfirme.set(true);
        } else if (erreur.status === 401) {
          this.erreurGenerale.set('E-mail ou mot de passe incorrect.');
        } else if (erreur.status === 0) {
          this.erreurGenerale.set('Le serveur est injoignable. Réessaie plus tard.');
        } else {
          this.erreurGenerale.set('Une erreur est survenue. Réessaie plus tard.');
        }
      }
    });
  }

  protected renvoyerConfirmation(): void {
    const email = this.formulaire.controls.email.value;

    this.http.post(`${API_URL}/renvoyer-confirmation`, { email }).subscribe({
      next: () => this.renvoiEnvoye.set(true),
      error: () => this.erreurGenerale.set("Impossible d'envoyer l'e-mail pour le moment. Réessaie plus tard.")
    });
  }

  private lireConfirmation(): 'ok' | 'echec' | null {
    const valeur = this.route.snapshot.queryParamMap.get('confirme');

    if (valeur === '1') {
      return 'ok';
    }

    return valeur === '0' ? 'echec' : null;
  }
}
