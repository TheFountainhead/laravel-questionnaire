<?php

namespace TheFountainhead\Questionnaire\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use TheFountainhead\Questionnaire\Models\QuestionnaireResponse;

class HighRiskClassification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Model $subject,
        public QuestionnaireResponse $response
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('High-Risk Classification Detected'))
            ->line(__('A high-risk classification has been detected for: :name', ['name' => $this->subject->name ?? $this->subject->getKey()]))
            ->line(__('Weighted Score: :score', ['score' => $this->response->weighted_score]))
            ->line(__('Risk Profile: :profile', ['profile' => $this->response->riskProfile?->name]))
            ->line(__('This classification requires enhanced due diligence.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subject_id' => $this->subject->getKey(),
            'subject_name' => $this->subject->name ?? null,
            'response_id' => $this->response->getKey(),
            'weighted_score' => $this->response->weighted_score,
            'risk_profile' => $this->response->riskProfile?->name,
        ];
    }
}
