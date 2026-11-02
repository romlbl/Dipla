# Dipla - Local business discovery platform

Dipla is a platform that lets individuals find products and services offered by local businesses, using a geolocated search approach (distance, route) rather than a simple directory.

Two types of accounts coexist:
- **User (individual)**: searches, browses, adds to favorites, asks questions, leaves reviews.
- **Business (shop)**: publishes products/services, manages its storefront, answers questions and reviews, tracks its statistics.

Dipla is **entirely free**, for both individuals and businesses: no subscription, no commission, no paid features.

> ⚠️ **Project status: showcase site / technical demonstration.**
> This repository is a complete, personal rebuild of the Dipla project, made for learning and portfolio purposes. **The site is currently not open to real businesses or real users**: there is no real data and no company is currently listed in production. It is a demonstration of what the site can do, not an active service.

---

## Table of contents
- [Access to Dipla](#access-to-dipla)
- [Features](#features)
- [Use of AI in this project](#use-of-ai-in-this-project)
- [Tech stack](#tech-stack)
- [Project architecture](#project-architecture)
- [Data model](#data-model)
- [Deployment](#deployment)
- [Known limitations / To do](#known-limitations--to-do)
- [License](#license)

---

## Access to Dipla

Demo accounts have been created so you can explore all of Dipla's features:

| Account type | Email address | Password |
| :--- | :--- | :--- |
| **Business** | `commerce<N>@exemple.com` | `mdpCommerceDemo.<N>` |
| **User** | `user<N>@exemple.com` | `mdpUserDemo.<N>` |

*(Replace `<N>` with the desired account number)*

Log in with these credentials to access the different dashboards.
Link to Dipla: https://dipla.me

## Features

### User area
- Sign up / log in (email + password)
- Forgotten password (** to be implemented **)
- Profile geolocation (interactive map + address autocomplete)
- Favorites, reviews, questions asked, browsing history
- Edit profile, email, password
- Account deletion with password confirmation

### Business area
- Sign-up with a **mandatory** address (the business must be locatable)
- Dashboard with statistics (cumulative views, favorites received, average rating, number of products)
- Storefront management (public showcase): cover photo, card photo, round avatar, phone number, description with formatting, opening hours per day
- Full product CRUD with image upload and cropping, fixed or variable price
- Management of and replies to customer reviews
- Management of and replies to customer questions
- Account settings, account deletion

### Search & discovery
- Full-text search
- Three modes: keyword search, "nearby" (20 km radius), "discover" (random)
- Filters: max distance, max price, type (product / service / business)
- Search by "business" type: directly finds company storefronts

### Product page & business storefront
- Image gallery
- Average rating, reviews, questions/answers
- Route with walking / driving time estimate
- Automatic addition to browsing history
- Storefront page per business: opening hours, "currently open / closed" indicator, contact details, search within its own catalog


## Use of AI in this project

For the sake of speed and efficiency, this project was developed with the help of an AI assistant (Claude, by Anthropic) used as an assisted code generation tool. **The site was not created "blindly" by delegating everything to the AI.**:

- The AI was used to generate targeted blocks of code: Livewire components, migrations, Eloquent model methods, SQL queries, Alpine.js scripts, Blade templates, Docker configuration, etc.
- **Every generated block of code was reviewed, understood and verified manually** before being integrated into the project - no code was copy-pasted blindly. Architecture choices were discussed and validated along the way rather than fully delegated.

The purpose of this mention is transparency: this repository reflects personal development work assisted by AI, not a project automatically generated without supervision.

## Tech stack

| Component | Choice |
|---|---|
| Backend framework | **Laravel 13** |
| Front-end interactivity | **Livewire 3** + **Alpine.js** |
| CSS | **Tailwind CSS v4** |
| Database | **PostgreSQL** hosted on **Neon** |
| Image storage | **ImageKit** |
| Maps / geolocation | **Leaflet.js + OpenStreetMap** (base map) + **LocationIQ** (address autocomplete, reverse geocoding, route calculation) |
| Image cropping | **Cropper.js v1.6.2** |
| Reordering | **SortableJS** |
| Animated background | **@firecms/neat** |
| Hosting | **Render** |

## Project architecture

```
DiplaV2/
├── app/
│   ├── Models/              → User, Company, Product, ProductImage, Review, Discussion, Favorite, ViewHistory
│   ├── Http/Controllers/    → ProductController, GeocodeController, Company/ProductController
│   ├── Livewire/            → Search, CompanyStorefront, Account/*, Company/*, Product/*
│   ├── Policies/            → ProductPolicy (company ↔ product authorization)
│   ├── Services/            → ImageKitService (image upload/deletion)
│   └── Console/Commands/    → MigrateImagesToImageKit (one-off Cloudinary → ImageKit migration)
├── database/
│   ├── migrations/          → complete versioned schema
│   └── seeders/
├── resources/
│   ├── views/                → Blade + Livewire components (public / app / company / user layouts)
│   ├── css/                  → Tailwind v4 (tokens in app.css)
│   └── js/                   → Alpine components (map, cropping, geolocation, drag & drop)
├── routes/
│   ├── web.php
│   ├── company-auth.php      → routes for the "company" guard
│   └── settings.php
├── docker/                   → Apache config + Render port-binding script     
└── Dockerfile                → multi-stage build (Node for assets, PHP 8.4 + Apache)
```

## Data model

| Table | Description |
|---|---|
| `users` | Individual accounts (name, email, password, optional address, lat/lng) |
| `companies` | Business accounts (name, email, optional phone, password, mandatory address, lat/lng, cover photo, card photo, avatar, description, opening hours) |
| `products` | Products/services published by a business (title, price (optional for variable pricing), description, type, keywords, address, lat/lng, `search_vector` for full-text) |
| `product_images` | Dedicated table for product photos (ImageKit URL, display position) |
| `reviews` | User reviews (rating, subject, content, business reply and reply date) |
| `discussions` | Questions/answers |
| `favorites` | User ↔ product relationship |
| `view_history` | Browsing history, capped at 7 entries per user |

All relations are defined with cascade deletion to ensure data consistency when an account, a business or a product is deleted.

### Legal pages & contact
- Contact form
- Legal notice, privacy policy, terms of use
- Cookie information banner (strictly necessary trackers only)

## Deployment

Since the project is currently only used as a portfolio, it is configured for Docker deployment on Render, which provides a free and sufficient solution. However, for real-world use, a change of host would be necessary.

## Known limitations / To do

- Switch to a paid hosting/database plan before any real use
- Tool for quickly duplicating a product catalog for a business

Until these points are finalized, the site remains a technical demonstration and is not intended to host real businesses.

## License

Personal project for learning and demonstration purposes. No commercial use license is granted on this repository unless otherwise stated.
