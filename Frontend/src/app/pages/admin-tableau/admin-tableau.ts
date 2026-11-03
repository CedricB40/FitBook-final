import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';

import { Espace } from '../../components/espace/espace';
import { liensAdmin } from '../../components/espace/liens-admin';
import {
  AvisAdmin,
  StatistiquesAdmin,
  UtilisateurAdmin,
  libelleRole
} from '../../models/espace-admin';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-tableau',
  imports: [DatePipe, RouterLink, Espace],
  templateUrl: './admin-tableau.html',
  styleUrl: './admin-tableau.css'
})
export class AdminTableau implements OnInit {
  private readonly adminService = inject(AdminService);

  protected readonly liens = liensAdmin('tableau');
  protected readonly libelleRole = libelleRole;

  protected readonly statistiques = signal<StatistiquesAdmin | null>(null);
  protected readonly utilisateurs = signal<UtilisateurAdmin[]>([]);
  protected readonly avis = signal<AvisAdmin[]>([]);
  protected readonly erreur = signal<string | null>(null);

  ngOnInit(): void {
    this.adminService.statistiques().subscribe({
      next: (statistiques) => this.statistiques.set(statistiques),
      error: () => this.erreur.set("Impossible de charger les statistiques. Réessaie plus tard.")
    });

    this.adminService.utilisateurs().subscribe({
      next: (utilisateurs) => this.utilisateurs.set(utilisateurs.slice(0, 5)),
      error: () => undefined
    });

    this.adminService.avis().subscribe({
      next: (avis) => this.avis.set(avis.slice(0, 3)),
      error: () => undefined
    });
  }
}
