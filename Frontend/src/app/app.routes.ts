import { Routes } from '@angular/router';

import { connecteGuard } from './guards/connecte.guard';
import { Accueil } from './pages/accueil/accueil';
import { Activites } from './pages/activites/activites';
import { Coach } from './pages/coach/coach';
import { Coachs } from './pages/coachs/coachs';
import { Connexion } from './pages/connexion/connexion';
import { Inscription } from './pages/inscription/inscription';
import { Reservations } from './pages/reservations/reservations';
import { Resultats } from './pages/resultats/resultats';
import { Salle } from './pages/salle/salle';
import { Salles } from './pages/salles/salles';
import { Seances } from './pages/seances/seances';
import { TableauDeBord } from './pages/tableau-de-bord/tableau-de-bord';

export const routes: Routes = [
  { path: '', pathMatch: 'full', component: Accueil },
  { path: 'resultats', component: Resultats },
  { path: 'seances', component: Seances },
  { path: 'salles', component: Salles },
  { path: 'salles/:id', component: Salle },
  { path: 'coachs', component: Coachs },
  { path: 'coachs/:id', component: Coach },
  { path: 'activites', component: Activites },
  { path: 'reservations', component: Reservations, canActivate: [connecteGuard] },
  { path: 'tableau-de-bord', component: TableauDeBord, canActivate: [connecteGuard] },
  { path: 'inscription', component: Inscription },
  { path: 'connexion', component: Connexion }
];
