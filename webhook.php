<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Webhook endpoint.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../config.php');

// We only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    die();
}

// Get the Svix headers.
$svixid = $_SERVER['HTTP_SVIX_ID'] ?? '';
$svixrawsignature = $_SERVER['HTTP_SVIX_SIGNATURE'] ?? '';
$svixtimestamp = (int) ($_SERVER['HTTP_SVIX_TIMESTAMP'] ?? 0);

// Pre-condition checks.
if (empty($svixid) || empty($svixrawsignature) || empty($svixtimestamp)) {
    header('HTTP/1.1 400 Bad Request');
    die();
} else if ($svixtimestamp < time() - 600) {
    header('HTTP/1.1 400 Bad Request');
    die();
}

// Parse the provided signature into validatable signatures.
$svixsignatures = array_filter(array_map(function($rawsign) {
    return explode(',', $rawsign, 2)[1] ?? null;
}, explode(' ', $svixrawsignature)));
if (empty($svixsignatures)) {
    header('HTTP/1.1 400 Bad Request');
    die();
}

// Validate that we're expecting webhooks.
$webhooksecret = get_config('block_motrain', 'webhooksecret');
if (empty($webhooksecret) || strpos($webhooksecret, 'whsec_') !== 0) {
    header('HTTP/1.1 501 Not Implemented');
    die();
}

// Extract the signing secret from the secret.
$signingsecret = base64_decode(explode('_', $webhooksecret, 2)[1]);
if (empty($signingsecret)) {
    header('HTTP/1.1 501 Not Implemented');
    die();
}

// Validate the authenticity of the request.
$body = file_get_contents('php://input');
$signedcontent = "{$svixid}.{$svixtimestamp}.{$body}";
$signature = base64_encode(hash_hmac('sha256', $signedcontent, $signingsecret, true));
$isvalid = false;
foreach ($svixsignatures as $svixsignature) {
    if (hash_equals($signature, $svixsignature)) {
        $isvalid = true;
        break;
    }
}
if (!$isvalid) {
    header('HTTP/1.1 401 Unauthorized');
    die();
}

// Parse and basic validation of the content of the webhook.
$data = json_decode($body);
if (empty($data) || empty($data->type) || empty($data->payload)) {
    header('HTTP/1.1 400 Bad Request');
    die();
}

// Ok we're good!
