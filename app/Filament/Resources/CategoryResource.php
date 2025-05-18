<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Models\Language;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{Section, Select, Toggle, Repeater, TextInput, Textarea, Hidden, Grid, Placeholder};
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'Category';
    protected static ?string $pluralModelLabel = 'Categories';

    public static function form(Form $form): Form
    {
        $languages = Language::all();

        return $form->schema([
            Section::make('Main Info')->schema([
                Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(function () {
                        return Category::with('descriptions')->get()->mapWithKeys(function ($category) {
                            $name = $category->descriptions->first()?->name ?? 'Unnamed';
                            return [$category->id => $name];
                        });
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Toggle::make('status')->label('Active')->default(true),
            ]),

            Section::make('Descriptions')
                ->schema([
                    Repeater::make('descriptions')
                        ->relationship()
                        ->hiddenLabel()
                        ->schema([
                            Grid::make(1)->schema([
                                Hidden::make('language_id'),

                                Placeholder::make('language_display')
                                    ->label('Language')
                                    ->content(function ($get) {
                                        $lang = Language::find($get('language_id'));
                                        return $lang?->name ?? 'Unknown';
                                    }),

                                TextInput::make('name')
                                    ->label('Name')
                                    ->required(),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required(),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3),
                            ]),
                        ])
                        ->default(
                            $languages->map(fn($lang) => [
                                'language_id' => $lang->id,
                            ])->toArray()
                        )
                        ->disableItemDeletion()
                        ->disableItemCreation()
                        ->columns(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),

                TextColumn::make('descriptions.name')
                    ->label('Name')
                    ->searchable()
                    ->formatStateUsing(function (Category $record) {
                        $languageId = Language::where('code', app()->getLocale())->value('id');
                        return $record->descriptions()
                            ->where('language_id', $languageId)
                            ->first()?->name ?? '-';
                    }),
                    
                TextColumn::make('parent.descriptions.name')
                    ->label('Parent Name')
                    ->formatStateUsing(function (Category $record) {
                        $languageId = Language::where('code', app()->getLocale())->value('id');
                        return $record->parent?->descriptions()
                            ->where('language_id', $languageId)
                            ->first()?->name ?? '-';
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
