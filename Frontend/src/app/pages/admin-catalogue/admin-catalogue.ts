import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { Espace } from '../../components/espace/espace';
import { liensAdmin } from '../../components/espace/liens-admin';
import { ActiviteAdmin } from '../../models/espace-admin';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-catalogue',
  imports: [ReactiveFormsModule, Espace],
  templateUrl: './admin-catalogue.html',
  styleUrl: './admin-catalogue.css'
})
export class AdminCatalogue implements OnInit {
  private readonly adminService = inject(AdminService);
  private readonly fb = inject(FormBuilder);

  protected readonly liens = liensAdmin('catalogue');

  protected readonly activites = signal<ActiviteAdmin[]>([]);
  protected readonly chargement = signal(true);
  protected readonly formulaireOuvert = signal(false);
  protected readonly enEdition = signal<number | null>(null);
  protected readonly aSupprimer = signal<number | null>(null);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);
  protected readonly envoi = signal(false);

  protected readonly formulaire = this.fb.nonNullable.group({
    nom: ['', [Validators.required, Validators.maxLength(255)]],
    description: ['', [Validators.required]]
  });

  ngOnInit(): void {
    this.charger();
  }

  protected ouvrirCreation(): void {
    this.erreur.set(null);
    this.succes.set(null);
    this.enEdition.set(null);
    this.formulaire.reset({ nom: '', description: '' });
    this.formulaireOuvert.set(true);
  }

  protected ouvrirModification(activite: ActiviteAdmin): void {
    this.erreur.set(null);
    this.succes.set(null);
    this.aSupprimer.set(null);
    this.enEdition.set(activite.id);
    this.formulaire.setValue({ nom: activite.nom, description: activite.description });
    this.formulaireOuvert.set(true);
  }

  protected fermer(): void {
    this.formulaireOuvert.set(false);
    this.enEdition.set(null);
  }

  protected enregistrer(): void {
    this.erreur.set(null);
    this.succes.set(null);

    const valeurs = this.formulaire.getRawValue();
    const donnees = { nom: valeurs.nom.trim(), description: valeurs.description.trim() };

    if (donnees.nom === '' || donnees.description === '') {
      this.formulaire.markAllAsTouched();
      this.erreur.set('Le nom et la description sont obligatoires.');
      return;
    }

    const id = this.enEdition();
    const requete = id === null
      ? this.adminService.creerActivite(donnees)
      : this.adminService.modifierActivite(id, donnees);

    this.envoi.set(true);

    requete.subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set(id === null ? 'Activité créée.' : 'Activité modifiée.');
        this.fermer();
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => {
        this.envoi.set(false);
        this.erreur.set(this.message(erreur));
      }
    });
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

    this.adminService.supprimerActivite(id).subscribe({
      next: () => {
        this.aSupprimer.set(null);
        this.succes.set('Activité supprimée.');
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => {
        this.aSupprimer.set(null);
        this.erreur.set(this.message(erreur));
      }
    });
  }

  private charger(): void {
    this.adminService.activites().subscribe({
      next: (activites) => {
        this.activites.set(activites);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger le catalogue.');
      }
    });
  }

  private message(erreur: HttpErrorResponse): string {
    const details = erreur.error?.erreurs;

    return details && typeof details === 'object'
      ? Object.values(details).join(' ')
      : (erreur.error?.erreur ?? "L'opération a échoué. Réessaie plus tard.");
  }
}
