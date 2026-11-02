import { Routes } from '@angular/router';

import { Activites } from './pages/activites/activites';
import { Inscription } from './pages/inscription/inscription';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'activites' },
  { path: 'activites', component: Activites },
  { path: 'inscription', component: Inscription }
];
