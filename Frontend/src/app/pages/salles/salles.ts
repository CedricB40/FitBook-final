import { Component, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { catchError, debounceTime, distinctUntilChanged, of, startWith, switchMap, tap } from 'rxjs';

import { Salle } from '../../models/consultation';
import { ConsultationService } from '../../services/consultation.service';

@Component({
  selector: 'app-salles',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './salles.html'
})
export class Salles {
  private readonly consultation = inject(ConsultationService);

  protected readonly recherche = new FormControl('', { nonNullable: true });
  protected readonly salles = signal<Salle[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);

  constructor() {
    this.recherche.valueChanges
      .pipe(
        startWith(this.recherche.value),
        debounceTime(300),
        distinctUntilChanged(),
        tap(() => {
          this.chargement.set(true);
          this.erreur.set(false);
        }),
        switchMap((terme) =>
          this.consultation.salles(terme).pipe(
            catchError(() => {
              this.erreur.set(true);
              return of([] as Salle[]);
            })
          )
        ),
        takeUntilDestroyed()
      )
      .subscribe((salles) => {
        this.salles.set(salles);
        this.chargement.set(false);
      });
  }
}
