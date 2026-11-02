import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';

import { Espace } from '../../components/espace/espace';
import { liensAdmin } from '../../components/espace/liens-admin';
import { AvisAdmin } from '../../models/espace-admin';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-avis',
  imports: [DatePipe, Espace],
  templateUrl: './admin-avis.html',
  styleUrl: './admin-avis.css'
})
export class AdminAvis implements OnInit {
  private readonly adminService = inject(AdminService);

  protected readonly liens = liensAdmin('avis');

  protected readonly avis = signal<AvisAdmin[]>([]);
  protected readonly chargement = signal(true);
  protected readonly aSupprimer = signal<number | null>(null);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);

  ngOnInit(): void {
    this.charger();
  }

  protected demanderSuppression(id: number): void {
    this.aSupprimer.set(id);
  }

  protected annulerSuppression(): void {
    this.aSupprimer.set(null);
  }

  protected supprimer(id: number): void {
    this.erreur.set(null);
    this.succes.set(null);

    this.adminService.supprimerAvis(id).subscribe({
      next: () => {
        this.aSupprimer.set(null);
        this.succes.set('Avis supprimé.');
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => {
        this.aSupprimer.set(null);
        this.erreur.set(erreur.error?.erreur ?? "La suppression a échoué. Réessaie plus tard.");
      }
    });
  }

  private charger(): void {
    this.adminService.avis().subscribe({
      next: (avis) => {
        this.avis.set(avis);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger les avis.');
      }
    });
  }
}
