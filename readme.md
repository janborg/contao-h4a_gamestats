# contao-h4a_gamestats

Spielberichte, Spielerstatistiken und Spielverläufe von [handball.net](https://www.handball.net) (handball4all) in Contao CMS integrieren.

Das Bundle ergänzt Kalender-Events um detaillierte Statistiken zu einem Handballspiel – Torschützen, Strafen und Karten je Mannschaft sowie den kompletten Spielverlauf als Zeitstrahl bzw. Diagramm. Die Daten werden automatisch über die handball.net-Spiele-API abgerufen und in Contao gespeichert.

## Voraussetzungen

- PHP `^8.3`
- Contao `^5.3` (core-bundle & calendar-bundle)
- [janborg/contao-h4a_tabellen](https://github.com/janborg/contao-h4a_tabellen) `^5.0` – liefert den `HandballnetApiClient`, die Team-/Turnier-Verwaltung sowie das `H4aReportUpdatedEvent`, an das dieses Bundle andockt.

## Installation

Über den Contao Manager (Paketname `janborg/contao-h4a_gamestats`) oder via Composer:

```bash
composer require janborg/contao-h4a_gamestats
```

## Funktionsumfang

### Content-Elemente

Im Backend stehen unter der Gruppe **handball4all** drei Content-Elemente zur Verfügung. Alle werden über Kalender, Saison und Turnier (handball.net) sowie die jeweilige Spiel-ID konfiguriert.

| Element | Typ | Beschreibung |
| --- | --- | --- |
| **Spielbericht (Gamescore)** | `h4a_gamescore` | Statistik eines einzelnen Spiels je Mannschaft: Spieler mit Toren, 7-Meter, gelben/roten/blauen Karten und Zeitstrafen sowie die Offiziellen (A–D). |
| **Saisonstatistik (Seasonscore)** | `h4a_seasonscore` | Aggregierte Spielerstatistik der eigenen Mannschaft über eine ganze Saison/Turnier. |
| **Zeitstrahl (Timeline)** | `h4a_timeline` | Spielverlauf mit Torfolge als Zeitstrahl und Diagramm (Chart.js) inkl. Spielstand über die Zeit. |

### Frontend-Modul

| Modul | Typ | Beschreibung |
| --- | --- | --- |
| **Spielbericht** | `h4a_event_report` | Kompletter Spielbericht zum aktuellen Event (über `auto_item`): Heim- und Gaststatistik plus Zeitstrahl-Diagramm in einem Modul. |

### Automatischer Datenabruf

Die Spielstatistiken werden auf mehreren Wegen aktuell gehalten:

- **Event-Listener** – reagiert auf das `H4aReportUpdatedEvent` aus `contao-h4a_tabellen`. Sobald ein Spielergebnis vorliegt, werden Playerscores und Timeline für das Kalender-Event abgerufen und gespeichert.
- **Cronjobs** (stündlich) – rufen für vergangene Events mit vollständigem Ergebnis (`hn_resultComplete`) automatisch die noch fehlenden Spielberichte ab:
  - `UpdateH4aScoresCron::updateScores` – Spielerstatistiken
  - `UpdateH4aTimelineCron::updateTimeline` – Spielverlauf
- **Console-Commands** – für manuelle Aktualisierung bzw. Backfill:

  ```bash
  # Spielerstatistiken aktualisieren
  vendor/bin/contao-console h4a:update:scores [--update-all]

  # Spielverlauf/Timeline aktualisieren
  vendor/bin/contao-console h4a:update:timeline [--update-all]
  ```

  Standardmäßig werden nur Events ohne vorhandene Daten verarbeitet. Mit `--update-all` werden bereits gespeicherte Statistiken überschrieben.

### Backend-Werkzeuge

Innerhalb des Kalender-Moduls gibt es Lookup-Operationen, um Statistiken (`lookup_scores`) und Spielverlauf (`lookup_timeline`) eines Events manuell aus handball.net nachzuladen. Gespeicherte Daten liegen in den Tabellen `tl_h4a_playerscores` und `tl_h4a_timeline` und sind im Backend einsehbar.

## Datenquelle & Mapping

Die Klasse `HandballnetGamestatsParser` ruft den kombinierten Spiel-Endpunkt von handball.net (Summary + Lineup + Events) einmalig ab und bildet ihn auf die intern verwendeten Datenstrukturen ab:

- **Spieler** → Tore, 7-Meter-Tore/-Versuche, gelbe/rote/blaue Karten, Zeitstrafen
- **Offizielle** → Verwarnungen, Zeitstrafen, Disqualifikationen (Position OA–OD → A–D)
- **Events** → chronologische Timeline mit Spielzeit, aktuellem Spielstand, Aktion (Tor, 7m-Tor, 7m-Versuch, 2-min, Auszeit, Verwarnung, Disqualifikation), Team und Spieler

> Hinweis: Ab Version 3/5 nutzt das Bundle die handball.net-API. Der frühere PDF-basierte Report-Parser wurde ersetzt.

## Templates

Die Ausgabe lässt sich über eigene Twig-Templates anpassen (Standardvorlagen im Bundle):

- Content-Elemente: `ce_h4a_gamescore`, `ce_h4a_seasonscore`, `ce_h4a_timeline`
- Frontend-Modul: `mod_h4a_event_report`
- Partials: `h4a_gamescores`, `h4a_timeline`

Das Timeline-Diagramm nutzt das mitgelieferte `chart.umd.min.js` (Chart.js).

## Entwicklung

```bash
composer all          # depcheck + ecs + phpstan + unit-tests
composer ecs          # Coding Standard (Fix)
composer phpstan      # statische Analyse
composer unit-tests   # PHPUnit
```

## Lizenz

MIT © Jan Lünborg – siehe [LICENCE.md](LICENCE.md).
