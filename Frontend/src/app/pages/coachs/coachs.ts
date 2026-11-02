import { Component, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { catchError, debounceTime, distinctUntilChanged, of, startWith, switchMap, tap } from 'rxjs';

import { Coach } from '../../models/consultation';
import { ConsultationService } from '../../services/consultation.service';

@Component({
  selector: 'app-coachs',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './coachs.html'
})
export class Coachs {
  private readonly consultation = inject(ConsultationService);

  protected readonly recherche = new FormControl('', { nonNullable: true });
  protected readonly coachs = signal<Coach[]>([]);
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
          this.consultation.coachs(terme).pipe(
            catchError(() => {
              this.erreur.set(true);
              return of([] as Coach[]);
            })
          )
        ),
        takeUntilDestroyed()
      )
      .subscribe((coachs) => {
        this.coachs.set(coachs);
        this.chargement.set(false);
      });
  }
}
