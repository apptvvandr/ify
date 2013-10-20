# Ify


**THIS PROJECT IS NOT USEABLE RIGHT NOW. EARLY ALPHA OF ALPHA!**

I made this as I could'nt find an easy PHP tool to stream my music.

As I'm learning PHP/JavaScript/JQuery/CSS/Bootstrap... It may not be a very beautifull code. Moreover, it's full of various tests ...


## End user
Ify was designed to be as easy to install than easy to use. 

### Features
Basically, Ify provides:
   - IQL: Fully featured language to query your music database
   - Interface: Responsive for desktop, tablet and mobile phone.
   - Compatibility: Works on all devices, but you must ahve HTML5 support or Flash support.
   

### Requirements
Ify was designed to work on a very classic environment:
   - Webserver: Apache2, nginx
   - MySQL server: MySQL, Percona
   - PHP Engine: Apache2's mod_php5, php5-fpm (not implemented yet)
You can optionnally install:
   - PHP-APC (not implemented yet)
   - Memcache (not implemented yet)


### Intallation
You just need to clone this repository into your Document Root. Your Document Root may be a root vhost or a subdirectory.


### Usage
Once installed, you have to create your administrator password.


## Developers
Ify is basically split in two parts:
   - GUI: It is a static HTML page, with everything managed in JavaScript. The JavaScript code will call Ify API.
   - Engine: Provide the core application and the API.


### IQL API
There are two APIs:
   - api.php: It serves everything, from user authentication to DB querie managment
   - media.php: It delivers all binary media, mainly audio binary.

#### api.php
   -


#### media.php
   -

## Versions
   - 0.1: Early alpha
    - Ify can now serve music from ID
    - Improving libray scan, now between 10 and 15 files scaned per seconds instead 3 before :p
    - First version of IQL, a basic query language for Ify
    - New and secure way to manage mp3 handling for streaming or downloading. Can serve other files as well.


## Issues, Fixes and Todo:
- PHP SANITIZE filter doesn't allow to use 'lesser than' comparison char in IQL
- Fix for the proper way to get config.ini
- Add a DB fields: user (for a per user database directory), tagLenght
