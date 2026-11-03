import { Component, computed, inject, input } from '@angular/core';
import { RouterLink } from '@angular/router';

import { TypeFavori } from '../../models/favoris';
import { AuthService } from '../../services/auth.service';
import { FavorisStore } from '../../services/favoris.store';

@Component({
  selector: 'app-bouton-favori',
  imports: [RouterLink],
  templateUrl: './bouton-favori.html',
  styleUrl: './bouton-favori.css'
})
export class BoutonFavori {
  readonly type = input.required<TypeFavori>();
  readonly id = input.required<number>();
  readonly compact = input(false);

  protected readonly auth = inject(AuthService);
  protected readonly store = inject(FavorisStore);

  protected readonly actif = computed(() => this.store.estFavori(this.type(), this.id()));
}
