<?php

namespace App\Application\ActiveGenerationBootstrap;

enum BootstrapActiveProjectionGenerationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case InvalidInput = 'invalid_input';
    case ActiveGenerationExists = 'active_generation_exists';
    case CandidateGenerationExists = 'candidate_generation_exists';
    case CandidateRejected = 'candidate_rejected';
    case RebuildFailed = 'rebuild_failed';
    case ManifestInvalid = 'manifest_invalid';
    case ActivationFailed = 'activation_failed';
    case ActiveReaderFailed = 'active_reader_failed';
    case DependencyUnavailable = 'dependency_unavailable';
}
