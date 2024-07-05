<?php
// Include settings.php
require_once(dirname(dirname(dirname(__DIR__))) . '/settings.php');
require_once(CREST_PATH . '/crest.php');

/**
 * Checks the server compatibility with the UNi4 Online Bots application.
 *
 * This function uses the CRest class to perform the check. It calls the CRest::checkServer()
 * method to determine if the server meets the minimum requirements for the application.
 *
 * @throws None
 * @return None
 */
CRest::checkServer();
