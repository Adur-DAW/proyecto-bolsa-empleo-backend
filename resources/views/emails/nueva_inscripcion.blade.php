<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Inscripción</title>
</head>
<body>
    <h1>¡Nueva inscripción en tu oferta!</h1>
    <p>Un candidato se ha inscrito en la oferta <strong>{{ $oferta->nombre }}</strong>.</p>
    
    <h3>Datos del Candidato</h3>
    <p><strong>Nombre:</strong> {{ $demandante->nombre }} {{ $demandante->apellido1 }} {{ $demandante->apellido2 }}</p>
    <p><strong>Email:</strong> {{ $demandante->email }}</p>
    <p><strong>Teléfono:</strong> {{ $demandante->telefono_movil }}</p>
    
    @if($demandante->familiaProfesional)
        <p><strong>Familia Profesional:</strong> {{ $demandante->familiaProfesional->nombre }}</p>
    @endif

    @if($demandante->cv_path)
        <p>El candidato ha adjuntado su currículum. Puedes verlo en el panel de administración.</p>
    @endif
    
    <p>Entra en la aplicación para gestionar la candidatura.</p>
</body>
</html>
