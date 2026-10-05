# FitBook : Epics et user stories

Dans ce document, je regroupe les grands objectifs de FitBook (les Epics) et, pour chacun, les user stories qui en découlent.

## Comment je procède

- Un **Epic**, c'est un grand objectif du projet. Je le découpe ensuite en petites user stories.
- Une **user story** décrit un besoin précis d'un utilisateur : « En tant que [rôle], je veux [action] afin de [bénéfice] ».
- Je reste sur des rôles génériques, sans prénoms ni personas.

## Les rôles

- **Visiteur** : il consulte la plateforme sans être connecté.
- **Sportif** : il cherche, compare et réserve.
- **Coach** : il gère son activité.
- **Gestionnaire de salle** : il gère son établissement.
- **Administrateur** : il veille sur la plateforme.

## À ne pas oublier

Quatre fonctionnalités sont obligatoires : les favoris, les avis et notes, le calendrier et la barre de recherche.

L'Epic 9 (Bonus) est facultatif : je ne m'y attaque que si le reste est terminé.

---

## Epic 1 : Comptes et sécurité

Chacun doit pouvoir s'inscrire, se connecter et confirmer son email, et ne voir que ce que son rôle lui permet. Le compte regroupe les identifiants de connexion ; le profil (Epic 2) regroupe les informations affichées.

*User stories :*

1. En tant que sportif, je veux créer un compte afin de pouvoir réserver mes séances.
2. En tant que coach, je veux me créer un compte afin de pouvoir proposer mes séances.
3. En tant que gestionnaire de salle, je veux créer un compte afin de présenter ma salle.
4. En tant que nouvel utilisateur, je veux valider mon adresse email avec un lien reçu par mail afin d'activer mon compte.
5. En tant qu'utilisateur, je veux me connecter avec mon email et mon mot de passe afin de retrouver mon espace.
6. En tant qu'utilisateur connecté, je veux me déconnecter afin de protéger mon compte.
7. En tant qu'utilisateur connecté, je veux changer mon mot de passe afin de garder mon compte protégé.
8. En tant qu'administrateur, je veux que chaque rôle n'ait accès qu'à ce qui le concerne afin de protéger les données de la plateforme.
9. En tant que visiteur non connecté, je veux être redirigé vers la connexion quand j'essaie d'ouvrir une page réservée afin de savoir que je dois m'identifier.

## Epic 2 : Profils

Le sportif, le coach et le gestionnaire de salle complètent leur profil, c'est-à-dire les informations visibles par les autres, et peuvent le modifier quand ils veulent.

*User stories :*

1. En tant qu'utilisateur connecté, je veux voir mon profil afin de vérifier mes informations.
2. En tant que sportif, je veux créer mon profil afin de personnaliser mon expérience.
3. En tant que sportif, je veux modifier mon profil afin de garder mes informations à jour.
4. En tant que coach, je veux créer mon profil professionnel afin de me présenter aux sportifs.
5. En tant que coach, je veux modifier mon profil professionnel afin de garder mes informations à jour.
6. En tant que gestionnaire de salle, je veux renseigner les informations de ma salle afin de la présenter aux sportifs.
7. En tant que gestionnaire de salle, je veux modifier les informations de ma salle afin qu'elles restent à jour.

## Epic 3 : Recherche et consultation

On doit pouvoir trouver vite une salle, un cours ou un coach, grâce à la barre de recherche et aux filtres.

*User stories :*

1. En tant que visiteur, je veux consulter la liste des salles, des cours et des coachs afin de découvrir l'offre sans créer de compte.
2. En tant que sportif, je veux rechercher une salle, un cours ou un coach avec la barre de recherche afin de trouver rapidement ce qui m'intéresse.
3. En tant que sportif, je veux filtrer les résultats afin d'affiner ma recherche.
4. En tant que sportif, je veux être prévenu quand ma recherche ne donne aucun résultat afin de pouvoir changer mes critères.
5. En tant que sportif, je veux consulter la fiche d'une salle afin de connaître ses informations et ses horaires.
6. En tant que sportif, je veux consulter la fiche d'un coach afin de connaître son profil et ses disponibilités.
7. En tant que sportif, je veux consulter la fiche d'un cours afin de savoir s'il me convient.

## Epic 4 : Séances et disponibilités

Le coach et le gestionnaire de salle organisent leurs séances, leurs horaires et leurs disponibilités.

*User stories :*

1. En tant que coach, je veux créer une séance afin de la proposer aux sportifs.
2. En tant que coach, je veux modifier une séance afin de corriger ou de mettre à jour ses informations.
3. En tant que coach, je veux supprimer une séance afin de retirer celle qui n'a plus lieu.
4. En tant que coach, je veux définir mes horaires et mes disponibilités afin que les sportifs sachent quand me réserver.
5. En tant que gestionnaire de salle, je veux ajouter un cours ou une activité afin de les proposer aux sportifs.
6. En tant que gestionnaire de salle, je veux modifier ou supprimer un cours ou une activité afin de garder mon offre à jour.
7. En tant que gestionnaire de salle, je veux définir les horaires de ma salle afin que les sportifs sachent quand elle est ouverte.
8. En tant que gestionnaire de salle, je veux fixer la capacité d'un cours afin que le nombre de places soit limité.
9. En tant que gestionnaire de salle, je veux gérer les disponibilités de ma salle afin d'indiquer quand elle peut accueillir des séances.

