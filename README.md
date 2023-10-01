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
http://localhost/index.php?Ort=Oberammergau&Strasse=Schwedengasse&Hausnummer=12&Tag=2023-10-25
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

## Auswertung

### PHP-Code

```php
<?php

$json = file_get_contents("https://mt183.de/abfallgap/?Ort=Oberammergau&Strasse=Schwedengasse&Hausnummer=12&Hausnummerzusatz=");
  
  // JSON dekodieren
  $data = json_decode($json);
  
  // Werte auslesen 
  $address = $data->Daten->Adresse;
  $status = $data->Daten->Stand;
  
  // Termine ausgeben
  echo "Adresse: " . $address . "<br>"; 
  echo "Stand: " . $status . "<br>";
  
  foreach ($data->Daten->Müll as $wasteType) {
  
    echo "<h4>" . $wasteType->Typ . " (" . $wasteType->Zeichen . ")</h4>";
    
    // Termine ausgeben
    foreach ($wasteType->Termine as $termin) {
      echo $termin . "<br>";
    }
  
    echo "<br>";
  
  }
```

## JS-Code
```Javascript
// URL zur API
const url = 'https://mt183.de/abfallgap/?Ort=Oberammergau&Strasse=Schwedengasse&Hausnummer=12';

// Daten abrufen
async function getData() {

  // Fetch Request senden 
  const response = await fetch(url);

  // Antwort als JSON parsen
  const data = await response.json();

  // Daten verarbeiten
  showData(data);

}

// Daten anzeigen
function showData(data) {

  // Werte auslesen
  const address = data.Daten.Adresse;
  const status = data.Daten.Stand;
  let waste = data.Daten.Müll;
  waste = Object.entries(waste);
  // Ausgabe zusammenstellen
  let output = `
    <h3>Adresse</h3>
    ${address}

    <h3>Stand</h3> 
    ${status}
  `;

  // Termine auslesen
  for (let i = 0; i < waste.length; i++) {
    waste[i] = waste[i][1];
    output += `
      <h4>${waste[i].Typ}</h4>
      <p>${waste[i].Termine}</p>
    `;
  }
  

  // Ausgabe setzen
  document.getElementById('data').innerHTML = output;

}

// Los gehts
getData();
```


#### Tagabfrage
##### Eingabe
```Javascript
// URL zur API
const url = 'https://mt183.de/abfallgap/?Ort=Oberammergau&Strasse=Schwedengasse&Hausnummer=12&Tag=2023-10-10';

// Daten abrufen
async function getData() {

  // Fetch Request senden 
  const response = await fetch(url);

  // Antwort als JSON parsen
  const data = await response.json();

  // Daten verarbeiten
  showData(data);

}

// Daten anzeigen
function showData(data) {

  // Werte auslesen
  const date = data.Daten.Datum;
  const status = data.Daten.Abfuhr;
  let output = `
  <h3>Abfuhr</h3>
  ${date}

  <h3>Stand</h3> 
  ${status}
`;
  if (status != false) {
    const Typ = data.Daten.Typ;
    const Zeichen = data.Daten.Zeichen;
    output += `
      <h4>${Typ}</h4>
      <p>${Zeichen}</p>
    `;

  }
  // Ausgabe setzen
  document.getElementById('data').innerHTML = output;

}

// Los gehts
getData();
```


##### Ausgabe

```
Abfuhr
2023-10-10
Stand
true
Restmüll
R
``````
Anhand der Antwort kann man erkennen, dass die Abholung am 10.10.2023 stattfindet und Aktionen anhand der Werte auslösen.

## Hinweise

- Es werden nur Daten für den Landkreis Garmisch-Partenkirchen unterstützt
- Bei ungültigen Parametern kann es zu Fehlern kommen
- Performance ist nicht optimiert, Caching empfohlen bei häufigen Anfragen
