<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\AuthenticatedSessionContext;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use App\Application\MediaAuthoringHttp\Contract\MediaAuthoringHttpRuntime;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpResult;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class MediaAuthoringPublicSurfaceTest extends TestCase
{
    public const string ACCOUNT = '69000000-0000-4000-8000-000000000001';

    private const string PROPERTY = '69000000-0000-4000-8000-000000000002';

    public const string MEDIA = '69000000-0000-4000-8000-000000000003';

    private const string INTENT = '69000000-0000-4000-8000-000000000004';

    private CapturingMediaAuthoringHttpRuntime $runtime;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('m', 32)),
            'identity_access_http.cookie.name' => '__Host-appart_session',
        ]);
        $this->withoutMiddleware(EncryptCookies::class);
        $this->app->instance(IdentityAccessHttpRuntime::class, new class implements IdentityAccessHttpRuntime
        {
            public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
            {
                throw new \LogicException('Not used.');
            }

            public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
            {
                return $secret === 'media-session'
                    ? IdentityAccessSessionInspection::valid(new AuthenticatedSessionContext(
                        AccountId::fromString(MediaAuthoringPublicSurfaceTest::ACCOUNT),
                        '93000000-0000-4000-8000-000000000002',
                    ))
                    : IdentityAccessSessionInspection::invalid();
            }
        });
        $this->runtime = new CapturingMediaAuthoringHttpRuntime;
        $this->app->instance(MediaAuthoringHttpRuntime::class, $this->runtime);
    }

    public function test_session_is_required_and_owner_never_comes_from_payload(): void
    {
        $this->getJson('/api/authoring/properties/'.self::PROPERTY.'/media')->assertUnauthorized();

        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'media-session')
            ->getJson('/api/authoring/properties/'.self::PROPERTY.'/media')
            ->assertOk()->assertJson(['status' => 'empty']);

        self::assertSame(self::ACCOUNT, $this->runtime->owner);
    }

    public function test_real_multipart_upload_is_forwarded_without_owner_input(): void
    {
        $image = UploadedFile::fake()->createWithContent('photo1.jpg', "\xFF\xD8\xFF\xE0".str_repeat('a', 64)."\xFF\xD9");
        $response = $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'media-session')
            ->withHeader('Idempotency-Key', self::INTENT)
            ->post('/api/authoring/properties/'.self::PROPERTY.'/media', ['image' => $image, 'order' => 1, 'caption' => 'Façade']);

        $response->assertCreated()->assertHeader('X-Content-Type-Options', 'nosniff')->assertJson(['status' => 'created']);
        self::assertSame(self::ACCOUNT, $this->runtime->owner);
        self::assertSame(self::INTENT, $this->runtime->intent);
        self::assertSame('photo1.jpg', $this->runtime->originalName);
        self::assertNotSame('', $this->runtime->bytes);
    }

    public function test_unknown_fields_are_rejected_and_archive_is_owner_scoped(): void
    {
        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'media-session')
            ->deleteJson('/api/authoring/properties/'.self::PROPERTY.'/media/'.self::MEDIA, ['ownerId' => self::ACCOUNT])
            ->assertUnprocessable();

        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'media-session')
            ->deleteJson('/api/authoring/properties/'.self::PROPERTY.'/media/'.self::MEDIA)
            ->assertOk()->assertJson(['status' => 'available']);

        self::assertSame(self::ACCOUNT, $this->runtime->owner);
        self::assertSame(self::MEDIA, $this->runtime->mediaId);
    }
}

final class CapturingMediaAuthoringHttpRuntime implements MediaAuthoringHttpRuntime
{
    public string $owner = '';

    public string $intent = '';

    public string $originalName = '';

    public string $bytes = '';

    public string $mediaId = '';

    public function upload(string $ownerAccountId, string $propertyId, string $intentId, string $originalName, string $contentType, mixed $stream, int $order, ?string $caption, string $occurredAt): MediaAuthoringHttpResult
    {
        $this->owner = $ownerAccountId;
        $this->intent = $intentId;
        $this->originalName = $originalName;
        $bytes = is_resource($stream) ? stream_get_contents($stream) : false;
        $this->bytes = is_string($bytes) ? $bytes : '';

        return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Created, ['mediaId' => MediaAuthoringPublicSurfaceTest::MEDIA]);
    }

    public function collection(string $ownerAccountId, string $propertyId): MediaAuthoringHttpResult
    {
        $this->owner = $ownerAccountId;

        return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Empty, ['items' => []]);
    }

    public function archive(string $ownerAccountId, string $propertyId, string $mediaId, ?string $replacementMediaId, string $occurredAt): MediaAuthoringHttpResult
    {
        $this->owner = $ownerAccountId;
        $this->mediaId = $mediaId;

        return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Available);
    }
}
