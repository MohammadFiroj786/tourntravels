<?php
/* ================= LOAD .ENV FILE ================= */
$envPath = __DIR__ . "/.env";

if (!file_exists($envPath)) {
    die("❌ .env file not found");
}

$env = parse_ini_file($envPath);

if (!$env) {
    die("❌ Error loading .env file");
}

/* ================= MAIL CONFIG ================= */
define("MAIL_HOST", $env["MAIL_HOST"] ?? "");
define("MAIL_USERNAME", $env["MAIL_USERNAME"] ?? "");
define("MAIL_PASSWORD", $env["MAIL_PASSWORD"] ?? "");
define("MAIL_PORT", (int)($env["MAIL_PORT"] ?? 587));

define("MAIL_FROM", $env["MAIL_FROM"] ?? MAIL_USERNAME);
define("MAIL_FROM_NAME", $env["MAIL_FROM_NAME"] ?? "Tour N Travel");

/* ================= APP ================= */
define("APP_URL", rtrim($env["APP_URL"] ?? "http://localhost/tourntravels", "/"));

/* ================= PAYMENT ================= */
define("UPI_ID", $env["UPI_ID"] ?? "");
define("UPI_NAME", $env["UPI_NAME"] ?? "");