import { Component, OnInit, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensGestionnaire } from '../../components/espace/liens-gestionnaire';
import { CoachSalle } from '../../models/espace-gestionnaire';
import { GestionnaireService } from '../../services/gestionnaire.service';

@Component({
  selector: 'app-gestionnaire-coachs',
  imports: [Espace],
  templateUrl: './gestionnaire-coachs.html',
  styleUrl: './gestionnaire-coachs.css'
})
export class GestionnaireCoachs implements OnInit {
  private readonly gestionnaireService = inject(GestionnaireService);

  protected readonly liens = liensGestionnaire('coachs');

  protected readonly coachs = signal<CoachSalle[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal<string | null>(null);

  ngOnInit(): void {
    this.gestionnaireService.coachs().subscribe({
      next: (coachs) => {
        this.coachs.set(coachs);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger les coachs. As-tu enregistré ta salle ?');
      }
    });
  }
}
