import { Routes } from '@angular/router';

import { connecteGuard } from './guards/connecte.guard';
import { Accueil } from './pages/accueil/accueil';
import { Activites } from './pages/activites/activites';
import { Authentification } from './pages/authentification/authentification';
import { Calendrier } from './pages/calendrier/calendrier';
import { Coach } from './pages/coach/coach';
import { Coachs } from './pages/coachs/coachs';
import { EspaceSportif } from './pages/espace-sportif/espace-sportif';
import { Reservations } from './pages/reservations/reservations';
import { Resultats } from './pages/resultats/resultats';
import { Salle } from './pages/salle/salle';
import { Salles } from './pages/salles/salles';
import { Seances } from './pages/seances/seances';
import { TableauDeBord } from './pages/tableau-de-bord/tableau-de-bord';
import { CoachSeances } from './pages/coach-seances/coach-seances';
import { CoachSportifs } from './pages/coach-sportifs/coach-sportifs';
import { CoachAvis } from './pages/coach-avis/coach-avis';
import { CoachProfil } from './pages/coach-profil/coach-profil';
import { GestionnaireTableau } from './pages/gestionnaire-tableau/gestionnaire-tableau';
import { roleGuard } from './guards/role.guard';

export const routes: Routes = [
  { path: '', pathMatch: 'full', component: Accueil },
  { path: 'resultats', component: Resultats },
  { path: 'seances', component: Seances },
  { path: 'calendrier', component: Calendrier },
  { path: 'salles', component: Salles },
  { path: 'salles/:id', component: Salle },
  { path: 'coachs', component: Coachs },
  { path: 'coachs/:id', component: Coach },
  { path: 'activites', component: Activites },
  { path: 'mon-espace', component: EspaceSportif, canActivate: [connecteGuard] },
  { path: 'reservations', component: Reservations, canActivate: [connecteGuard] },
  { path: 'tableau-de-bord', component: TableauDeBord, canActivate: [connecteGuard] },
  { path: 'coach/seances', component: CoachSeances, canActivate: [roleGuard('ROLE_COACH')] },
  { path: 'coach/sportifs', component: CoachSportifs, canActivate: [roleGuard('ROLE_COACH')] },
  { path: 'coach/avis', component: CoachAvis, canActivate: [roleGuard('ROLE_COACH')] },
  { path: 'coach/profil', component: CoachProfil, canActivate: [roleGuard('ROLE_COACH')] },
  { path: 'gestionnaire', component: GestionnaireTableau, canActivate: [roleGuard('ROLE_GESTIONNAIRE')] },
  { path: 'inscription', component: Authentification },
  { path: 'connexion', component: Authentification }
];
