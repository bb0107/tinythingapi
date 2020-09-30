# [tinythingapi](https://github.com/Icenbeis/tinythingapi)

[![License: CC BY-NC-SA 4.0](https://licensebuttons.net/l/by-nc-sa/4.0/80x15.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

## Requirements
- PHP5 or higher
- Webserver e.g. Apache
- MySQL compatible database, e.g. MariaDB

## Features
- create mutiple channels and sub-channels to post e.g. sensor data from IOT devices
- hash and time-based read / write authentication
- Web-GUI for administration and data visualization
- Easy-Setup Script to create the necessary database and tables

## Introduction
tinyhingapi provides an API to read and write data from e.g. IOT devices like an Arduino or an ESP8266. The Web-GUI lets you create, edit or remove channels and sub-channels quickly. Authentification ensures access only for authorized users. To ensure integrity of sent or requested data, all information are hashed with the current timestamp and the individual read- or write key which is only known to the channel owner.

## Examples
Check https://github.com/Icenbeis/tinythingapi/example for implementation in PHP or on Arduino (e.g. WEMOS D1 mini, ESP8266 or similar).

## Usage

## 3rd Party Libraries

- Bootstrap 4: https://github.com/twbs/bootstrap (MIT)
- Moment.js: https://github.com/moment/moment (MIT)
- Chart.js: https://github.com/chartjs/Chart.js (MIT)
- JQuery.js: https://github.com/jquery/jquery (MIT)
- JS-SHA256: https://github.com/emn178/js-sha256 (MIT)
