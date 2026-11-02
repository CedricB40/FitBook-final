/**
 * Transforme l'adresse d'une photo en valeur CSS `background-image`.
 * Renvoie null quand il n'y a pas de photo : le dégradé de la page reste alors affiché.
 * L'adresse est encodée pour qu'une valeur saisie par un utilisateur ne puisse pas sortir du `url("...")`.
 */
export const fondPhoto = (photo: string | null | undefined): string | null =>
  photo && photo.trim() !== '' ? `url("${encodeURI(photo.trim())}")` : null;
