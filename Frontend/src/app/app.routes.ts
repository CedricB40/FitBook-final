import { Routes } from '@angular/router';

import { Activites } from './pages/activites/activites';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'activites' },
  { path: 'activites', component: Activites }
];
