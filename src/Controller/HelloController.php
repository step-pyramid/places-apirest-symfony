<?php
// src/Controller/HelloController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelloController
{
    #[Route('/hello', name: 'hello_world')]
    public function hello(): Response
    {
        return new Response(
            '<html><body><h1>Hello World! 🎉</h1><p>My first Symfony app in Docker!</p></body></html>'
        );
    }

    #[Route('/hello/{name}', name: 'hello_name')]
    public function helloName(string $name): Response
    {
        return new Response(
            "<html><body><h1>Hello $name! 👋</h1><p>Welcome to your Symfony app!</p></body></html>"
        );
    }
}