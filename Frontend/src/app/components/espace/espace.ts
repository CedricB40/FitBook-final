import { Component, inject, input } from '@angular/core';
import { Router, RouterLink } from '@angular/router';

import { AuthService } from '../../services/auth.service';

export interface LienEspace {
  libelle: string;
  route: string;
  fragment?: string;
  actif?: boolean;
}

@Component({
  selector: 'app-espace',
  imports: [RouterLink],
  templateUrl: './espace.html',
  styleUrl: './espace.css'
})
export class Espace {
  readonly titre = input.required<string>();
  readonly liens = input.required<LienEspace[]>();

  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  protected deconnecter(): void {
    this.auth.deconnecter();
    this.router.navigateByUrl('/connexion');
  }
}
