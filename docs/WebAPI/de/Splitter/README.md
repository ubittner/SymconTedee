[![Image](../../../../imgs/tedee_logo.png)](https://tedee.com)

### Splitter Web API

Dieses Modul stellt die Kommunikation mit der [tedee Web API](https://tedee-tedee-api-doc.readthedocs-hosted.com/en/latest/index.html#) her.

Für dieses Modul besteht kein Anspruch auf Fehlerfreiheit, Weiterentwicklung, sonstige Unterstützung oder Support.  
Bevor das Modul installiert wird, sollte unbedingt ein Backup von IP-Symcon durchgeführt werden.  
Der Entwickler haftet nicht für eventuell auftretende Datenverluste oder sonstige Schäden.  
Der Nutzer stimmt den o.a. Bedingungen, sowie den Lizenzbedingungen ausdrücklich zu.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Kommunikation mit der tedee Web API

### 2. Voraussetzungen

- IP-Symcon ab Version 6.0
- tedee Smart Lock
- tedee Bridge
- Persönlicher Zugangsschlüssel (PAK)
- Internetverbindung

### 3. Software-Installation

* Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
* Über den Module Store das `tedee` Modul installieren.

### 4. Einrichten der Instanzen in IP-Symcon

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `Tedee Splitter Web API` auswählen, welches unter dem Hersteller `tedee` aufgeführt ist.
- Es wird eine neue `Tedee Splitter Web API` Instanz angelegt.
- Weitere Informationen zum Hinzufügen von Instanzen finden Sie in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name                    | Beschreibung
----------------------- | ------------------
Aktiv                   | Schaltet den Splitter in- bzw. aktiv
Personal Acess Key      | persönlichen Zugangsschlüssel (PAK)
Timeout                 | Netzwerk Timeout

Um sich über den persönlichen Zugangsschlüssel (PAK) zu authentifizieren, müssen Sie ihn zunächst in Ihrem Konto generieren.  
Dazu können Sie das [Tedee Portal](https://portal.tedee.com) verwenden.  
Weitere Informationen zum Erstellen des persönlichen Zugangsschlüssels finden Sie in der [API Dokumentation](https://tedee-tedee-api-doc.readthedocs-hosted.com/en/latest/howtos/authenticate.html#personal-access-key).

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt.  
Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Es werden keine Statusvariablen verwendet.

#### Profile

Es werden keine Profile verwendet.

### 6. WebFront

Der Splitter hat im WebFront keine Funktionalität.

### 7. PHP-Befehlsreferenz

```text
Vorhandene Geräte ermitteln

TEDEESW_GetDevices(integer $InstanzID);

Ermittelt die vorhandenen Geräte.
Liefert als Rückgabewert einen json kodierten String mit dem Ergebnis.

Beispiel:

//Geräte abrufen
$devices = TEDEESW_GetDevices(12345);
//Rückgabewert
print_r(json_decode($devices, true));  
```

```text
Vorhande Geräte mit Details ermitteln

TEDEESW_GetDevicesWithDetails(integer $InstanzID);

Ermittelt die vorhandenen Geräte und beinhaltet Details zu den Geräten.
Liefert als Rückgabewert einen json kodierten String mit dem Ergebnis.

Beispiel:

//Geräte abrufen
$devices = TEDEESW_GetDevicesWithDetails(12345);
//Rückgabewert
print_r(json_decode($devices, true));  
```

```text
Zusperren

TEDEESW_LockDoor(integer $InstanzID, integer $GeräteID);

Sperrt ein bestimmtes Schloss zu. 
Liefert als Rückgabewert einen json kodierten String mit dem Ergebnis.

Beispiel:

//Zusperren
$result = TEDEESW_LockDoor(12345, 98765);
//Rückgabewert
print_r(json_decode($result, true));  
```

```text
Aufsperren

TEDEESW_UnlockDoor(integer $InstanzID, integer $GeräteID);

Sperrt ein bestimmtes Schloss auf. 
Liefert als Rückgabewert einen json kodierten String mit dem Ergebnis.

Beispiel:

//Aufsperren
$result = TEDEESW_UnlockDoor(12345, 98765);
//Rückgabewert
print_r(json_decode($result, true));  
```

```text
Falle ziehen / Tür öffnen

TEDEESW_PullDoor(integer $InstanzID, integer $GeräteID);

Zieht die Falle eines bestimmten Schlosses. 
Liefert als Rückgabewert einen json kodierten String mit dem Ergebnis.

Je nach Konfiguration des Gerätes kann es sein, dass das Schloß zunächst geöffnet werden muss, um anschließend die Falle zu ziehen.

Beispiel:

//Falle ziehen
$result = TEDEESW_PullDoor(12345, 98765);
//Rückgabewert
print_r(json_decode($result, true));  
```

```text
Gerätestatus ermitteln

TEDEESW_GetLockStatus(integer $InstanzID, integer $GeräteID);

Ermittelt den Status eines bestimmten Schlosses. 
Liefert als Rückgabewert einen json kodierten String mit dem Ergebnis.

Beispiel:

//Status ermitteln
$result = TEDEESW_GetLockStatus(12345, 98765);
//Rückgabewert
print_r(json_decode($result, true));  
```

```text
Ereignisse ermitteln

TEDEESW_GetDeviceActivity(integer $InstanzID, integer $GeräteID, integer $AnzahlEinträge);

Fragt die Ereignisse (Protokoll) eines bestimmten Schlosses ab. 
Liefert als Rückgabewert einen json kodierten String mit dem Ergebnis.

Beispiel:

//Protokoll 
$result = TEDEESW_GetDeviceActivity(12345, 98765, 10);
//Rückgabewert
print_r(json_decode($result, true));  
```







