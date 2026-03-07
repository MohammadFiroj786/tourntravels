<?php

$env = parse_ini_file(__DIR__ . "/.env");

if(!$env){
    die("Error loading .env file");
}

define("MAIL_HOST", $env["MAIL_HOST"] ?? "");
define("MAIL_USERNAME", $env["MAIL_USERNAME"] ?? "");
define("MAIL_PASSWORD", $env["MAIL_PASSWORD"] ?? "");
define("MAIL_PORT", $env["MAIL_PORT"] ?? 587);
define("MAIL_FROM", $env["MAIL_FROM"] ?? $env["MAIL_USERNAME"]);
define("MAIL_FROM_NAME", $env["MAIL_FROM_NAME"] ?? "Tour N Travel");

define("APP_URL", $env["APP_URL"] ?? "http://localhost/tourntravels");

?>