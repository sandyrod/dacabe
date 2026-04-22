@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - 503 Servicio en Mantenimiento')

@section('titulo_header', '503')
@section('subtitulo_header', 'Servicio en Mantenimiento')

@section('content')
<style>
.maintenance-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
}

.maintenance-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    text-align: center;
    max-width: 600px;
    width: 100%;
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.maintenance-icon {
    font-size: 5rem;
    color: #667eea;
    margin-bottom: 1.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.maintenance-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
}

.maintenance-subtitle {
    font-size: 1.25rem;
    color: #4a5568;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.maintenance-features {
    display: flex;
    justify-content: space-around;
    margin: 2rem 0;
    flex-wrap: wrap;
    gap: 1rem;
}

.feature-item {
    flex: 1;
    min-width: 150px;
    padding: 1rem;
    background: linear-gradient(135deg, #f6f9fc 0%, #e9ecef 100%);
    border-radius: 15px;
    transition: transform 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-5px);
}

.feature-icon {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.feature-title {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.btn-maintenance {
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
    transform: translateY(-2px);
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #fef3c7;
    color: #92400e;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #f59e0b;
    border-radius: 50%;
    animation: blink 1.5s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

@media (max-width: 768px) {
    .maintenance-card {
        padding: 2rem;
        margin: 1rem;
    }
    
    .maintenance-title {
        font-size: 2rem;
    }
    
    .maintenance-features {
        flex-direction: column;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-maintenance {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="maintenance-container">
    <div class="maintenance-card">
        <!-- Logo de DACABE -->
        <div style="margin-bottom: 2rem;">
            <img src="{{ asset('imgs/logos/dacabe.png') }}" alt="DACABE" style="height: 80px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">
        </div>
        
        <!-- Icono de mantenimiento animado -->
        <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
        </div>
        
        <!-- Indicador de estado -->
        <div class="status-indicator">
            <span class="status-dot"></span>
            <span>Servicio en Mantenimiento</span>
        </div>
        
        <!-- Título principal -->
        <h1 class="maintenance-title">
            Estamos Mejorando para Ti
        </h1>
        
        <!-- Subtítulo -->
        <p class="maintenance-subtitle">
            Nuestro sistema está temporalmente en mantenimiento para brindarte una mejor experiencia. 
            Estamos trabajando para mejorar nuestro servicio y volveremos pronto.
        </p>
        
        <!-- Características del mantenimiento -->
        <div class="maintenance-features">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="feature-title">
                    Mejoras de Rendimiento
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="feature-title">
                    Seguridad Reforzada
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-sparkles"></i>
                </div>
                <div class="feature-title">
                    Nuevas Funcionalidades
                </div>
            </div>
        </div>
        
        <!-- Mensaje adicional -->
        <p style="color: #718096; font-size: 0.9rem; margin: 1.5rem 0;">
            <i class="fas fa-info-circle"></i> 
            Lamentamos las molestias. Para consultas urgentes, contacta a nuestro equipo de soporte.
        </p>
        
        <!-- Botones de acción -->
        <div class="action-buttons">
            <button onclick="window.location.reload()" class="btn-maintenance btn-primary">
                <i class="fas fa-sync-alt"></i>
                Reintentar
            </button>
        </div>
        
        <!-- Información de contacto -->
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
            <p style="color: #a0aec0; font-size: 0.85rem;">
                <i class="fas fa-envelope"></i> soporte@dacabe.com | 
                <i class="fas fa-phone"></i> +58 123-4567890
            </p>
        </div>
    </div>
</div>

<script>
// Mantenimiento en progreso - La página se actualizará manualmente por el usuario
</script>
@endsection
