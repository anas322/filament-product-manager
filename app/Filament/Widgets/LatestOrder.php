<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class LatestOrder extends BaseWidget
{

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;


    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge(true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date(),
            ]);
    }
}
