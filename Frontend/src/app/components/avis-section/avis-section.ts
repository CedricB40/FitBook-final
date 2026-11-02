import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe, DecimalPipe } from '@angular/common';
import { Component, computed, inject, input, output, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { CibleAvis, ResumeAvis } from '../../models/avis';
import { AuthService } from '../../services/auth.service';
import { AvisService } from '../../services/avis.service';

@Component({
  selector: 'app-avis-section',
  imports: [DatePipe, DecimalPipe, ReactiveFormsModule],
  templateUrl: './avis-section.html',
  styleUrl: './avis-section.css'
})
export class AvisSection {
  readonly cible = input.required<CibleAvis>();
  readonly id = input.required<number>();
  readonly resume = input.required<ResumeAvis | null>();
  readonly modifie = output<void>();

  protected readonly auth = inject(AuthService);
  private readonly avisService = inject(AvisService);
  private readonly fb = inject(FormBuilder);

  protected readonly niveaux = [5, 4, 3, 2, 1];
  protected readonly etoiles = [1, 2, 3, 4, 5];

  protected readonly formulaireOuvert = signal(false);
  protected readonly envoi = signal(false);
  protected readonly message = signal<{ texte: string; erreur: boolean } | null>(null);

  protected readonly formulaire = this.fb.nonNullable.group({
    note: [0, [Validators.min(1), Validators.max(5)]],
    commentaire: ['', [Validators.maxLength(1000)]]
  });

  protected readonly etoilesMoyenne = computed(() => Math.round(this.resume()?.moyenne ?? 0));

  protected pourcentage(niveau: number): number {
    const resume = this.resume();

    return resume && resume.total > 0 ? ((resume.repartition[niveau] ?? 0) / resume.total) * 100 : 0;
  }

  protected choisirNote(note: number): void {
    this.formulaire.controls.note.setValue(note);
  }

  protected ouvrir(): void {
    this.message.set(null);
    this.formulaire.reset({ note: 0, commentaire: '' });
    this.formulaireOuvert.set(true);
  }

  protected fermer(): void {
    this.formulaireOuvert.set(false);
  }

  protected publier(): void {
    const valeurs = this.formulaire.getRawValue();

    if (valeurs.note < 1) {
      this.message.set({ texte: 'Choisis une note de 1 à 5 étoiles.', erreur: true });
      return;
    }

    this.envoi.set(true);
    this.message.set(null);

    this.avisService
      .publier({
        cible: this.cible(),
        id: this.id(),
        note: valeurs.note,
        commentaire: valeurs.commentaire.trim() || undefined
      })
      .subscribe({
        next: () => {
          this.envoi.set(false);
          this.formulaireOuvert.set(false);
          this.message.set({ texte: 'Merci, ton avis a bien été publié.', erreur: false });
          this.modifie.emit();
        },
        error: (erreur: HttpErrorResponse) => {
          this.envoi.set(false);
          this.message.set({
            texte:
              erreur.error?.erreur ??
              Object.values<string>(erreur.error?.erreurs ?? {})[0] ??
              "La publication de l'avis a échoué. Réessaie plus tard.",
            erreur: true
          });
        }
      });
  }

  protected supprimer(idAvis: number): void {
    this.avisService.supprimer(idAvis).subscribe({
      next: () => {
        this.message.set({ texte: 'Ton avis a été supprimé.', erreur: false });
        this.modifie.emit();
      },
      error: () => this.message.set({ texte: 'La suppression a échoué. Réessaie plus tard.', erreur: true })
    });
  }
}
