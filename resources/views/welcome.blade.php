<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ECOAL API</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        <style>
            :root {
                --bg: #f4efe7;
                --panel: rgba(255, 250, 241, 0.88);
                --ink: #2d241d;
                --muted: #6b5d51;
                --line: rgba(92, 67, 43, 0.18);
                --accent: #c86419;
                --accent-dark: #8c4510;
                --accent-soft: #f1d7bf;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Instrument Sans", sans-serif;
                color: var(--ink);
                background:
                    radial-gradient(circle at top left, rgba(200, 100, 25, 0.24), transparent 26%),
                    radial-gradient(circle at bottom right, rgba(140, 69, 16, 0.18), transparent 24%),
                    linear-gradient(180deg, #fbf5ec 0%, var(--bg) 100%);
            }

            .page {
                width: min(1180px, calc(100vw - 32px));
                margin: 24px auto;
                padding: 24px;
                border: 1px solid var(--line);
                border-radius: 28px;
                background: var(--panel);
                backdrop-filter: blur(14px);
                box-shadow: 0 28px 90px rgba(68, 40, 14, 0.14);
            }

            .nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 36px;
            }

            .brand {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .eyebrow {
                margin: 0;
                font-size: 12px;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: var(--accent-dark);
            }

            .brand strong {
                font-size: 18px;
            }

            .nav-links,
            .hero-actions,
            .resource-links {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 44px;
                padding: 0 18px;
                border-radius: 999px;
                border: 1px solid var(--line);
                text-decoration: none;
                color: var(--ink);
                background: rgba(255, 255, 255, 0.72);
                font-weight: 600;
            }

            .button.primary {
                color: #fff8f1;
                background: linear-gradient(135deg, var(--accent), var(--accent-dark));
                border-color: transparent;
                box-shadow: 0 12px 24px rgba(140, 69, 16, 0.24);
            }

            .hero {
                display: grid;
                grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr);
                gap: 22px;
                align-items: stretch;
            }

            .hero-copy,
            .hero-panel,
            .section-card {
                border: 1px solid var(--line);
                border-radius: 24px;
                background: rgba(255, 251, 244, 0.86);
            }

            .hero-copy {
                padding: 28px;
            }

            h1 {
                margin: 0 0 14px;
                font-size: clamp(40px, 7vw, 72px);
                line-height: 0.94;
                letter-spacing: -0.05em;
            }

            .lead {
                max-width: 680px;
                margin: 0 0 24px;
                font-size: 17px;
                line-height: 1.6;
                color: var(--muted);
            }

            .pill-row {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-bottom: 28px;
            }

            .pill {
                padding: 8px 12px;
                border-radius: 999px;
                background: var(--accent-soft);
                color: var(--accent-dark);
                font-size: 13px;
                font-weight: 700;
            }

            .hero-panel {
                padding: 24px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                background:
                    linear-gradient(180deg, rgba(200, 100, 25, 0.09), rgba(255, 251, 244, 0.86)),
                    rgba(255, 251, 244, 0.86);
            }

            .metric-grid {
                display: grid;
                gap: 12px;
            }

            .metric {
                padding: 16px;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.8);
                border: 1px solid rgba(92, 67, 43, 0.12);
            }

            .metric strong {
                display: block;
                font-size: 28px;
                margin-bottom: 4px;
            }

            .metric span {
                color: var(--muted);
                font-size: 14px;
            }

            .sections {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
                margin-top: 18px;
            }

            .section-card {
                padding: 22px;
            }

            .section-card h2 {
                margin: 0 0 8px;
                font-size: 20px;
            }

            .section-card p {
                margin: 0 0 16px;
                color: var(--muted);
                line-height: 1.55;
            }

            .code {
                display: block;
                padding: 12px 14px;
                border-radius: 16px;
                background: #2f241d;
                color: #fff4e8;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                font-size: 13px;
                overflow-x: auto;
            }

            @media (max-width: 960px) {
                .hero,
                .sections {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 640px) {
                .page {
                    width: min(100vw - 16px, 1180px);
                    margin: 8px auto;
                    padding: 16px;
                    border-radius: 20px;
                }

                .nav {
                    flex-direction: column;
                    align-items: flex-start;
                }

                h1 {
                    font-size: 40px;
                }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <header class="nav">
                <div class="brand">
                    <p class="eyebrow">ECOAL 26 Team 4</p>
                    <strong>Collection Scoring API</strong>
                </div>
                <div class="nav-links">
                    <a class="button" href="{{ route('docs.openapi') }}" target="_blank" rel="noreferrer">OpenAPI</a>
                    <a class="button primary" href="{{ route('docs') }}">Swagger UI</a>
                </div>
            </header>

            <section class="hero">
                <div class="hero-copy">
                    <div class="pill-row">
                        <span class="pill">Laravel 12</span>
                        <span class="pill">Sanctum Auth</span>
                        <span class="pill">Policies + FormRequests</span>
                    </div>
                    <h1>Structured API for users, collections, items, criteria, and scoring.</h1>
                    <p class="lead">
                        ECOAL API exposes public read endpoints, authenticated ownership flows, admin/editor controls,
                        password reset, audit logs, and an embedded Swagger UI for integration work.
                    </p>
                    <div class="hero-actions">
                        <a class="button primary" href="{{ route('docs') }}">Explore the API</a>
                        <a class="button" href="{{ route('docs.openapi') }}" target="_blank" rel="noreferrer">Download spec</a>
                    </div>
                </div>

                <aside class="hero-panel">
                    <div>
                        <p class="eyebrow">Quick Snapshot</p>
                        <div class="metric-grid">
                            <div class="metric">
                                <strong>Auth</strong>
                                <span>Register, login, logout, reset password, bearer token profile access.</span>
                            </div>
                            <div class="metric">
                                <strong>Ownership</strong>
                                <span>Policies enforce profile, collection, item, and scoring permissions.</span>
                            </div>
                            <div class="metric">
                                <strong>Docs</strong>
                                <span>Swagger UI and OpenAPI are served directly from the app.</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="sections">
                <article class="section-card">
                    <h2>Run locally</h2>
                    <p>Bring the API up with seeded data, then inspect the routes through the docs UI.</p>
                    <code class="code">composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve</code>
                </article>

                <article class="section-card">
                    <h2>Main resources</h2>
                    <p>The current surface includes public lists/details and protected write flows.</p>
                    <div class="resource-links">
                        <a class="button" href="{{ route('docs') }}#/" >Users</a>
                        <a class="button" href="{{ route('docs') }}#/" >Collections</a>
                        <a class="button" href="{{ route('docs') }}#/" >Items</a>
                        <a class="button" href="{{ route('docs') }}#/" >Criteria</a>
                    </div>
                </article>

                <article class="section-card">
                    <h2>Testing</h2>
                    <p>The project already has feature coverage for auth, ownership, CRUD, scoring, and docs access behavior.</p>
                    <code class="code">php artisan test</code>
                </article>
            </section>
        </div>
    </body>
</html>
