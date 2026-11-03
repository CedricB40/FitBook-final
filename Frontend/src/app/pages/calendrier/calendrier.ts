import { DatePipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';

import { SeanceLigne } from '../../components/seance-ligne/seance-ligne';
import { Seance } from '../../models/consultation';
import { ConsultationService } from '../../services/consultation.service';

interface CaseJour {
  jour: number;
  cle: string;
  aujourdhui: boolean;
  nombreSeances: number;
}

@Component({
  selector: 'app-calendrier',
  imports: [DatePipe, RouterLink, SeanceLigne],
  templateUrl: './calendrier.html',
  styleUrl: './calendrier.css'
})
export class Calendrier implements OnInit {
  private readonly consultation = inject(ConsultationService);
  private readonly route = inject(ActivatedRoute);

  protected readonly joursSemaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

  protected readonly seances = signal<Seance[]>([]);
  protected readonly chargement = signal(true);
  protected readonly erreur = signal(false);
  protected readonly mois = signal(this.debutDuMois(new Date()));
  protected readonly jourSelectionne = signal(this.cle(new Date()));
  protected readonly lieu = signal('');
  protected readonly lienRetour = signal<(string | number)[]>(['/resultats']);
  protected readonly masquer = signal<'salle' | 'coach' | null>(null);

  protected readonly seancesParJour = computed(() => {
    const parJour = new Map<string, Seance[]>();

    for (const seance of this.seances()) {
      const cle = seance.debut.slice(0, 10);
      parJour.set(cle, [...(parJour.get(cle) ?? []), seance]);
    }

    return parJour;
  });

  protected readonly cases = computed(() => {
    const mois = this.mois();
    const decalage = (new Date(mois.getFullYear(), mois.getMonth(), 1).getDay() + 6) % 7;
    const nombreJours = new Date(mois.getFullYear(), mois.getMonth() + 1, 0).getDate();
    const aujourdhui = this.cle(new Date());
    const cases: (CaseJour | null)[] = Array(decalage).fill(null);

    for (let jour = 1; jour <= nombreJours; jour++) {
      const cle = this.cle(new Date(mois.getFullYear(), mois.getMonth(), jour));

      cases.push({
        jour,
        cle,
        aujourdhui: cle === aujourdhui,
        nombreSeances: this.seancesParJour().get(cle)?.length ?? 0
      });
    }

    return cases;
  });

  protected readonly seancesDuJour = computed(() => this.seancesParJour().get(this.jourSelectionne()) ?? []);

  protected readonly dateSelectionnee = computed(() => new Date(this.jourSelectionne() + 'T00:00:00'));

  ngOnInit(): void {
    const parametres = this.route.snapshot.queryParamMap;
    const salle = parametres.get('salle');
    const coach = parametres.get('coach');

    if (salle) {
      this.masquer.set('salle');
      this.lienRetour.set(['/salles', Number(salle)]);
      this.consultation.salle(Number(salle)).subscribe({
        next: (detail) => this.lieu.set(detail.nom)
      });
    } else if (coach) {
      this.masquer.set('coach');
      this.lienRetour.set(['/coachs', Number(coach)]);
      this.consultation.coach(Number(coach)).subscribe({
        next: (detail) => this.lieu.set(`${detail.prenom} ${detail.nom}`)
      });
    }

    this.consultation
      .seances({
        salle: salle ? Number(salle) : undefined,
        coach: coach ? Number(coach) : undefined
      })
      .subscribe({
        next: (seances) => {
          this.seances.set(seances);
          this.chargement.set(false);

          if (seances.length > 0) {
            const premiere = new Date(seances[0].debut);
            this.mois.set(this.debutDuMois(premiere));
            this.jourSelectionne.set(this.cle(premiere));
          }
        },
        error: () => {
          this.erreur.set(true);
          this.chargement.set(false);
        }
      });
  }

  protected changerMois(decalage: number): void {
    this.mois.update((mois) => new Date(mois.getFullYear(), mois.getMonth() + decalage, 1));
  }

  protected choisir(cle: string): void {
    this.jourSelectionne.set(cle);
  }

  private debutDuMois(date: Date): Date {
    return new Date(date.getFullYear(), date.getMonth(), 1);
  }

  private cle(date: Date): string {
    const mois = String(date.getMonth() + 1).padStart(2, '0');
    const jour = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${mois}-${jour}`;
  }
}
