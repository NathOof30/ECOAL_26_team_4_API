<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocsAccessTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('APP_DOCS_ENABLED');
        unset($_ENV['APP_DOCS_ENABLED'], $_SERVER['APP_DOCS_ENABLED']);

        parent::tearDown();
    }

    public function test_docs_are_available_in_local_environment(): void
    {
        config()->set('app.env', 'local');
        putenv('APP_DOCS_ENABLED');
        unset($_ENV['APP_DOCS_ENABLED'], $_SERVER['APP_DOCS_ENABLED']);

        $this->get('/docs')->assertOk();
        $this->get('/docs/openapi.yaml')->assertOk();
    }

    public function test_docs_are_hidden_outside_local_by_default(): void
    {
        config()->set('app.env', 'production');
        putenv('APP_DOCS_ENABLED=false');
        $_ENV['APP_DOCS_ENABLED'] = 'false';
        $_SERVER['APP_DOCS_ENABLED'] = 'false';

        $this->get('/docs')->assertNotFound();
        $this->get('/docs/openapi.yaml')->assertNotFound();
    }

    public function test_docs_can_be_enabled_explicitly_outside_local(): void
    {
        config()->set('app.env', 'production');
        putenv('APP_DOCS_ENABLED=true');
        $_ENV['APP_DOCS_ENABLED'] = 'true';
        $_SERVER['APP_DOCS_ENABLED'] = 'true';

        $this->get('/docs')->assertOk();
        $this->get('/docs/openapi.yaml')->assertOk();
    }
}
