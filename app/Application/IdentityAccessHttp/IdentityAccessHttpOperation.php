<?php

namespace App\Application\IdentityAccessHttp;

enum IdentityAccessHttpOperation: string
{
    case Login = 'login';
    case Logout = 'logout';
    case RenewSession = 'renew_session';
    case ListSessions = 'list_sessions';
    case RequestRecovery = 'request_recovery';
    case CompleteRecovery = 'complete_recovery';
    case ReadProfile = 'read_profile';
    case MutateProfile = 'mutate_profile';
    case RequestContactChange = 'request_contact_change';
    case VerifyContactChange = 'verify_contact_change';
    case RequestClosure = 'request_closure';
    case ConfirmClosure = 'confirm_closure';
    case Reopen = 'reopen';
}
