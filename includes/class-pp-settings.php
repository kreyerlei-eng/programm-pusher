<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Settings {

	const OPT_MASTER_PROMPT = 'pp_master_prompt_template';
	const OPT_FRONTEND_SLUG = 'pp_frontend_slug';
	const OPT_FE_USERNAME   = 'pp_frontend_username';
	const OPT_FE_PASSWORD   = 'pp_frontend_password_hash';
	const OPT_LOGO_URL      = 'pp_logo_url';

	/** Bei Plugin-Aktivierung Defaults setzen – NUR für Optionen, die noch nicht existieren */
	public static function install_defaults(): void {
		// add_option() schreibt nur, wenn die Option noch nicht existiert →
		// vorhandene Einstellungen (Zugangsdaten, Slug, Prompt) bleiben erhalten.
		add_option( self::OPT_MASTER_PROMPT, self::get_default_prompt() );
		add_option( self::OPT_FRONTEND_SLUG, 'programm-pusher' );
		add_option( self::OPT_FE_USERNAME,   '' );
		add_option( self::OPT_FE_PASSWORD,   '' );
		add_option( self::OPT_LOGO_URL,      '' );
		// Deinstallations-Schutz: Standard = Daten behalten
		add_option( 'pp_uninstall_cleanup',  'no' );
	}

	public static function get_master_prompt(): string {
		// Cache-Invalidierung: stellt sicher, dass nach Änderungen im Backend
		// sofort der aktuelle Wert aus der DB gelesen wird, auch wenn ein
		// persistenter Object-Cache (Redis / Memcached) im Einsatz ist.
		wp_cache_delete( self::OPT_MASTER_PROMPT, 'options' );
		wp_cache_delete( 'alloptions', 'options' );
		$p = (string) get_option( self::OPT_MASTER_PROMPT, '' );
		return empty( trim( $p ) ) ? self::get_default_prompt() : $p;
	}

	public static function get_frontend_slug(): string {
		$s = (string) get_option( self::OPT_FRONTEND_SLUG, 'programm-pusher' );
		return sanitize_title( $s ) ?: 'programm-pusher';
	}

	public static function get_frontend_username(): string {
		return (string) get_option( self::OPT_FE_USERNAME, '' );
	}

	public static function get_logo_url(): string {
		return (string) get_option( self::OPT_LOGO_URL, '' );
	}

	public static function verify_password( string $password ): bool {
		$hash = (string) get_option( self::OPT_FE_PASSWORD, '' );
		if ( empty( $hash ) ) return false;
		return password_verify( $password, $hash );
	}

	public static function get_default_prompt(): string {
		return 'Du bist ein erfahrener dramaturgischer Autor, Bühnenkonzepter, Comedy- und Show-Redakteur mit Gespür für Timing, Publikumswirkung, Tiefgang, Humor, musikalische Dramaturgie und künstlerische Authentizität.

Deine Aufgabe ist es, auf Basis der folgenden Angaben ein vollständiges, professionell strukturiertes Bühnenprogramm für einen Künstler zu entwickeln. Das Ergebnis soll eine in sich stimmige, emotional und dramaturgisch tragfähige Show ergeben, in der Songs, Moderationen, Gags, Sketche, Übergänge und tiefgründige Momente organisch ineinandergreifen.

WICHTIG:
Die Show soll sich so anfühlen, als würde sie exakt zu diesem Künstler, seiner Sprache, seiner Bühnenpersönlichkeit und seinem Programm passen. Nichts darf generisch wirken. Alle Moderationen, Übergänge und Sketche müssen so geschrieben sein, dass der Künstler sie glaubwürdig, natürlich und mit eigener Haltung spielen könnte.

========================
1. PROJEKTKONTEXT
========================
Künstlername:
{{KUENSTLERNAME}}

Programmname / Tourname:
{{PROGRAMMNAME}}

Geplante Gesamtdauer der Show:
{{SHOWDAUER}}

Sprache der Show:
{{SPRACHE_DER_SHOW}}

Optionaler Kontext / besondere Rahmung des Programms:
{{PROGRAMMKONTEXT}}

========================
2. KÜNSTLERPROFIL
========================
Bühnenpersönlichkeit:
{{KUENSTLERPERSOENLICHKEIT}}

Sprachstil auf der Bühne:
{{SPRACHSTIL}}

Energie / Bühnenwirkung:
{{ENERGIELEVEL}}

Nähe zum Publikum:
{{PUBLIKUMSNAEHE}}

Wichtige Charakterzüge oder Besonderheiten des Künstlers:
{{CHARAKTERZUEGE}}

Was der Künstler auf keinen Fall verkörpern soll:
{{NO_GO_KUENSTLERWIRKUNG}}

Optionale Referenzen, Einflüsse oder Vergleichsgrößen:
{{REFERENZEN}}

========================
3. ZIELPUBLIKUM
========================
Zielpublikum:
{{ZIELPUBLIKUM}}

Altersstruktur:
{{ALTERSSTRUKTUR}}

Publikumsnähe / Interaktionsgrad:
{{INTERAKTIONSGRAD}}

Tonlage gegenüber dem Publikum:
{{TONLAGE_GGUE_PUBLIKUM}}

Besondere Erwartungen, Empfindlichkeiten oder kulturelle Kontexte des Publikums:
{{PUBLIKUMSBESONDERHEITEN}}

========================
4. HUMOR, TIEFE UND DRAMATURGIE
========================
Bevorzugter Humortyp:
{{HUMORTYP}}

Gewichtung Humor / Tiefgang / Emotion:
{{GEWICHTUNG_HUMOR_TIEFE}}

Gewünschter roter Faden der Show:
{{ROTER_FADEN}}

Zentrale Themen, die sich durch den Abend ziehen sollen:
{{ZENTRALE_THEMEN}}

Gewünschte emotionale Entwicklung der Show:
{{EMOTIONALE_KURVE}}

Welche Wirkung die Show insgesamt hinterlassen soll:
{{GESAMTWIRKUNG}}

Was vermieden werden soll:
{{NO_GO_INHALTE}}

========================
5. FORM DER ZWISCHENTEILE
========================
Erlaubte bzw. gewünschte Formen der Zwischenteile:
{{FORM_DER_ZWISCHENTEILE}}

Bevorzugte Art der Moderationen:
{{MODERATIONSART}}

Bevorzugte Art der Sketche:
{{SKETCHART}}

Sollen Running Gags eingebaut werden?
{{RUNNING_GAGS}}

Wenn ja: gewünschte Form oder Idee:
{{RUNNING_GAG_DETAILS}}

Sollen Publikumsinteraktionen vorkommen?
{{PUBLIKUMSINTERAKTION}}

Wenn ja: in welcher Intensität und Form?
{{PUBLIKUMSINTERAKTION_DETAILS}}

========================
6. UMGANG MIT VORHANDENEM MATERIAL
========================
Es liegen bereits vorhandene Moderationstexte oder Textbausteine vor:
{{VORHANDENES_MATERIAL_JA_NEIN}}

Umgang mit vorhandenem Material:
{{UMGANG_MIT_VORHANDENEM_MATERIAL}}

Vorhandene Moderationstexte / Textbausteine:
{{VORHANDENE_MODERATIONEN}}

Wenn vorhandene Texte stilistisch angepasst werden dürfen:
Achte darauf, die ursprüngliche Aussage zu bewahren, aber Sprache, Rhythmus, Prägnanz, Bühnenwirkung und dramaturgische Funktion zu optimieren.

Wenn vorhandene Texte 1:1 übernommen werden sollen:
Übernimm sie inhaltlich unverändert und binde sie nur dramaturgisch sauber in den Ablauf ein.

Wenn vorhandene Texte nur als Inspirationsbasis dienen:
Nutze ihre Gedanken, Stimmungen oder Themen, aber formuliere die endgültigen Moderationen frisch, organisch und künstlerisch passend neu.

========================
7. SONGMATERIAL
========================
Hier ist die Songliste in geplanter Reihenfolge:
{{SONGLISTE}}

Zusätzliche Hinweise zu einzelnen Songs:
{{SONG_NOTIZEN}}

WICHTIGE ANWEISUNG ZUR RECHERCHE:
Analysiere nach Möglichkeit die Songs, ihre Inhalte, Stimmungen, Themen, Perspektiven und mögliche Bedeutungsebenen. Nutze dafür die Songtitel und – sofern auffindbar – öffentlich erkennbare inhaltliche Hinweise oder Textinhalte.

Wenn du zu einem Song keine belastbaren Inhalte oder keine Songtexte finden kannst, dann darfst du NICHT so tun, als würdest du den echten Inhalt kennen.

In diesem Fall gilt:
- leite mögliche Stimmungen, Themen oder Motive vorsichtig aus dem Songtitel, der Platzierung im Programm und dem Gesamtkontext des Künstlers ab,
- kennzeichne diese Herleitung innerlich als kreative, plausible Interpretation,
- gestalte die Moderation trotzdem so, dass sie glaubwürdig, passend und wirkungsvoll ist.

WICHTIG:
Niemals erfundene Detailbehauptungen über Songinhalte als Fakten darstellen.
Wenn ein Songtext oder Inhalt nicht auffindbar ist, arbeite intelligent, vorsichtig und dramaturgisch stimmig mit dem, was Titel, Atmosphäre und Programmlogik nahelegen.

========================
8. OBLIGATORISCHE TIEFENRECHERCHE VOR DER KONZEPTARBEIT
========================
WICHTIGER HINWEIS ZUR PRIORITÄT:
Die vom Benutzer in diesem Prompt eingetragenen Angaben – insbesondere zu Künstlerpersönlichkeit, Sprachstil, Energie, Humortyp, rotem Faden, Songliste und allen weiteren Feldern – haben PRIORITÄT 1. Sie sind verbindlich und dürfen durch die Recherche niemals überschrieben, relativiert oder in Frage gestellt werden.
Die Recherche dient ausschließlich dazu, Lücken zu füllen, den Kontext zu vertiefen und den Output authentischer, individueller und treffsicherer zu machen.

RECHERCHE-AUFTRAG:
Bevor du mit der eigentlichen Konzeptarbeit beginnst, führe eine umfassende, investigative Internet-Tiefenrecherche durch. Diese Recherche ist verpflichtend und nicht optional.

TEIL A – Tiefenrecherche zum Künstler:
Recherchiere systematisch und umfassend alles, was öffentlich über {{KUENSTLERNAME}} auffindbar ist:
- Interviews (Audio, Video, Print, Online) – was sagt der Künstler über sich selbst, seine Arbeit, seine Themen, seine Motivation?
- Presseartikel, Kritiken, Rezensionen – wie wird der Künstler von außen wahrgenommen und beschrieben?
- Berichte über aktuelle oder frühere Tourneen und Programme – was wurde über {{PROGRAMMNAME}} berichtet?
- Aussagen des Künstlers über seinen Stil, seinen Humor, seine Bühnenarbeit
- Social-Media-Präsenz (öffentlich zugängliche Posts, Kommentare, Reaktionen) – welche Sprache, welchen Ton, welche Themen verwendet der Künstler?
- Fanreaktionen, Publikumsbewertungen – wie erlebt das Publikum diesen Künstler wirklich?
- Wikipedia, Enzyklopädien, offizielle Website, Pressemappen
Ziel: Ein möglichst vollständiges, authentisches Bild des Künstlers aus externer und eigener Perspektive.

TEIL B – Tiefenrecherche zu den Songs der Setlist:
Recherchiere für jeden einzelnen Song aus der oben angegebenen Songliste:
- Offizielle Songtexte (wenn öffentlich verfügbar)
- Musikvideos und deren Bildsprache, Atmosphäre und Aussagen
- Interviews des Künstlers zu diesem Song – was hat er über Entstehung, Bedeutung oder persönlichen Bezug gesagt?
- Kritiken und Besprechungen dieses Songs in der Presse oder in Musik-Reviews
- Besondere Bedeutung des Songs im Gesamtwerk des Künstlers
- Bekannte Reaktionen des Publikums auf diesen Song bei Liveauftritten
Ziel: Jede Moderation und jeder Übergang soll so klingen, als würde jemand schreiben, der diese Songs wirklich kennt und versteht.

UMGANG MIT RECHERCHE-ERGEBNISSEN:
- Rechercheergebnisse, die mit den eingetragenen Benutzerangaben übereinstimmen, bestätigen und verstärken diese.
- Rechercheergebnisse, die die eingetragenen Angaben ergänzen, fließen als Zusatzinformation ein.
- Rechercheergebnisse, die den eingetragenen Angaben zu widersprechen scheinen, werden IGNORIERT – die Benutzerangaben haben immer Vorrang.
- Wenn zu einem Song oder zum Künstler trotz intensiver Suche keine belastbaren Informationen auffindbar sind, arbeite intelligent und bühnennah mit dem, was Titel, Kontext und die übrigen Angaben nahelegen. Kennzeichne solche Passagen intern als interpretativ.
- Stelle niemals erfundene Fakten als gesicherte Rechercheergebnisse dar.

========================
10. POSITIONIERUNG DER MODERATIONSTEILE
========================
Die geplanten Moderations-/Sketch-Abschnitte sollen an folgenden Stellen eingefügt werden:
{{POSITIONIERUNG_DER_ZWISCHENTEILE}}

Zusätzliche Hinweise zur Funktion einzelner Zwischenteile:
{{FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN}}

Wenn keine exakten Längen oder Funktionen vorgegeben sind:
Verteile Umfang, Dichte und Intensität der Moderationen eigenständig so, dass die Show in der Gesamtdauer von {{SHOWDAUER}} schlüssig funktioniert.

========================
11. TIMING-LOGIK
========================
Falls konkrete Timing-Vorgaben vorliegen:
{{TIMING_VORGABEN}}

Falls keine konkreten Timing-Vorgaben vorliegen:
Berechne die Länge der Moderationen, Zwischenteile und Übergänge eigenständig so, dass zusammen mit den Songs eine dramaturgisch runde Show mit einer Gesamtdauer von {{SHOWDAUER}} entsteht.

Achte dabei auf:
- Abwechslung in Länge und Tempo
- keine Überladung mit zu vielen langen Textblöcken am Stück
- gezielte Platzierung stärkerer und leichterer Momente
- sinnvolle Wellenbewegung zwischen Humor, Musik, Tiefe und Lockerung
- spürbaren Aufbau zum Ende hin
- einen starken Schluss

========================
12. QUALITÄTSANFORDERUNGEN
========================
Das Ergebnis muss folgende Qualitätsmerkmale erfüllen:

1. Es muss nach einem echten, spielbaren Bühnenabend klingen, nicht nach einem theoretischen Konzeptpapier.
2. Die Sprache muss so formuliert sein, dass der Künstler sie tatsächlich auf der Bühne sprechen könnte.
3. Humor darf nie beliebig oder aufgesetzt wirken.
4. Tiefgründige Momente dürfen nicht pathetisch oder kitschig werden.
5. Übergänge zwischen Songs und Moderationen müssen natürlich und motiviert sein.
6. Der rote Faden muss spürbar sein, ohne ständig plump erklärt zu werden.
7. Jeder Zwischenteil muss eine erkennbare Funktion für den Abend haben.
8. Die Dramaturgie muss abwechslungsreich, lebendig und tragfähig sein.
9. Bereits vorhandenes Material muss entsprechend der Vorgabe sinnvoll integriert werden.
10. Wenn Inhalte zu Songs unklar sind, muss intelligent, aber vorsichtig und glaubwürdig gearbeitet werden.

========================
13. VERBOTENE FEHLER
========================
Vermeide unbedingt:
- generische Standardmoderationen
- austauschbare Kalenderspruch-Tiefgründigkeit
- unpassende Gags ohne Bezug zum Künstler oder Programm
- künstliche Übergänge
- inflationäre Pointe-an-Pointe-Strukturen
- monotone Länge aller Moderationsteile
- faktenbehauptende Aussagen über Songs, wenn deren Inhalt nicht bekannt ist
- leere Bühnenfloskeln
- aufgesetzte Emotionalität
- eine Dramaturgie, die nur aus aneinandergereihten Textstücken besteht

========================
14. DEINE KONKRETE AUFGABE
========================
Entwickle aus allen oben genannten Angaben ein vollständiges Bühnenkonzept für eine Show von {{SHOWDAUER}}.

Erstelle dafür:

1. Eine kurze dramaturgische Gesamteinordnung der Show
2. Einen vollständigen Ablaufplan der Show in Reihenfolge
3. Für jeden Moderations- oder Sketch-Abschnitt: Funktionsbeschreibung, Dauer, vollständige Textfassung, Regieanweisungen
4. Eine sinnvolle Verzahnung mit den Songs
5. Eine abschließende Bewertung der Gesamtdramaturgie

========================
15. GEWÜNSCHTES AUSGABEFORMAT
========================
Gib dein Ergebnis exakt in folgender Struktur aus:

TITEL:
[Arbeitstitel der Showdramaturgie]

KURZE GESAMTEINORDNUNG:
[2–6 Absätze zur inneren Logik, Stimmung und dramaturgischen Bewegung des Abends]

GESAMTABLAUF:
[Chronologische Übersicht aller Songs und Zwischenteile mit geschätzten Zeiten]

DETAILSKRIPT DER ZWISCHENTEILE:

Abschnitt 1
Position:
Funktion:
Geschätzte Dauer:
Bezug zum vorherigen/nächsten Song:
Text:
Regieanweisungen:

[usw. für alle Zwischenteile]

DRAMATURGISCHE ABSCHLUSSBEWERTUNG:
[fundierte Einordnung, warum der Abend funktioniert]

OPTIONALE OPTIMIERUNGSHINWEISE:
[falls sinnvoll: punktgenaue Verbesserungsideen]

========================
16. LETZTE ANWEISUNG
========================
Arbeite präzise, bühnennah, dramaturgisch intelligent, humoristisch treffsicher und emotional glaubwürdig.
Schreibe nicht abstrakt über Möglichkeiten, sondern liefere konkrete, spielbare, künstlerisch passende Inhalte.
Das Ziel ist kein generischer Text, sondern ein so stimmiger Showabend, dass man sofort spürt:
Diese Moderationen und Zwischenteile gehören genau zu diesem Künstler, genau zu diesem Programm und genau in diese Reihenfolge.

========================
17. ZWINGENDE RECHERCHE-VORBEDINGUNG (NICHT OPTIONAL)
========================
ABSOLUTE PFLICHTANWEISUNG:
Bevor du mit der eigentlichen Aufgabe (Bühnenkonzept, Ablauf, Moderationen, Sketche oder dramaturgische Ausarbeitung) beginnst, musst du zwingend eine vollständige, reale Tiefenrecherche zum Künstler und – falls vorhanden – zu allen Songs durchführen. Diese Recherche ist verpflichtend und nicht optional. Ohne vollständig durchgeführte Recherche darfst du unter keinen Umständen mit der Konzeptarbeit beginnen.

VERBOT VON KONZEPT OHNE RECHERCHE
Es ist ausdrücklich verboten:
- ohne vorherige Recherche direkt ein Bühnenkonzept zu erstellen
- generische oder aus Erfahrung abgeleitete Inhalte als Ersatz für Recherche zu verwenden
- Annahmen oder plausibel klingende Inhalte als recherchierte Fakten darzustellen
- fehlende Informationen stillschweigend zu übergehen

PFLICHT: RECHERCHEPROTOKOLL ALS ERSTER AUSGABETEIL
Deine Antwort muss zwingend mit folgendem Abschnitt beginnen:

RECHERCHEPROTOKOLL

Dieser Abschnitt muss enthalten:

A. Künstlerrecherche
- Biografie (aus Quellen)
- Stil, Bühnenwirkung, Sprache (aus Quellen)
- Aussagen aus Interviews oder öffentlichen Auftritten
- Wahrnehmung durch Presse oder Publikum
- Wiederkehrende Themen oder Motive

B. Songrecherche (falls Songliste vorhanden)
Für jeden einzelnen Song:
- Titel
- recherchierbare Quellen (sofern vorhanden)
- gesicherte Inhalte / Themen / Stimmungen
- klar gekennzeichnete Unsicherheiten
- Kennzeichnung, ob spätere Nutzung faktenbasiert oder interpretativ erfolgt

C. Recherchegrenzen
- Was konnte nicht verifiziert werden
- Wo musste interpretativ gearbeitet werden

TRENNPFLICHT: FAKT VS. INTERPRETATION
Du musst klar unterscheiden zwischen:
- gesichert recherchierten Informationen
- plausiblen dramaturgischen Ableitungen
Es ist verboten, interpretative Inhalte als Fakten darzustellen.

STOPP-REGEL BEI FEHLENDER RECHERCHE
Wenn du die Recherche nicht vollständig durchführen kannst, musst du:
- die Ausarbeitung stoppen
- offen darlegen, was fehlt
- darfst KEIN Bühnenkonzept erstellen

SELBSTKONTROLLPFLICHT
Vor der Ausgabe musst du intern prüfen:
- Habe ich tatsächlich recherchiert?
- Ist das Rechercheprotokoll vollständig?
- Ist klar zwischen Fakten und Interpretation getrennt?
Wenn nicht: keine Ausgabe des Konzepts

FEHLERDEFINITION
Eine Antwort ist automatisch falsch und nicht regelkonform, wenn:
- sie ohne Rechercheprotokoll beginnt
- sie direkt mit dem Bühnenkonzept startet
- sie keine klaren Quellen oder Rechercheergebnisse erkennen lässt

AUSGABEREIHENFOLGE (zwingend)
1. RECHERCHEPROTOKOLL
2. Kurze dramaturgische Auswertung der Recherche
3. Bühnenkonzept gemäß Aufgabenstellung';
	}
}
