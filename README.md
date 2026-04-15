# Gestion - Système de Gestion de Stock, Ventes et Comptabilité

## 1. Introduction Générale

`Gestion` est une application web complète destinée aux commerces qui veulent piloter leurs activités quotidiennes depuis une interface unique.

Le projet couvre l’ensemble du cycle opérationnel:

- gestion des référentiels (articles, catégories, clients, fournisseurs)
- achats fournisseurs (commandes, réceptions, paiements)
- ventes clients (facturation, encaissements, historique, rapports)
- organisation des emplacements physiques (magasins, étagères)
- comptabilité opérationnelle
- audit et traçabilité des actions
- gestion des dettes historiques ("Anciens") pour les soldes hors système
- sécurité fine par permissions utilisateur

L’objectif est de fournir un outil pragmatique, orienté terrain, pour suivre les flux de stock et d’argent avec une expérience fluide sur desktop et mobile.

## 2. Stack Technique

- Backend: `Laravel 12` (`PHP ^8.2`)
- Frontend applicatif: `Livewire 3`
- Build frontend: `Vite`
- UI: `Blade + Bootstrap + Tailwind (build tooling)`
- Base de données: MySQL/MariaDB (ou compatible Laravel)
- Journalisation: helper d’activité (`app/Helpers/LogActivityHelper.php`)

## 3. Architecture du Projet

Le code est organisé autour des responsabilités suivantes:

- `app/Livewire`: composants métier (chaque écran principal)
- `app/Models`: modèles Eloquent par domaine (Stock, Ventes, Legacy, etc.)
- `resources/views/livewire`: vues associées aux composants
- `routes/web.php`: routes web, routes publiques signées et routes protégées
- `config/access.php`: matrice des permissions, mapping routes -> permissions
- `database/migrations`: schéma applicatif et évolutions
- `public/js/print.js`: utilitaires d’impression

## 4. Fonctionnalités Détaillées

### 4.1 Tableau de Bord

- Vue d’entrée de l’application après authentification.
- Donne un aperçu synthétique de l’activité.
- Visible selon permission `dashboard`.

### 4.2 Gestion des Ventes

#### Ventes

- Création de vente avec sélection client, devise, remise, date.
- Ajout multi-lignes d’articles avec choix d’étagère et contrôle de stock disponible.
- Calcul automatique du sous-total, remise, total net.
- Gestion du statut de vente selon paiements (`IMPAYEE`, `PARTIELLE`, `PAYEE`).
- Modales de détails, suppression et annulation selon droits.

#### Encaissement client

- Enregistrement de paiements partiels ou totaux.
- Contrôle du montant maximal payé (pas de dépassement du dû).
- Calcul du reste à payer en temps réel.

#### Historique

- Consultation des ventes passées avec filtres.
- Traçabilité des opérations commerciales.

#### Rapports de ventes

- Rapport des ventes par période (`aujourd’hui`, `hier`, `semaine`, `mois`) et plage personnalisée.
- Exposition en vue interne et en lien public signé imprimable.
- Totaux agrégés (montant, payé, reste, nombre d’articles).

### 4.3 Gestion des Articles

- CRUD des articles avec informations commerciales.
- Association à des catégories.
- Sélection et recherche rapide dans les écrans de vente/réception.

### 4.4 Gestion des Clients

- CRUD clients avec informations de contact.
- Support client par défaut (`is_default`) pour accélérer la saisie.
- Création rapide d’un client depuis l’écran de vente.

### 4.5 Gestion des Fournisseurs

- CRUD fournisseurs.
- Utilisés dans le cycle d’achat (commandes, réceptions, paiements).

### 4.6 Stock - Commandes Fournisseurs

- Création de commandes fournisseurs multi-lignes.
- Suivi des quantités commandées et réceptionnées.
- Consultation des détails et documents imprimables.

### 4.7 Stock - Approvisionnements (Réceptions)

- Création de réceptions liées aux commandes.
- Affectation des articles reçus à un `magasin` et une `étagère`.
- Contrôle des quantités reçues et cohérence stock.
- Intégration des defaults de stockage (`is_default` sur magasin/étagère).

### 4.8 Stock - Paiements Fournisseurs

- Enregistrement des paiements fournisseurs.
- Suivi des soldes par commande/réception.
- Historique et impressions de justificatifs.

### 4.9 Gestion des Entrepôts

#### Magasins

- CRUD des magasins physiques.
- Activation/désactivation.
- Possibilité de marquer un magasin par défaut (`is_default`).

#### Étagères

- CRUD des étagères rattachées à un magasin.
- Activation/désactivation.
- Possibilité de définir une étagère par défaut par magasin (`is_default`).

### 4.10 Configuration

#### Catégories d’articles

