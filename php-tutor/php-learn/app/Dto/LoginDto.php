<?php

namespace App\Dto;

class LoginDto
{

    public function __construct(public string $username, public string $password)
    {
    }
}