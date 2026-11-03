import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { Espace } from '../../components/espace/espace';
import { liensCoach } from '../../components/espace/liens';
import { CoachService } from '../../services/coach.service';

@Component({
  selector: 'app-coach-profil',
  imports: [ReactiveFormsModule, Espace],
  templateUrl: './coach-profil.html',
  styleUrl: './coach-profil.css'
})
export class CoachProfil implements OnInit {
  private readonly coachService = inject(CoachService);
  private readonly fb = inject(FormBuilder);

  protected readonly liens = liensCoach('profil');

  protected readonly existe = signal(false);
  protected readonly chargement = signal(true);
  protected readonly envoi = signal(false);
  protected readonly erreur = signal<string | null>(null);
  protected readonly succes = signal<string | null>(null);

  protected readonly formulaire = this.fb.nonNullable.group({
    specialite: ['', [Validators.required, Validators.maxLength(255)]],
    biographie: [''],
    photo: ['', [Validators.maxLength(255)]]
  });

  ngOnInit(): void {
    this.coachService.profil().subscribe({
      next: (profil) => {
        this.existe.set(true);
        this.formulaire.setValue({
          specialite: profil.specialite ?? '',
          biographie: profil.biographie ?? '',
          photo: profil.photo ?? ''
        });
        this.chargement.set(false);
      },
      error: (erreur: HttpErrorResponse) => {
        this.chargement.set(false);

        if (erreur.status !== 404) {
          this.erreur.set('Impossible de charger ton profil.');
        }
      }
    });
  }

  protected enregistrer(): void {
    this.erreur.set(null);
    this.succes.set(null);

    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      this.erreur.set('Indique ta spécialité (255 caractères maximum).');
      return;
    }

    const { specialite, biographie, photo } = this.formulaire.getRawValue();
    const donnees = {
      specialite: specialite.trim(),
      biographie: biographie.trim() === '' ? null : biographie.trim(),
      photo: photo.trim() === '' ? null : photo.trim()
    };

    const requete = this.existe()
      ? this.coachService.modifierProfil(donnees)
      : this.coachService.creerProfil(donnees);

    this.envoi.set(true);

    requete.subscribe({
      next: () => {
        this.envoi.set(false);
        this.succes.set(this.existe() ? 'Profil mis à jour.' : 'Profil créé.');
        this.existe.set(true);
      },
      error: (erreur: HttpErrorResponse) => {
        this.envoi.set(false);
        const details = erreur.error?.erreurs;

        this.erreur.set(
          details && typeof details === 'object'
            ? Object.values(details).join(' ')
            : (erreur.error?.erreur ?? "L'enregistrement a échoué. Réessaie plus tard.")
        );
      }
    });
  }
}
