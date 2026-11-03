import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { AuthService } from '../services/auth.service';

export const roleGuard = (role: string): CanActivateFn => () => {
  const auth = inject(AuthService);
  const router = inject(Router);

  if (!auth.estConnecte()) {
    return router.createUrlTree(['/connexion']);
  }

  return auth.aRole(role) ? true : router.createUrlTree(['/']);
};
