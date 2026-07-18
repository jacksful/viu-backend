<?php

namespace App\Filament\Resources\UserZipcodeSubscriptionResource\Pages;

use App\Filament\Resources\UserZipcodeSubscriptionResource;
use App\Models\ClientActivityLog;
use App\Models\CustomerIntake;
use App\Models\StripePayment;
use App\Models\User;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Components\View as ViewComponent;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ViewClient extends ViewRecord
{
    protected static string $resource = UserZipcodeSubscriptionResource::class;

    protected static bool $shouldRegisterNavigation = false;

    /** @var array<string, mixed>|null */
    protected ?array $clientViewData = null;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing(['user', 'customerIntake.zipcode']);
    }

    public function getTitle(): string
    {
        return $this->getClientUser()?->name ?? 'Client';
    }

    public function getBreadcrumbs(): array
    {
        return [
            UserZipcodeSubscriptionResource::getUrl() => 'Clients',
            $this->getTitle(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $data = fn (): array => $this->clientViewData();

        return $schema
            ->components([
                ViewComponent::make('filament.resources.user-zipcode-subscription.partials.client-profile')
                    ->viewData($data)
                    ->visible(fn (): bool => filled($data()['user'])),

                SchemaComponents\Grid::make([
                    'default' => 1,
                    'md' => 2,
                    'xl' => 4,
                ])
                    ->schema([
                        SchemaComponents\Section::make('Lifetime revenue')
                            ->description(fn (): string => $data()['lifetimeRevenueNote'])
                            ->schema([
                                SchemaComponents\Text::make(fn (): string => $data()['lifetimeRevenue'])
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ])
                            ->icon('heroicon-o-banknotes')
                            ->compact(),

                        SchemaComponents\Section::make('Territories')
                            ->description(fn (): string => $data()['territoriesSummary']['details'])
                            ->schema([
                                SchemaComponents\Text::make(fn (): string => $data()['territoriesSummary']['count'].' active')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ])
                            ->icon('heroicon-o-map-pin')
                            ->compact(),

                        SchemaComponents\Section::make('Intake')
                            ->description(fn (): string => $data()['intakeSummary']['description'])
                            ->schema([
                                SchemaComponents\Text::make(fn (): string => $data()['intakeSummary']['status'])
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color(fn (): string => match ($data()['intakeSummary']['status']) {
                                        'Submitted' => 'success',
                                        'Draft' => 'warning',
                                        default => 'gray',
                                    }),
                            ])
                            ->icon('heroicon-o-clipboard-document-list')
                            ->compact(),

                        SchemaComponents\Section::make('Next renewal')
                            ->description(function () use ($data): string {
                                $nextRenewal = $data()['nextRenewal'];

                                if (! $nextRenewal) {
                                    return 'No upcoming renewal scheduled';
                                }

                                $parts = array_filter([
                                    filled($nextRenewal['zipcode']) ? 'ZIP '.$nextRenewal['zipcode'] : null,
                                    $nextRenewal['amount'] ?? null,
                                    ($nextRenewal['reminderSent'] ?? false) ? 'Renewal reminder sent' : null,
                                ]);

                                return implode(' · ', $parts);
                            })
                            ->schema([
                                SchemaComponents\Text::make(fn (): string => $data()['nextRenewal']['date'] ?? '—')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ])
                            ->icon('heroicon-o-calendar-days')
                            ->compact(),
                    ])
                    ->visible(fn (): bool => filled($data()['user'])),

                SchemaComponents\Grid::make([
                    'default' => 1,
                    'xl' => 3,
                ])
                    ->schema([
                        SchemaComponents\Group::make([
                            $this->subscriptionsSection(),
                            $this->paymentsSection(),
                            $this->intakeReviewSection(),
                        ])->columnSpan(2),

                        SchemaComponents\Section::make('Activity & communication')
                            ->headerActions([
                                $this->logNoteOrCallAction(),
                            ])
                            ->schema([
                                ViewComponent::make('filament.resources.user-zipcode-subscription.partials.client-activity')
                                    ->viewData(fn (): array => [
                                        'activities' => $this->clientViewData()['activities'],
                                    ]),
                            ])
                            ->columnSpan(1),
                    ])
                    ->visible(fn (): bool => filled($data()['user'])),

                SchemaComponents\Section::make('Client unavailable')
                    ->description('Client details are unavailable because no user is linked to this subscription.')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('warning')
                    ->visible(fn (): bool => blank($data()['user'])),
            ]);
    }

    protected function subscriptionsSection(): SchemaComponents\Section
    {
        return SchemaComponents\Section::make('Subscriptions')
            ->headerActions([
                Action::make('openClientsList')
                    ->label('Open list')
                    ->icon('heroicon-o-queue-list')
                    ->url(fn (): string => UserZipcodeSubscriptionResource::getUrl())
                    ->link(),
            ])
            ->schema([
                RepeatableEntry::make('subscriptions')
                    ->hiddenLabel()
                    ->state(fn (): array => $this->clientViewData()['subscriptions']->all())
                    ->table([
                        TableColumn::make('ID'),
                        TableColumn::make('ZIP'),
                        TableColumn::make('Plan'),
                        TableColumn::make('Period'),
                        TableColumn::make('Status'),
                    ])
                    ->schema([
                        TextEntry::make('id')
                            ->formatStateUsing(fn (string | int $state): string => '#'.$state),
                        TextEntry::make('zipcodes')
                            ->placeholder('—'),
                        TextEntry::make('plan'),
                        TextEntry::make('period'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                                'active' => 'success',
                                'expired' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            }),
                    ])
                    ->placeholder('No subscriptions found for this client.'),
            ]);
    }

    protected function paymentsSection(): SchemaComponents\Section
    {
        return SchemaComponents\Section::make('Payments')
            ->headerActions([
                Action::make('viewInStripe')
                    ->label('View in Stripe')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (): ?string => $this->clientViewData()['stripeDashboardUrl'])
                    ->openUrlInNewTab()
                    ->visible(fn (): bool => filled($this->clientViewData()['stripeDashboardUrl']))
                    ->link(),
            ])
            ->schema([
                RepeatableEntry::make('payments')
                    ->hiddenLabel()
                    ->state(fn (): array => $this->clientViewData()['payments']->all())
                    ->table([
                        TableColumn::make('Date'),
                        TableColumn::make('ZIP'),
                        TableColumn::make('Amount'),
                        TableColumn::make('Status'),
                    ])
                    ->schema([
                        TextEntry::make('date'),
                        TextEntry::make('zipcode'),
                        TextEntry::make('amount')
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'Paid' => 'success',
                                'Renewal failed' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->placeholder('No payments recorded for this client.'),
            ]);
    }

    protected function intakeReviewSection(): SchemaComponents\Section
    {
        return SchemaComponents\Section::make('Intake review')
            ->description(function (): ?string {
                $intake = $this->clientViewData()['intakeSummary']['intake'];

                return $intake
                    ? 'Submitted intake for '.$intake->full_name
                    : null;
            })
            ->schema([
                ViewComponent::make('filament.resources.user-zipcode-subscription.intake-modal')
                    ->viewData(function (): array {
                        $intake = $this->clientViewData()['intakeSummary']['intake'];

                        return [
                            'subscription' => $intake?->subscription,
                            'intake' => $intake,
                        ];
                    })
                    ->visible(fn (): bool => filled($this->clientViewData()['intakeSummary']['intake']?->subscription)),
                SchemaComponents\Text::make('Intake details are available, but the linked subscription could not be loaded.')
                    ->color('gray')
                    ->visible(function (): bool {
                        $intake = $this->clientViewData()['intakeSummary']['intake'];

                        return filled($intake) && blank($intake->subscription);
                    }),
            ])
            ->visible(fn (): bool => filled($this->clientViewData()['intakeSummary']['intake']))
            ->extraAttributes(['id' => 'client-intake-review']);
    }

    protected function logNoteOrCallAction(): Action
    {
        return Action::make('logNoteOrCall')
            ->label('Log note or call')
            ->icon('heroicon-o-phone')
            ->color('gray')
            ->outlined()
            ->visible(fn (): bool => filled($this->getClientUser()))
            ->modalHeading('Log note or call')
            ->modalDescription('Added to the client\'s communication record with your name and timestamp.')
            ->modalWidth('md')
            ->modalSubmitActionLabel('Save to record')
            ->modalCancelActionLabel('Cancel')
            ->form([
                Components\Select::make('type')
                    ->label('Type')
                    ->options(ClientActivityLog::typeOptions())
                    ->default(ClientActivityLog::TYPE_PHONE_CALL)
                    ->required()
                    ->native(false),

                Components\Textarea::make('body')
                    ->label('What happened')
                    ->required()
                    ->rows(4)
                    ->placeholder('e.g. Sarah asked about adding a second ZIP next quarter...')
                    ->maxLength(5000),
            ])
            ->action(function (array $data): void {
                $client = $this->getClientUser();

                if (! $client) {
                    return;
                }

                ClientActivityLog::query()->create([
                    'user_id' => $client->id,
                    'admin_user_id' => auth()->id(),
                    'type' => $data['type'],
                    'body' => $data['body'],
                ]);

                $this->clientViewData = null;

                Notification::make()
                    ->title('Activity logged')
                    ->success()
                    ->send();
            });
    }

    protected function getHeaderActions(): array
    {
        $user = $this->getClientUser();

        return [
            Actions\Action::make('email')
                ->label('Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->url(fn (): string => 'mailto:'.($user?->email ?? ''))
                ->openUrlInNewTab(),

            Actions\Action::make('resetPassword')
                ->label('Reset password')
                ->icon('heroicon-o-key')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Send password reset email?')
                ->modalDescription(fn (): string => "We'll email {$user?->email} a link to reset their password.")
                ->action(function () use ($user): void {
                    if (! $user?->email) {
                        return;
                    }

                    $status = Password::sendResetLink(['email' => $user->email]);

                    if ($status === Password::RESET_LINK_SENT) {
                        Notification::make()
                            ->title('Password reset email sent')
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Unable to send password reset email')
                        ->danger()
                        ->send();
                }),

            Actions\EditAction::make()
                ->label('Edit')
                ->modalHeading('Edit Subscription')
                ->modalWidth('5xl'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function clientViewData(): array
    {
        return $this->clientViewData ??= $this->buildClientViewData();
    }

    protected function getClientUser(): ?User
    {
        return $this->getRecord()->user;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildClientViewData(): array
    {
        $user = $this->getClientUser();

        if (! $user) {
            return [
                'user' => null,
                'initials' => '—',
                'companyName' => null,
                'locationLabel' => '—',
                'clientSince' => '—',
                'lifetimeRevenue' => '$0.00',
                'lifetimeRevenueNote' => 'No payments recorded',
                'territoriesSummary' => ['count' => 0, 'details' => '—'],
                'intakeSummary' => ['status' => 'Not started', 'description' => 'No intake submitted yet', 'intake' => null],
                'nextRenewal' => null,
                'subscriptions' => collect(),
                'payments' => collect(),
                'activities' => collect(),
                'stripeDashboardUrl' => null,
                'clientsIndexUrl' => UserZipcodeSubscriptionResource::getUrl(),
            ];
        }

        $subscriptions = UserZipcodeSubscription::query()
            ->where('user_id', $user->id)
            ->with(['customerIntake.zipcode'])
            ->orderByDesc('created_at')
            ->get();

        $zipcodeIds = $subscriptions
            ->flatMap(fn (UserZipcodeSubscription $subscription): array => $subscription->zipcode_ids ?? [])
            ->unique()
            ->values()
            ->all();

        $zipcodesById = Zipcode::query()
            ->whereIn('id', $zipcodeIds)
            ->get()
            ->keyBy('id');

        $payments = StripePayment::query()
            ->where('user_id', $user->id)
            ->with('zipcode')
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();

        $communicationLogs = ClientActivityLog::query()
            ->where('user_id', $user->id)
            ->with('admin')
            ->latest()
            ->get();

        $lifetimeRevenueCents = $payments
            ->where('status', 'paid')
            ->sum('amount_cents');

        $latestIntake = CustomerIntake::query()
            ->where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->with(['zipcode', 'subscription.user'])
            ->latest('submitted_at')
            ->first();

        $draftIntake = CustomerIntake::query()
            ->where('user_id', $user->id)
            ->whereNull('submitted_at')
            ->exists();

        $activeSubscriptions = $subscriptions->where('status', 'active');
        $territoryDetails = $subscriptions
            ->flatMap(function (UserZipcodeSubscription $subscription) use ($zipcodesById): array {
                return collect($subscription->zipcode_ids ?? [])
                    ->map(function ($zipcodeId) use ($subscription, $zipcodesById): ?string {
                        $zipcode = $zipcodesById->get($zipcodeId);

                        if (! $zipcode) {
                            return null;
                        }

                        return "{$zipcode->code} ".ucfirst($subscription->status);
                    })
                    ->filter()
                    ->all();
            })
            ->unique()
            ->values();

        $nextRenewalSubscription = $activeSubscriptions
            ->filter(fn (UserZipcodeSubscription $subscription): bool => $subscription->end_date !== null)
            ->sortBy('end_date')
            ->first();

        $nextRenewal = null;

        if ($nextRenewalSubscription) {
            $primaryZipcodeId = $nextRenewalSubscription->zipcode_ids[0] ?? null;
            $primaryZipcode = $primaryZipcodeId ? $zipcodesById->get($primaryZipcodeId) : null;
            $interval = $nextRenewalSubscription->billing_interval === 'year' ? 'yr' : 'mo';
            $amount = $primaryZipcode
                ? ($nextRenewalSubscription->billing_interval === 'year'
                    ? $primaryZipcode->yearly_price
                    : $primaryZipcode->monthly_price)
                : null;

            $nextRenewal = [
                'date' => $nextRenewalSubscription->end_date?->format('M j, Y'),
                'zipcode' => $primaryZipcode?->code,
                'amount' => $amount !== null ? '$'.number_format((float) $amount, 0).'/'.$interval : null,
                'reminderSent' => filled($nextRenewalSubscription->renewal_reminder_sent_for_end_date),
            ];
        }

        $stripeCustomerId = $subscriptions
            ->pluck('stripe_customer_id')
            ->filter()
            ->first() ?? $user->stripe_id;

        return [
            'user' => $user,
            'initials' => $this->getInitials($user),
            'companyName' => $latestIntake?->brokerage_name,
            'locationLabel' => $this->getLocationLabel($user, $zipcodesById, $subscriptions),
            'clientSince' => $this->getClientSinceLabel($user, $subscriptions),
            'lifetimeRevenue' => '$'.number_format($lifetimeRevenueCents / 100, 2),
            'lifetimeRevenueNote' => $this->getLifetimeRevenueNote($payments),
            'territoriesSummary' => [
                'count' => $activeSubscriptions->count(),
                'details' => $territoryDetails->isNotEmpty()
                    ? $territoryDetails->join(' · ')
                    : 'No territories assigned',
            ],
            'intakeSummary' => $this->getIntakeSummary($latestIntake, $draftIntake),
            'nextRenewal' => $nextRenewal,
            'subscriptions' => $this->mapSubscriptions($subscriptions, $zipcodesById),
            'payments' => $this->mapPayments($payments),
            'activities' => $this->buildActivityTimeline($user, $subscriptions, $payments, $latestIntake, $communicationLogs),
            'stripeDashboardUrl' => $stripeCustomerId
                ? 'https://dashboard.stripe.com/customers/'.$stripeCustomerId
                : null,
            'clientsIndexUrl' => UserZipcodeSubscriptionResource::getUrl(),
        ];
    }

    protected function getInitials(User $user): string
    {
        $first = Str::substr($user->first_name ?: Str::before($user->name, ' '), 0, 1);
        $last = Str::substr($user->last_name ?: Str::afterLast($user->name, ' '), 0, 1);

        $initials = Str::upper(trim($first.$last));

        return $initials !== '' ? $initials : Str::upper(Str::substr($user->name, 0, 2));
    }

    /**
     * @param  Collection<int, Zipcode>  $zipcodesById
     * @param  Collection<int, UserZipcodeSubscription>  $subscriptions
     */
    protected function getLocationLabel(User $user, Collection $zipcodesById, Collection $subscriptions): string
    {
        if ($user->city && $user->state) {
            return "{$user->city}, {$user->state}";
        }

        $firstSubscription = $subscriptions->first();
        $firstZipcodeId = $firstSubscription?->zipcode_ids[0] ?? null;
        $firstZipcode = $firstZipcodeId ? $zipcodesById->get($firstZipcodeId) : null;

        if ($firstZipcode?->city && $firstZipcode?->state) {
            return "{$firstZipcode->city}, {$firstZipcode->state}";
        }

        return '—';
    }

    /**
     * @param  Collection<int, UserZipcodeSubscription>  $subscriptions
     */
    protected function getClientSinceLabel(User $user, Collection $subscriptions): string
    {
        $earliestStart = $subscriptions->min('start_date') ?? $user->created_at;

        return 'Client since '.$earliestStart?->format('M j, Y');
    }

    /**
     * @param  Collection<int, StripePayment>  $payments
     */
    protected function getLifetimeRevenueNote(Collection $payments): string
    {
        if ($payments->where('status', 'paid')->isEmpty()) {
            return 'No payments recorded';
        }

        $modes = $payments
            ->pluck('metadata.test_mode')
            ->filter()
            ->unique();

        if ($modes->count() === 1 && $modes->first()) {
            return 'All test mode';
        }

        if ($modes->contains(true)) {
            return 'Includes test mode payments';
        }

        return 'Paid invoices only';
    }

    /**
     * @return array{status: string, description: string, intake: ?CustomerIntake}
     */
    protected function getIntakeSummary(?CustomerIntake $latestIntake, bool $draftIntake): array
    {
        if ($latestIntake) {
            return [
                'status' => 'Submitted',
                'description' => 'Awaiting review — scroll to intake review below',
                'intake' => $latestIntake,
            ];
        }

        if ($draftIntake) {
            return [
                'status' => 'Draft',
                'description' => 'Intake started but not submitted yet',
                'intake' => null,
            ];
        }

        return [
            'status' => 'Not started',
            'description' => 'No intake submitted yet',
            'intake' => null,
        ];
    }

    /**
     * @param  Collection<int, UserZipcodeSubscription>  $subscriptions
     * @param  Collection<int, Zipcode>  $zipcodesById
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapSubscriptions(Collection $subscriptions, Collection $zipcodesById): Collection
    {
        return $subscriptions->map(function (UserZipcodeSubscription $subscription) use ($zipcodesById): array {
            $zipcodes = collect($subscription->zipcode_ids ?? [])
                ->map(fn ($zipcodeId) => $zipcodesById->get($zipcodeId)?->code)
                ->filter()
                ->values();

            return [
                'id' => $subscription->id,
                'zipcodes' => $zipcodes->join(', '),
                'plan' => match ($subscription->billing_interval) {
                    'year' => 'Yearly',
                    'month' => 'Monthly',
                    default => '—',
                },
                'period' => $subscription->formattedStartDate().' – '.$subscription->formattedEndDate(),
                'status' => ucfirst($subscription->status),
                'statusColor' => match ($subscription->status) {
                    'active' => 'success',
                    'expired' => 'danger',
                    'pending' => 'warning',
                    default => 'gray',
                },
            ];
        });
    }

    /**
     * @param  Collection<int, StripePayment>  $payments
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapPayments(Collection $payments): Collection
    {
        return $payments->map(function (StripePayment $payment): array {
            return [
                'date' => ($payment->paid_at ?? $payment->created_at)?->format('M j, Y') ?? '—',
                'zipcode' => $payment->zipcode?->code ?? '—',
                'amount' => $payment->formattedAmount(),
                'status' => $this->formatPaymentStatus($payment->status),
                'statusColor' => match ($payment->status) {
                    'paid' => 'success',
                    'failed' => 'danger',
                    default => 'gray',
                },
            ];
        });
    }

    protected function formatPaymentStatus(?string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'failed' => 'Renewal failed',
            'checkout_pending' => 'Checkout pending',
            'cancelled_unavailable' => 'Cancelled',
            default => Str::headline((string) $status),
        };
    }

    /**
     * @param  Collection<int, UserZipcodeSubscription>  $subscriptions
     * @param  Collection<int, StripePayment>  $payments
     * @param  Collection<int, ClientActivityLog>  $communicationLogs
     * @return Collection<int, array<string, mixed>>
     */
    protected function buildActivityTimeline(
        User $user,
        Collection $subscriptions,
        Collection $payments,
        ?CustomerIntake $latestIntake,
        Collection $communicationLogs,
    ): Collection {
        $activities = collect();

        foreach ($communicationLogs as $log) {
            $activities->push([
                'category' => 'communication',
                'title' => $log->typeLabel(),
                'summary' => $log->body,
                'timestamp' => $log->created_at,
                'meta_suffix' => $log->admin ? 'by '.$log->admin->name : null,
                'badge' => null,
                'error' => null,
                'action_label' => null,
            ]);
        }

        if ($latestIntake) {
            $activities->push([
                'category' => 'system',
                'title' => 'Intake submitted',
                'summary' => 'brand assets, bio, licensing',
                'timestamp' => $latestIntake->submitted_at,
                'meta_suffix' => null,
                'badge' => null,
                'error' => null,
                'action_label' => 'click to review',
            ]);
        }

        foreach ($subscriptions as $subscription) {
            if ($subscription->status === 'active') {
                $activities->push([
                    'category' => 'billing',
                    'title' => 'Subscription activated',
                    'summary' => 'Subscription #'.$subscription->id,
                    'timestamp' => $subscription->created_at,
                    'meta_suffix' => 'automatic',
                    'badge' => null,
                    'error' => null,
                    'action_label' => null,
                ]);
            }

            if (in_array($subscription->status, ['expired', 'canceled'], true)) {
                $activities->push([
                    'category' => 'billing',
                    'title' => 'Subscription #'.$subscription->id.' auto-expired',
                    'summary' => 'grace period started',
                    'timestamp' => $subscription->updated_at ?? $subscription->end_date ?? $subscription->created_at,
                    'meta_suffix' => 'automatic',
                    'badge' => null,
                    'error' => null,
                    'action_label' => null,
                ]);
            }
        }

        foreach ($payments as $payment) {
            $planLabel = match ($payment->billing_interval) {
                'year' => 'yearly',
                'month' => 'monthly',
                default => null,
            };

            $summaryParts = [$payment->formattedAmount()];

            if ($payment->zipcode) {
                $zipSummary = 'ZIP '.$payment->zipcode->code;

                if ($planLabel) {
                    $zipSummary .= ' '.$planLabel;
                }

                $summaryParts[] = $zipSummary;
            }

            $activities->push([
                'category' => 'billing',
                'title' => $payment->status === 'paid' ? 'Payment received' : 'Payment update',
                'summary' => implode(', ', $summaryParts),
                'timestamp' => $payment->paid_at ?? $payment->created_at,
                'meta_suffix' => $payment->status === 'paid' ? 'automatic' : null,
                'badge' => null,
                'error' => $payment->status === 'failed' ? 'renewal failed' : null,
                'action_label' => null,
            ]);
        }

        $activities->push([
            'category' => 'system',
            'title' => 'Account created',
            'summary' => $user->name.' joined the platform',
            'timestamp' => $user->created_at,
            'meta_suffix' => 'automatic',
            'badge' => null,
            'error' => null,
            'action_label' => null,
        ]);

        return $activities
            ->filter(fn (array $activity): bool => $activity['timestamp'] !== null)
            ->sortByDesc('timestamp')
            ->values();
    }
}
