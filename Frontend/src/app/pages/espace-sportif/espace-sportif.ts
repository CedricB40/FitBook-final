import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';

import { BoutonFavori } from '../../components/bouton-favori/bouton-favori';
import { Espace, LienEspace } from '../../components/espace/espace';
import { ResultatRecherche } from '../../models/recherche';
import { Reservation } from '../../models/reservation';
import { FavorisStore } from '../../services/favoris.store';
import { ProfilService } from '../../services/profil.service';
import { ReservationService } from '../../services/reservation.service';

@Component({
  selector: 'app-espace-sportif',
  imports: [DatePipe, ReactiveFormsModule, RouterLink, Espace, BoutonFavori],
  templateUrl: './espace-sportif.html',
  styleUrl: './espace-sportif.css'
})
export class EspaceSportif implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly profilService = inject(ProfilService);
  private readonly reservationService = inject(ReservationService);
  protected readonly favorisStore = inject(FavorisStore);

  protected readonly liens: LienEspace[] = [
    { libelle: 'Mes informations', route: '/mon-espace', fragment: 'profil', actif: true },
    { libelle: 'Mes favoris', route: '/mon-espace', fragment: 'favoris' },
    { libelle: 'Mes réservations', route: '/mon-espace', fragment: 'reservations' }
  ];

  protected readonly formulaire = this.fb.nonNullable.group({
    nom: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
    prenom: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
    email: ['', [Validators.required, Validators.email, Validators.maxLength(255)]]
  });

  protected readonly envoi = signal(false);
  protected readonly message = signal<{ texte: string; erreur: boolean } | null>(null);
  protected readonly initiales = signal('');
  protected readonly reservations = signal<Reservation[]>([]);
  protected readonly enCours = signal<number | null>(null);
  protected readonly messageReservation = signal<{ texte: string; erreur: boolean } | null>(null);

  protected readonly favoris = computed(() => {
    const favoris = this.favorisStore.favoris();

    return [...favoris.salles, ...favoris.coachs, ...favoris.cours];
  });

  protected readonly aVenir = computed(() =>
    this.reservations()
      .filter((r) => r.statut === 'CONFIRMEE' && new Date(r.seance.debut) > new Date())
      .sort((a, b) => a.seance.debut.localeCompare(b.seance.debut))
  );

  ngOnInit(): void {
    this.profilService.lire().subscribe({
      next: (profil) => {
        this.formulaire.patchValue(profil);
        this.initiales.set(`${profil.prenom.charAt(0)}${profil.nom.charAt(0)}`.toUpperCase());
      }
    });

    this.chargerReservations();
  }

  protected erreur(champ: string): string | null {
    const controle = this.formulaire.get(champ);

    if (!controle || !controle.invalid || !(controle.touched || controle.dirty)) {
      return null;
    }

    const erreurs = controle.errors ?? {};

    if (erreurs['serveur']) {
      return erreurs['serveur'];
    }
    if (erreurs['required']) {
      return 'Ce champ est obligatoire.';
    }
    if (erreurs['email']) {
      return 'Adresse e-mail invalide.';
    }
    if (erreurs['minlength']) {
      return `Minimum ${erreurs['minlength'].requiredLength} caractères.`;
    }
    if (erreurs['maxlength']) {
      return `Maximum ${erreurs['maxlength'].requiredLength} caractères.`;
    }

    return 'Valeur invalide.';
  }

  protected enregistrer(): void {
    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      return;
    }

    this.envoi.set(true);
    this.message.set(null);

    this.profilService.enregistrer(this.formulaire.getRawValue()).subscribe({
      next: (reponse) => {
        this.envoi.set(false);
        this.initiales.set(`${reponse.profil.prenom.charAt(0)}${reponse.profil.nom.charAt(0)}`.toUpperCase());
        this.message.set({ texte: 'Ton profil a bien été enregistré.', erreur: false });
      },
      error: (erreur: HttpErrorResponse) => {
        this.envoi.set(false);
        const erreursServeur = erreur.error?.erreurs;

        if ((erreur.status === 409 || erreur.status === 422) && erreursServeur) {
          for (const [champ, texte] of Object.entries(erreursServeur)) {
            const controle = this.formulaire.get(champ);
            controle?.setErrors({ serveur: String(texte) });
            controle?.markAsTouched();
          }
        } else {
          this.message.set({ texte: "L'enregistrement a échoué. Réessaie plus tard.", erreur: true });
        }
      }
    });
  }

  protected annuler(reservation: Reservation): void {
    this.enCours.set(reservation.id);
    this.messageReservation.set(null);

    this.reservationService.annuler(reservation.id).subscribe({
      next: (misAJour) => {
        this.enCours.set(null);
        this.reservations.update((liste) => liste.map((r) => (r.id === misAJour.id ? misAJour : r)));
        this.messageReservation.set({ texte: 'Ta réservation a bien été annulée.', erreur: false });
      },
      error: (erreur: HttpErrorResponse) => {
        this.enCours.set(null);
        this.messageReservation.set({
          texte: erreur.error?.erreur ?? "L'annulation a échoué. Réessaie plus tard.",
          erreur: true
        });
      }
    });
  }

  protected lien(favori: ResultatRecherche): (string | number)[] {
    if (favori.type === 'coach') {
      return ['/coachs', favori.id];
    }

    return ['/salles', favori.type === 'cours' ? (favori.salleId ?? favori.id) : favori.id];
  }

  protected libelleType(type: string): string {
    return type === 'salle' ? 'Salle' : type === 'coach' ? 'Coach' : 'Cours';
  }

  private chargerReservations(): void {
    this.reservationService.lister().subscribe({
      next: (reservations) => this.reservations.set(reservations)
    });
  }
}
