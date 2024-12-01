<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\OrderStatusEnum;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
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
use Filament\Forms\Components\Repeater;

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
                        TextInput::make('number')->disabled()->dehydrated()->default('OR-' . date('Ymd') . '-' . rand(1000, 9999)),
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required(),
                        Select::make('status')->options([
                            'pending' => OrderStatusEnum::PENDING->value,
                            'processing' => OrderStatusEnum::PROCESSING->value,
                            'completed' => OrderStatusEnum::COMPLETED->value,
                            'declined' => OrderStatusEnum::DECLINED->value,
                        ])->required(),

                        MarkdownEditor::make('notes')->columnSpanFull(),
                    ])->columns(2),


                    Step::make('Order Items')->schema([

                        Repeater::make('items')->relationship()->schema([
                            Select::make('product_id')->options(Product::query()->pluck('name', 'id'))->required(),

                            TextInput::make('quantity')->numeric()->required()
                                ->maxValue(100)
                                ->minValue(1)
                                ->default(1),

                            TextInput::make('unit_price')->numeric()->required()->label('Unit Price')->disabled()->dehydrated()
                                ->maxValue(1000000)
                                ->minValue(1),
                        ])->columns(3)
                    ])
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
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
                Tables\Columns\TextColumn::make('total_price')
                    ->searchable()
                    ->sortable()
                    ->summarize(
                        [
                            Sum::make()
                                ->money()
                        ]
                    ),
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
