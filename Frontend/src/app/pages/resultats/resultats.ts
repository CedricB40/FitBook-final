import { Component, computed, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, ParamMap, Router, RouterLink } from '@angular/router';
import { catchError, of, switchMap, tap } from 'rxjs';

import { Activite } from '../../models/activite';
import { FiltresRecherche, ReponseRecherche, ResultatRecherche, TypeResultat } from '../../models/recherche';
import { ActiviteService } from '../../services/activite.service';
import { ConsultationService } from '../../services/consultation.service';

@Component({
  selector: 'app-resultats',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './resultats.html',
  styleUrl: './resultats.css'
})
export class Resultats {
  private readonly fb = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly consultation = inject(ConsultationService);
  private readonly activiteService = inject(ActiviteService);

  protected readonly filtres = this.fb.nonNullable.group({
    salle: [false],
    coach: [false],
    cours: [false],
    activite: [''],
    ville: [''],
    q: ['']
  });

  protected readonly activites = signal<Activite[]>([]);
  protected readonly reponse = signal<ReponseRecherche | null>(null);
  protected readonly parametres = signal<ParamMap | null>(null);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);

  protected readonly titre = computed(() => {
    const parametres = this.parametres();
    const morceaux: string[] = [];

    const idActivite = parametres?.get('activite');
    const activite = this.activites().find((a) => String(a.id) === idActivite);

    if (activite) {
      morceaux.push(activite.nom);
    }
    if (parametres?.get('q')) {
      morceaux.push(parametres.get('q') as string);
    }
    if (parametres?.get('ville')) {
      morceaux.push(parametres.get('ville') as string);
    }

    return morceaux.length > 0 ? morceaux.join(' · ') : 'Tous les résultats';
  });

  protected readonly numeros = computed(() => {
    const reponse = this.reponse();
    const numeros: (number | null)[] = [];

    if (!reponse) {
      return numeros;
    }

    for (let n = 1; n <= reponse.pages; n++) {
      if (n === 1 || n === reponse.pages || Math.abs(n - reponse.page) <= 1) {
        numeros.push(n);
      } else if (numeros[numeros.length - 1] !== null) {
        numeros.push(null);
      }
    }

    return numeros;
  });

  constructor() {
    this.activiteService.lister().subscribe({
      next: (activites) => this.activites.set(activites)
    });

    this.route.queryParamMap
      .pipe(
        tap((parametres) => {
          this.parametres.set(parametres);
          this.remplirFormulaire(parametres);
          this.chargement.set(true);
          this.erreur.set(false);
        }),
        switchMap((parametres) =>
          this.consultation.rechercher(this.versFiltres(parametres)).pipe(
            catchError(() => {
              this.erreur.set(true);
              return of(null);
            })
          )
        ),
        takeUntilDestroyed()
      )
      .subscribe((reponse) => {
        this.reponse.set(reponse);
        this.chargement.set(false);
      });
  }

  protected choisirActivite(id: number): void {
    const courante = this.filtres.controls.activite.value;
    this.filtres.controls.activite.setValue(courante === String(id) ? '' : String(id));
  }

  protected appliquer(): void {
    const valeurs = this.filtres.getRawValue();
    const types: TypeResultat[] = [];

    if (valeurs.salle) {
      types.push('salle');
    }
    if (valeurs.coach) {
      types.push('coach');
    }
    if (valeurs.cours) {
      types.push('cours');
    }

    this.router.navigate(['/resultats'], {
      queryParams: {
        types: types.length > 0 ? types.join(',') : null,
        activite: valeurs.activite || null,
        ville: valeurs.ville.trim() || null,
        q: valeurs.q.trim() || null,
        page: null
      }
    });
  }

  protected aller(page: number): void {
    this.router.navigate(['/resultats'], {
      queryParams: { page: page > 1 ? page : null },
      queryParamsHandling: 'merge'
    });
  }

  protected lien(resultat: ResultatRecherche): (string | number)[] {
    if (resultat.type === 'coach') {
      return ['/coachs', resultat.id];
    }

    return ['/salles', resultat.type === 'cours' ? (resultat.salleId ?? resultat.id) : resultat.id];
  }

  protected libelleType(type: TypeResultat): string {
    return type === 'salle' ? 'Salle' : type === 'coach' ? 'Coach' : 'Cours';
  }

  private remplirFormulaire(parametres: ParamMap): void {
    const types = (parametres.get('types') ?? '').split(',');

    this.filtres.patchValue(
      {
        salle: types.includes('salle'),
        coach: types.includes('coach'),
        cours: types.includes('cours'),
        activite: parametres.get('activite') ?? '',
        ville: parametres.get('ville') ?? '',
        q: parametres.get('q') ?? ''
      },
      { emitEvent: false }
    );
  }

  private versFiltres(parametres: ParamMap): FiltresRecherche {
    const types = (parametres.get('types') ?? '')
      .split(',')
      .filter((t): t is TypeResultat => t === 'salle' || t === 'coach' || t === 'cours');
    const activite = parametres.get('activite');
    const page = parametres.get('page');

    return {
      types: types.length > 0 ? types : undefined,
      q: parametres.get('q') ?? undefined,
      ville: parametres.get('ville') ?? undefined,
      activite: activite ? Number(activite) : null,
      page: page ? Number(page) : 1
    };
  }
}
