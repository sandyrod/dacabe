<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Usuario suspendido</title>
    <style>
        :root {
            --bg-a: #fff7ed;
            --bg-b: #fee2e2;
            --card: #ffffff;
            --danger: #b91c1c;
            --danger-soft: #fef2f2;
            --text: #3f3f46;
            --title: #7f1d1d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 20%, rgba(185, 28, 28, 0.1), transparent 35%),
                radial-gradient(circle at 80% 85%, rgba(194, 65, 12, 0.12), transparent 30%),
                linear-gradient(140deg, var(--bg-a) 0%, var(--bg-b) 100%);
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .panel {
            width: min(760px, 100%);
            background: var(--card);
            border-radius: 20px;
            border: 1px solid #fecaca;
            box-shadow: 0 20px 45px rgba(127, 29, 29, 0.16);
            overflow: hidden;
            animation: panelIn 0.35s ease-out;
        }

        .head {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #fff;
            padding: 28px;
        }

        .head h1 {
            margin: 0;
            font-size: clamp(1.5rem, 2.8vw, 2.2rem);
            letter-spacing: 0.3px;
        }

        .head p {
            margin: 8px 0 0 0;
            font-size: 1rem;
            opacity: 0.95;
        }

        .body {
            padding: 28px;
            background: var(--danger-soft);
        }

        .message {
            font-size: 1.06rem;
            line-height: 1.65;
            margin: 0;
        }

        .badge {
            display: inline-block;
            margin-top: 14px;
            font-weight: 700;
            font-size: 0.86rem;
            color: var(--danger);
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 999px;
            padding: 8px 14px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .foot {
            padding: 18px 28px 24px;
            background: #fff;
            font-size: 0.95rem;
        }

        .logout {
            display: inline-block;
            margin-top: 14px;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            color: #fff;
            background: #1f2937;
            cursor: pointer;
            font-weight: 600;
        }

        .logout:hover {
            background: #111827;
        }

        @keyframes panelIn {
            from {
                transform: translateY(10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <section class="panel" role="alert" aria-live="assertive">
        <header class="head">
            <h1>Cuenta suspendida</h1>
            <p>No tienes acceso operativo en este momento.</p>
        </header>

        <div class="body">
            <p class="message">
                {{ optional($user)->name }} {{ optional($user)->last_name }}, tu usuario de vendedor se encuentra suspendido.
                Mientras esta suspension este activa no podras ejecutar acciones dentro del sistema.
                Para reactivar el acceso, contacta a un administrador.
            </p>
            <span class="badge">Estado: Suspendido</span>
        </div>

        <footer class="foot">
            Si consideras que esto es un error, solicita revision de tu estatus con administracion.
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Cerrar sesion</button>
            </form>
        </footer>
    </section>
</body>
</html>
