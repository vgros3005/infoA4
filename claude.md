# Prompt — Application web « Gestion & Suivi des fiches A4 »

> Destiné à **Claude Code**. À coller dans la session, ou à placer dans un fichier `CLAUDE.md` à la racine du projet pour servir de référence permanente.
> Le projet doit respecter **strictement** la stack et les contraintes ci-dessous.
> En cas d'ambiguïté, demande-moi avant de coder. Travaille par étapes, attends ma validation entre chaque, et tiens `CLAUDE.md` à jour au fil de l'avancement.

---

## CONTEXTE & OBJECTIF

Tu vas développer une application web de **gestion et de suivi des « fiches A4 »** (demandes de développement) pour une DSI. L'outil remplace un fichier Excel partagé et des fichiers Word déplacés manuellement. Il doit couvrir tout le cycle de vie d'une demande, **de la création à la mise en production**, avec workflow, suivi de tâches, reporting de temps, diagramme de Gantt, gestion d'équipes/rôles et reporting.

Objectifs métier :
- Gérer une demande de la création à la mise en prod en suivant toutes ses étapes.
- S'engager sur les demandes et les délais ; **figer** demandes et priorités à partir de certaines étapes.
- Fournir du reporting (projets traités / en cours / à venir), suivre les dérives entre étapes, visualiser la charge des équipes.

---

## STACK TECHNIQUE IMPOSÉE (non négociable)

- **Backend** : Laravel 13
- **Frontend** : Bootstrap 5
- **JavaScript** : **vanilla JS / ES6** — **PAS de jQuery** (AdminLTE 4 / Bootstrap 5 fonctionnent sans jQuery)
- **Template d'administration** : AdminLTE 4
- **Base de données** : MySQL
- **Génération PDF** : DomPDF ou SnappyPDF
- **Éditeur WYSIWYG** : TinyMCE, CKEditor ou équivalent (en version sans dépendance jQuery)
- **Gantt** : Frappe Gantt, DHTMLX Gantt ou équivalent (sans dépendance jQuery)

⚠️ **Toutes les bibliothèques JavaScript/CSS doivent être hébergées en local** (aucune dépendance à un CDN ou serveur externe). Privilégie une intégration via le bundler de Laravel (**Vite**) ou des assets copiés en `public/`.

> Note : le cahier des charges initial mentionnait jQuery et Bootstrap 4 ; cette contrainte est **explicitement remplacée** par AdminLTE 4 + Bootstrap 5 **sans jQuery**. Toute lib retenue (WYSIWYG, Gantt) doit donc être choisie dans sa variante autonome, sans jQuery.

Livrable : **projet web Laravel 13 versionné (GitLab)** + documentation.

---

## ARCHITECTURE & STANDARDS (à respecter systématiquement)

- Architecture **MVC** Laravel.
- **Migrations** pour tout le schéma de base.
- **Seeders** pour les listes administrables et un **jeu de données de démonstration**.
- **Form Requests** utilisés systématiquement pour toute validation.
- **Policies** Laravel pour tous les contrôles d'accès.
- **Services** lorsque la logique métier le justifie.
- **API REST interne** possible.
- Respect **PSR-12**.
- Contrôle d'accès via **méthodes de contrôleurs + Policies** sur chaque fonctionnalité.

### Conventions de code imposées
- Noms de **variables, contrôleurs, fonctions, classes… en anglais**.
- Variables au format **PascalCase avec préfixe de type hongrois** :
  - `o` = object, `s` = string, `i` = integer, `n` = numérique, etc.
  - Exemple : `$sSql`, `$iUserId`, `$oRequestA4`, `$nTotalHours`.
- **Chaque endpoint d'API doit être précédé de son commentaire au format L5-Swagger / OpenAPL** afin de générer automatiquement la documentation Swagger.

---

## MODÈLE DE DONNÉES ATTENDU

Crée les migrations, modèles Eloquent (avec relations), et seeders pour les entités suivantes. Respecte exactement ces noms de tables :

`users`, `teams`, `roles`, `team_user_roles`, `requests_a4`, `request_types`, `priorities`, `companies`, `softwares`, `statuses`, `status_actions`, `tasks`, `task_types`, `task_time_entries`, `status_histories`, `attachments`.

Relations clés à modéliser :
- Un **user** appartient à **plusieurs teams**, avec un **rôle différent par équipe** (via `team_user_roles` : user × team × role).
- Une **request_a4** référence : type, priorité, statut courant, demandeur, société(s), logiciel(s) (relations many-to-many pour sociétés et logiciels).
- Un **statut** (`statuses`) possède des **actions** (`status_actions`) définissant : action(s) autorisée(s), statut cible, rôles autorisés.
- `status_histories` historise chaque changement de statut.
- Une **request_a4** possède plusieurs **tasks** ; une `task` peut aussi être **indépendante** (sans fiche).
- `task_time_entries` rattache un report de temps à une tâche (et indirectement à la fiche).
- `attachments` gère les pièces jointes (polymorphe de préférence).

---

## FONCTIONNALITÉS DÉTAILLÉES

### 1. Saisie de la demande (fiche A4)
Champs **obligatoires** :
- Titre explicite (**50 caractères max**)
- Description détaillée
- Type de demande (**liste administrable en base**)
- Priorité (**liste administrable en base**)
- **Flag** sur la priorité indiquant si une **justification est obligatoire**
- Justification de priorité (conditionnelle au flag)
- Société(s) concernée(s)
- Logiciel(s) concerné(s)
- Statut courant
- Demandeur
- Date de demande
- Date souhaitée de réalisation

