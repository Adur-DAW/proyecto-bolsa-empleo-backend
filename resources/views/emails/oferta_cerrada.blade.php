<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oferta Cerrada</title>
</head>
<body>
    <h1>Actualización de Oferta de Empleo</h1>
    
    @if($motivo === 'adjudicada')
        <p><strong>¡Enhorabuena!</strong></p>
        <p>Te informamos sobre la oferta <strong>{{ $oferta->nombre }}</strong> en la que estabas inscrito.</p>
        <p>Has sido seleccionado. La empresa se pondrá en contacto contigo pronto para los siguientes pasos.</p>
    @elseif($motivo === 'invitacion')
        <p>Una empresa te ha enviado esta oferta de empleo directamente.</p>
        <p>Puedes entrar en la plataforma para ver los detalles y aceptar o rechazar la propuesta.</p>
    @else
        <p>La oferta ha sido <strong>cerrada</strong> por límite temporal o por decisión de la empresa.</p>
    @endif
</body>
</html>
