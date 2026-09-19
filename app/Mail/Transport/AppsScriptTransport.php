<?php

declare(strict_types=1);

namespace App\Mail\Transport;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

/**
 * Sends mail through a small Google Apps Script web app instead of SMTP.
 *
 * Why: hosts like Railway block outbound SMTP (ports 25/465/587) on their
 * non-Pro plans, so Laravel's normal "smtp" mailer can only hang and time out
 * there. This transport talks plain HTTPS to a script owned by the project's
 * Gmail account, which then sends the message with MailApp.sendEmail() — the
 * mail leaves from that real Gmail address. The script's code and setup are in
 * docs/DEPLOY_RAILWAY.md.
 *
 * The request carries a shared secret token; the script rejects anything
 * without it. Only meant for a small volume (Gmail's daily sending limit).
 */
class AppsScriptTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $url,
        private readonly string $token,
        private readonly int $timeoutSeconds = 12,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if ($this->url === '' || $this->token === '') {
            throw new TransportException('Apps Script mailer is not configured (APPSSCRIPT_MAIL_URL / APPSSCRIPT_MAIL_TOKEN).');
        }

        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new TransportException('Apps Script mailer can only send Email messages.');
        }

        $html = (string) $email->getHtmlBody();
        $text = (string) $email->getTextBody();

        if ($text === '' && $html !== '') {
            $text = trim(html_entity_decode(strip_tags($html)));
        }

        $from = $email->getFrom()[0] ?? null;

        foreach ($message->getEnvelope()->getRecipients() as $recipient) {
            $this->post([
                'token' => $this->token,
                'to' => $recipient->getAddress(),
                'subject' => (string) $email->getSubject(),
                'html' => $html,
                'text' => $text,
                'fromName' => $from?->getName() ?: '',
            ]);
        }
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function post(array $payload): void
    {
        try {
            // Apps Script answers a POST with a 302 to the result page; the
            // HTTP client follows it (default), and the script has already run.
            $response = Http::timeout($this->timeoutSeconds)
                ->acceptJson()
                ->asJson()
                ->post($this->url, $payload);
        } catch (ConnectionException $e) {
            throw new TransportException('Could not reach the Apps Script mailer: '.$e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            throw new TransportException('Apps Script mailer answered HTTP '.$response->status().'.');
        }

        // A script that isn't deployed for "Anyone" answers with a Google
        // sign-in HTML page (still HTTP 200), so the JSON flag is what counts.
        if ($response->json('ok') !== true) {
            $reason = $response->json('error') ?: 'unexpected response (is the web app deployed with access "Anyone"?)';

            throw new TransportException('Apps Script mailer refused the message: '.$reason);
        }
    }

    public function __toString(): string
    {
        return 'appsscript';
    }
}
