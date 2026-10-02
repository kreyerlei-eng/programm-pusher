=== Programm-Pusher ===
Contributors: jurgen
Tags: stage show, prompt, AI, generator, comedy
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 0.9.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prompt-Generator für Bühnenshows. Sammelt strukturierte Showdaten und
erstellt daraus einen fertigen KI-Prompt zum Kopieren.

== Description ==

Programm-Pusher stellt eine passwortgeschützte Web-App unter einer
konfigurierbaren URL bereit (z. B. https://deinedomain.de/programm-pusher/).

Produktseite und kostenloser Download: https://amepres-tools.de/lp/programm-pusher

**Was das Plugin tut:**

* Stellt eine vollständige Browser-App an einer eigenen URL bereit
* Login per Benutzername + Passwort (im WP-Backend einstellbar)
* Mehrere Projekte anlegen, benennen, speichern und verwalten
* 8 strukturierte Eingabeblöcke (Künstlerprofil, Sprache, Publikum,
  Humor, Dramaturgie, Moderationsstil, Setlist, Zwischenteile)
* Prompt automatisch aus den eingegebenen Daten generieren
* Fertigen Prompt kopieren oder als .txt herunterladen
* Vollständig anpassbarer Master-Prompt mit Platzhaltern

**Was das Plugin NICHT tut:**

* Keine direkte KI-Integration – der Prompt wird nur generiert,
  nicht automatisch an eine KI gesendet
* Keine Daten werden an externe Dienste übertragen

== Installation ==

1. Plugin-ZIP hochladen unter WordPress → Plugins → Neu hinzufügen
2. Plugin aktivieren
3. Im WordPress-Backend unter **Programm-Pusher** navigieren
4. Slug, Benutzername und Passwort festlegen und speichern
5. Einmalig unter WordPress → Einstellungen → Permalinks speichern
   (damit die Rewrite-Regeln aktiv werden)
6. Frontend unter der angezeigten URL aufrufen und einloggen

== Frequently Asked Questions ==

= Unter welcher URL ist das Frontend erreichbar? =

Unter https://deinedomain.de/[slug]/ – den Slug kannst du im
WordPress-Backend unter Programm-Pusher frei festlegen.

= Kann ich den Master-Prompt anpassen? =

Ja. Im WP-Backend unter Programm-Pusher kannst du den Prompt vollständig
bearbeiten. Platzhalter im Format {{PLATZHALTER}} werden automatisch durch
die jeweiligen Projektdaten ersetzt.

= Werden Projektdaten gespeichert? =

Ja. Alle Projekte werden in der WordPress-Datenbank gespeichert und können
jederzeit wieder geöffnet und bearbeitet werden.

= Was passiert beim Deinstallieren? =

Standardmäßig bleiben Projekte und Einstellungen beim Löschen des Plugins
erhalten, damit eine Neuinstallation nichts verliert. Nur wenn im
WordPress-Backend unter Programm-Pusher „Alle Daten beim Deinstallieren
unwiderruflich löschen“ eingeschaltet ist, werden alle Plugin-Optionen und alle
gespeicherten Projektdaten unwiderruflich aus der Datenbank entfernt.

== Changelog ==

= 0.9.4 =
* Sitzungs-Cookie gehärtet: HttpOnly, Secure auf https-Seiten, SameSite=Lax, strikter Sitzungsmodus
* Projektseite im Plugin-Kopf auf https://amepres-tools.de/lp/programm-pusher korrigiert
* Beschreibung des Deinstallierens an das tatsächliche Verhalten angepasst

= 0.9.0 =
* Drei-Punkte-Menü auf Projektkarten (Umbenennen, Duplizieren, Bearbeiten, Löschen mit Bestätigung)
* Projekt-Duplizierung mit Übernahme aller Daten und "(Dublikat)"-Suffix
* Inline-Umbenennen direkt auf der Projektkarte
* Inline-Löschbestätigung ohne Browser-Dialog
* Logo-Upload im WP-Backend (WP Media Library)
* Logo in Anmeldemaske und App-Header
* Integriertes Hilfe-System mit Side-Panel und 12 Abschnitten inkl. Erste-Schritte-Anleitung
* Tiefenrecherche-Anweisung im Default-Prompt (KI-Recherche zu Künstler + Songs)

= 2.0.0 =
* Komplette Neuentwicklung als Browser-basierte SPA
* Frontend mit Login, Projekt-Dashboard und Prompt-Editor
* Passwortschutz mit PHP-Sessions und CSRF-Tokens
* Repeatable Fields für Setlist (Block G) und Zwischenteile (Block H)
* Auto-Titel aus Künstlername + Programmname
* Prompt-Download als .txt-Datei

= 1.0.0 =
* Erste Version (WP-Admin-basiert)

== Upgrade Notice ==

= 2.0.0 =
Vollständige Neuentwicklung. Bitte nach Update einmalig unter
WordPress → Einstellungen → Permalinks speichern.
