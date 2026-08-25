# Gestion des Stagiaires — Hôpital Laquintinie de Douala

Plateforme complète (Backend Laravel 11 + Frontend Nuxt 4) pour le suivi et
l'évaluation des stagiaires du service informatique de l'Hôpital Laquintinie
de Douala. Thème visuel : blanc dominant + bleu.

## Structure du projet

```
backend/    → API Laravel 11 (Sanctum, MySQL, spatie/activitylog)
frontend/   → Application Nuxt 4 (Vue 3, Pinia, Tailwind CSS v4)
```

## Démarrage rapide

### 1. Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# configurer MySQL dans .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate --seed
php artisan storage:link
php artisan serve
```
→ API disponible sur http://localhost:8000

### 2. Frontend

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```
→ Application disponible sur http://localhost:3000

## Comptes de démonstration

| Rôle       | Email                       | Mot de passe    |
|------------|------------------------------|-----------------|
| Admin      | admin@laquintinie.cm         | Admin@2026      |
| Encadrant  | p.ngono@laquintinie.cm       | Encadrant@2026  |
| Stagiaire  | a.fokou@stagiaires.cm        | Stagiaire@2026  |

## Rôles et permissions

**Administrateur** — Accès total à la plateforme :
- Créer, modifier, supprimer, bloquer, débloquer et restreindre les comptes
  admin et encadrant
- Voir tous les utilisateurs de l'application (y compris les stagiaires)
- Consulter le journal d'activité complet (toutes les actions de tous les
  utilisateurs, horodatées et attribuées)
- Voir tous les rendez-vous de la plateforme, tous stagiaires confondus
- Gérer les établissements partenaires (parcours académique)
- Assigner/réassigner un encadrant à un stagiaire
- Réinitialiser le mot de passe de n'importe quel compte

**Encadrant / Tuteur** — Gère ses stagiaires assignés :
- Créer des dossiers stagiaires (auto-assignés)
- Suivi quotidien : présences, absences (approbation/rejet)
- Notation : grilles académiques, bilans de compétences, suivis mensuels
- Validation des documents et livrables déposés
- Planification de rendez-vous avec ses stagiaires

**Stagiaire** — Son espace personnel :
- Feuille de route (infos du stage, encadrant, prochains rendez-vous)
- Pointage de présence (arrivée / départ) en un clic
- Demandes d'absence avec motif
- Dépôt de documents (convention, rapport, mémoire, livrables)
- Consultation de ses évaluations validées

## Fonctionnalités ajoutées au-delà du cahier des charges

- **Journal d'activité complet** (spatie/laravel-activitylog) : chaque action
  sensible (création/modification/blocage de compte, traitement de document,
  évaluation, etc.) est tracée avec auteur, horodatage et description —
  consultable et filtrable par l'admin.
- **Distinction blocage vs restriction** : le blocage (avec motif obligatoire)
  est une sanction disciplinaire explicite, distincte de la restriction
  temporaire (désactivation simple), pour un contrôle plus fin des accès.
- **Génération automatique de matricule** stagiaire (format `STG-AA-0000`).
- **Pointage horodaté avec détection automatique de retard** (après 08h15).
- **Feuille de route dynamique** adaptée au type de parcours (champs
  spécifiques académique vs professionnel affichés conditionnellement).
- **Dashboard admin** avec statistiques agrégées en temps réel (répartition
  par rôle, par type de parcours, éléments en attente de traitement).
- **Sécurité** : un admin ne peut ni se bloquer ni se supprimer lui-même ;
  contrôle d'accès strict par middleware de rôle sur chaque route API.

## Stack technique

- **Backend** : Laravel 11, Sanctum (auth par token), MySQL, spatie/activitylog,
  barryvdh/laravel-dompdf (prêt pour génération d'attestations PDF)
- **Frontend** : Nuxt 4, Vue 3 (Composition API), Pinia, Tailwind CSS v4,
  VueUse

## Notes de déploiement

- Le dossier `frontend/node_modules` n'est pas inclus dans cette archive
  (régénéré via `npm install`).
- Le dossier `backend/vendor` n'est pas inclus (régénéré via `composer install`).
- Penser à configurer `FRONTEND_URL` et `SANCTUM_STATEFUL_DOMAINS` côté
  backend, et `NUXT_PUBLIC_API_BASE` côté frontend, selon l'environnement de
  production.
