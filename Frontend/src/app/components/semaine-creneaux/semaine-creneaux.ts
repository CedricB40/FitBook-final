import { Component, computed, input, output, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { inject } from '@angular/core';

import { Creneau, JOURS_SEMAINE, NouveauCreneau } from '../../models/creneau';

/**
 * Affiche une semaine de créneaux (un bloc par jour) et un formulaire pour en ajouter.
 * Utilisé pour les disponibilités d'un coach et pour les horaires d'une salle.
 */
@Component({
  selector: 'app-semaine-creneaux',
  imports: [ReactiveFormsModule],
  templateUrl: './semaine-creneaux.html',
  styleUrl: './semaine-creneaux.css'
})
export class SemaineCreneaux {
  private readonly fb = inject(FormBuilder);

  readonly creneaux = input.required<Creneau[]>();
  readonly libelleVide = input('Aucun créneau');
  readonly ajouter = output<NouveauCreneau>();
  readonly supprimer = output<number>();

  protected readonly jours = JOURS_SEMAINE;
  protected readonly aSupprimer = signal<number | null>(null);
  protected readonly erreur = signal<string | null>(null);

  protected readonly parJour = computed(() =>
    this.jours.map((jour) => ({
      ...jour,
      creneaux: this.creneaux().filter((creneau) => creneau.jourSemaine === jour.numero)
    }))
  );

  protected readonly formulaire = this.fb.nonNullable.group({
    jourSemaine: [1, [Validators.required]],
    debut: ['', [Validators.required]],
    fin: ['', [Validators.required]]
  });

  protected valider(): void {
    this.erreur.set(null);

    if (this.formulaire.invalid) {
      this.formulaire.markAllAsTouched();
      this.erreur.set('Choisis un jour, une heure de début et une heure de fin.');
      return;
    }

    const { jourSemaine, debut, fin } = this.formulaire.getRawValue();

    if (fin <= debut) {
      this.erreur.set("L'heure de fin doit être après l'heure de début.");
      return;
    }

    this.ajouter.emit({ jourSemaine: Number(jourSemaine), debut, fin });
    this.formulaire.patchValue({ debut: '', fin: '' });
  }

  protected demander(id: number): void {
    this.aSupprimer.set(id);
  }

  protected annuler(): void {
    this.aSupprimer.set(null);
  }

  protected confirmer(id: number): void {
    this.aSupprimer.set(null);
    this.supprimer.emit(id);
  }
}
