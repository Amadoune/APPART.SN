<?php
declare(strict_types=1);

function packagingCampaignTimestamp(): string
{
    $required = ['PACKAGING_CAMPAIGN_RECORD', 'PACKAGING_CAMPAIGN_SHA256', 'PACKAGING_CAMPAIGN_ID', 'PACKAGING_CAMPAIGN_BUILDDATEUTC', 'BUILD_SHA', 'SOURCE_BASE_SHA', 'CANDIDATE_TAG'];
    $input = [];
    foreach ($required as $name) {
        $value = getenv($name);
        if ($value === false || $value === '') { throw new RuntimeException('CAMPAIGN_INPUT_REQUIRED: '.$name); }
        $input[$name] = $value;
    }
    $value = $input['PACKAGING_CAMPAIGN_BUILDDATEUTC'];
    if (!preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z\z/D', $value)) {
        throw new RuntimeException('CAMPAIGN_TIMESTAMP_FORMAT');
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s\Z', $value, new DateTimeZone('UTC'));
    $errors = DateTimeImmutable::getLastErrors();
    if ($date === false || ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) || $date->format('Y-m-d\TH:i:s\Z') !== $value) {
        throw new RuntimeException('CAMPAIGN_TIMESTAMP_CALENDAR');
    }
    $bytes = file_get_contents($input['PACKAGING_CAMPAIGN_RECORD']);
    if ($bytes === false || !preg_match('/\A[0-9a-f]{64}\z/D', $input['PACKAGING_CAMPAIGN_SHA256']) || !hash_equals($input['PACKAGING_CAMPAIGN_SHA256'], hash('sha256', $bytes))) {
        throw new RuntimeException('CAMPAIGN_RECORD_INTEGRITY');
    }
    $record = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($record) || ($record['schema'] ?? null) !== 'appart.packaging-campaign.v1') { throw new RuntimeException('CAMPAIGN_SCHEMA'); }
    foreach (['PACKAGING_CAMPAIGN_ID'=>'campaignId', 'PACKAGING_CAMPAIGN_BUILDDATEUTC'=>'buildDateUtc', 'BUILD_SHA'=>'BUILD_SHA', 'SOURCE_BASE_SHA'=>'SOURCE_BASE_SHA', 'CANDIDATE_TAG'=>'CANDIDATE_TAG'] as $environment => $field) {
        if (($record[$field] ?? null) !== $input[$environment]) { throw new RuntimeException('CAMPAIGN_IDENTITY_MISMATCH: '.$field); }
    }
    return $value;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try { echo packagingCampaignTimestamp(), "\n"; }
    catch (Throwable $error) { fwrite(STDERR, $error->getMessage()."\n"); exit(1); }
}
