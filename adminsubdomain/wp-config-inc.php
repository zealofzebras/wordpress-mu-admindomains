<?php


function isCustomAdmin($domain) {
	global $ADMIN_DOMAINS, $ADMIN_SUBDOMAIN, $ADMIN_EXPLICIT_ON;
	$custom = $ADMIN_DOMAINS[$domain];
	
	if ((isset($custom) && ($custom === $domain || $custom === false )))
		return false;

	if ($ADMIN_EXPLICIT_ON == true) {
		if ($custom == true || !empty($custom))
			return true;

		$custom = array_search($domain, $ADMIN_DOMAINS, true);
		if (!empty($custom))
			return true;

		return false;
	}
	
	
	return true;
}

function getAdminDomain($domain) {
	global $ADMIN_DOMAINS, $ADMIN_SUBDOMAIN, $ADMIN_EXPLICIT_ON;
	$custom = $ADMIN_DOMAINS[$domain];

	if ((isset($custom) && ($custom === $domain || $custom === false ))
		 || ($ADMIN_EXPLICIT_ON === true && empty($custom) && $custom !== true)
		 )
		return $domain;

	//if ($ADMIN_EXPLICIT_ON == true && (empty($custom) && $custom != true))
	//	return $domain;

	if (!empty($custom) && $custom !== true)
		return $custom;
	
	$topdomain = str_replace($ADMIN_SUBDOMAIN.".", "", $domain);
	if ($topdomain != $domain)
		return $domain;
	
	return $ADMIN_SUBDOMAIN.".".$domain;
}

function getMainDomain($domain) {
	global $ADMIN_DOMAINS, $ADMIN_SUBDOMAIN;
	$custom = array_search($domain, $ADMIN_DOMAINS, true);
	if (!empty($custom))
		return $custom;
	
	$topdomain = str_replace($ADMIN_SUBDOMAIN.".", "", $domain);
	if ($topdomain != $domain)
		return $topdomain;
	
	return $domain;
}

$_SERVER['HTTP_HOST_ORG'] = $_SERVER['HTTP_HOST'];
$_SERVER['HTTP_HOST_MAIN'] = getMainDomain($_SERVER['HTTP_HOST']); 
$_SERVER['HTTP_HOST_ADMIN'] = getAdminDomain($_SERVER['HTTP_HOST']);

$customAdmin = isCustomAdmin($_SERVER['HTTP_HOST_MAIN']);
if ($customAdmin) {
	$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST_MAIN'];
	define( 'COOKIE_DOMAIN', $_SERVER[ 'HTTP_HOST_ADMIN' ] );
} else {
	define( 'COOKIE_DOMAIN', $_SERVER[ 'HTTP_HOST' ] );
}
header("X-COOKIE-DOMAIN: " . COOKIE_DOMAIN);