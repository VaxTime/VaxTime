VaxTime: Free Vaccination reminders via email
=============================================

Vaccination is considered one of the most cost-effective ways –together with
bednets– to reduce unnecessary human suffering.

Measle vaccinations alone have saved  17.1 million lives from 2000-2014,
according to WHO. In 2015 there were about 5,4 million people dying yearly
because of not getting vaccinated. This means there are 81 million deaths in 15 years.

Vaccination Alerts increase vaccinations by about 13% as demonstrated in various
studies. 13% of 81 million are 10,5 million lives that could be saved in 15
years if vaccination alerts would be ubiquious.

The VaxTime project is also available at [VaxTime](https://www.vaxtime.com). This website
allows anyone to find out which vaccines are suggested by WHO in each country,
and receive an email reminder 2 weeks before the vaccination moment.

From this point, anyone can run a copy of VaxTime, adding new features, other
languages or setting it along with another website.

## Table of Contents
* [Table of Contents](#table-of-contents)
* [License](#license)
* [Contributing](#contributing)
* [Configuration](#configuration)
* [Translations](#translations)
* [Add new languages](#add-new-languages)
   * [Variables in translations](#variables-in-translations)
      * [Special Variables](#special-variables)
* [Set up VaxTime in your server](#set-up-vaxtime-in-your-server)
   * [Requirements](#requirements)
   * [Before you start](#before-you-start)
   * [1. Download and install](#1-download-and-install)
   * [2. Htaccess (or Nginx config)](#2-htaccess-or-nginx-config)
   * [3. Database](#3-database)
   * [4. Configuration](#4-configuration)
   * [5. Set up the email server and configure it](#5-set-up-the-email-server-and-configure-it)
   * [6. Enable the cronjobs](#6-enable-the-cronjobs)
   * [7. Do any modification](#7-do-any-modification)
* [To Do](#to-do)



## License

This project is under the [Creative Commons 4.0](https://creativecommons.org/licenses/by/4.0/) license


## Contributing

This project is a charity website created and managed by LanguageCourse S.L. and EffectGive.

If you want to contribute, feel free to do so by adding your changes and making a pull-request. We're happy to receive a couple more eyes and many more pairs of fingers to type on such a great project.


## Configuration
Once you download the project, it is structured in some folders:

    ├── config ← All the configuration files
    ├── import ← Some scripts or files for the initial import step
    ├── jobs ← Cronjobs live here
    │   ├── forever ← A special cronjob type
    │   │   └── logs
    │   └── translations ← Folder hosting translation files
    ├── public ← Public files for the website
    │   ├── css
    │   ├── fonts
    │   ├── images
    │   └── js
    ├── src
    │   ├── emails ← Email generation classes are here
    │   ├── models ← Any entity coming from the database is defined here
    │   └── utils ← Classes that help us are here
    ├── templates
    │   ├── email
    │   ├── partials ← Pieces for the site (footer, header…) are here
    │   └── site ← The layout and the sections are here
    └── vendor ← 3rd party plugins used by VaxTime

The majority of basic configuration happen in one single file:
`config/config.php`. They are generated as globals:

#### VAX_VERSION:
A code used to let you know which version this configuration is based on.

#### VAX_ROOT_PATH:
The project's root path. By default, if the configuration file lives in
`my-path/config`, then the root path is `my-path`.

#### VAX_SRC
The source code path.

#### VAX_CONFIG
The configuration path.

#### VAX_TEMPLATES
The templates path.

#### VAX_PUBLIC
The public directory path.

#### VAX_ANALYTICS_KEY
The Google Analytics key, if you want to use it. If you don't fill it, the analytics block is not added.

#### VAX_NAME
The public name you want to use around the Internet. Ours is VaxTime.com

#### VAX_HOME_URL
The home url. In our case, it's [https://www.vaxtime.com](https://www.vaxtime.com)

#### VAX_HASH_SALT
A made up text that helps us secure the user public links.

#### VAX_DB_HOST
The database host. It can be `localhost` too.

#### VAX_DB_USER
The database username. It needs CRUD permissions, but nothing more.

#### VAX_DB_PASSWORD
The database password.

#### VAX_DB_DBNAME
The database database name.

#### VAX_DB_PREFIX
A prefix for your tables in the database. It's a good way to add an extra level of security (against SQL injections, for instance). It comes empty by default, but we encourage you to rename the tables with the prefix of your choice. Once done and filled in the config, you're ok to go.

#### VAX_EMAIL_PORT
The email server port. It is `587` by default (and also in most email servers).

#### VAX_EMAIL_HOST
The email server hostname. In Amazon SES, it tends to have a structure similar
to `email-smtp.SERVER-ZONE.amazonaws.com`

#### VAX_EMAIL_SMTP_AUTH
Is the email server authenticated? Then set it to `true` and fill the fields
below.

#### VAX_EMAIL_SMTP_SECURE
The kind of authentication needed to send emails. `tls`, `ssl` or similar, depending on your server. For Amazon SES, it's `tls`.

#### VAX_EMAIL_USER, VAX_EMAIL_PASSWORD
The username and password to send emails. Sometimes, it's the email account/password combination you'd use to log into a webmail. Amazon SES is not following the rule, though, as it's only used to send emails, not to receive them.

#### VAX_EMAIL_ACCOUNT_REMINDER
An email address used as a sender for the periodic reminders.

#### VAX_EMAIL_ACCOUNT_HELLO
An email address used for the welcome email.


## Translations

VaxTime runs in multiple languages. This means the texts are translated and the dates localized. We include a total of 43 languages in the website, although not all the texts are done yet:

|             |              |              |             |
|:-----------:|:------------:|:------------:|:-----------:|
| Arabic      ️| Bengali      | Bulgarian    | Catalan    *|
| Chinese     ️| Croatian     ️| Czech        ️| Danish      |
| Dutch       ️| English     *| Farsi        | Finnish     ️|
| French      ️| German       ️| Greek        | Gujarati    ️|
| Hebrew      ️| Hindi        ️| Hungarian    ️| Italian     |
| Japanese    | Kannada      ️| Korean       ️| Malaysian   ️|
| Malayalam   | Marathi      ️| Norwegian    ️| Odia        |
| Polish      | Portuguese   | Romanian     | Russian     |
| Slovak      ️| Slovene      | Spanish     *| Swedish     ️|
| Tamil       ️| Telugu       ️| Thai         | Turkish     ️|
| Ukrainian   | Urdu         ️| Vietnamese   |             |

They **all** come enabled by default, but you can simply remove them from the `languages` table.

The ones marked with an _*_ are fully finished. We plan to keep adding the missing translations in the future, but for those that are not completed yet, they'll show the text in English. To know how to update them, refer to [Add new languages](#add-new-languages).

## Add new languages

If you want to add other languages or update one you already have, add it into the `languages` table (if it was not done already) and put a translated `.csv` file inside the `jobs/translations` folder. You have an example file there. In order to add the translations in the system, you should run the command `jobs/refresh_translation_file.php`, passing the `lang_code` parameter with the 2 (or 3, or 5) letters language code you want to add. There should be a `.csv` file with the same language code in that folder.

We only translated the antigens, comments and descriptions that are more likely to be needed by each language and country. For instance, there are these contents translated in Slovene only for vaccines you should take in Slovenia, but we translated them in French for France, Canada and many other countries. Since we don't know your country, we will give you all of them to be translated.

### Variables in translations

Some translations will inevitably have variable fields. Some are disease names, some others are country names, dates, etc. All the variables are done in our translations as `<VARIABLE_NAME&&>`. You will have, for instance, `<COUNTRY_NAME&&>`, `<SHOT_NUMBER&&>`. If you want to translate VaxTime to a new language, or you want to make a new section and need to use variables, we encourange you to use this same nomenclature, to make it consistent.

In order to use translations in the templates, you can use the fancy function:

    {{tx('translation_key')}}

Or if it has variables:

    {{tx('translation_key', {'<VARIABLE_NAME1&&>': 'variable_value1', '<VARIABLE_NAME2&&>': 'variable_value2'})}}

This is inside the `Translation` class, but we have this useful shortcut defined in the `config/templates.db` file.

#### Special Variables

Some variables are special:

* `<WEB_NAME&&>` is replaced by the contents in the `VAX_NAME url.
* `<BOLD_START&&>` and `<BOLD_END&&>` are used for make a text block relevant (hence, **bold**). It is automatically translated to `<b>` and `</b>`.
* `<ARTICLE_L&&>`, `<ARTICLE_D&&>` and `<ARTICLE_AU&&>` are special determinative particles that work special in Catalan and French. They are automatically translated to the proper form based on whether the following letter is a vowel or not.
* `<WHATEVER_LINK_START&&>` and `<WHATEVER_LINK_END&&>` are used for link generation. You can use our functions `linkOpen` and `linkClose`, defined in the `config/templates.db` file.

## Set up VaxTime in your server

### Requirements
In order to run a copy of VaxTime, your server should meet these requirements.

1. WebServer. VaxTime is ready for Apache 2.7 and Nginx 1.10
  with some adaptations).
2. The server should have _modrewrite_ enabled. Nginx needs some modification
here.
3. PHP. We tested on **7.0.27** and **7.1.11**. Any lower version is not tested.
4. [Composer](https://getcomposer.org/). You can skip it if you download it
offline and then upload the `/vendor` folder. Not recommended, though.
5. MySQL **5.7.20**. Our favorite and the used one online is
[Percona's](https://www.percona.com/)
6. An SMTP server for email deliveries.
[Amazon SES](https://aws.amazon.com/ses) is a good option.
7. A certificate. We work together with Cloudflare (and it also provides us some
  DDoS safety!). _Note: actually it is not required, but since you are taking
  personal information, you should secure this information!_


### Before you start
The project runs using the [Silex](https://silex.symfony.com/) framework, and a
template generator called [Twig](https://twig.symfony.com/). We connect to our
data via [Doctrine](http://www.doctrine-project.org/), but since our structure
is in MySQL, the fetch queries are written in MySQL (sorry!).

Also, this was thought from the beginning as a private project, but we opened to the public to help others set this up and spread a cheap way to help others vaccinate. This means that the code is not documented yet, it is not applying most of the [PSR](www.php-fig.org/psr/), and some stuff could be done better. If you want to contribute, we'll be happy to check for your requests and apply them everywhere.

### 1. Download and install

You can just clone or fork our repository.

Before you download your project, make sure you meet all the requirements. Then, open the folder you just downloaded and run:

    composer install

To get all the 3rd party plugins we'll be using. Also, make sure you have your web server and the MySQL server up and running.

### 2. Htaccess (or Nginx config)

In order to make it work with Silex, we need a couple of `.htaccess` rules:

    RewriteEngine On
    
    <OTHER HTACCESS RULES>
    
    RewriteBase /
    RewriteCond %{SCRIPT_FILENAME} !-d
    RewriteCond %{SCRIPT_FILENAME} !-f
    RewriteRule ^.*$ ./index.php [L]

If you use Nginx, use this rule instead:

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

Obviously, other special cases might require other configurations. This is the basic one that works for us.

Also, there are some folders you should definitely keep safe. These are `jobs`, `import`, `vendor` and `config`.

### 3. Database

The database structure is in the `import` folder, in a file named _vaxtime.sql_. You can import it into your MySQL server by simply running:

    mysql -u<USERNAME> -p <DATABASE> < vaxtime.sql

This will create all the tables prefilled with the necessary information.

### 4. Configuration

Make a copy of `config/config.php.defaults` to `config/config.php`. Configure The bare minimums to create a connection to the database. Once done, you should be able to run your web server, but you can quickly test it works by entering the directory and running `php -S localhost:8888` (or any other port). When you opened the `localhost:8888` address on your browser, you should see something similar to what's on [VaxTime](https://www.vaxtime.com).

### 5. Set up the email server and configure it

We won't go through how to set this up. Just use one service like [Amazon SES](https://aws.amazon.com/ses) and set the needed configuration in `config/config.php`. To test it out, just register yourself and you will receive an email.

### 6. Enable the cronjobs

We have one cron job that should be executed. It's the file `jobs/forever.php`. It is a special cron that runs any cron under `jobs/forever` that has an extension of `.forever.php` and extends `ForeverCron`. The more often you run this script, the better. It will just run any other script if it is not already playing.

It is much better not to execute the scripts inside `jobs/forever` directly, as this one handles non concurrency of scripts that handle database entries and that they should be processed only once.

### 7. Do any modification

Customisations away from the configuration can be done, but in such case, bear in mind that if you update the project (by pulling our repository), they will be lost or incompatible with the new modifications.


## To Do

The vaccines and antigens are fixed and come from WHO, but if there is new information, we will upgrade this. Since this might be inconvenient for most people, we'll try to make this change as frictionless as possible for everyone, maybe adding a similar system to the one to add new languages.
