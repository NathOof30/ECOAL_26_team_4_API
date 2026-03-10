<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ECOAL API Docs</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
        <style>
            :root {
                --bg: #f3efe6;
                --panel: #fffaf2;
                --ink: #2f2419;
                --muted: #6d5b4b;
                --accent: #c96a1b;
                --accent-dark: #8f4710;
                --line: #dfcfbb;
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
                    radial-gradient(circle at top left, rgba(201, 106, 27, 0.18), transparent 28%),
                    radial-gradient(circle at right center, rgba(143, 71, 16, 0.12), transparent 24%),
                    linear-gradient(180deg, #f7f1e8 0%, var(--bg) 100%);
            }

            .shell {
                width: min(1400px, calc(100vw - 32px));
                margin: 24px auto;
                border: 1px solid var(--line);
                border-radius: 24px;
                overflow: hidden;
                background: rgba(255, 250, 242, 0.92);
                box-shadow: 0 20px 80px rgba(73, 43, 16, 0.14);
                backdrop-filter: blur(12px);
            }

            .topbar {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: end;
                padding: 28px 32px 24px;
                border-bottom: 1px solid var(--line);
                background: linear-gradient(135deg, rgba(201, 106, 27, 0.1), rgba(255, 250, 242, 0.3));
            }

            .eyebrow {
                margin: 0 0 8px;
                font-size: 12px;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: var(--accent-dark);
            }

            h1 {
                margin: 0;
                font-size: clamp(28px, 4vw, 42px);
                line-height: 1;
            }

            .sub {
                margin: 10px 0 0;
                max-width: 720px;
                color: var(--muted);
                font-size: 15px;
            }

            .links {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .link {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 42px;
                padding: 0 16px;
                border-radius: 999px;
                text-decoration: none;
                border: 1px solid var(--line);
                color: var(--ink);
                background: rgba(255, 255, 255, 0.75);
            }

            .link.primary {
                color: #fffaf2;
                background: linear-gradient(135deg, var(--accent), var(--accent-dark));
                border-color: transparent;
            }

            #swagger-ui {
                padding: 20px 20px 36px;
            }

            .swagger-ui .topbar {
                display: none;
            }

            .swagger-ui .information-container.wrapper {
                padding: 0 12px 18px;
            }

            .swagger-ui .scheme-container {
                background: transparent;
                box-shadow: none;
                padding: 12px;
            }

            .swagger-ui .opblock-tag {
                border-bottom-color: var(--line);
            }

            .swagger-ui .opblock .opblock-summary {
                border-color: rgba(47, 36, 25, 0.08);
            }

            @media (max-width: 900px) {
                .topbar {
                    align-items: start;
                    flex-direction: column;
                }

                .shell {
                    width: min(100vw - 16px, 1400px);
                    margin: 8px auto;
                    border-radius: 18px;
                }

                #swagger-ui {
                    padding: 12px 8px 24px;
                }
            }
        </style>
    </head>
    <body>
        <div class="shell">
            <div class="topbar">
                <div>
                    <p class="eyebrow">ECOAL 26 Team 4</p>
                    <h1>API Documentation</h1>
                    <p class="sub">Swagger UI backed by the local OpenAPI file. Use this page to inspect routes, payloads, responses, and auth requirements.</p>
                </div>
                <div class="links">
                    <a class="link primary" href="{{ route('docs.openapi') }}" target="_blank" rel="noreferrer">Open raw spec</a>
                    <a class="link" href="{{ url('/') }}">Back to app</a>
                </div>
            </div>
            <div id="swagger-ui"></div>
        </div>

        <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
        <script>
            window.ui = SwaggerUIBundle({
                url: "{{ route('docs.openapi') }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                displayRequestDuration: true,
                filter: true,
                persistAuthorization: true,
            });
        </script>
    </body>
</html>
