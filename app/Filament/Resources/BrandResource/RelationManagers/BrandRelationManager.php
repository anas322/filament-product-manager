<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrandRelationManager extends RelationManager
{
    protected static string $relationship = 'brand';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')

                    ->required()
                    ->maxLength(255)->default(function (RelationManager $livewire): string {
                        return $livewire->getOwnerRecord()->name;
                    }),

                Forms\Components\TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique('brands', 'slug', ignoreRecord: true),

                Forms\Components\TextInput::make('url')
                    ->url()
                    ->nullable(),

                Forms\Components\TextInput::make('primary_hex')
                    ->required()
                    ->maxLength(7),

                Forms\Components\Toggle::make('is_visible')
                    ->default(false),

                Forms\Components\MarkdownEditor::make('description')
                    ->nullable(),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('url'),
                Tables\Columns\TextColumn::make('primary_hex'),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
                Tables\Columns\TextColumn::make('description'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
