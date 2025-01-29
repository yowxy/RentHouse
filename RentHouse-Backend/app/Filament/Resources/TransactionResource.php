<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function canCreate():bool {
        return false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user')
                    ->relationship('user','name')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('listing_id')
                    ->relationship('listing','title')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->required(),
                Forms\Components\TextInput::make('price_per_day')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_days')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable()->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('listing.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state): string => match($state){
                    'waiting' => 'warning',
                    'approved' => 'success',
                    'canceled' => 'danger',
                }),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'approved' => 'Approved',
                        'canceled' => 'Canceled',
                    ])
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                ActionsAction::make('approved')
                    ->button()
                    ->color('success')
                    ->action(function(Transaction $transaction){
                        Transaction::find($transaction->id)->update([
                            'status' => 'approved'
                        ]);
                        Notification::make()->success()->title('Transaction Approved')->body('transaction has been successfully')->icon('heroicon-o-check')->send();
                    })
                    ->hidden(fn(Transaction $transaction)=>$transaction->status !== 'waiting')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
