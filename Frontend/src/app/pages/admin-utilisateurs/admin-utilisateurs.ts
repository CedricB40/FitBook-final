import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { Espace } from '../../components/espace/espace';
import { liensAdmin } from '../../components/espace/liens-admin';
import { ROLES_ADMIN, UtilisateurAdmin, libelleRole } from '../../models/espace-admin';
import { AdminService } from '../../services/admin.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-admin-utilisateurs',
  imports: [ReactiveFormsModule, Espace],
  templateUrl: './admin-utilisateurs.html',
  styleUrl: './admin-utilisateurs.css'
})
export class AdminUtilisateurs implements OnInit {
  private readonly adminService = inject(AdminService);
  private readonly auth = inject(AuthService);
  private readonly fb = inject(FormBuilder);

  protected readonly liens = liensAdmin('utilisateurs');
  protected readonly roles = ROLES_ADMIN;
  protected readonly libelleRole = libelleRole;

  protected readonly utilisateurs = signal<UtilisateurAdmin[]>([]);
  protected readonly recherche = signal('');
  protected readonly chargement = signal(true);
  protected readonly enEdition = signal<number | null>(null);
  protected readonly aSupprimer = signal<number | null>(null);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);
  protected readonly envoi = signal(false);

  protected readonly affiches = computed(() => {
    const terme = this.recherche().trim().toLowerCase();

    if (terme === '') {
      return this.utilisateurs();
    }

    return this.utilisateurs().filter((utilisateur) =>
      `${utilisateur.prenom} ${utilisateur.nom} ${utilisateur.email}`.toLowerCase().includes(terme)
    );
  });

  protected readonly formulaire = this.fb.nonNullable.group({
    prenom: ['', [Validators.required]],
    nom: ['', [Validators.required]],
    email: ['', [Validators.required, Validators.email]],
    role: ['ROLE_SPORTIF', [Validators.required]]
  });

  ngOnInit(): void {
    this.charger();
  }

  protected estMoi(utilisateur: UtilisateurAdmin): boolean {
    return utilisateur.email === this.auth.utilisateur()?.email;
  }

  protected chercher(evenement: Event): void {
    this.recherche.set((evenement.target as HTMLInputElement).value);
  }

  protected modifier(utilisateur: UtilisateurAdmin): void {
    this.erreur.set(null);
    this.succes.set(null);
    this.aSupprimer.set(null);
    this.enEdition.set(utilisateur.id);
    this.formulaire.setValue({
      prenom: utilisateur.prenom,
      nom: utilisateur.nom,
      email: utilisateur.email,
      role: utilisateur.role
    });
  }

  protected annulerEdition(): void {
    this.enEdition.set(null);
  }

  protected enregistrer(): void {
    const id = this.enEdition();

    if (id === null) {
      return;
    }

    this.erreur.set(null);
    this.succes.set(null);

    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      this.erreur.set('Le prénom, le nom et une adresse e-mail valide sont obligatoires.');
      return;
    }

    this.envoi.set(true);

    this.adminService.modifierUtilisateur(id, this.formulaire.getRawValue()).subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set('Utilisateur modifié.');
        this.enEdition.set(null);
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => this.echec(erreur)
    });
  }

  protected demanderSuppression(id: number): void {
    this.enEdition.set(null);
    this.aSupprimer.set(id);
  }

  protected annulerSuppression(): void {
    this.aSupprimer.set(null);
  }

  protected supprimer(id: number): void {
    this.erreur.set(null);
    this.succes.set(null);

    this.adminService.supprimerUtilisateur(id).subscribe({
      next: () => {
        this.aSupprimer.set(null);
        this.succes.set('Utilisateur supprimé.');
        this.charger();
      },
      error: (erreur: HttpErrorResponse) => {
        this.aSupprimer.set(null);
        this.echec(erreur);
      }
    });
  }

  private charger(): void {
    this.adminService.utilisateurs().subscribe({
      next: (utilisateurs) => {
        this.utilisateurs.set(utilisateurs);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.erreur.set('Impossible de charger les utilisateurs.');
      }
    });
  }

  private echec(erreur: HttpErrorResponse): void {
    this.envoi.set(false);
    const details = erreur.error?.erreurs;

    this.erreur.set(
      details && typeof details === 'object'
        ? Object.values(details).join(' ')
        : (erreur.error?.erreur ?? "L'opération a échoué. Réessaie plus tard.")
    );
  }
}
