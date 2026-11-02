import { HttpErrorResponse } from '@angular/common/http';
import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { catchError, debounceTime, distinctUntilChanged, of, startWith, switchMap, tap } from 'rxjs';

import { Activite } from '../../models/activite';
import { Seance } from '../../models/consultation';
import { ActiviteService } from '../../services/activite.service';
import { AuthService } from '../../services/auth.service';
import { ConsultationService } from '../../services/consultation.service';
import { ReservationService } from '../../services/reservation.service';

@Component({
  selector: 'app-seances',
  imports: [DatePipe, ReactiveFormsModule, RouterLink],
  templateUrl: './seances.html'
})
export class Seances implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly consultation = inject(ConsultationService);
  private readonly activiteService = inject(ActiviteService);
  private readonly reservationService = inject(ReservationService);
  private readonly route = inject(ActivatedRoute);

  protected readonly auth = inject(AuthService);

  protected readonly filtres = this.fb.nonNullable.group({
    q: [''],
    activite: [this.route.snapshot.queryParamMap.get('activite') ?? '']
  });

  protected readonly activites = signal<Activite[]>([]);
  protected readonly seances = signal<Seance[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);
  protected readonly reservees = signal<Set<number>>(new Set());
  protected readonly enCours = signal<number | null>(null);
  protected readonly message = signal<{ texte: string; erreur: boolean } | null>(null);

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

    if (this.auth.estSportif()) {
      this.reservationService.lister().subscribe({
        next: (reservations) =>
          this.reservees.set(
            new Set(reservations.filter((r) => r.statut === 'CONFIRMEE').map((r) => r.seance.id))
          )
      });
    }
  }

  protected reserver(seance: Seance): void {
    this.enCours.set(seance.id);
    this.message.set(null);

    this.reservationService.reserver(seance.id).subscribe({
      next: () => {
        this.enCours.set(null);
        this.reservees.update((ids) => new Set(ids).add(seance.id));
        this.seances.update((liste) =>
          liste.map((s) =>
            s.id === seance.id ? { ...s, placesRestantes: Math.max(0, s.placesRestantes - 1) } : s
          )
        );
        this.message.set({ texte: `Ta place pour « ${seance.cours.titre} » est réservée.`, erreur: false });
      },
      error: (erreur: HttpErrorResponse) => {
        this.enCours.set(null);

        if (erreur.status === 401) {
          this.message.set({ texte: 'Ta session a expiré. Reconnecte-toi pour réserver.', erreur: true });
        } else {
          this.message.set({
            texte: erreur.error?.erreur ?? 'La réservation a échoué. Réessaie plus tard.',
            erreur: true
          });
        }
      }
    });
  }
}
