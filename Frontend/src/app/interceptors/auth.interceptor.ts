import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';

import { API_URL } from '../config/api';
import { AuthService } from '../services/auth.service';

export const authInterceptor: HttpInterceptorFn = (requete, suivant) => {
  const auth = inject(AuthService);
  const jeton = auth.jeton();

  if (!jeton || !auth.estConnecte() || !requete.url.startsWith(API_URL)) {
    return suivant(requete);
  }

  return suivant(requete.clone({ setHeaders: { Authorization: `Bearer ${jeton}` } }));
};
