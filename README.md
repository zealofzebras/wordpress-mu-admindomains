# wordpress-mu-admindomains

MU plugin to put admin pages on another domain or subdomain.

## Installation

Put the files and folder in mu-plugins, then open wp-config and configure the setup like this

```php

$ADMIN_DOMAINS = array(
	"sample.com" => false, //will not use admin.sample.com
	"mysample.io" => false, //will not use admin.mysample.io
    "demosite.global" => true, //will use admin.demosite.global
    "demo.com" => "demoadmindomain.com", //will use demoadmindomain.com as the administration domain
);
$ADMIN_SUBDOMAIN = "admin"; //the admin subdomain
$ADMIN_EXPLICIT_ON = true; //if this false all domains will use admin.domain.com except for those that are explicitly disabled in the list above

include_once("wp-content/mu-plugins/adminsubdomain/wp-config-inc.php");

```

## Contributers

This is built on a concept by Hakre that changes the admin url
* http://hakre.wordpress.com/
that idea was updated by 
* Mark Figueredo, <http://gruvii.com/>

