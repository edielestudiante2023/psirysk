# Migración de emails SMTP a SendGrid API con click tracking desactivado

## Problema

Los emails enviados via SMTP a través de SendGrid tienen click tracking activado por defecto. SendGrid reescribe todos los enlaces `<a href>` en el HTML a URLs de tracking tipo `https://urlXX.tudominio.com/ls/click?...`. Si el subdominio de tracking no tiene SSL configurado correctamente, los botones de los emails llevan a una página de error (`NET::ERR_CERT_COMMON_NAME_INVALID`).

## Causa raíz

- SendGrid reescribe enlaces automáticamente cuando se envía por SMTP
- No se puede desactivar click tracking por email individual via SMTP
- El subdominio de tracking no tiene SSL configurado correctamente

## Solución

Migrar de SMTP al **SDK de SendGrid** (API HTTP directa), que permite desactivar click tracking por email individual.

### Paso 1: Instalar SDK de SendGrid

```bash
composer require sendgrid/sendgrid "^7.0" -W
```

> Nota: La v8 requiere `ext-gmp`. Si no la tienes, usa v7.

### Paso 2: Patrón de envío con click tracking desactivado

Donde antes se tenía esto (SMTP):

```php
$email = \Config\Services::email();
$email->setFrom('noreply@midominio.com', 'Mi App');
$email->setTo($destinatario);
$email->setSubject('Asunto');
$email->setMessage($htmlBody);
$email->send();
```

Se reemplaza por esto (SendGrid API):

```php
try {
    $sgEmail = new \SendGrid\Mail\Mail();
    $sgEmail->setFrom('noreply@midominio.com', 'Mi App');
    $sgEmail->setSubject('Asunto');
    $sgEmail->addTo($destinatario);
    $sgEmail->addContent("text/html", $htmlBody);

    // CC (opcional)
    $sgEmail->addCc('alguien@ejemplo.com');

    // CLAVE: Desactivar click tracking
    $trackingSettings = new \SendGrid\Mail\TrackingSettings();
    $clickTracking = new \SendGrid\Mail\ClickTracking();
    $clickTracking->setEnable(false);
    $clickTracking->setEnableText(false);
    $trackingSettings->setClickTracking($clickTracking);
    $sgEmail->setTrackingSettings($trackingSettings);

    // Enviar - la API key es la misma que se usaba como SMTP password
    $apiKey = env('email.SMTPPass'); // o getenv('SENDGRID_API_KEY')
    $sendgrid = new \SendGrid($apiKey);
    $response = $sendgrid->send($sgEmail);

    if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
        log_message('info', "Email enviado a: {$destinatario}");
    } else {
        log_message('error', "Error HTTP {$response->statusCode()}: " . $response->body());
    }
} catch (\Exception $e) {
    log_message('error', "Error enviando email: " . $e->getMessage());
}
```

### Paso 3: Para un servicio centralizado (recomendado)

Si tienes muchos métodos que envían email, crea un método central:

```php
protected function sendViaSendGrid(string $toEmail, string $subject, string $htmlContent, array $options = []): bool
{
    try {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom(
            $options['fromEmail'] ?? env('email.fromEmail'),
            $options['fromName'] ?? env('email.fromName')
        );
        $email->setSubject($subject);
        $email->addTo($toEmail);
        $email->addContent("text/html", $htmlContent);

        if (!empty($options['cc'])) {
            foreach ($options['cc'] as $cc) {
                $email->addCc($cc);
            }
        }

        // Desactivar click tracking
        $trackingSettings = new \SendGrid\Mail\TrackingSettings();
        $clickTracking = new \SendGrid\Mail\ClickTracking();
        $clickTracking->setEnable(false);
        $clickTracking->setEnableText(false);
        $trackingSettings->setClickTracking($clickTracking);
        $email->setTrackingSettings($trackingSettings);

        $sendgrid = new \SendGrid(env('email.SMTPPass'));
        $response = $sendgrid->send($email);

        return $response->statusCode() >= 200 && $response->statusCode() < 300;
    } catch (\Exception $e) {
        log_message('error', "Error enviando email a {$toEmail}: " . $e->getMessage());
        return false;
    }
}
```

Luego cada método simplemente llama:

```php
return $this->sendViaSendGrid($toEmail, 'Mi Asunto', $htmlMessage);
```

### Paso 4: Buscar todos los puntos de envío en tu proyecto

```bash
grep -rn "Services::email()" app/ --include="*.php"
```

Migra cada resultado al patrón del Paso 2.

### Paso 5: Deploy

```bash
git add .
git commit -m "fix: Migrar emails a SendGrid API con click tracking desactivado"
git push origin main
```

En el servidor:

```bash
git pull origin main
composer install --no-dev
```

> `composer install` es obligatorio porque se agregó la dependencia `sendgrid/sendgrid`.

### Paso 6: Probar

Si tienes un comando de test email:

```bash
php spark test:email tu@email.com
```

Verificar que el email llega y los enlaces apuntan directamente a tu dominio sin pasar por el subdominio de tracking de SendGrid.

## Notas importantes

- La **API key** es la misma que se usaba como password SMTP (`SG.xxx...`). No necesitas crear una nueva.
- La config del `.env` (`email.SMTPHost`, `email.SMTPPort`, etc.) ya no se usa para los emails migrados, pero puedes dejarla por si acaso.
- Si en el futuro se arregla el SSL del subdominio de tracking, puedes quitar las líneas de `TrackingSettings` para reactivar el click tracking.
- SendGrid API tiene mejor rendimiento que SMTP (una sola request HTTP vs handshake SMTP).
