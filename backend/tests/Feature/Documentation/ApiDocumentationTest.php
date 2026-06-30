<?php

namespace Tests\Feature\Documentation;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_api_documentation_covers_required_guides_and_endpoint_groups(): void
    {
        $documentation = file_get_contents(base_path('docs/API.md'));

        $this->assertIsString($documentation);

        foreach ([
            '## Authentication',
            '## Public catalogue',
            '## Cart',
            '## Wishlist',
            '## Coupons and checkout',
            '## Orders',
            '## Payments',
            '## Reviews',
            '## Admin API',
            '## Pagination',
            '## Errors',
            '## React integration',
            'POST /auth/register',
            'GET /search',
            'POST /checkout',
            '/admin/dashboard',
        ] as $requiredText) {
            $this->assertStringContainsString($requiredText, $documentation);
        }
    }

    public function test_readme_links_to_api_documentation(): void
    {
        $readme = file_get_contents(base_path('README.md'));

        $this->assertIsString($readme);
        $this->assertStringContainsString('[REST API guide](docs/API.md)', $readme);
    }
}
