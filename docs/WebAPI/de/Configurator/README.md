[![Image](../../../../imgs/tedee_logo.png)](https://tedee.com)

### Konfigurator Web API

Dieses Modul verwaltet die vorhandenen Geräte.  
Der Nutzer kann die ausgewählten Geräte automatisch anlegen lassen.  

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

* Listet die verfügbaren Geräte auf
* Automatisches Anlegen der Geräte

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

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `Tedee Konfigurator Web API` auswählen, welches unter dem Hersteller `tedee` aufgeführt ist.
- Es wird eine neue `Tedee Konfigurator Web API` Instanz angelegt.
- Weitere Informationen zum Hinzufügen von Instanzen finden Sie in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name        | Beschreibung
----------- | ------------------------------------
Kategorie   | Auswahl der Kategorie für die Geräte
Geräte      | Liste der verfügbaren Geräte

__Schaltflächen__:

Name            | Beschreibung
--------------- | -----------------------------------------------------------
Alle erstellen  | Erstellt für alle aufgelisteten Geräte jeweils eine Instanz
Erstellen       | Erstellt für das ausgewählte Gerät eine Instanz

Über die Schaltfläche `AKTUALISIEREN` können Sie im Konfigurator die Liste der verfügbaren Geräte jederzeit aktualisieren.  
Wählen Sie `ALLE ERSTELLEN` oder wählen Sie ein Gerät aus der Liste aus und drücken dann die Schaltfläche `ERSTELLEN`, um das Gerät automatisch anzulegen.  
Sofern noch keine [Tedee Splitter Web API](../../../../docs/WebAPI/de/Splitter/README.md) Instanz angelegt wurde, muss einmalig beim Erstellen der Konfigurator Instanz die Konfiguration der Splitter Instanz vorgenommen werden.  
Geben Sie Ihren persönlichen Zugangsschlüssel (PAK) und den Netzwerk-Timeout an.  
Wählen Sie anschließend `WEITER` aus.

Sofern Sie mehrere Splitter Instanzen verwenden, können Sie in der Instanzkonfiguration unter `GATEWAY ÄNDERN` die entsprechende Splitter Instanz auswählen.  
Die Splitter Instanz muss dafür bereits vorhanden sein.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt.  
Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Es werden keine Statusvariablen verwendet.

#### Profile

Es werden keine Profile verwendet.

### 6. WebFront

Der Konfigurator hat im WebFront keine Funktionalität.

### 7. PHP-Befehlsreferenz

Es ist keine Befehlsreferenz verfügbar.