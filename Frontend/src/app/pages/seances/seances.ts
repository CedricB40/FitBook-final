import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { catchError, debounceTime, distinctUntilChanged, of, startWith, switchMap, tap } from 'rxjs';

import { Activite } from '../../models/activite';
import { Seance } from '../../models/consultation';
import { ActiviteService } from '../../services/activite.service';
import { ConsultationService } from '../../services/consultation.service';

@Component({
  selector: 'app-seances',
  imports: [DatePipe, ReactiveFormsModule, RouterLink],
  templateUrl: './seances.html'
})
export class Seances implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly consultation = inject(ConsultationService);
  private readonly activiteService = inject(ActiviteService);
  private readonly route = inject(ActivatedRoute);

  protected readonly filtres = this.fb.nonNullable.group({
    q: [''],
    activite: [this.route.snapshot.queryParamMap.get('activite') ?? '']
  });

  protected readonly activites = signal<Activite[]>([]);
  protected readonly seances = signal<Seance[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);

  constructor() {
    this.filtres.valueChanges
      .pipe(
        startWith(this.filtres.getRawValue()),
        debounceTime(300),
        distinctUntilChanged((a, b) => a.q === b.q && a.activite === b.activite),
        tap(() => {
          this.chargement.set(true);
          this.erreur.set(false);
        }),
        switchMap((valeurs) =>
          this.consultation
            .seances({
              q: valeurs.q,
              activite: valeurs.activite ? Number(valeurs.activite) : null
            })
            .pipe(
              catchError(() => {
                this.erreur.set(true);
                return of([] as Seance[]);
              })
            )
        ),
        takeUntilDestroyed()
      )
      .subscribe((seances) => {
        this.seances.set(seances);
        this.chargement.set(false);
      });
  }

  ngOnInit(): void {
    this.activiteService.lister().subscribe({
      next: (activites) => this.activites.set(activites)
    });
  }
}
