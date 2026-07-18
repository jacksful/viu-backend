<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckoutHoldResource\Pages;
use App\Models\CheckoutHold;
use App\Services\CheckoutHoldService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CheckoutHoldResource extends Resource
{
    protected static ?string $model = CheckoutHold::class;

    protected static ?string $navigationLabel = 'Checkouts & Holds';

    protected static ?string $modelLabel = 'Checkout Hold';

    protected static ?string $pluralModelLabel = 'Checkouts & Holds';

    protected static ?string $slug = 'checkout-holds';

    protected static ?int $navigationSort = 3;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-lock-closed';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CheckoutHold::query()->active()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['stripePayment', 'zipcode']))
            ->columns([
                Tables\Columns\TextColumn::make('agent')
                    ->label('Agent')
                    ->state(function (CheckoutHold $record): string {
                        $payment = $record->stripePayment;
                        $lines = array_filter([
                            $payment?->customer_name,
                            $payment?->customer_email,
                            $record->stripePayment?->metadata['customer_phone'] ?? null,
                        ]);

                        return implode("\n", $lines) ?: '—';
                    })
                    ->html()
                    ->formatStateUsing(fn (string $state): string => nl2br(e($state)))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('stripePayment', function (Builder $paymentQuery) use ($search): void {
                            $paymentQuery
                                ->where('customer_name', 'like', "%{$search}%")
                                ->orWhere('customer_email', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('zipcode.code')
                    ->label('ZIP')
                    ->state(function (CheckoutHold $record): string {
                        $zip = $record->zipcode?->code ?? '—';

                        return $zip."\n".$record->formattedPlanLabel();
                    })
                    ->html()
                    ->formatStateUsing(fn (string $state): string => nl2br(e($state)))
                    ->sortable(),

                Tables\Columns\TextColumn::make('checkout_started_at')
                    ->label('Checkout started')
                    ->dateTime('M j, Y • g:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hold_expires_at')
                    ->label('Hold expires')
                    ->state(function (CheckoutHold $record): string {
                        if (! $record->hold_expires_at) {
                            return '—';
                        }

                        $relative = $record->isActive()
                            ? $record->hold_expires_at->diffForHumans(['parts' => 2, 'short' => true])
                            : ucfirst((string) $record->status);

                        return $relative."\n".$record->hold_expires_at->format('M j, Y • g:i A');
                    })
                    ->html()
                    ->formatStateUsing(fn (string $state): string => nl2br(e($state)))
                    ->icon(fn (CheckoutHold $record): ?string => $record->isExpiringSoon() ? 'heroicon-o-exclamation-triangle' : null)
                    ->iconColor('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('recovery_email_status')
                    ->label('Recovery email')
                    ->badge()
                    ->formatStateUsing(function (?string $state, CheckoutHold $record): string {
                        if ($state === CheckoutHold::RECOVERY_STATUS_SENT) {
                            return 'Sent';
                        }

                        if ($state === CheckoutHold::RECOVERY_STATUS_FAILED) {
                            return 'Failed';
                        }

                        return $record->recovery_email_sent_at ? 'Sent' : 'Pending';
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        CheckoutHold::RECOVERY_STATUS_SENT => 'success',
                        CheckoutHold::RECOVERY_STATUS_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->description(fn (CheckoutHold $record): ?string => $record->recovery_email_error),
            ])
            ->filters([
                Tables\Filters\Filter::make('zip_code')
                    ->label('ZIP code')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('zip_code')
                            ->label('ZIP code')
                            ->maxLength(5),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['zip_code'] ?? null),
                            fn (Builder $query): Builder => $query->whereHas(
                                'zipcode',
                                fn (Builder $zipQuery) => $zipQuery->where('code', $data['zip_code']),
                            ),
                        );
                    }),

                Tables\Filters\SelectFilter::make('recovery_email_status')
                    ->label('Recovery email')
                    ->options([
                        CheckoutHold::RECOVERY_STATUS_SENT => 'Sent',
                        CheckoutHold::RECOVERY_STATUS_FAILED => 'Failed',
                    ]),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('resendRecoveryEmail')
                        ->label('Resend recovery email')
                        ->icon('heroicon-o-envelope')
                        ->action(function (CheckoutHold $record, CheckoutHoldService $holdService): void {
                            try {
                                $holdService->resendRecoveryEmail($record);

                                Notification::make()
                                    ->success()
                                    ->title('Recovery email queued')
                                    ->body('The checkout recovery email will be sent shortly.')
                                    ->send();
                            } catch (\Throwable $exception) {
                                Notification::make()
                                    ->danger()
                                    ->title('Unable to send recovery email')
                                    ->body($exception->getMessage())
                                    ->send();
                            }
                        })
                        ->visible(fn (CheckoutHold $record): bool => $record->isActive()),

                    Actions\Action::make('extendHold')
                        ->label('Extend hold by 24h')
                        ->icon('heroicon-o-calendar')
                        ->requiresConfirmation()
                        ->modalHeading('Extend hold by 24 hours?')
                        ->modalDescription(fn (CheckoutHold $record): string => "Extend the hold for {$record->stripePayment?->customer_name} until ".$record->hold_expires_at?->addHours(CheckoutHold::EXTEND_HOURS)->format('M j, Y g:i A').'.')
                        ->action(function (CheckoutHold $record, CheckoutHoldService $holdService): void {
                            try {
                                $holdService->extend($record);

                                Notification::make()
                                    ->success()
                                    ->title('Hold extended')
                                    ->body('The ZIP hold was extended by 24 hours.')
                                    ->send();
                            } catch (\Throwable $exception) {
                                Notification::make()
                                    ->danger()
                                    ->title('Unable to extend hold')
                                    ->body($exception->getMessage())
                                    ->send();
                            }
                        })
                        ->visible(fn (CheckoutHold $record): bool => $record->isActive()),

                    Actions\Action::make('sendRepaymentLink')
                        ->label('Send checkout link')
                        ->icon('heroicon-o-link')
                        ->requiresConfirmation()
                        ->modalHeading('Send a new Stripe checkout link?')
                        ->modalDescription('Creates a fresh checkout session and emails the customer a repayment link.')
                        ->action(function (CheckoutHold $record, CheckoutHoldService $holdService): void {
                            try {
                                $holdService->createRepaymentSession($record);
                                $holdService->resendRecoveryEmail($record->fresh());

                                Notification::make()
                                    ->success()
                                    ->title('Checkout link sent')
                                    ->body('A new Stripe checkout link was created and emailed to the customer.')
                                    ->send();
                            } catch (\Throwable $exception) {
                                Notification::make()
                                    ->danger()
                                    ->title('Unable to send checkout link')
                                    ->body($exception->getMessage())
                                    ->send();
                            }
                        })
                        ->visible(fn (CheckoutHold $record): bool => $record->isActive()),

                    Actions\Action::make('viewInStripe')
                        ->label('View in Stripe')
                        ->icon('heroicon-o-eye')
                        ->url(fn (CheckoutHold $record): ?string => $record->stripeDashboardUrl())
                        ->openUrlInNewTab()
                        ->visible(fn (CheckoutHold $record): bool => filled($record->stripeDashboardUrl())),

                    Actions\Action::make('releaseZip')
                        ->label('Release ZIP now')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Release this ZIP from hold?')
                        ->modalDescription('The ZIP will become available immediately and waitlisted users will be notified by email.')
                        ->action(function (CheckoutHold $record, CheckoutHoldService $holdService): void {
                            try {
                                $holdService->release($record);

                                Notification::make()
                                    ->success()
                                    ->title('ZIP released')
                                    ->body('The hold was released and waitlist notifications were queued.')
                                    ->send();
                            } catch (\Throwable $exception) {
                                Notification::make()
                                    ->danger()
                                    ->title('Unable to release ZIP')
                                    ->body($exception->getMessage())
                                    ->send();
                            }
                        })
                        ->visible(fn (CheckoutHold $record): bool => $record->isActive()),
                ]),
            ])
            ->defaultSort('checkout_started_at', 'desc')
            ->emptyStateHeading('No checkout holds yet')
            ->emptyStateDescription('When a customer cancels checkout or payment fails, their hold will appear here.')
            ->emptyStateIcon('heroicon-o-lock-closed')
            ->paginated([10, 25, 50])
            ->poll('60s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCheckoutHolds::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
