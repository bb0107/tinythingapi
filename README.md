# [tinythingapi](https://github.com/bb0107/tinythingapi)

[![License: MPL 2.0](https://img.shields.io/badge/License-MPL%202.0-brightgreen.svg)](https://opensource.org/licenses/MPL-2.0)

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
Check https://github.com/bb0107/tinythingapi/example for implementation in PHP or on Arduino (e.g. WEMOS D1 mini, ESP8266 or similar).

## Usage
- For experimental purpose, XAMPP is highly recommened as all-in-one package (PHP, APACHE Web-Server, MariaDB Database). Project was tested and developed with XAMPP 7.2.33.
https://www.apachefriends.org/download.html
- Clone code to htdocs folder
- Make sure Apache and MySQL Server are both running
- Update /config/config.php with your database parameters
- Got to /install/install.php and follow the instructions
- got to /public/admin/index.php and check whether the Web-GUI is working
- Update /example/WRITE.php or /example/READ.php with the actual API address and open the files in a Browser

## License
This project - except of 3rd party libraries (see next chapter) -  is subject to the terms of the Mozilla Public License, v. 2.0. Please refer to https://github.com/bb0107/tinythingapi/blob/master/LICENSE for further details.


## 3rd Party Libraries

- Bootstrap 4: https://github.com/twbs/bootstrap (MIT)
- Moment.js: https://github.com/moment/moment (MIT)
- Chart.js: https://github.com/chartjs/Chart.js (MIT)
- JQuery.js: https://github.com/jquery/jquery (MIT)
- JS-SHA256: https://github.com/emn178/js-sha256 (MIT)
