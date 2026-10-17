# Dipla - Plateforme de découverte de commerces locaux

Dipla est une plateforme qui permet à des particuliers de trouver des produits et services proposés par des commerces de proximité, avec une logique de recherche géolocalisée (distance, itinéraire) plutôt qu'un simple annuaire.

Deux types de comptes cohabitent :
- **Utilisateur (particulier)** : cherche, consulte, met en favoris, pose des questions, laisse des avis.
- **Entreprise (commerce)** : publie des produits/services, gère sa vitrine, répond aux questions et avis, suit ses statistiques.

Dipla est **entièrement gratuit**, aussi bien pour les particuliers que pour les commerces : aucun abonnement, aucune commission, aucune fonctionnalité payante.

> ⚠️ **Statut du projet : site vitrine / démonstration technique.**
> Ce dépôt est une reconstruction complète et personnelle du projet Dipla, réalisée à des fins d'apprentissage et de portfolio. **Le site n'est pour l'instant pas ouvert à de vrais commerces ni à de vrais utilisateurs** : il n'y a pas de données réelles et aucune entreprise n'y est actuellement référencée en production. C'est une démonstration de ce que le site peut faire, pas un service actif.

---

## Sommaire
- [Accès a Dipla](#acces-a-dipla)
- [Fonctionnalités](#fonctionnalités)
- [Utilisation de l'IA dans ce projet](#utilisation-de-lia-dans-ce-projet)
- [Stack technique](#stack-technique)
- [Architecture du projet](#architecture-du-projet)
- [Modèle de données](#modèle-de-données)
- [Déploiement](#déploiement)
- [Limites connues / reste à faire](#limites-connues--reste-à-faire)
- [Licence](#licence)

---

## Accès a Dipla

> **Remarque :** Le site est hébergé sur l'offre gratuite de Render. En raison de la mise en veille automatique après une période d'inactivité, le premier chargement peut prendre entre 30 secondes et 1 minute.

Des comptes de démonstration ont été créés pour vous permettre d'explorer l'ensemble des fonctionnalités de Dipla :

| Type de compte | Adresse e-mail | Mot de passe |
| :--- | :--- | :--- |
| **Commerçant** | `commerce<N>@exemple.com` | `mdpCommerceDemo.<N>` |
| **Utilisateur** | `user<N>@exemple.com` | `mdpUserDemo.<N>` |

*(Remplacer `<N>` par le numéro de compte souhaité)*

Connectez-vous avec ces identifiants pour accéder aux différents tableaux de bord.
Lien vers Dipla : https://dipla.me

## Fonctionnalités

### Espace utilisateur
- Inscription / connexion (email + mot de passe)
- Mot de passe oublié (** à mettre en place **)
- Géolocalisation du profil (carte interactive + autocomplete d'adresse)
- Favoris, avis, questions posées, historique de consultation 
- Modification du profil, de l'email, du mot de passe
- Suppression de compte avec confirmation par mot de passe

### Espace entreprise
- Inscription avec adresse **obligatoire** (le commerce doit être localisable)
- Dashboard avec statistiques (vues cumulées, favoris reçus, note moyenne, nombre de produits)
- Gestion de la devanture (vitrine publique) : photo de couverture, photo carte , avatar rond, téléphone, description avec mise en forme, horaires d'ouverture par jour
- CRUD produits complet avec upload et recadrage d'images, prix fixe ou variable
- Gestion et réponse aux avis clients
- Gestion et réponse aux questions clients
- Paramètres de compte, suppression de compte

### Recherche & découverte
- Recherche plein texte
- Trois modes : recherche par mot-clé, "à proximité" (rayon 20 km), "découvrir" (aléatoire)
- Filtres : distance max, prix max, type (produit / service / commerce)
- Recherche par type "commerce" : trouve directement les vitrines d'entreprise

### Fiche produit & vitrine commerce
- Galerie d'images
- Note moyenne, avis, questions/réponses 
- Itinéraire avec estimation de temps à pied / voiture
- Ajout automatique à l'historique de consultation
- Page vitrine par commerce : horaires, indicateur "ouvert / fermé actuellement", coordonnées, recherche dans son propre catalogue


## Utilisation de l'IA dans ce projet

Dans un soucis de rapidité et d'efficacité ce projet a été développé avec l'aide d'un assistant IA (Claude, d'Anthropic) utilisé comme outil de génération de code assistée. **Le site n'a pas été créé « à l'aveugle » en déléguant tout à l'IA.** :

- L'IA a été utilisée pour générer des blocs de code ciblés : composants Livewire, migrations, méthodes de modèles Eloquent, requêtes SQL, scripts Alpine.js, gabarits Blade, configuration Docker, etc.
- **Chaque bloc de code généré a été relu, compris et vérifié manuellement** avant d'être intégré au projet - aucun code n'a été copié-collé aveuglément. Les choix d'architecture ont été discutés et validés au fil de l'eau plutôt que délégués intégralement.

L'objectif de cette mention est la transparence : ce dépôt reflète un travail de développement personnel assisté par IA, pas un projet généré automatiquement sans supervision.

## Stack technique

| Composant | Choix |
|---|---|
| Framework backend | **Laravel 13** |
| Interactivité front | **Livewire 3** + **Alpine.js** |
| CSS | **Tailwind CSS v4** |
| Base de données | **PostgreSQL** hébergée sur **Neon** |
| Stockage des images | **ImageKit** | 
| Cartes / géolocalisation | **Leaflet.js + OpenStreetMap** (fond de carte) + **LocationIQ** (autocomplete d'adresse, géocodage inverse, calcul d'itinéraires) |
| Recadrage d'images | **Cropper.js v1.6.2** |
| Réordonnancement | **SortableJS** |
| Fond animé | **@firecms/neat** |
| Hébergement | **Render** |

## Architecture du projet

```
DiplaV2/
├── app/
│   ├── Models/              → User, Company, Product, ProductImage, Review, Discussion, Favorite, ViewHistory
│   ├── Http/Controllers/    → ProductController, GeocodeController, Company/ProductController
│   ├── Livewire/            → Search, CompanyStorefront, Account/*, Company/*, Product/*
│   ├── Policies/            → ProductPolicy (autorisation entreprise ↔ produit)
│   ├── Services/            → ImageKitService (upload/suppression d'images)
│   └── Console/Commands/    → MigrateImagesToImageKit (migration ponctuelle Cloudinary → ImageKit)
├── database/
│   ├── migrations/          → schéma versionné complet
│   └── seeders/
├── resources/
│   ├── views/                → Blade + composants Livewire (layouts public / app / company / user)
│   ├── css/                  → Tailwind v4 (tokens dans app.css)
│   └── js/                   → Alpine components (carte, recadrage, géolocalisation, drag & drop)
├── routes/
│   ├── web.php
│   ├── company-auth.php      → routes du guard "company"
│   └── settings.php
├── docker/                   → config Apache + script de binding du port Render     
└── Dockerfile                → build multi-stage (Node pour les assets, PHP 8.4 + Apache)
```

## Modèle de données

| Table | Description |
|---|---|
| `users` | Comptes particuliers (nom, email, mot de passe, adresse optionnelle, lat/lng) |
| `companies` | Comptes entreprise (nom, email, téléphone optionnel, mot de passe, adresse obligatoire, lat/lng, photo de couverture, photo carte, avatar, description, horaires) |
| `products` | Produits/services publiés par une entreprise (titre, prix (optionnel pour un tarif variable), description, type, mots-clés, adresse, lat/lng, `search_vector` pour le full-text) |
| `product_images` | Table dédiée aux photos produit (URL ImageKit, position d'affichage) |
| `reviews` | Avis utilisateurs (note, sujet, contenu, réponse de l'entreprise et date de réponse) |
| `discussions` | Questions/réponses |
| `favorites` | Relation utilisateur ↔ produit |
| `view_history` | Historique de consultation, plafonné à 7 entrées par utilisateur |

Toutes les relations sont définies avec suppression en cascade pour garantir la cohérence des données lors de la suppression d'un compte, d'une entreprise ou d'un produit.

### Pages légales & contact
- Formulaire de contact 
- Mentions légales, politique de confidentialité, conditions d'utilisation
- Bandeau d'information cookies (uniquement des traceurs indispensables)

## Déploiement

Le projet n'étant actuellement utilisé que comme portfolio, il est configuré pour un déploiement Docker sur Render, ce qui fournit une solution gratuite et suffisante. Cependant, pour une utilisation réelle, un changement d'hébergeur serait nécessaire.

## Limites connues / reste à faire

- Passage à une offre d'hébergement/base de données payante avant toute utilisation réelle 
- Outil de duplication rapide d'un catalogue produit pour une entreprise

Tant que ces points ne sont pas finalisés, le site reste une démonstration technique et n'a pas vocation à héberger de vrais commerces.

## Licence

Projet personnel à but d'apprentissage et de démonstration. Aucune licence d'utilisation commerciale n'est accordée sur ce dépôt sauf mention contraire.
