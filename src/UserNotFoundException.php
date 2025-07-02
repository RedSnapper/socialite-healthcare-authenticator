<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

class UserNotFoundException extends HealthCareAuthenticatorRequestException
{
    protected string $userId;
    protected ?array $responseBody;

    public function __construct(
        string $userId,
        ?array $responseBody = null,
        string $message = '',
        int $code = 404,
        ?\Throwable $previous = null
    ) {
        $this->userId = $userId;
        $this->responseBody = $responseBody;
        
        if (empty($message)) {
            $message = "User with ID '{$userId}' was not found in Healthcare Authenticator.";
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    public function getUserId(): string
    {
        return $this->userId;
    }
    
    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}