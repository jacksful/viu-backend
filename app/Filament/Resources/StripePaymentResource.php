<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StripePaymentResource\Pages;
use App\Models\StripePayment;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StripePaymentResource extends Resource
{
    protected static ?string $model = StripePayment::class;

    protected static ?string $navigationLabel = 'Subscriptions';

    protected static ?string $modelLabel = 'Subscription';

    protected static ?string $pluralModelLabel = 'Subscriptions';

    protected static ?int $navigationSort = 4;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Payment Details')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('customer_name')
                            ->label('Customer')
                            ->content(fn (?StripePayment $record): string => $record?->customer_name ?: '—'),

                        \Filament\Forms\Components\Placeholder::make('customer_email')
                            ->label('Email')
                            ->content(fn (?StripePayment $record): string => $record?->customer_email ?: '—'),

                        \Filament\Forms\Components\Placeholder::make('amount')
                            ->label('Amount')
                            ->content(fn (?StripePayment $record): string => $record?->formattedAmount() ?: '—'),

                        \Filament\Forms\Components\Placeholder::make('status')
                            ->label('Status')
                            ->content(fn (?StripePayment $record): string => $record?->status ?: '—'),

                        \Filament\Forms\Components\Placeholder::make('stripe_subscription_id')
                            ->label('Stripe subscription ID')
                            ->content(fn (?StripePayment $record): string => $record?->stripe_subscription_id ?: '—')
                            ->copyable(),

                        \Filament\Forms\Components\Placeholder::make('stripe_invoice_id')
                            ->label('Stripe invoice ID')
                            ->content(fn (?StripePayment $record): string => $record?->stripe_invoice_id ?: '—')
                            ->copyable(),

                        \Filament\Forms\Components\Placeholder::make('paid_at')
                            ->label('Paid at')
                            ->content(fn (?StripePayment $record): string => $record?->paid_at?->format('M j, Y g:i A') ?: '—'),

                        \Filament\Forms\Components\Placeholder::make('subscription_start')
                            ->label('Subscription start')
                            ->content(fn (?StripePayment $record): string => $record?->subscription?->formattedStartDate() ?: '—'),

                        \Filament\Forms\Components\Placeholder::make('subscription_end')
                            ->label('Subscription end')
                            ->content(fn (?StripePayment $record): string => $record?->subscription?->formattedEndDate() ?: '—'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('zipcode.code')
                    ->label('ZIP')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (StripePayment $record): string => $record->formattedAmount())
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_interval')
                    ->label('Plan')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'year' => 'Yearly',
                        'month' => 'Monthly',
                        default => '—',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'checkout_pending' => 'warning',
                        'cancelled_unavailable' => 'gray',
                        default => 'info',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscription.start_date')
                    ->label('Sub. start')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subscription.end_date')
                    ->label('Sub. end')
                    ->date('M j, Y')
                    ->placeholder('Ongoing')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stripe_subscription_id')
                    ->label('Subscription')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),

                Tables\Columns\TextColumn::make('billing_reason')
                    ->label('Billing reason')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'checkout_pending' => 'Checkout pending',
                        'cancelled_unavailable' => 'Cancelled (unavailable)',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make(),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripePayments::route('/'),
            'view' => Pages\ViewStripePayment::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
