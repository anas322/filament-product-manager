<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\OrderStatusEnum;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Shop';


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()->where('status', OrderStatusEnum::PENDING)->count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([

                    Step::make('Order Information')->schema([

                        TextInput::make('number')
                            ->disabled()
                            ->dehydrated()
                            ->default('OR-' . date('Ymd') . '-' . rand(1000, 9999)),

                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required(),

                        Select::make('status')->options([

                            'pending' => OrderStatusEnum::PENDING->value,
                            'processing' => OrderStatusEnum::PROCESSING->value,
                            'completed' => OrderStatusEnum::COMPLETED->value,
                            'declined' => OrderStatusEnum::DECLINED->value,

                        ])->required(),

                        TextInput::make('shipping_price')
                            ->numeric()
                            ->live()
                            ->dehydrated()
                            ->required(),



                        MarkdownEditor::make('notes')->columnSpanFull(),

                    ])->columns(2),


                    Step::make('Order Items')->schema([

                        Repeater::make('items')
                            ->relationship()
                            ->schema([

                                Select::make('product_id')
                                    ->required()
                                    ->options(
                                        Product::query()->pluck('name', 'id')
                                    )
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $price = Product::find($state)?->price ?? 0;

                                        $set('unit_price',  $price);
                                    }),



                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->dehydrated()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->default(1),


                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxValue(1000000)
                                    ->minValue(1),

                                Section::make('Price Calculation')
                                    ->schema([

                                        Placeholder::make('sub_total')
                                            ->label('Sub Total')
                                            ->content(function ($get) {
                                                return (int)$get('quantity') * (int)$get('unit_price');
                                            }),

                                        Placeholder::make('total_price')
                                            ->label('Total Price')
                                            ->content(function (Get $get) {
                                                return (int)$get('unit_price') * (int)$get('quantity') + (int) $get('../../shipping_price');
                                            }),

                                    ])->inlineLabel()
                            ])->columns(2)
                    ])
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
