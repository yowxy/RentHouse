<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Filament\Resources\ListingResource\RelationManagers;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Components\FileUpload;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->live(debounce: 250)
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->required(),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sqft')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('wifi_speed')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('max_person')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('price_per_day')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Checkbox::make('full_support_available')
                    ->default(0),
                Forms\Components\Checkbox::make('gym_area_available')
                    ->default(0),
                Forms\Components\Checkbox::make('mini_cafe_available')
                    ->default(0),
                Forms\Components\Checkbox::make('cinema_available')
                    ->default(0),
                FileUpload::make(name: 'attachments')
                    ->directory('listings')
                    ->openable()
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sqft')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_day')
                    ->money('USD'),
                 Tables\Columns\ImageColumn::make('attachments')
                    ->rounded(),


            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
