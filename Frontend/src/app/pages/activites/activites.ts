import { Component, OnInit, inject, signal } from '@angular/core';

import { Activite } from '../../models/activite';
import { ActiviteService } from '../../services/activite.service';

@Component({
  selector: 'app-activites',
  templateUrl: './activites.html',
  styleUrl: './activites.css'
})
export class Activites implements OnInit {
  private readonly activiteService = inject(ActiviteService);

  protected readonly activites = signal<Activite[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);

  ngOnInit(): void {
    this.activiteService.lister().subscribe({
      next: (activites) => {
        this.activites.set(activites);
        this.chargement.set(false);
      },
      error: () => {
        this.erreur.set(true);
        this.chargement.set(false);
      }
    });
  }
}
