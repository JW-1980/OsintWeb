<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\EmailUnsubscribe;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Base template mailable class
 *
 * All platform emails extend this class to use database-stored templates
 * with support for unsubscribe links.
 */
abstract class TemplateMailable extends Mailable
{
    use Queueable, SerializesModels;

    protected ?User $user = null;
    protected ?EmailTemplate $template = null;
    protected array $templateData = [];
    protected ?EmailLog $emailLog = null;

    /**
     * Get the template slug for this mailable
     */
    abstract protected function getTemplateSlug(): string;

    /**
     * Get the email category for unsubscribe checking
     */
    abstract protected function getEmailCategory(): string;

    /**
     * Set the recipient user
     */
    public function forUser(User $user): static
    {
        $this->user = $user;
        $this->to($user->email, $user->name);
        return $this;
    }

    /**
     * Build the message
     */
    public function build(): static
    {
        $this->loadTemplate();
        $this->prepareTemplateData();
        $this->createEmailLog();

        if ($this->template === null) {
            return $this->buildFallback();
        }

        return $this
            ->subject($this->template->renderSubject($this->templateData))
            ->html($this->template->renderHtml($this->templateData))
            ->text($this->buildTextContent());
    }

    /**
     * Load the template from database
     */
    protected function loadTemplate(): void
    {
        $this->template = EmailTemplate::where('slug', $this->getTemplateSlug())
            ->where('is_active', true)
            ->first();
    }

    /**
     * Prepare the template data with common variables
     */
    protected function prepareTemplateData(): void
    {
        $baseData = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'year' => date('Y'),
            'unsubscribe_url' => $this->generateUnsubscribeUrl(),
            'preferences_url' => $this->generatePreferencesUrl(),
        ];

        if ($this->user) {
            $baseData['user_name'] = $this->user->name;
            $baseData['user_email'] = $this->user->email;
            $baseData['user_first_name'] = explode(' ', $this->user->name)[0] ?? $this->user->name;
        }

        $this->templateData = array_merge($baseData, $this->getCustomData());
    }

    /**
     * Get custom data for this specific mailable
     */
    protected function getCustomData(): array
    {
        return [];
    }

    /**
     * Generate signed unsubscribe URL
     */
    protected function generateUnsubscribeUrl(): string
    {
        $email = $this->user?->email ?? $this->to[0]['address'] ?? '';
        $category = $this->getEmailCategory();

        return URL::temporarySignedRoute(
            'email.unsubscribe',
            now()->addDays(30),
            [
                'email' => base64_encode($email),
                'type' => $category,
            ]
        );
    }

    /**
     * Generate URL to email preferences page
     */
    protected function generatePreferencesUrl(): string
    {
        return config('app.url') . '/settings/notifications';
    }

    /**
     * Build text content from template
     */
    protected function buildTextContent(): string
    {
        if ($this->template) {
            return $this->template->renderText($this->templateData);
        }

        return strip_tags($this->getDefaultHtmlContent());
    }

    /**
     * Build fallback email when template not found
     */
    protected function buildFallback(): static
    {
        return $this
            ->subject($this->getDefaultSubject())
            ->html($this->getDefaultHtmlContent());
    }

    /**
     * Get default subject (override in child classes)
     */
    protected function getDefaultSubject(): string
    {
        return config('app.name') . ' Notification';
    }

    /**
     * Get default HTML content (override in child classes)
     */
    protected function getDefaultHtmlContent(): string
    {
        return '<p>This is a notification from ' . config('app.name') . '</p>';
    }

    /**
     * Create email log entry
     */
    protected function createEmailLog(): void
    {
        $email = $this->user?->email ?? $this->to[0]['address'] ?? '';

        $this->emailLog = EmailLog::create([
            'user_id' => $this->user?->id,
            'email' => $email,
            'template_slug' => $this->getTemplateSlug(),
            'subject' => $this->template?->renderSubject($this->templateData) ?? $this->getDefaultSubject(),
            'status' => 'pending',
            'variables' => $this->templateData,
        ]);
    }

    /**
     * Check if the recipient has unsubscribed
     */
    public function shouldSend(): bool
    {
        $email = $this->user?->email ?? $this->to[0]['address'] ?? '';
        $category = $this->getEmailCategory();

        // Always send authentication emails
        if ($category === 'authentication') {
            return true;
        }

        // Check user's email notification setting
        if ($this->user && !$this->user->email_notifications_enabled) {
            return false;
        }

        // Check if unsubscribed
        return !EmailUnsubscribe::isUnsubscribed($email, $category);
    }

    /**
     * Get the email log for this message
     */
    public function getEmailLog(): ?EmailLog
    {
        return $this->emailLog;
    }
}