Le **contenu de la fiche** doit être éditable en **mode WYSIWYG** et permettre : texte formaté, images & captures d'écran, dessins, tableaux, annotations libres.

### 2. Génération PDF
Export PDF de chaque fiche A4 **en conservant** : mise en page, images, dessins, styles de texte, tableaux et annotations.
À certaines étapes du workflow, l'export PDF doit **figer la demande** : le fichier généré porte un **n° de version** et une **date**.

### 3. Statuts & workflow
- Statuts **entièrement paramétrables en base**.
- Pour chaque statut, définir : une ou plusieurs **actions autorisées**, le **statut cible** après action, les **rôles autorisés**.
  - Ex. : Statut « En attente » → Action « Valider » → Nouveau statut « À développer ».
- Chaque changement de statut **historise** : date, utilisateur, ancien statut, nouveau statut, commentaire optionnel.

### 4. Gestion des tâches
À partir d'une fiche A4, créer des **tâches**. Chaque tâche contient : titre, type de tâche, durée estimée, durée réelle, date de début, date de fin, développeur assigné, statut, priorité.
Le système doit permettre :
- Tâches **indépendantes** d'une fiche A4.
- Tâches **récurrentes** avec un **nombre d'heures prévues par semaine** (ex. : support).
- Suivi de l'avancement, **déplacement visuel dans le Gantt**, **gestion des dépendances** entre tâches.

Visualisation :
- **Diagramme de Gantt par fiche A4**.
- **Vue globale par développeur**.

### 5. Gestion du temps
Les utilisateurs reportent du temps sur les tâches. Chaque saisie comporte : tâche, nombre d'heures, date, commentaire, utilisateur.
Le temps reporté alimente automatiquement : la tâche, la fiche A4 associée (si rattachée), et les **statistiques / tableaux de bord**.
Faciliter la saisie : proposer **par défaut la liste des tâches prévues** pour l'utilisateur, tout en lui permettant de reporter sur **toutes** les tâches qui lui sont attribuées.

### 6. Équipes & rôles
Gestion des équipes. Une personne peut appartenir à plusieurs équipes et y avoir un rôle différent.
Rôles d'exemple : Administrateur, Chef de projet, Développeur, Testeur, Demandeur.

### 7. Sécurité & droits
Toutes les actions passent par des **méthodes de contrôleurs** + **Policies Laravel**. Chaque fonctionnalité est protégée selon rôles/permissions, notamment : création de fiche, validation de demande, modification de statut, affectation de tâche, suppression de tâche, export PDF.

---

## FONCTIONNALITÉS COMPLÉMENTAIRES
- Notifications par **email**
- **Historique** des modifications
- **Recherche multicritères** et **filtres avancés**
- **Tableau de bord** statistiques
- Gestion des **pièces jointes**
- **Responsive design**
- **Journalisation** des actions

## CONTRAINTES TECHNIQUES
- Compatible navigateurs modernes ; responsive **desktop / tablette**.
- Respect **RGPD**.
- **Sauvegarde** et **sécurisation** des uploads.
- Limitation des accès selon les droits.
- **Jeux de données de démonstration** (seeders).
- **Tests unitaires et fonctionnels**.
- **Application multilingue** (i18n).

---

## DÉROULÉ DE TRAVAIL ATTENDU (procède par étapes, attends ma validation entre chaque)

1. **Initialisation** : projet Laravel 13, configuration `.env` MySQL, intégration **locale via Vite** d'AdminLTE 4 + Bootstrap 5 (**sans jQuery**), mise en place de L5-Swagger, génération d'un `CLAUDE.md` synthétisant ces règles, structure des dossiers.
2. **Base de données** : migrations + modèles + relations + seeders (listes administrables + jeu de démo).
3. **Auth & rôles/équipes** : authentification, gestion `teams` / `roles` / `team_user_roles`, Policies de base.
4. **Fiches A4** : CRUD complet, Form Requests, éditeur WYSIWYG, pièces jointes, recherche/filtres.
5. **Workflow** : statuts paramétrables, actions, transitions contrôlées par rôle, historisation.
6. **Export PDF** : génération avec versionnage et figement.
7. **Tâches & Gantt** : CRUD tâches, récurrentes, dépendances, Gantt par fiche + vue par développeur.
8. **Reporting de temps** : saisie, agrégations, tableaux de bord statistiques.
9. **Notifications, journalisation, i18n, finitions responsive.**
10. **Tests** unitaires et fonctionnels + documentation (README + Swagger).

À chaque étape : code conforme PSR-12, conventions de nommage imposées, commentaires Swagger sur les routes d'API, et migrations/seeders à jour.

---

## RAPPELS FINAUX
- Toutes les libs JS/CSS **en local** (via Vite de préférence) ; **aucune dépendance à jQuery**.
- Variables en **PascalCase préfixées par type** ; code en **anglais**.
- **Form Requests + Policies systématiques**.
- Commentaire **Swagger** avant chaque API.
- Demande confirmation avant toute décision structurante non précisée ici.

Commence par l'**étape 1** et présente-moi l'arborescence et les commandes d'installation avant d'écrire le code.
