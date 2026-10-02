# Programm-Pusher

**Prompt-Generator für Bühnenprogramme und Showkonzepte**: ein kostenloses WordPress-Plugin von [AMEPRES-Tools](https://amepres-tools.de/lp/programm-pusher).

Künstlerinnen und Künstler, Comedians, Moderatorinnen und Moderatoren, Autorinnen und Autoren sowie Showproduzentinnen und Showproduzenten erfassen ihre Show strukturiert im Browser. Der Programm-Pusher macht daraus einen fertigen KI-Prompt für ChatGPT oder andere KI-Werkzeuge, zum Kopieren oder als Textdatei.

## Funktionen

- Strukturierte Erfassung von Showdaten in acht Eingabeblöcken: Künstlerprofil, Sprache, Publikum, Humor, Dramaturgie, Moderationsstil, Setlist, Zwischenteile
- Automatische Prompt-Erzeugung aus den eingegebenen Daten
- Prompt kopieren oder als `.txt`-Datei herunterladen
- Mehrere Showprojekte parallel: anlegen, umbenennen, duplizieren, löschen
- Browser-App unter eigener Adresse mit Passwortschutz
- Projekt-Dashboard und eingebaute Hilfe
- Vollständig anpassbarer Master-Prompt mit Platzhaltern im Format `{{PLATZHALTER}}`
- **Keine Datenübertragung an externe KI-Dienste**: Der Prompt wird nur erzeugt, nicht an eine KI geschickt. Alle Projekte bleiben in der eigenen WordPress-Datenbank.

## Voraussetzungen

- WordPress ab 6.5
- PHP ab 8.0

## Installation

1. Das Plugin-ZIP aus den [Releases](https://github.com/kreyerlei-eng/programm-pusher/releases) oder von der [Produktseite](https://amepres-tools.de/lp/programm-pusher) herunterladen.
2. In WordPress unter **Plugins, Installieren, Plugin hochladen** einspielen und aktivieren.
3. Im WordPress-Backend unter **Programm-Pusher** Adresse (Slug), Benutzername und Passwort festlegen und speichern.
4. Einmal unter **Einstellungen, Permalinks** speichern, damit die Adresse aktiv wird.
5. Die angezeigte Adresse aufrufen, zum Beispiel `https://deinedomain.de/programm-pusher/`, und anmelden.

## Deinstallieren

Beim Löschen des Plugins bleiben Projekte und Einstellungen standardmäßig erhalten. Nur wenn im Backend „Alle Daten beim Deinstallieren unwiderruflich löschen“ eingeschaltet ist, werden alle Daten entfernt.

## Lizenz

GPL v2 oder später, siehe <https://www.gnu.org/licenses/gpl-2.0.html>.

## Downloads

Fertige Versionen als installierbares Plugin-ZIP stehen unter [Releases](https://github.com/kreyerlei-eng/programm-pusher/releases) und kostenlos auf der [Produktseite im AMEPRES Tools Shop](https://amepres-tools.de/lp/programm-pusher).
