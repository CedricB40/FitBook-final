import { Component, computed, input } from '@angular/core';

import { JOURS_SEMAINE } from '../../models/creneau';

export interface CreneauPublic {
  jourSemaine: number;
  debut: string;
  fin: string;
}

/**
 * Affiche en lecture seule une semaine de créneaux (horaires d'une salle, disponibilités d'un coach).
 */
@Component({
  selector: 'app-creneaux-lecture',
  templateUrl: './creneaux-lecture.html',
  styleUrl: './creneaux-lecture.css'
})
export class CreneauxLecture {
  readonly creneaux = input.required<CreneauPublic[]>();
  readonly libelleVide = input('Fermé');

  protected readonly parJour = computed(() =>
    JOURS_SEMAINE.map((jour) => ({
      ...jour,
      creneaux: this.creneaux().filter((creneau) => creneau.jourSemaine === jour.numero)
    }))
  );
}
