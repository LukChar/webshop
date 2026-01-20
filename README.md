# CampusShop – Webshop für Studierende

## Projektidee
CampusShop ist ein webbasiertes Shopsystem für Studierende.
Ziel ist es, studienrelevante Produkte übersichtlich darzustellen,
zu favorisieren, zu bewerten und zu kaufen.

## Live-Website
https://webshop-production-31e3.up.railway.app

## GitHub-Repository
https://github.com/LukChar/webshop

## Technische Umsetzung
- Backend: PHP (prozedural, ohne Framework)
- Datenbank: MySQL (Railway)
- Frontend: HTML5, Tailwind CSS
- Icons: Google Material Symbols
- Session-Handling: PHP Sessions
- DB-Zugriff: PDO mit Prepared Statements

## Projektstruktur
- `public/` – öffentliche Seiten (Einstiegspunkt)
- `includes/` – DB-Verbindung, Header, Auth, Utilities
- `admin/` – Admin-Bereich (Produkt- & Kategorienverwaltung)

## Einstiegspunkt
Startseite der Anwendung:  
`/public/index.php`

## Sicherheit & Konfiguration
- Datenbank-Zugangsdaten werden über Environment-Variablen geladen
- OAuth-Keys (Google Login) sind **nicht** im Repository enthalten
- Lokale Secrets liegen in `.local.php` Dateien (nicht versioniert)

## Datenbank
- Relationale MySQL-Datenbank
- Tabellen u. a.: users, products, categories, stock, orders, reviews, favorites
- Struktur siehe Projektdokumentation

## Rollenverteilung
**Lukas**
- Backend- & Frontend-Entwicklung
- Warenkorb, Favoriten, Admin-Bereich
- UI/UX mit Tailwind CSS
- Hosting & Deployment (Railway)

**Oktay**
- Produkt- & Kategoriesystem
- Suchfunktion
- Bewertungssystem
- Google Login & PayPal-Integration

## Versionsstand
Finale Abgabeversion