- Gestion des catégories servant à classifier les produits.

#### Devises

- Gestion multi-devises.
- Définition d’une devise par défaut (`is_default`).

#### Paramètres Entreprise

- Nom complet, nom court, logo, informations branding.
- Ces paramètres alimentent l’UI (sidebar, pages d’authentification, etc.).

### 4.11 Comptabilité

#### Types d’opérations

- Paramétrage des classes d’opérations comptables.

#### Opérations

- Saisie et suivi des opérations.
- Lien avec devise et type d’opération.
- Soft delete et restrictions de suppression selon dépendances.

### 4.12 Audit

#### Audit Stock Article

- Suivi orienté stock (dont usages d’expiration selon les règles métier).

#### Audit Activité

- Journal des actions utilisateur via `logActivity`.
- Permet de retracer créations, modifications, paiements, changements de statut.

### 4.13 Module "Anciens" (Dettes Historiques)

Ce module répond au besoin de migrer le passif "hors système" (carnets papier) vers l’application, sans polluer le calcul principal des ventes courantes.

#### Dettes clients historiques

- Enregistrement d’une dette initiale (date, montant dû, notes).
- Paiements partiels successifs.
- Clôture automatique lorsque la dette est totalement réglée.

#### Dettes fournisseurs historiques

- Même logique que clients, orientée dette envers fournisseur.

#### Rapports "Anciens"

- Vue statistique dédiée.
- Impression des listes filtrées (payé/non payé/tous) avec plage de dates.
- Détails ligne par ligne (visualisation + impression).

### 4.14 Gestion des Utilisateurs & Accès

- Gestion des utilisateurs (`settings/users`).
- Permissions granulaires par module avec capacités:
    - voir (`can_view`)
    - créer (`can_create`)
    - modifier (`can_update`)
    - supprimer (`can_delete`)
    - activer/désactiver (`can_toggle_status`)
- Middleware `access` appliqué aux routes protégées.
- Directive Blade `@access(...)` pour afficher/masquer les actions en interface.

### 4.15 Profil Utilisateur

- Mise à jour des informations personnelles.
- Gestion de l’image/avatar utilisateur.

### 4.16 Recherche Globale et Notifications

- Composants transverses pour accélérer la navigation et les alertes contextuelles.

### 4.17 Impression & Partage Public Signé

Le projet fournit des pages publiques en lecture seule, protégées par signature (`signed`) pour partager/imprimer:

- commandes
- ventes
- réceptions
- paiements
- rapport ventes du jour/période

## 5. Sécurité et Contrôle d’Accès

- Authentification par formulaires dédiés (`login`, etc.).
- Contrôle d’accès centralisé via `CheckAccess` + `config/access.php`.
- Vérification à deux niveaux:
    - niveau route (middleware)
    - niveau composant/UI (conditions d’affichage + checks serveur)
- Rôle super administrateur pris en charge.

## 6. Installation et Lancement

## Prérequis

- `PHP >= 8.2`
- `Composer`
- `Node.js` + `npm`
- Une base de données configurée

## Installation rapide

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Ou via le script Composer prévu:

```bash
composer run setup
```

## Démarrage en développement

```bash
composer run dev
```

Ce script lance en parallèle:

- serveur Laravel
- écoute de queue
- logs Laravel Pail
- serveur Vite

## Tests

```bash
composer run test
```

## 7. Routes Principales (Aperçu)

- `/dashboard`
- `/articles`
- `/clients`
- `/fournisseurs`
- `/stock/commandes`
- `/stock/approvisions`
- `/stock/approvisions/paiements`
- `/ventes/ventes`
- `/ventes/create`
- `/ventes/rapports`
- `/ventes/historique`
- `/warehouse/magasins`
- `/warehouse/etageres`
- `/configuration/categories`
- `/configuration/devises`
- `/configuration/settings`
- `/comptabilite/types-operations`
- `/comptabilite/operations`
- `/audit/stock-article`
- `/audit/activity`
- `/anciens/clients`
- `/anciens/fournisseurs`
- `/anciens/rapports`
- `/settings/users`
- `/settings/profile`

## 8. Points Métier Importants

- Le stock est calculé à partir des entrées (réceptions) moins sorties (ventes non annulées).
- Les contrôles évitent les ventes au-delà du stock disponible.
- Les paiements recalculent automatiquement l’état des documents.
- Le module "Anciens" est séparé pour préserver la logique de pilotage opérationnel courant.
- Les permissions sont pensées pour déléguer finement les actions selon le poste utilisateur.

## 9. État du README

Ce README a été réécrit pour documenter l’application métier réelle (et non le template Laravel par défaut).  
Pour maintenir sa qualité, toute nouvelle fonctionnalité devrait ajouter sa section dans ce document.
