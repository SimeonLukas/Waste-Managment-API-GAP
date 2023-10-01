<?php
// Zum Debuggen
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
header("Content-Type: application/json");
$SessionId = "";
$ApplicationName = "";
$Build = "";
$Day = "";
if (isset($_GET["Tag"])) {
$Day = $_GET["Tag"];
$Check = false;
$Typ = "";
$Zeichen = "";
if ($Day == "Heute") {
    $Day = date('Y-m-d');
} elseif ($Day == "Morgen") {
    $Day = date("Y-m-d", strtotime("+1 day"));
} else {
    $Day = date("Y-m-d", strtotime($_GET["Tag"]));
}

if (validateDate($Day) != true) {
    echo "Datumsformat falsch! \n";
    echo $Day;
}}

$html = file_get_contents("https://abfuhrkalender.lkr-gap.de/webapps/WasteManagementGarmisch/WasteManagementServlet?SubmitAction=wasteDisposalServices&InFrameMode=TRUE");

$doc = new DOMDocument;
$doc->loadHTML($html);
$input = $doc->getElementsByTagName("input");
$length = $input->length;
for ($i = 0; $i < $length; $i++) {
    if ($input->item($i)->getAttribute("name") == "SessionId") {
        $SessionId = $input->item($i)->getAttribute("value");
    }
    if ($input->item($i)->getAttribute("name") == "ApplicationName") {
        $ApplicationName = $input->item($i)->getAttribute("value");
    }
    if ($input->item($i)->getAttribute("name") == "Build") {
        $Build = $input->item($i)->getAttribute("value");
    }
}
$url = "https://abfuhrkalender.lkr-gap.de/webapps/WasteManagementGarmisch/WasteManagementServlet";
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$headers = array(
    "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$data = "Ajax=false&AjaxDelay=0&ApplicationName=" . $ApplicationName . "&Build=" . $Build . "&Focus=Hausnummer&ID=&InFrameMode=TRUE&IsLastPage=false&IsSubmitPage=false&Method=POST&ModulName=&NewTab=default&NextPageName=&PageName=Lageadresse&PageXMLVers=1.0&VerticalOffset=0&RedirectFunctionNachVorgang=&PageName=Lageadresse&SessionId=" . $SessionId . "&SubmitAction=CITYCHANGED&Ort=" . $_GET["Ort"] . "&Strasse=" . $_GET["Strasse"] . "&Hausnummer=" . $_GET["Hausnummer"] . "&Hausnummerzusatz=" . $_GET["Hausnummerzusatz"];
$data2 = "Ajax=false&AjaxDelay=0&ApplicationName=" . $ApplicationName . "&Build=" . $Build . "&Focus=Hausnummer&ID=&InFrameMode=TRUE&IsLastPage=false&IsSubmitPage=false&Method=POST&ModulName=&NewTab=default&NextPageName=&PageName=Lageadresse&PageXMLVers=1.0&VerticalOffset=0&RedirectFunctionNachVorgang=&PageName=Lageadresse&SessionId=" . $SessionId . "&SubmitAction=forward&Ort=" . $_GET["Ort"] . "&Strasse=" . $_GET["Strasse"] . "&Hausnummer=" . $_GET["Hausnummer"] . "&Hausnummerzusatz=" . $_GET["Hausnummerzusatz"];
// Zweimal die url mit Parametern aufrufen, damit SessionId registriert wird und der Ort klar ist.
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_exec($curl);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data2);
$html = curl_exec($curl);

$doc = new DOMDocument;
$doc->loadHTML($html);
$JsonObj = new stdClass();
$JsonObj->Daten = new stdClass();

// Generiere JSON
$array = [];
$Kopfdaten = $doc->getElementsByTagName("table")[0]->getElementsByTagName("tr");
$JsonObj->Daten->Adresse = trim($Kopfdaten->item(0)->textContent);
$JsonObj->Daten->Stand = trim($Kopfdaten->item(1)->textContent);
$JsonObj->Daten->Müll = new stdClass();

// Restmüll
$array = [];
if ($doc->getElementById("termineR")) {
    $termineR = $doc->getElementById("termineR")->getElementsByTagName("tr");
    $length = $termineR->length;
    for ($i = 0; $i < $length; $i++) {
        $date = trim($termineR->item($i)->textContent);
        $date = str_replace('!', '', $date);
        $date = substr($date, 3);
        $date = strtotime($date);
        $date = date('Y-m-d', $date);
        $array[] = $date;
        if ($date == $Day && isset($_GET["Tag"])){
          $Check = True;
          $Typ = "Restmüll";
          $Zeichen = "R";
        }
    }
    $JsonObj->Daten->Müll->R = new stdClass();
    $JsonObj->Daten->Müll->R->Termine = array_filter($array);
    $JsonObj->Daten->Müll->R->Typ = "Restmüll";
    $JsonObj->Daten->Müll->R->Zeichen = "R";
}

