<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }}</title>

        <style>
            * { box-sizing: border-box; }
            body {
                font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
                background: #f8fafc;
                color: #1e293b;
                margin: 0;
                min-height: 100vh;
            }
            .container { max-width: 960px; margin: 0 auto; padding: 48px 24px; }
            header {
                text-align: center;
                margin-bottom: 56px;
            }
            header h1 {
                font-size: 2.5rem;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 8px;
            }
            header p {
                color: #64748b;
                font-size: 1.1rem;
                margin: 0;
            }
            .stats {
                display: flex;
                gap: 24px;
                justify-content: center;
                margin-bottom: 56px;
                flex-wrap: wrap;
            }
            .stat {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 20px 32px;
                text-align: center;
                min-width: 120px;
            }
            .stat .num {
                font-size: 2rem;
                font-weight: 700;
                color: #2563eb;
            }
            .stat .label {
                font-size: 0.875rem;
                color: #64748b;
                margin-top: 4px;
            }
            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 20px;
                margin-bottom: 56px;
            }
            .card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 24px;
                transition: box-shadow 0.2s;
            }
            .card:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            }
            .card .icon { font-size: 2rem; margin-bottom: 12px; }
            .card h3 {
                font-size: 1.15rem;
                font-weight: 600;
                margin: 0 0 6px;
                color: #0f172a;
            }
            .card p {
                color: #64748b;
                font-size: 0.9rem;
                margin: 0;
            }
            .cta {
                text-align: center;
            }
            .cta a {
                display: inline-block;
                background: #2563eb;
                color: #fff;
                padding: 12px 28px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                font-size: 1rem;
                margin: 0 8px;
                transition: background 0.2s;
            }
            .cta a:hover { background: #1d4ed8; }
            .cta a.secondary { background: #f1f5f9; color: #334155; }
            .cta a.secondary:hover { background: #e2e8f0; }
            footer {
                text-align: center;
                color: #94a3b8;
                font-size: 0.85rem;
                margin-top: 64px;
                padding-top: 24px;
                border-top: 1px solid #e2e8f0;
            }
        </style>
    </head>
    <body>
        <div class="container">

            <header>
                <h1>🎭 {{ $title }}</h1>
                <p>用假数据加速前端联调 · 基于 Laravel 12 + Faker</p>
            </header>

            <div class="stats">
                <div class="stat">
                    <div class="num">{{ $stats['projects'] }}</div>
                    <div class="label">项目</div>
                </div>
                <div class="stat">
                    <div class="num">{{ $stats['resources'] }}</div>
                    <div class="label">资源定义</div>
                </div>
                <div class="stat">
                    <div class="num">{{ $stats['records'] }}</div>
                    <div class="label">已生成数据</div>
                </div>
            </div>

            <div class="features">
                @foreach($features as $feature)
                    <div class="card">
                        <div class="icon">{{ $feature['icon'] }}</div>
                        <h3>{{ $feature['name'] }}</h3>
                        <p>{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="cta">
                @if($user)
                    <p style="margin-bottom:12px;">你好，{{ $user->name }} 👋</p>
                    <a href="{{ url('/api/projects') }}">查看我的项目</a>
                    <a href="{{ url('/docs') }}" class="secondary">API 文档</a>
                @else
                    <a href="{{ url('/docs') }}" class="secondary">浏览 API 文档</a>
                    <a href="{{ url('/api/v1/mock/demo/users') }}" class="secondary">体验公开 Mock 接口</a>
                @endif
            </div>

            <footer>
                v{{ app()->version() }} · {{ config('app.name') }}
            </footer>

        </div>
    </body>
</html>