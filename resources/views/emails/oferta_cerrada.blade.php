<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oferta Cerrada</title>
</head>
<body>
    <h1>Actualización de Oferta de Empleo</h1>
    <p>Te informamos sobre la oferta <strong>{{ $oferta->nombre }}</strong> en la que estabas inscrito.</p>
    
    @if($motivo === 'adjudicada')
        <p>La oferta ha sido <strong>adjudicada</strong>. El proceso de selección ha finalizado.</p>
    @else
        <p>La oferta ha sido <strong>cerrada</strong> por límite temporal o por decisión de la empresa.</p>
    @endif
    
    <p>Gracias por participar en el proceso de selección.</p>
    <p>¡Mucha suerte en tu búsqueda de empleo!</p>
</body>
</html>
