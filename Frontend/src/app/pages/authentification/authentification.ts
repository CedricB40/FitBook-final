import { Component } from '@angular/core';

import { Connexion } from '../connexion/connexion';
import { Inscription } from '../inscription/inscription';

@Component({
  selector: 'app-authentification',
  imports: [Connexion, Inscription],
  template: `
    <div class="authentification">
      <app-connexion />
      <app-inscription />
    </div>
  `,
  styles: `
    .authentification {
      display: grid;
      gap: 1rem;
      max-width: 1180px;
      margin: 0 auto;
      padding: 0 1.5rem;
      align-items: start;
    }

    @media (min-width: 900px) {
      .authentification {
        grid-template-columns: 1fr 1fr;
      }
    }
  `
})
export class Authentification {}
