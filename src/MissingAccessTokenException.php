<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

class MissingAccessTokenException extends HealthCareAuthenticatorRequestException
{
    protected ?int $status;

    protected ?string $rawBody;

    public function __construct(
        ?int $status = null,
        ?string $rawBody = null,
        string $message = 'Healthcare Authenticator did not return an access token.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->status = $status;
        $this->rawBody = $rawBody;

        parent::__construct($message, $code, $previous);
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getRawBody(): ?string
    {
        return $this->rawBody;
    }
}
