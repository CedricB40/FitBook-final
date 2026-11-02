import { Routes } from '@angular/router';

import { Activites } from './pages/activites/activites';
import { Coach } from './pages/coach/coach';
import { Coachs } from './pages/coachs/coachs';
import { Connexion } from './pages/connexion/connexion';
import { Inscription } from './pages/inscription/inscription';
import { Salle } from './pages/salle/salle';
import { Salles } from './pages/salles/salles';
import { Seances } from './pages/seances/seances';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'seances' },
  { path: 'seances', component: Seances },
  { path: 'salles', component: Salles },
  { path: 'salles/:id', component: Salle },
  { path: 'coachs', component: Coachs },
  { path: 'coachs/:id', component: Coach },
  { path: 'activites', component: Activites },
  { path: 'inscription', component: Inscription },
  { path: 'connexion', component: Connexion }
];
