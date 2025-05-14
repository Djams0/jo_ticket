# 🎫 JJO Tickets

## 📌 Présentation

**JJO Tickets** est une application web de gestion de billets pour les matchs de football des Jeux Olympiques. Elle repose sur une architecture découpée en trois modules :

- 🧠 Une API en **Node.js/Express.js** qui centralise la logique métier
- 📱 Une application web pour les **supporters** (achat et gestion de billets)
- 🎫 Une interface pour les **stadiers** (scan et validation de billets)

Les échanges se font via une API REST sécurisée. Les billets sont associés à un **QR code unique** pour le contrôle d’accès au stade.

---

## 🛠️ Architecture du projet

/admin/api/ -> API Node.js + Express + MySQL
/mobile/ -> Application web supporter (HTML, CSS, JS, Bootstrap)
/scanner/ -> Interface scan QR code (JavaScript, QR Scanner)

### 🔐 Technologies utilisées

- **Back-end** : Node.js, Express.js, MySQL
- **Front-end** : HTML, CSS, JavaScript, Bootstrap
- **Sécurité** : Sessions (ou JWT), CORS
- **Fonctionnalités avancées** : QR Code (génération + lecture)

---

## 🚀 Lancer le projet

### 1. Pré-requis

- Node.js installé (v16+ recommandé)
- MySQL ou MariaDB
- Live Server (VS Code ou équivalent) pour le front

### 2. Installer les dépendances

Dans `/admin/api/` :

```bash
npm install

3. Configuration de la base de données
Crée une base de données MySQL (ex: jjo_tickets)

Importer les données :

mysql -u root -p jjo_tickets < data_jo.sql
Vérifie/modifie les identifiants de connexion dans /admin/api/config/db.js

4. Démarrer l'API
npm start
L’API sera disponible sur :
http://localhost:3000

5. Lancer les interfaces web
Ouvre les fichiers index.html dans /mobile/ et /scanner/ avec Live Server

Assure-toi que les appels à l’API utilisent bien http://localhost:3000

Fonctionnalités principales
API Node.js
Authentification utilisateur (session)

Gestion des matchs, scores, catégories de places

Achat de billets avec QR code

Endpoints sécurisés pour vérification

Application mobile (Supporters)
Liste des matchs

Connexion/inscription via API

Achat de billets (Silver, Gold, Platinium)

QR codes personnels

Interface Stadier
Scan via webcam ou image

Vérification via API

Affichage : nom, match, validité du billet