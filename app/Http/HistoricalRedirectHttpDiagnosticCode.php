<?php

namespace App\Http;

enum HistoricalRedirectHttpDiagnosticCode: string
{
    case QualificationCurrent = 'qualification_current';
    case QualificationUnknown = 'qualification_unknown';
    case QualificationAmbiguous = 'qualification_ambiguous';
    case QualificationCorrupted = 'qualification_corrupted';
    case QualificationUnavailable = 'qualification_unavailable';
    case ResolverNotFound = 'resolver_not_found';
    case DestinationMissing = 'destination_missing';
    case LoopDetected = 'loop_detected';
    case ChainDetected = 'chain_detected';
    case ResolverAmbiguous = 'resolver_ambiguous';
    case ResolverCorrupted = 'resolver_corrupted';
    case ResolverUnavailable = 'resolver_unavailable';
}
