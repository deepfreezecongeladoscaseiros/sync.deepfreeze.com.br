<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Controller público para página de Contato.
 *
 * Exibe a página de contato e processa envio de mensagens.
 */
class ContactController extends Controller
{
    /**
     * Exibe a página de contato.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        // Obtém configurações
        $settings = ContactSetting::getSettings();

        // Verifica se página está ativa
        if (!$settings->isActive()) {
            abort(404);
        }

        return view('storefront.contact.index', compact('settings'));
    }

    /**
     * Processa envio do formulário de contato.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        // Rate limiting: máximo 5 mensagens por IP a cada 10 minutos
        $key = 'contact-form:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => "Você enviou muitas mensagens. Tente novamente em {$seconds} segundos.",
            ], 429);
        }

        // Validação dos dados
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
        ], [
            'name.required' => 'Por favor, informe seu nome.',
            'name.max' => 'O nome deve ter no máximo 100 caracteres.',
            'email.required' => 'Por favor, informe seu e-mail.',
            'email.email' => 'Por favor, informe um e-mail válido.',
            'message.required' => 'Por favor, escreva sua mensagem.',
            'message.max' => 'A mensagem deve ter no máximo 1000 caracteres.',
        ]);

        try {
            // Obtém configurações
            $settings = ContactSetting::getSettings();

            // Salva mensagem no banco
            $contactMessage = ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'message' => $validated['message'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Tenta enviar e-mail (se configurado)
            if ($settings->form_recipient_email) {
                $this->sendEmailNotification($settings, $contactMessage);
            }

            // Incrementa rate limiter
            RateLimiter::hit($key, 600); // 10 minutos

            return response()->json([
                'success' => true,
                'message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.',
            ]);

        } catch (\Exception $e) {
            // Log do erro para debug
            Log::error('Erro ao processar formulário de contato: ' . $e->getMessage(), [
                'data' => $validated,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao enviar sua mensagem. Por favor, tente novamente.',
            ], 500);
        }
    }

    /**
     * Envia notificação por e-mail.
     *
     * @param ContactSetting $settings
     * @param ContactMessage $message
     * @return void
     */
    protected function sendEmailNotification(ContactSetting $settings, ContactMessage $message): void
    {
        try {
            // Envia e-mail usando Mail facade
            Mail::send([], [], function ($mail) use ($settings, $message) {
                $mail->to($settings->form_recipient_email)
                    ->subject($settings->form_subject ?: 'Nova mensagem de contato')
                    ->html($this->buildEmailHtml($message));

                // Reply-To para o e-mail do remetente
                $mail->replyTo($message->email, $message->name);
            });

        } catch (\Exception $e) {
            // Log do erro mas não falha a operação
            Log::warning('Falha ao enviar e-mail de notificação de contato: ' . $e->getMessage());
        }
    }

    /**
     * Constrói HTML do e-mail de notificação.
     *
     * @param ContactMessage $message
     * @return string
     */
    protected function buildEmailHtml(ContactMessage $message): string
    {
        $phone = $message->getFormattedPhone() ?: 'Não informado';
        $date = $message->created_at->format('d/m/Y H:i');

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #1e5245; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #1e5245; }
                .value { margin-top: 5px; }
                .message-box { background-color: white; padding: 15px; border-left: 4px solid #1e5245; }
                .footer { padding: 15px; font-size: 12px; color: #666; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Nova Mensagem de Contato</h2>
                </div>
                <div class='content'>
                    <div class='field'>
                        <div class='label'>Nome:</div>
                        <div class='value'>" . e($message->name) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>E-mail:</div>
                        <div class='value'><a href='mailto:" . e($message->email) . "'>" . e($message->email) . "</a></div>
                    </div>
                    <div class='field'>
                        <div class='label'>Telefone:</div>
                        <div class='value'>{$phone}</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Mensagem:</div>
                        <div class='message-box'>" . nl2br(e($message->message)) . "</div>
                    </div>
                </div>
                <div class='footer'>
                    Mensagem recebida em {$date}<br>
                    IP: " . e($message->ip_address) . "
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
