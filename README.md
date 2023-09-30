# Müllabfuhr-Abfrage

Dieses Programm ermöglicht die Abfrage von Müllabfuhrterminen über die gehackte API des Landkreises Garmisch-Partenkirchen.

Link zum selbstgehosteten Webserver zur freien Nutzung der API:
[API-Endpunkt](https://mt183.de/abfallgap/?Ort=Oberammergau&Strasse=Schwedengasse&Hausnummer=12)

## Nutzung

Um die Müllabfuhrtermine für einen Ort abzufragen, sendet man einen GET Request an die selbstgehostete Datei oder den oben geführten Link mit folgenden Parametern:

- Ort: Name des Ortes
- Strasse: Name der Straße  
- Hausnummer: Hausnummer
- Hausnummerzusatz: Zusatz zur Hausnummer (optional)

Beispiel:

```
http://localhost/index.php?Ort=Oberammergau&Strasse=Schwedengasse&Hausnummer=12 
```
Die Antwort enthält ein JSON Objekt mit den Terminen für Restmüll, Gelbe Tonne, Papier, Bio und Sondermüll.

```json
{
    "Daten": {
        "Adresse": "Schwedengasse   12, 82487 Oberammergau",
        "Stand": "Jahres\u00fcbersicht 2023",
        "M\u00fcll": {
            "R": {
                "Termine": [
                    "2023-10-10",
                    "2023-10-24",
                    "2023-11-07",
                    "2023-11-21",
                    "2023-12-05",
                    "2023-12-19"
                ],
                "Typ": "Restm\u00fcll",
                "Zeichen": "R"
            },
            "G": {
                "Termine": [
                    "2023-10-11",
                    "2023-11-08",
                    "2023-12-06"
                ],
                "Typ": "Gelbetonne",
                "Zeichen": "G"
            },
            "P": {
                "Termine": [
                    "2023-10-25",
                    "2023-11-22",
                    "2023-12-19"
                ],
                "Typ": "Papier",
                "Zeichen": "P"
            },
            "S": {
                "Termine": [
                    "2023-10-21"
                ],
                "Typ": "Sonderm\u00fcll",
                "Zeichen": "S"
            }
        }
    }
}
```

Um zu prüfen, ob an einem bestimmten Tag Müll abgeholt wird, kann man zusätzlich den Parameter `Tag` angeben, mögliche Werte sind Heute, Morgen und ein Datum im Format Y-m-d (2023-12-01):

```
http://localhost/index.php?Ort=Oberammergau&Strasse=Schwedengasse&Hausnummer=12&Tag=2023-03-15
```

Die Antwort enthält dann ein vereinfachtes JSON Objekt mit dem Datum, ob an diesem Tag Müll abgeholt wird sowie Typ und Zeichen falls eine Abholung stattfindet.

```json
{
    "Daten": {
        "Datum": "2023-10-25",
        "Abfuhr": true,
        "Typ": "Papier",
        "Zeichen": "P"
    }
}
```

Bei einem Datum ohne Abholung wird ein `false` in Abfuhr zurückgegeben.

```json
{
    "Daten": {
        "Datum": "2023-10-20",
        "Abfuhr": false
    }
}
```


## Installation

- Datei index.php auf Webserver hochladen
- PHP und php-curl muss installiert und aktiviert sein  
- ggf. Pfad zur Datei in `.htaccess` eintragen

## Hinweise

- Es werden nur Daten für den Landkreis Garmisch-Partenkirchen unterstützt
- Bei ungültigen Parametern kann es zu Fehlern kommen
- Performance ist nicht optimiert, Caching empfohlen bei häufigen Anfragen
