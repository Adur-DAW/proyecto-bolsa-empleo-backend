<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Oferta de Empleo</title>
</head>
<body>
    <h1>¡Nueva oferta disponible!</h1>
    <p>Se ha publicado una nueva oferta de empleo que podría interesarte:</p>
    
    <h2>{{ $oferta->nombre }}</h2>
    <p><strong>Empresa:</strong> {{ $oferta->empresa->nombre }}</p>
    <p><strong>Localidad:</strong> {{ $oferta->empresa->localidad }}</p>
    <p><strong>Tipo de Contrato:</strong> {{ $oferta->tipo_contrato }}</p>
    <p><strong>Horario:</strong> {{ $oferta->horario }}</p>
    @if($oferta->dias_descanso)
        <p><strong>Días de Descanso:</strong> {{ $oferta->dias_descanso }}</p>
    @endif
    
    <p>Entra en la aplicación para ver más detalles e inscribirte.</p>
</body>
</html>
