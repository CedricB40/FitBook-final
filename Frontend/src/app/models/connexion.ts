export interface IdentifiantsConnexion {
  email: string;
  motDePasse: string;
}

export interface ReponseConnexion {
  token: string;
}

export interface ProfilJwt {
  username?: string;
  email?: string;
  roles?: string[];
  exp?: number;
}
