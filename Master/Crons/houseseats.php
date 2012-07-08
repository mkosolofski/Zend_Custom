<?php
/**
 * This cron scrapes houseseats.com for active shows and will send an email
 * to the configured recipients of every "new" show. The first time that
 * this cron runs, it will consider all active shows as "new".
 *
 * Things you will need: hotmail account, mysql server, houseseats account
 *                       the house seats service object found in this branch.
 */

/************************
 * Configuration for cron
 ***********************/
$hotmailUsername = '@hotmail.com';
$hotmailPassword = '';

$databaseHost = 'localhost';
$databaseUsername = 'root';
$databasePassword = '';
$databaseName = 'houseseats';
$databaseTable = 'shows';

// Recommend making the table field varchar(100)
$databaseTableFieldName = 'name';

$houseSeatsEmail = '@hotmail.com';
$houseSeatsPassword = '';

// You can find this out by logging in to "www.houseseats.com". The subdomain will change. Use this.
$houseSeatsSubdomain = 'lv';

$emailAddressesToNotify = array(
    '@hotmail.com' => 'Huey',
    '@gmail.com' => 'Donald'
);
/***********************/


$startTime = time();

require_once 'Bootstrap.php';
use Extended\Service\HouseSeats;

// Instantiate the houseseats object.
$houseSeats = new HouseSeats($houseSeatsEmail, $houseSeatsPassword);
$houseSeats->setSubdomain($houseSeatsSubdomain);

// Instantiate the database adapter.
$dbAdapter = new Zend_Db_Adapter_Mysqli(
    array(
        'host' => $databaseHost,
        'dbname' => $databaseName,
        'username' => $databaseUsername,
        'password' => $databasePassword
    )
);

// Get all shows that have already been emailed.
$query = $dbAdapter->query('SELECT name FROM ' . $databaseTable);
$results = $query->fetchAll(Zend_Db::FETCH_NUM);
$knownShows = array();
if (is_array($results)) {
    foreach($results as $row) {
        $knownShows[] = $row[0];
    }
}

// Get all active shows that are listed on the site.
$siteShows = $houseSeats->getShowNames();

// Figure out which shows are new.
$newShows = array_diff($siteShows, $knownShows);
sort($newShows);

// Build email and table insert for new shows.
$insert = array();
$emailNewShows = '';
foreach ($newShows as $showName) {
    $showDetails = $houseSeats->getShowDetails($showName);
    $insert[] = '(' . $dbAdapter->quote($showName) . ')';
    $emailNewShows .= '<dt style="font-weight:bold;">' .
                          $showName .
                      '</dt>' .
                      '<dd style="padding-bottom:15px;overflow:auto;width:100%">' .
                          '<img src="' . $houseSeats->getShowImageLink($showName) .
                             '" style="float:left;margin-right:15px;border:1px solid blue;"/>' .
                          $showDetails .
                      '</dd>';
}

// Build email and table insert for old shows.
$emailOldShows = '';
foreach ($knownShows as $showName) {
    // Don't include removed shows.
    if (!in_array($showName, $siteShows)) {
        continue;
    }

    $insert[] = '(' . $dbAdapter->quote($showName) . ')';
    $showDetails = $houseSeats->getShowDetails($showName);
    $emailOldShows .= '<dt style="font-weight:bold;">' .
                           $showName .
                      '</dt>' .
                      '<dd style="padding-bottom:15px;overflow:auto;width:100%">' .
                          '<img src="' . $houseSeats->getShowImageLink($showName) .
                              '" style="float:left;margin-right:15px;border:1px solid blue;"/>' .
                          $showDetails .
                       '</dd>';
}

// If there are new shows, send an email out.
if ($emailNewShows != '') {
    Zend_Mail::setDefaultTransport(
        new Zend_Mail_Transport_Smtp('smtp.live.com',
            array('auth' => 'login',
                'username' => $hotmailUsername,
                'password' => $hotmailPassword,
                'port' => 587,
                'ssl' => 'tls'
            )
        )
    );
    $mailer = new Zend_Mail();
    $mailer->setFrom($hotmailUsername, 'Cron - House Seats!')
        ->setBodyHtml(
            '<a href="http://www.houseseats.com">http://www.houseseats.com</a>' .
            '<div style="padding-top:25px;font-weight:bold;color:blue;">NEW SHOWS</div>' .
            '<dl>' . $emailNewShows . '</dl>' .
            '<div style="padding-top:25px;font-weight:bold;color:blue;">OLD ACTIVE SHOWS</div>' .
            '<dl style=padding-bottom:15px;">' . $emailOldShows . '</dl>' .
            '<span style="font-weight:bold">Cron Stats:</span>' .
            '<br/>Ran At: ' . date('Y-m-d H:i:s') .
            '<br/>Total Time To Run: ' . (time() - $startTime) . ' seconds'
        )
        ->setSubject('New Shows Posted (' . count($newShows) . ')');
        
    foreach ($emailAddressesToNotify as $address => $name) {
        $mailer->addTo($address, $name);
    }
 
    $mailer->send();
}

// Update the database table with the latest show listing.
if (!empty($insert)) {
    $dbAdapter->query('TRUNCATE ' . $databaseTable);
    $dbAdapter->query('INSERT INTO ' . $databaseTable .
        ' (' . $databaseTableFieldName . ') VALUE ' . implode(',', $insert));
}
