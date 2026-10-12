# FitBook

Plateforme web de réservation de salles et de coachs sportifs, réalisée dans le cadre de l'UAA MI08 « Produire un projet Web » (CFITECH).

## Le projet

FitBook permet de rechercher des salles, des cours et des coachs, puis de réserver une séance. Les professionnels disposent d'un espace pour gérer leurs activités et leurs réservations.

## Les rôles

- **Visiteur** : consulte la plateforme sans être connecté
- **Sportif** : recherche, réservation, historique, favoris, avis et notes
- **Coach** : profil professionnel, séances, disponibilités, calendrier
- **Gestionnaire de salle** : informations de la salle, cours, horaires, capacités
- **Administrateur** : gestion des utilisateurs et modération des avis

## Fonctionnalités obligatoires

Favoris, avis et notes, calendrier, barre de recherche.

## Technologies prévues

- Frontend : Angular 21, HTML, CSS
- Backend : PHP, Symfony
- Base de données : MySQL
- Tests : Postman / Newman

## Structure du dépôt

- `Backend` : application Symfony
- `Frontend` : application Angular
- `docs` : documentation de conception
  - `Epics et User Stories` : epics et user stories
  - `Use-Case` : diagramme de cas d'utilisation
  - `MCD-MLD` : modèle conceptuel et logique de données
  - `Diagramme de Classes` : diagramme de classes
  - `Zoning` : zonages des écrans (Desktop, Tablette, Mobile)
  - `Wireframe` : wireframes des dix écrans (Desktop, Tablette, Mobile)
  - `Maquette` : design system (`Composants`), maquettes visuelles desktop (`Visuel`) et prototype Figma (`Prototype`)
  - `Rapport` : rapport de conception (Word et PDF)
  - `Github` : captures du workflow Git (branches, pull requests, structure)
  - `trello` : captures du tableau de suivi

## Organisation du projet

- **Suivi** : tableau Trello
- **Versioning** : GitFlow (`main`, `develop`, `feat_xxx`, `release`)

## Feuille de route

### FAIT

- [x] Analyse : epics et user stories
- [x] MCD et MLD
- [x] Use-cases et diagramme de classes
- [x] Zonages et wireframes (desktop, tablette, mobile)
- [x] Maquettes Figma et prototype (desktop)
- [x] Rapport de conception

### EN COURS

- [ ] Cahier des charges
- [ ] Validation par les formateurs

### À FAIRE

- [ ] Maquettes visuelles tablette et mobile
- [ ] Développement backend et frontend
- [ ] Tests, sécurité et sauvegardes
- [ ] Déploiement et présentation finale