// Gelbetonne
$array = [];
if ($doc->getElementById("termineG")) {
    $termineG = $doc->getElementById("termineG")->getElementsByTagName("tr");
    $length = $termineG->length;
    for ($i = 0; $i < $length; $i++) {
        $date = trim($termineG->item($i)->textContent);
        $date = str_replace('!', '', $date);
        $date = substr($date, 3);
        $date = strtotime($date);
        $date = date('Y-m-d', $date);
        $array[] = $date;
        if ($date == $Day && isset($_GET["Tag"])){
          $Check = True;
          $Typ = "Gelbetonne";
          $Zeichen = "G";
        }
    }
    $JsonObj->Daten->Müll->G = new stdClass();
    $JsonObj->Daten->Müll->G->Termine = array_filter($array);
    $JsonObj->Daten->Müll->G->Typ = "Gelbetonne";
    $JsonObj->Daten->Müll->G->Zeichen = "G";

}

// Papier
$array = [];
if ($doc->getElementById("termineP")) {
    $termineP = $doc->getElementById("termineP")->getElementsByTagName("tr");
    $length = $termineP->length;
    for ($i = 0; $i < $length; $i++) {
        $date = trim($termineP->item($i)->textContent);
        $date = str_replace('!', '', $date);
        $date = substr($date, 3);
        $date = strtotime($date);
        $date = date('Y-m-d', $date);
        $array[] = $date;
        if ($date == $Day && isset($_GET["Tag"])){
          $Check = True;
          $Typ = "Papier";
          $Zeichen = "P";
        }
    }
    $JsonObj->Daten->Müll->P = new stdClass();
    $JsonObj->Daten->Müll->P->Termine = array_filter($array);
    $JsonObj->Daten->Müll->P->Typ = "Papier";
    $JsonObj->Daten->Müll->P->Zeichen = "P";
}

// Biomüll
$array = [];
if ($doc->getElementById("termineB")) {
    $termineB = $doc->getElementById("termineB")->getElementsByTagName("tr");
    $length = $termineB->length;
    for ($i = 0; $i < $length; $i++) {
        $date = trim($termineB->item($i)->textContent);
        $date = str_replace('!', '', $date);
        $date = substr($date, 3);
        $date = strtotime($date);
        $date = date('Y-m-d', $date);
        $array[] = $date;
        if ($date == $Day && isset($_GET["Tag"])){
          $Check = True;
          $Typ = "Biomüll";
          $Zeichen = "B";
        }
    }
    $JsonObj->Daten->Müll->B = new stdClass();
    $JsonObj->Daten->Müll->B->Termine = array_filter($array);
    $JsonObj->Daten->Müll->B->Typ = "Biomüll";
    $JsonObj->Daten->Müll->B->Zeichen = "B";
}

// Sondermüll
$array = [];
if ($doc->getElementById("termineS")) {
    $termineS = $doc->getElementById("termineS")->getElementsByTagName("tr");
    $length = $termineS->length;
    for ($i = 0; $i < $length; $i++) {
        $date = trim($termineS->item($i)->textContent);
        $date = str_replace('!', '', $date);
        $date = substr($date, 3);
        $date = strtotime($date);
        $date = date('Y-m-d', $date);
        $array[] = $date;
        if ($date == $Day && isset($_GET["Tag"])){
          $Check = True;
          $Typ = "Sondermüll";
          $Zeichen = "S";
        }
    }
    $JsonObj->Daten->Müll->S = new stdClass();
    $JsonObj->Daten->Müll->S->Termine = array_filter($array);
    $JsonObj->Daten->Müll->S->Typ = "Sondermüll";
    $JsonObj->Daten->Müll->S->Zeichen = "S";

}

curl_close($curl);
$JSON = json_encode($JsonObj, JSON_PRETTY_PRINT);

if (isset($_GET["Tag"])) {
    $JsonObj = new stdClass();
    $JsonObj->Daten = new stdClass();
    $JsonObj->Daten->Datum = $Day;
    $JsonObj->Daten->Abfuhr = $Check;
    if ($Check != false){
    $JsonObj->Daten->Typ = $Typ;
    $JsonObj->Daten->Zeichen = $Zeichen;
    }
    $JSON = json_encode($JsonObj, JSON_PRETTY_PRINT);
    echo $JSON;
} else {
    echo $JSON;
}

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}