## Epic 5 : Calendrier

Les coachs, les gestionnaires de salle et les sportifs retrouvent leurs séances et leurs réservations dans un calendrier clair.

*User stories :*

1. En tant que coach, je veux consulter mon calendrier afin de voir mes séances et mes disponibilités.
2. En tant que coach, je veux voir les réservations sur mon calendrier afin de savoir qui vient à mes séances.
3. En tant que gestionnaire de salle, je veux consulter le calendrier de ma salle afin de voir les cours et les réservations à venir.
4. En tant que sportif, je veux consulter le calendrier d'une salle ou d'un coach afin de choisir un créneau qui m'arrange.
5. En tant que sportif, je veux voir mes réservations dans un calendrier afin de ne pas les oublier.

## Epic 6 : Réservations

Le sportif réserve ou annule une séance dans la limite des places disponibles, retrouve son historique et reçoit des emails liés à ses réservations.

*User stories :*

1. En tant que sportif, je veux réserver une séance afin de m'assurer une place.
2. En tant que sportif, je veux être prévenu quand une séance est complète afin de pouvoir en choisir une autre.
3. En tant que sportif, je veux annuler une réservation afin de libérer ma place si je ne peux plus venir.
4. En tant que sportif, je veux consulter l'historique de mes réservations afin de retrouver mes séances passées et à venir.
5. En tant que sportif, je veux recevoir un email de confirmation après une réservation afin d'en garder une preuve.
6. En tant que sportif, je veux recevoir un email quand j'annule une réservation afin d'être sûr que l'annulation est bien prise en compte.
7. En tant que coach, je veux consulter la liste des réservations de mes séances afin de savoir combien de sportifs viennent.
8. En tant que gestionnaire de salle, je veux consulter les réservations de ma salle afin de préparer l'accueil.

## Epic 7 : Favoris, avis et notes

Le sportif garde ses salles, cours et coachs préférés en favoris, et laisse un avis accompagné d'une note.

*User stories :*

1. En tant que sportif, je veux ajouter une salle, un cours ou un coach à mes favoris afin de les retrouver facilement.
2. En tant que sportif, je veux retirer un favori afin de garder une liste à jour.
3. En tant que sportif, je veux consulter la liste de mes favoris afin d'accéder rapidement à ce qui m'intéresse.
4. En tant que sportif, je veux publier un avis avec une note après une séance afin de partager mon expérience.
5. En tant que sportif, je veux modifier ou supprimer mon avis afin de le corriger si besoin.
6. En tant que sportif, je veux lire les avis et les notes des autres afin de choisir une salle, un cours ou un coach en confiance.
7. En tant que coach, je veux consulter les avis laissés sur mes séances afin de m'améliorer.
8. En tant que gestionnaire de salle, je veux consulter les avis laissés sur ma salle afin de connaître l'opinion des sportifs.

## Epic 8 : Administration et modération

L'administrateur gère les utilisateurs, les salles, les coachs, les activités et les réservations, et modère les avis et les notes.

*User stories :*

1. En tant qu'administrateur, je veux consulter la liste des utilisateurs afin de suivre les comptes de la plateforme.
2. En tant qu'administrateur, je veux modifier ou supprimer un compte utilisateur afin de garder la plateforme saine.
3. En tant qu'administrateur, je veux gérer les salles afin de contrôler les informations publiées.
4. En tant qu'administrateur, je veux gérer les coachs afin de contrôler les profils proposés aux sportifs.
5. En tant qu'administrateur, je veux gérer les activités afin de garder une offre cohérente.
6. En tant qu'administrateur, je veux consulter toutes les réservations afin de suivre l'activité de la plateforme.
7. En tant qu'administrateur, je veux modifier ou annuler une réservation afin de régler un problème signalé.
8. En tant qu'administrateur, je veux consulter les avis et les notes afin de vérifier qu'ils respectent les règles.
9. En tant qu'administrateur, je veux supprimer un avis inapproprié afin de protéger les utilisateurs.

## Epic 9 : Bonus (facultatif)

Si le temps le permet : carte interactive, recherche par distance, notifications avant une séance, statistiques et QR code de confirmation.

*User stories :*

1. En tant que sportif, je veux recevoir un rappel par email avant ma séance afin de ne pas l'oublier.
2. En tant que sportif, je veux recevoir un QR code avec ma confirmation de réservation afin de le présenter à mon arrivée.

---

## Choix retenus

Voici les choix que j'ai faits là où le cahier des charges ne tranchait pas.

- **Visiteur** : je le garde comme rôle à part entière. Il consulte les salles, les cours et les coachs sans compte. Réserver, mettre en favori et publier un avis demandent un compte.
- **Activité, cours et séance** : l'activité est un type de sport (yoga, boxe), le cours est une offre régulière, et la séance est une occurrence datée.
- **Avis** : un sportif qui a suivi une séance peut noter la salle, le coach ou le cours.
- **Administrateur** : son compte est créé directement par l'équipe, sans inscription publique, ce qui explique l'absence de user story d'inscription pour ce rôle.
- **Emails** : seul le sportif reçoit des emails (confirmation et annulation). Le coach et le gestionnaire de salle consultent leurs réservations sur la plateforme.