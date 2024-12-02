<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Components\Tab;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')->tabs([
                    Tabs\Tab::make('Product Info')->schema([
                        TextInput::make('name')->label('Product Name')
                            ->required()
                            ->live(onBlur: true)
                            ->unique(ignoreRecord: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                if ($operation !== 'create') return;

                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        FileUpload::make('image')->label('Product Image')
                            ->preserveFilenames()
                            ->image()
                            ->imageEditor(),

                        RichEditor::make('description')->label('Description')->columnSpan('full')->fileAttachmentsDirectory('attachments')
                            ->fileAttachmentsVisibility('private'),
                    ]),

                    Tabs\Tab::make('Pricing & Inventory')->schema([


                        TextInput::make('sku')->label('SKU (Stock Keeping Unit)')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        TextInput::make('price')->label('Price')
                            ->numeric()
                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/']),
                        TextInput::make('quantity')->label('Quantity')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Select::make('type')->label("Type")->options([
                            'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                            'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                        ])->required()

                    ]),

                    Tabs\Tab::make('Status')->schema([



                        Toggle::make('is_visible')->label('Visibility')
                            ->helperText('Whether the product is visible to customers')
                            ->default(true),

                        Toggle::make('is_featured')->label('Featured')
                            ->helperText('Whether the product is featured on the homepage'),

                        DatePicker::make('published_at')->label('Published At')
                            ->default(now()),


                        Select::make('categories')->label('Categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->required()

                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return ProductResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\DissociateAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\DissociateBulkAction::make(),
                ]),
            ]);
    }
}
